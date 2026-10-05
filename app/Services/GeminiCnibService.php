<?php

namespace App\Services;

use App\Exceptions\ApiException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiCnibService
{
    private string $apiKey;
    private string $model;
    private string $endpoint;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-3.8-flash');
        $this->endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";
    }

    public function scanCnib(UploadedFile $recto, ?UploadedFile $verso = null): array
    {
        if (empty($this->apiKey)) {
            throw new ApiException('Configuration Gemini API manquante.', 500);
        }

        try {
            $rectoData = base64_encode($recto->get());
            $rectoMimeType = $recto->getMimeType();

            $parts = [
                ['text' => $this->buildPrompt()],
                [
                    'inline_data' => [
                        'mime_type' => $rectoMimeType,
                        'data' => $rectoData,
                    ],
                ],
            ];

            if ($verso !== null) {
                $versoData = base64_encode($verso->get());
                $versoMimeType = $verso->getMimeType();
                $parts[] = [
                    'inline_data' => [
                        'mime_type' => $versoMimeType,
                        'data' => $versoData,
                    ],
                ];
            }

            $response = Http::timeout(30)->post($this->endpoint.'?key='.$this->apiKey, [
                'contents' => [
                    [
                        'parts' => $parts,
                    ],
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json',
                    'response_schema' => $this->buildJsonSchema(),
                    'temperature' => 0.1,
                ],
            ]);

            if (! $response->successful()) {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new ApiException('Erreur lors de la communication avec Gemini API.', 503);
            }

            $data = $response->json();

            if (! isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                throw new ApiException('Réponse Gemini invalide.', 502);
            }

            $jsonText = $data['candidates'][0]['content']['parts'][0]['text'];
            $extractedData = json_decode($jsonText, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ApiException('Impossible de parser la réponse JSON.', 502);
            }

            // Log de la réponse JSON brute pour diagnostic
            Log::info('Gemini raw response', [
                'raw_json' => $extractedData,
            ]);

            return $this->normalizeResponse($extractedData);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Gemini connection error', ['message' => $e->getMessage()]);
            throw new ApiException('Erreur de connexion à Gemini API.', 503);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Gemini request error', ['message' => $e->getMessage()]);
            throw new ApiException('Erreur lors de la requête Gemini API.', 503);
        } catch (\Exception $e) {
            Log::error('Gemini unexpected error', ['message' => $e->getMessage()]);
            throw new ApiException('Erreur inattendue lors du traitement.', 500);
        }
    }

    private function buildPrompt(): string
    {
        return <<<'PROMPT'
Tu es spécialisé dans l'extraction des données des documents d'identité burkinabè.
Analyse les images fournies (recto et/ou verso) et retourne uniquement ce JSON :
{
"nom": null,
"prenoms": null,
"date_naissance": null,
"nationalite": null,
"type_piece": null,
"numero_piece": null,
"date_delivrance_piece": null,
"date_expiration_piece": null
}
Règles :
- Identifie `CNIB` ou `CIB-AES` uniquement si le document le permet explicitement.
- `type_piece` = `"CNIB"` ou `"CIB-AES"`, sinon `null`.
- `numero_piece` correspond au numéro de référence de la pièce, généralement le numéro commençant par `B`. Pour la CIB-AES, il peut se trouver au verso.
- Ne confonds pas le numéro de référence avec un numéro de série.
- Recopie le numéro exactement ; s'il n'est pas visible ou identifiable sur les images fournies, retourne `null`.
- Si une information est absente ou illisible, retourne `null`.
- Ne devine, ne complète et ne déduis aucune information.
- Conserve l'orthographe visible des noms et prénoms.
- Retourne les dates au format `YYYY-MM-DD`.
- Ne calcule jamais une date absente des images.
- Retourne uniquement le JSON, sans explication ni Markdown.
PROMPT;
    }

    private function buildJsonSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'nom' => ['type' => 'string', 'nullable' => true],
                'prenoms' => ['type' => 'string', 'nullable' => true],
                'date_naissance' => ['type' => 'string', 'nullable' => true],
                'nationalite' => ['type' => 'string', 'nullable' => true],
                'type_piece' => ['type' => 'string', 'nullable' => true],
                'numero_piece' => ['type' => 'string', 'nullable' => true],
                'date_delivrance_piece' => ['type' => 'string', 'nullable' => true],
                'date_expiration_piece' => ['type' => 'string', 'nullable' => true],
            ],
            'required' => [],
        ];
    }

    private function normalizeResponse(array $data): array
    {
        return [
            'nom' => $this->nullIfEmpty($data['nom'] ?? null),
            'prenoms' => $this->nullIfEmpty($data['prenoms'] ?? null),
            'date_naissance' => $this->validateDate($data['date_naissance'] ?? null),
            'nationalite' => $this->nullIfEmpty($data['nationalite'] ?? null),
            'type_piece' => $this->nullIfEmpty($data['type_piece'] ?? null),
            'numero_piece' => $this->nullIfEmpty($data['numero_piece'] ?? null),
            'date_delivrance_piece' => $this->validateDate($data['date_delivrance_piece'] ?? null),
            'date_expiration_piece' => $this->validateDate($data['date_expiration_piece'] ?? null),
        ];
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_string($value) ? trim($value) : null;
    }

    private function validateDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        return $value;
    }
}
