# MobiTrace API Documentation

## Overview

MobiTrace est une API RESTful pour la gestion des transactions de transfert d'argent. Cette documentation fournit un contrat complet pour l'intégration avec l'application mobile frontend.

**Base URL:** `https://headscarf-spotless-onto.ngrok-free.dev/api/v1`

**Version:** 1.0

**Date:** 2026-09-14

## Authentication

L'API utilise Laravel Sanctum pour l'authentification via des tokens Bearer.

### Headers

```http
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Rate Limiting

- **Endpoints publics (auth):** 60 requêtes/minute
- **Endpoints protégés (API):** 120 requêtes/minute  
- **Reset password:** 5 requêtes/heure

## Response Format

Toutes les réponses suivent ce format standard :

### Success Response
```json
{
  "success": true,
  "message": "Message de succès",
  "data": { ... },
  "meta": { ... }  // Pour les réponses paginées
}
```

### Error Response
```json
{
  "success": false,
  "message": "Message d'erreur",
  "errors": { ... }  // Pour les erreurs de validation
}
```

### HTTP Status Codes

- `200` - OK
- `201` - Created
- `401` - Unauthorized (token manquant ou invalide)
- `403` - Forbidden (action non autorisée)
- `404` - Not Found (ressource introuvable)
- `422` - Unprocessable Entity (erreur de validation)
- `500` - Internal Server Error

---

## Endpoints

## 1. Authentication

### 1.1 Register
Créer un nouveau compte utilisateur.

**Endpoint:** `POST /auth/register`

**Auth:** Non requis

**Rate Limit:** 60/minute

**Request Body:**
```json
{
  "name": "string (required, max:255)",
  "telephone": "string (required, max:20, unique)",
  "email": "string (optional, email, max:255, unique)",
  "password": "string (required, min:8)",
  "password_confirmation": "string (required)"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Compte créé avec succès.",
  "data": {
    "token": "string",
    "user": {
      "id": "uuid",
      "name": "string",
      "telephone": "string",
      "email": "string|null",
      "code_agent": "string|null",
      "photo": "string|null",
      "localisation_point": "string|null"
    }
  }
}
```

### 1.2 Login
Authentifier un utilisateur existant.

**Endpoint:** `POST /auth/login`

**Auth:** Non requis

**Rate Limit:** 60/minute

**Request Body:**
```json
{
  "telephone": "string (required, max:20)",
  "password": "string (required)"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Connexion réussie.",
  "data": {
    "token": "string",
    "user": {
      "id": "uuid",
      "name": "string",
      "telephone": "string",
      "email": "string|null",
      "code_agent": "string|null",
      "photo": "string|null",
      "localisation_point": "string|null"
    }
  }
}
```

### 1.3 Logout
Déconnecter l'utilisateur actuel.

**Endpoint:** `POST /auth/logout`

**Auth:** Requis (Bearer token)

**Rate Limit:** 120/minute

**Request Body:** None

**Response (200):**
```json
{
  "success": true,
  "message": "Déconnexion réussie."
}
```

### 1.4 Get Current User
Récupérer les informations de l'utilisateur authentifié.

**Endpoint:** `GET /auth/me`

**Auth:** Requis (Bearer token)

**Rate Limit:** 120/minute

**Response (200):**
```json
{
  "id": "uuid",
  "name": "string",
  "telephone": "string",
  "email": "string|null",
  "code_agent": "string|null",
  "photo": "string|null",
  "localisation_point": "string|null"
}
```

### 1.5 Update Profile
Mettre à jour le profil de l'utilisateur.

**Endpoint:** `PATCH /auth/me`

**Auth:** Requis (Bearer token)

**Rate Limit:** 120/minute

**Request Body:**
```json
{
  "name": "string (optional, max:255)",
  "email": "string (optional, email, max:255, unique)",
  "photo": "string (optional, nullable, max:2048)",
  "localisation_point": "string (optional, nullable, max:255)"
}
```

**Response (200):**
```json
{
  "id": "uuid",
  "name": "string",
  "telephone": "string",
  "email": "string|null",
  "code_agent": "string|null",
  "photo": "string|null",
  "localisation_point": "string|null"
}
```

### 1.6 Change Password
Changer le mot de passe de l'utilisateur.

**Endpoint:** `POST /auth/change-password`

**Auth:** Requis (Bearer token)

**Rate Limit:** 120/minute

**Request Body:**
```json
{
  "current_password": "string (required)",
  "password": "string (required, min:8)",
  "password_confirmation": "string (required)"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Mot de passe modifié avec succès."
}
```

### 1.7 Forgot Password
Demander un code de réinitialisation de mot de passe.

**Endpoint:** `POST /auth/forgot-password`

**Auth:** Non requis

**Rate Limit:** 5/hour

**Request Body:**
```json
{
  "email": "string (required, email, max:255)"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Si un compte existe avec cet email, un code a été envoyé."
}
```

### 1.8 Reset Password
Réinitialiser le mot de passe avec un code.

**Endpoint:** `POST /auth/reset-password`

**Auth:** Non requis

**Rate Limit:** 60/minute

**Request Body:**
```json
{
  "email": "string (required, email, max:255)",
  "code": "string (required, digits:6)",
  "password": "string (required, min:8)",
  "password_confirmation": "string (required)"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Mot de passe réinitialisé avec succès."
}
```

---

## 2. Clients

### 2.1 List Clients
Lister tous les clients de l'utilisateur avec pagination.

**Endpoint:** `GET /clients`

**Auth:** Requis (Bearer token)

**Rate Limit:** 120/minute

**Query Parameters:**
- `page` (optional, integer, min:1) - Page number
- `per_page` (optional, integer, min:1, max:100) - Items per page

**Response (200):**
```json
{
  "success": true,
  "message": "Opération effectuée avec succès.",
  "data": [
    {
      "id": "uuid",
      "telephone": "string",
      "nom": "string|null",
      "prenoms": "string|null",
      "nombre_transactions": "integer",
      "derniere_transaction_at": "datetime|null"
    }
  ],
  "meta": {
    "current_page": "integer",
    "per_page": "integer",
    "total": "integer",
    "last_page": "integer"
  }
}
```

### 2.2 Lookup Client
Rechercher un client par numéro de téléphone.

**Endpoint:** `GET /clients/lookup`

**Auth:** Requis (Bearer token)

**Rate Limit:** 120/minute

**Query Parameters:**
- `telephone` (required, string, max:20) - Client phone number

**Response (200):**
```json
{
  "success": true,
  "data": {
    "found": "boolean",
    "client": {
      "id": "uuid",
      "telephone": "string",
      "nom": "string",
      "prenoms": "string"
    } | null
  }
}
```

### 2.3 Get Client Details
Récupérer les détails d'un client spécifique.

**Endpoint:** `GET /clients/{client}`

**Auth:** Requis (Bearer token)

**Rate Limit:** 120/minute

**URL Parameters:**
- `client` (required, uuid) - Client ID

**Response (200):**
```json
{
  "id": "uuid",
  "telephone": "string",
  "nom": "string|null",
  "prenoms": "string|null",
  "date_naissance": "date|null",
  "nationalite": "string|null",
  "type_piece": "string|null",
  "numero_piece": "string|null",
  "date_expiration_piece": "date|null",
  "created_at": "datetime",
  "statistiques": {
    // Statistiques du client
  }
}
```

### 2.4 Get Client Transactions
Lister les transactions d'un client.

**Endpoint:** `GET /clients/{client}/transactions`

**Auth:** Requis (Bearer token)

**Rate Limit:** 120/minute

**URL Parameters:**
- `client` (required, uuid) - Client ID

**Query Parameters:**
- `per_page` (optional, integer) - Items per page

**Response (200):**
```json
{
  "success": true,
  "message": "Opération effectuée avec succès.",
  "data": [
    {
      "id": "uuid",
      "type_operation": "depot|retrait",
      "montant": "decimal",
      "reference": "string|null",
      "solde_apres_operation": "decimal|null",
      "note": "string|null",
      "statut": "ENREGISTREE|MODIFIEE|ANNULEE",
      "version": "integer",
      "created_at": "datetime",
      "client": {
        "id": "uuid",
        "telephone": "string",
        "nom": "string",
        "prenoms": "string"
      },
      "reseau": {
        "id": "uuid",
        "nom": "string",
        "code": "string"
      }
    }
  ],
  "meta": {
    "current_page": "integer",
    "per_page": "integer",
    "total": "integer",
    "last_page": "integer"
  }
}
```

### 2.5 Update Client
Mettre à jour les informations d'un client.

**Endpoint:** `PATCH /clients/{client}`

**Auth:** Requis (Bearer token)

**Rate Limit:** 120/minute

**URL Parameters:**
- `client` (required, uuid) - Client ID

**Request Body:**
```json
{
  "nom": "string (optional, nullable, max:100)",
  "prenoms": "string (optional, nullable, max:150)",
  "date_naissance": "date (optional, nullable)",
  "nationalite": "string (optional, nullable, max:100)",
  "type_piece": "string (optional, nullable, max:50)",
  "numero_piece": "string (optional, nullable, max:50)",
  "date_expiration_piece": "date (optional, nullable)"
}
```

**Response (200):**
```json
{
  "id": "uuid",
  "telephone": "string",
  "nom": "string|null",
  "prenoms": "string|null"
}
```

---

## 3. Transactions

### 3.1 Create Transaction
Créer une nouvelle transaction (dépôt ou retrait).

**Endpoint:** `POST /transactions`

**Auth:** Requis (Bearer token)

**Rate Limit:** 120/minute

**Request Body:**
```json
{
  "telephone": "string (required, max:20)",
  "nom": "string (optional, nullable, max:100)",
  "prenoms": "string (optional, nullable, max:150)",
  "date_naissance": "date (optional, nullable)",
  "nationalite": "string (optional, nullable, max:100)",
  "type_piece": "string (optional, nullable, max:50)",
  "numero_piece": "string (optional, nullable, max:50)",
  "date_expiration_piece": "date (optional, nullable)",
  "reseau_id": "uuid (required, exists:reseaux)",
  "type_operation": "depot|retrait (required)",
  "montant": "decimal (required, gt:0)",
  "solde_apres_operation": "decimal (optional, nullable, gte:0)",
  "note": "string (optional, nullable)"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Transaction enregistrée avec succès.",
  "data": {
    "transaction": {
      "id": "uuid",
      "type_operation": "depot|retrait",
      "montant": "decimal",
      "reference": "string|null",
      "solde_apres_operation": "decimal|null",
      "note": "string|null",
      "statut": "ENREGISTREE",
      "version": "integer",
      "created_at": "datetime"
    },
    "client": {
      "id": "uuid",
      "telephone": "string",
      "nom": "string",
      "prenoms": "string"
    },
    "client_existant": "boolean"
  }
}
```

### 3.2 List Transactions
Lister les transactions avec filtres et pagination.

**Endpoint:** `GET /transactions`

**Auth:** Requis (Bearer token)

**Rate Limit:** 120/minute

**Query Parameters:**
- `telephone` (optional, string, max:20) - Filter by client phone
- `search` (optional, string, max:255) - Search in reference
- `reference` (optional, string, max:100) - Filter by reference
- `date_debut` (optional, date) - Start date filter
- `date_fin` (optional, date, after_or_equal:date_debut) - End date filter
- `reseau_id` (optional, uuid, exists:reseaux) - Filter by network
- `type_operation` (optional, depot|retrait) - Filter by operation type
- `statut` (optional, ENREGISTREE|MODIFIEE|ANNULEE) - Filter by status
- `page` (optional, integer, min:1) - Page number
- `per_page` (optional, integer, min:1, max:100) - Items per page

**Response (200):**
```json
{
  "success": true,
  "message": "Opération effectuée avec succès.",
  "data": [
    {
      "id": "uuid",
      "type_operation": "depot|retrait",
      "montant": "decimal",
      "reference": "string|null",
      "solde_apres_operation": "decimal|null",
      "note": "string|null",
      "statut": "ENREGISTREE|MODIFIEE|ANNULEE",
      "version": "integer",
      "created_at": "datetime",
      "client": {
        "id": "uuid",
        "telephone": "string",
        "nom": "string",
        "prenoms": "string"
      },
      "reseau": {
        "id": "uuid",
        "nom": "string",
        "code": "string"
      },
      "user": {
        "id": "uuid",
        "name": "string"
      }
    }
  ],
  "meta": {
    "current_page": "integer",
    "per_page": "integer",
    "total": "integer",
    "last_page": "integer"
  }
}
```

### 3.3 Get Transaction Details
Récupérer les détails d'une transaction spécifique.

**Endpoint:** `GET /transactions/{transaction}`

**Auth:** Requis (Bearer token)

**Rate Limit:** 120/minute

**URL Parameters:**
- `transaction` (required, uuid) - Transaction ID

**Response (200):**
```json
{
  "id": "uuid",
  "type_operation": "depot|retrait",
  "montant": "decimal",
  "reference": "string|null",
  "solde_apres_operation": "decimal|null",
  "note": "string|null",
  "statut": "ENREGISTREE|MODIFIEE|ANNULEE",
  "version": "integer",
  "created_at": "datetime",
  "client": {
    "id": "uuid",
    "telephone": "string",
    "nom": "string",
    "prenoms": "string"
  },
  "reseau": {
    "id": "uuid",
    "nom": "string",
    "code": "string"
  },
  "user": {
    "id": "uuid",
    "name": "string"
  }
}
```

### 3.4 Update Transaction
Modifier une transaction existante.

**Endpoint:** `PATCH /transactions/{transaction}`

**Auth:** Requis (Bearer token)

**Rate Limit:** 120/minute

**URL Parameters:**
- `transaction` (required, uuid) - Transaction ID

**Request Body:**
```json
{
  "montant": "decimal (optional, gt:0)",
  "solde_apres_operation": "decimal (optional, nullable, gte:0)",
  "note": "string (optional, nullable)",
  "motif": "string (required, max:1000)"
}
```

**Response (200):**
```json
{
  "id": "uuid",
  "type_operation": "depot|retrait",
  "montant": "decimal",
  "reference": "string|null",
  "solde_apres_operation": "decimal|null",
  "note": "string|null",
  "statut": "MODIFIEE",
  "version": "integer",
  "created_at": "datetime",
  "client": {
    "id": "uuid",
    "telephone": "string",
    "nom": "string",
    "prenoms": "string"
  },
  "reseau": {
    "id": "uuid",
    "nom": "string",
    "code": "string"
  },
  "user": {
    "id": "uuid",
    "name": "string"
  }
}
```

### 3.5 Cancel Transaction
Annuler une transaction.

**Endpoint:** `POST /transactions/{transaction}/cancel`

**Auth:** Requis (Bearer token)

**Rate Limit:** 120/minute

**URL Parameters:**
- `transaction` (required, uuid) - Transaction ID

**Request Body:**
```json
{
  "motif": "string (required, max:1000)"
}
```

**Response (200):**
```json
{
  "id": "uuid",
  "type_operation": "depot|retrait",
  "montant": "decimal",
  "reference": "string|null",
  "solde_apres_operation": "decimal|null",
  "note": "string|null",
  "statut": "ANNULEE",
  "version": "integer",
  "created_at": "datetime",
  "client": {
    "id": "uuid",
    "telephone": "string",
    "nom": "string",
    "prenoms": "string"
  },
  "reseau": {
    "id": "uuid",
    "nom": "string",
    "code": "string"
  },
  "user": {
    "id": "uuid",
    "name": "string"
  }
}
```

---

## 4. Transaction History

### 4.1 Get Transaction History
Récupérer l'historique des modifications d'une transaction.

**Endpoint:** `GET /transactions/{transaction}/history`

**Auth:** Requis (Bearer token)

**Rate Limit:** 120/minute

**URL Parameters:**
- `transaction` (required, uuid) - Transaction ID

**Response (200):**
```json
[
  {
    "id": "uuid",
    "action": "string",
    "user": {
      "id": "uuid",
      "name": "string"
    },
    "anciennes_donnees": { },
    "nouvelles_donnees": { },
    "motif": "string|null",
    "created_at": "datetime"
  }
]
```

---

## 5. Networks (Réseaux)

### 5.1 List Networks
Lister tous les réseaux disponibles.

**Endpoint:** `GET /reseaux`

**Auth:** Requis (Bearer token)

**Rate Limit:** 120/minute

**Query Parameters:**
- `avec_supprimes` (optional, boolean) - Include deleted networks

**Response (200):**
```json
[
  {
    "id": "uuid",
    "nom": "string",
    "code": "string",
    "logo": "string|null",
    "deleted_at": "datetime|null"
  }
]
```

### 5.2 Create Network
Créer un nouveau réseau (admin uniquement).

**Endpoint:** `POST /reseaux`

**Auth:** Requis (Bearer token)

**Rate Limit:** 120/minute

**Request Body:**
```json
{
  "nom": "string (required, max:100)",
  "code": "string (required, max:50, unique)",
  "logo": "string (optional, nullable, max:2048)"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Réseau créé avec succès.",
  "data": {
    "id": "uuid",
    "nom": "string",
    "code": "string",
    "logo": "string|null",
    "deleted_at": null
  }
}
```

### 5.3 Update Network
Mettre à jour un réseau existant.

**Endpoint:** `PATCH /reseaux/{reseau}`

**Auth:** Requis (Bearer token)

**Rate Limit:** 120/minute

**URL Parameters:**
- `reseau` (required, uuid) - Network ID

**Request Body:**
```json
{
  "nom": "string (optional, max:100)",
  "logo": "string (optional, nullable, max:2048)"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Réseau modifié avec succès.",
  "data": {
    "id": "uuid",
    "nom": "string",
    "code": "string",
    "logo": "string|null",
    "deleted_at": null
  }
}
```

### 5.4 Delete Network
Supprimer un réseau (soft delete).

**Endpoint:** `DELETE /reseaux/{reseau}`

**Auth:** Requis (Bearer token)

**Rate Limit:** 120/minute

**URL Parameters:**
- `reseau` (required, uuid) - Network ID

**Response (200):**
```json
{
  "success": true,
  "message": "Réseau supprimé avec succès."
}
```

---

## 6. Dashboard

### 6.1 Get Dashboard Summary
Récupérer le résumé du tableau de bord.

**Endpoint:** `GET /dashboard`

**Auth:** Requis (Bearer token)

**Rate Limit:** 120/minute

**Response (200):**
```json
{
  "date": "date",
  "nombre_transactions": "integer",
  "total_depots": "decimal",
  "total_retraits": "decimal",
  "dernieres_transactions": [
    {
      "id": "uuid",
      "type_operation": "depot|retrait",
      "montant": "decimal",
      "reference": "string|null",
      "solde_apres_operation": "decimal|null",
      "note": "string|null",
      "statut": "ENREGISTREE|MODIFIEE|ANNULEE",
      "version": "integer",
      "created_at": "datetime",
      "client": {
        "id": "uuid",
        "telephone": "string",
        "nom": "string",
        "prenoms": "string"
      },
      "reseau": {
        "id": "uuid",
        "nom": "string",
        "code": "string"
      },
      "user": {
        "id": "uuid",
        "name": "string"
      }
    }
  ],
  "derniers_soldes": [
    // Derniers soldes
  ]
}
```

---

## 7. Health Check

### 7.1 Public Health Check
Vérifier si l'API est opérationnelle.

**Endpoint:** `GET /health`

**Auth:** Non requis

**Response (200):**
```json
{
  "success": true,
  "message": "API opérationnelle.",
  "data": {
    "status": "ok"
  }
}
```

### 7.2 Authenticated Health Check
Vérifier si l'API authentifiée est opérationnelle.

**Endpoint:** `GET /health/authenticated`

**Auth:** Requis (Bearer token)

**Response (200):**
```json
{
  "success": true,
  "message": "API authentifiée opérationnelle.",
  "data": {
    "status": "ok"
  }
}
```

---

## Data Models

### User
```json
{
  "id": "uuid",
  "name": "string",
  "telephone": "string",
  "email": "string|null",
  "code_agent": "string|null",
  "photo": "string|null",
  "localisation_point": "string|null"
}
```

### Client
```json
{
  "id": "uuid",
  "user_id": "uuid",
  "telephone": "string",
  "nom": "string|null",
  "prenoms": "string|null",
  "date_naissance": "date|null",
  "nationalite": "string|null",
  "type_piece": "string|null",
  "numero_piece": "string|null",
  "date_expiration_piece": "date|null",
  "created_at": "datetime",
  "deleted_at": "datetime|null"
}
```

### Transaction
```json
{
  "id": "uuid",
  "user_id": "uuid",
  "client_id": "uuid",
  "reseau_id": "uuid",
  "type_operation": "depot|retrait",
  "montant": "decimal(12,2)",
  "reference": "string|null",
  "solde_apres_operation": "decimal(12,2)|null",
  "note": "string|null",
  "statut": "ENREGISTREE|MODIFIEE|ANNULEE",
  "sync_status": "SYNCED|PENDING|FAILED",
  "version": "integer",
  "synced_at": "datetime|null",
  "created_at": "datetime",
  "updated_at": "datetime"
}
```

### Reseau (Network)
```json
{
  "id": "uuid",
  "nom": "string",
  "code": "string",
  "logo": "string|null",
  "deleted_at": "datetime|null"
}
```

### TransactionHistory
```json
{
  "id": "uuid",
  "transaction_id": "uuid",
  "user_id": "uuid",
  "action": "string",
  "anciennes_donnees": "object|null",
  "nouvelles_donnees": "object|null",
  "motif": "string|null",
  "created_at": "datetime"
}
```

---

## Error Handling

### Authentication Error (401)
```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

### Authorization Error (403)
```json
{
  "success": false,
  "message": "Action non autorisée."
}
```

### Not Found Error (404)
```json
{
  "success": false,
  "message": "Ressource introuvable."
}
```

### Validation Error (422)
```json
{
  "success": false,
  "message": "Les données fournies sont invalides.",
  "errors": {
    "field_name": [
      "error message 1",
      "error message 2"
    ]
  }
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "Une erreur interne est survenue."
}
```

---

## Enums & Constants

### Transaction Status
- `ENREGISTREE` - Transaction enregistrée
- `MODIFIEE` - Transaction modifiée
- `ANNULEE` - Transaction annulée

### Transaction Type
- `depot` - Dépôt
- `retrait` - Retrait

### Sync Status
- `SYNCED` - Synchronisé
- `PENDING` - En attente de synchronisation
- `FAILED` - Échec de synchronisation

---

## Best Practices

1. **Authentication:** Toujours inclure le token Bearer dans les headers pour les endpoints protégés
2. **Pagination:** Utilisez les paramètres `page` et `per_page` pour les listes
3. **Error Handling:** Gérez toujours les erreurs de validation (422) côté client
4. **Rate Limiting:** Respectez les limites de taux pour éviter les blocages
5. **Date Format:** Utilisez le format ISO 8601 pour les dates (YYYY-MM-DD)
6. **UUID:** Tous les IDs sont des UUID v4
7. **Decimal:** Les montants sont des décimaux avec 2 chiffres après la virgule

---

## Changelog

### Version 1.0 (2026-09-14)
- Documentation initiale
- Authentification complète
- Gestion des clients
- Gestion des transactions
- Historique des transactions
- Gestion des réseaux
- Dashboard

---

## Support

Pour toute question ou problème technique, contactez l'équipe de développement MobiTrace.