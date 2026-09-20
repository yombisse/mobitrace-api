<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MobiTrace API - Documentation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.2em;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section h2 {
            color: #667eea;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 1.8em;
        }
        
        .section h3 {
            color: #764ba2;
            margin: 20px 0 10px 0;
            font-size: 1.3em;
        }
        
        .endpoint {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 15px 0;
            border-radius: 5px;
        }
        
        .method {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 0.9em;
            margin-right: 10px;
        }
        
        .method.get { background: #4CAF50; color: white; }
        .method.post { background: #2196F3; color: white; }
        .method.patch { background: #FF9800; color: white; }
        .method.delete { background: #f44336; color: white; }
        
        .endpoint-url {
            font-family: 'Courier New', monospace;
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            display: inline-block;
            margin: 10px 0;
        }
        
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
        
        pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 20px;
            border-radius: 5px;
            overflow-x: auto;
            margin: 15px 0;
        }
        
        pre code {
            background: none;
            padding: 0;
            color: inherit;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        
        .table th {
            background: #667eea;
            color: white;
        }
        
        .table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .badge.required { background: #f44336; color: white; }
        .badge.optional { background: #4CAF50; color: white; }
        
        .footer {
            background: #2d2d2d;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .header h1 { font-size: 1.8em; }
            .content { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>MobiTrace API</h1>
            <p>API RESTful pour la gestion des transactions de transfert d'argent</p>
            <p style="margin-top: 10px; font-size: 0.9em;">Version 1.2 | 2026-09-18</p>
        </div>
        
        <div class="content">
            <div class="section">
                <h2>Vue d'ensemble</h2>
                <p>MobiTrace est une API RESTful pour la gestion des transactions de transfert d'argent. Cette documentation fournit un contrat complet pour l'intégration avec l'application mobile frontend.</p>
                
                <h3>Authentification</h3>
                <p>L'API utilise Laravel Sanctum pour l'authentification via des tokens Bearer.</p>
                <pre><code>Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json</code></pre>
                
                <h3>Rate Limiting</h3>
                <ul>
                    <li>Endpoints publics (auth): 60 requêtes/minute</li>
                    <li>Endpoints protégés (API): 120 requêtes/minute</li>
                    <li>Reset password: 5 requêtes/heure</li>
                </ul>
            </div>
            
            <div class="section">
                <h2>1. Authentication</h2>
                
                <div class="endpoint">
                    <h3>1.1 Register</h3>
                    <p>Créer un nouveau compte utilisateur.</p>
                    <br>
                    <span class="method post">POST</span>
                    <code class="endpoint-url">/auth/register</code>
                    <p><span class="badge required">Non requis</span> Auth</p>
                    <p><strong>Request Body:</strong></p>
                    <pre><code>{
  "name": "string (required, max:255)",
  "telephone": "string (required, max:20, unique)",
  "email": "string (optional, email, max:255, unique)",
  "password": "string (required, min:8)",
  "password_confirmation": "string (required)"
}</code></pre>
                </div>
                
                <div class="endpoint">
                    <h3>1.2 Login</h3>
                    <p>Authentifier un utilisateur existant.</p>
                    <br>
                    <span class="method post">POST</span>
                    <code class="endpoint-url">/auth/login</code>
                    <p><span class="badge required">Non requis</span> Auth</p>
                    <p><strong>Request Body:</strong></p>
                    <pre><code>{
  "telephone": "string (required, max:20)",
  "password": "string (required)"
}</code></pre>
                </div>
                
                <div class="endpoint">
                    <h3>1.3 Logout</h3>
                    <p>Déconnecter l'utilisateur actuel.</p>
                    <br>
                    <span class="method post">POST</span>
                    <code class="endpoint-url">/auth/logout</code>
                    <p><span class="badge required">Requis</span> Auth (Bearer token)</p>
                </div>
                
                <div class="endpoint">
                    <h3>1.4 Get Current User</h3>
                    <p>Récupérer les informations de l'utilisateur authentifié.</p>
                    <br>
                    <span class="method get">GET</span>
                    <code class="endpoint-url">/auth/me</code>
                    <p><span class="badge required">Requis</span> Auth (Bearer token)</p>
                </div>
            </div>
            
            <div class="section">
                <h2>2. Clients</h2>
                
                <div class="endpoint">
                    <h3>2.1 List Clients</h3>
                    <p>Lister tous les clients de l'utilisateur avec pagination.</p>
                    <br>
                    <span class="method get">GET</span>
                    <code class="endpoint-url">/clients?page=1&per_page=20</code>
                    <p><span class="badge required">Requis</span> Auth (Bearer token)</p>
                </div>
                
                <div class="endpoint">
                    <h3>2.2 Lookup Client</h3>
                    <p>Rechercher un client par numéro de téléphone.</p>
                    <br>
                    <span class="method get">GET</span>
                    <code class="endpoint-url">/clients/lookup?telephone=1234567890</code>
                    <p><span class="badge required">Requis</span> Auth (Bearer token)</p>
                </div>
                
                <div class="endpoint">
                    <h3>2.3 Get Client Details</h3>
                    <p>Récupérer les détails d'un client spécifique.</p>
                    <br>
                    <span class="method get">GET</span>
                    <code class="endpoint-url">/clients/{client}</code>
                    <p><span class="badge required">Requis</span> Auth (Bearer token)</p>
                </div>
                
                <div class="endpoint">
                    <h3>2.4 Update Client</h3>
                    <p>Mettre à jour les informations d'un client.</p>
                    <br>
                    <span class="method patch">PATCH</span>
                    <code class="endpoint-url">/clients/{client}</code>
                    <p><span class="badge required">Requis</span> Auth (Bearer token)</p>
                </div>
            </div>
            
            <div class="section">
                <h2>3. Transactions</h2>
                
                <div class="endpoint">
                    <h3>3.1 Create Transaction</h3>
                    <p>Créer une nouvelle transaction (dépôt ou retrait).</p>
                    <br>
                    <span class="method post">POST</span>
                    <code class="endpoint-url">/transactions</code>
                    <p><span class="badge required">Requis</span> Auth (Bearer token)</p>
                    <p><strong>Request Body:</strong></p>
                    <pre><code>{
  "telephone": "string (required, max:20)",
  "nom": "string (optional, nullable, max:100)",
  "prenoms": "string (optional, nullable, max:150)",
  "reseau_id": "uuid (required, exists:reseaux)",
  "type_operation": "depot|retrait (required)",
  "montant": "decimal (required, gt:0)",
  "client_confirme": "boolean (required, accepted)"
}</code></pre>
                </div>
                
                <div class="endpoint">
                    <h3>3.2 List Transactions</h3>
                    <p>Lister les transactions avec filtres et pagination.</p>
                    <br>
                    <span class="method get">GET</span>
                    <code class="endpoint-url">/transactions?periode=aujourdhui&sort_by=date&sort_order=desc</code>
                    <p><span class="badge required">Requis</span> Auth (Bearer token)</p>
                    <p><strong>Filtres disponibles:</strong></p>
                    <ul>
                        <li><code>periode</code>: aujourdhui, 7jours, cemois, personnalisee</li>
                        <li><code>date_debut</code>, <code>date_fin</code>: pour période personnalisée</li>
                        <li><code>reseau_id</code>, <code>reseau_code</code>: filtre par réseau</li>
                        <li><code>type_operation</code>: depot, retrait</li>
                        <li><code>statut</code>: ENREGISTREE, MODIFIEE, ANNULEE</li>
                        <li><code>montant_min</code>, <code>montant_max</code>: tranche de montant</li>
                        <li><code>sort_by</code>: date, amount, created_at, montant</li>
                        <li><code>sort_order</code>: desc, asc</li>
                    </ul>
                </div>
                
                <div class="endpoint">
                    <h3>3.3 Get Transaction Details</h3>
                    <p>Récupérer les détails d'une transaction spécifique.</p>
                    <br>
                    <span class="method get">GET</span>
                    <code class="endpoint-url">/transactions/{transaction}</code>
                    <p><span class="badge required">Requis</span> Auth (Bearer token)</p>
                </div>
                
                <div class="endpoint">
                    <h3>3.4 Update Transaction</h3>
                    <p>Modifier une transaction existante.</p>
                    <br>
                    <span class="method patch">PATCH</span>
                    <code class="endpoint-url">/transactions/{transaction}</code>
                    <p><span class="badge required">Requis</span> Auth (Bearer token)</p>
                </div>
                
                <div class="endpoint">
                    <h3>3.5 Cancel Transaction</h3>
                    <p>Annuler une transaction.</p>
                    <br>
                    <span class="method post">POST</span>
                    <code class="endpoint-url">/transactions/{transaction}/cancel</code>
                    <p><span class="badge required">Requis</span> Auth (Bearer token)</p>
                </div>
                
                <div class="endpoint">
                    <h3>3.6 Export Transactions (PDF)</h3>
                    <p>Exporter les transactions en PDF pour audit.</p>
                    <br>
                    <span class="method get">GET</span>
                    <code class="endpoint-url">/transactions/export?debut=2026-09-01&fin=2026-09-19</code>
                    <p><span class="badge required">Requis</span> Auth (Bearer token)</p>
                    <p><strong>Validation:</strong></p>
                    <ul>
                        <li>Les dates doivent être dans le passé ou aujourd'hui</li>
                        <li>La période ne peut pas dépasser 3 mois</li>
                        <li>Maximum 2000 transactions par export</li>
                    </ul>
                </div>
            </div>
            
            <div class="section">
                <h2>4. Networks (Réseaux)</h2>
                
                <div class="endpoint">
                    <h3>4.1 List Networks</h3>
                    <p>Lister tous les réseaux disponibles.</p>
                    <br>
                    <span class="method get">GET</span>
                    <code class="endpoint-url">/reseaux</code>
                    <p><span class="badge required">Requis</span> Auth (Bearer token)</p>
                </div>
            </div>
            
            <div class="section">
                <h2>Format de Réponse</h2>
                
                <h3>Success Response</h3>
                <pre><code>{
  "success": true,
  "message": "Message de succès",
  "data": { ... },
  "meta": { ... }
}</code></pre>
                
                <h3>Error Response</h3>
                <pre><code>{
  "success": false,
  "message": "Message d'erreur",
  "errors": { ... }
}</code></pre>
                
                <h3>HTTP Status Codes</h3>
                <table class="table">
                    <tr>
                        <th>Code</th>
                        <th>Description</th>
                    </tr>
                    <tr>
                        <td>200</td>
                        <td>OK</td>
                    </tr>
                    <tr>
                        <td>201</td>
                        <td>Created</td>
                    </tr>
                    <tr>
                        <td>401</td>
                        <td>Unauthorized (token manquant ou invalide)</td>
                    </tr>
                    <tr>
                        <td>403</td>
                        <td>Forbidden (action non autorisée)</td>
                    </tr>
                    <tr>
                        <td>404</td>
                        <td>Not Found (ressource introuvable)</td>
                    </tr>
                    <tr>
                        <td>422</td>
                        <td>Unprocessable Entity (erreur de validation)</td>
                    </tr>
                    <tr>
                        <td>500</td>
                        <td>Internal Server Error</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="footer">
            <p>© 2026 MobiTrace - API Documentation</p>
            <p style="font-size: 0.8em; margin-top: 10px;">Built with Laravel 13.x | PHP 8.3 | PostgreSQL</p>
        </div>
    </div>
</body>
</html>