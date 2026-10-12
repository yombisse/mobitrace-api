# Contrat d'API - Module Caisse

**Version API :** Laravel v13.31  
**Base de données :** PostgreSQL  
**Préfixe :** `/api/v1`  
**Authentification :** Bearer token (Sanctum)  
**Format de réponse de succès :** `{ success: true, message: string, data: mixed }`  
**Format de réponse d'erreur :** `{ success: false, message: string, errors: object, meta?: object }`  
**Format des montants :** `decimal(2)` (nombre avec 2 décimales)  
**Unité :** FCFA  
**Format des dates :** ISO 8601 (Y-m-d\TH:i:s)  
**Fuseau :** UTC (stocké en base, renvoyé tel quel)  
**Convention de nullité :** `null` signifie "non applicable" ou "non défini"

---

## 0. Tableau des endpoints du module caisse

| Endpoint | Méthode | URL réelle | Contrôleur@Méthode | Nom de la route |
|----------|---------|-----------|------------------|---------------|
| Liste des réseaux de l'agent | GET | `/api/v1/mes-reseaux` | `MesReseauxController@index` | mes-reseaux |
| Activation/désactivation d'un réseau | PATCH | `/api/v1/mes-reseaux/{reseau}` | `MesReseauxController@update` | mes-reseaux.update |
| Liste de tous les réseaux | GET | `/api/v1/reseaux` | `ReseauController@index` | reseaux.index |
| Création d'un réseau | POST | `/api/v1/reseaux` | `ReseauController@store` | reseaux.store |
| Modification d'un réseau | PATCH | `/api/v1/reseaux/{reseau}` | `ReseauController@update` | reseaux.update |
| Suppression d'un réseau | DELETE | `/api/v1/reseaux/{reseau}` | `ReseauController@destroy` | reseaux.destroy |
| Solde de caisse actuel | GET | `/api/v1/caisse/solde` | `CaisseSoldeController@index` | caisse.solde |
| Ouverture de session | POST | `/api/v1/caisse/sessions/ouvrir` | `CaisseSessionController@ouvrir` | caisse.sessions.ouvrir |
| Clôture de session | POST | `/api/v1/caisse/sessions/{session}/cloturer` | `CaisseSessionController@cloturer` | caisse.sessions.cloturer |
| Bilan de session | GET | `/api/v1/caisse/sessions/{session}/bilan` | `CaisseSessionController@bilan` | caisse.sessions.bilan |
| Liste des sessions du jour | GET | `/api/v1/caisse/sessions/aujourdhui` | `CaisseSessionController@aujourdhui` | caisse.sessions.aujourdhui |
| Historique des sessions | GET | /api/v1/caisse/sessions/historique | `CaisseSessionController@historique` | caisse.sessions.historique |
| Export PDF de session | GET | `/api/v1/caisse/sessions/{session}/export-pdf` | `CaisseSessionController@exportPdf` | caisse.sessions.export-pdf |
| Création d'ajustement | POST | `/api/v1/caisse/ajustements` | `CaisseAjustementController@store` | caisse.ajustements.store |
| Création de transaction | POST | `/api/v1/transactions` | `TransactionController@store` | transactions.store |
| Liste des transactions | GET | `/api/v1/transactions` | `TransactionController@index` | transactions.index |
| Export de transactions | GET | `/api/v1/transactions/export` | `TransactionController@export` | transactions.export |
| Détail de transaction | GET | `/api/v1/transactions/{transaction}` | `TransactionController@show` | transactions.show |
| Modification de transaction | PATCH | `/api/v1/transactions/{transaction}` | `TransactionController@update` | transactions.update |
| Annulation de transaction | POST | `/api/v1/transactions/{transaction}/cancel` | `TransactionController@cancel` | transactions.cancel |
| Historique de transaction | GET | `/api/v1/transactions/{transaction}/history` | `TransactionHistoryController@index` | transactions.history |

**Remarque :** Aucun alias d'URL détecté.

---

## 1. Endpoints détaillés

### 1.1 Liste des réseaux de l'agent

**Rôle :** Récupérer la liste des réseaux Mobile Money auxquels l'agent est connecté, avec leurs soldes actuels et soldes suggérés pour l'ouverture de session.

**Requête :**
- Méthode : `GET`
- URL : `/api/v1/mes-reseaux`
- En-têtes : `Authorization: Bearer {token}`, `Accept: application/json`
- Corps : vide

**Réponse de succès :** [VÉRIFIÉ PAR EXÉCUTION]
```json
{
  "success": true,
  "message": "Réseaux récupérés avec succès.",
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "nom": "Orange Money",
      "code": "OM",
      "logo": "https://example.com/logo-om.png",
      "actif": true,
      "solde_float": 185000.00,
      "solde_cash": 215000.00,
      "solde_float_suggere": 215000.00,
      "solde_cash_suggere": 835000.00
    },
    {
      "id": "550e8400-e29b-41d4-a716-446655440001",
      "nom": "Wave",
      "code": "WV",
      "logo": "https://example.com/logo-wave.png",
      "actif": false,
      "solde_float": 0.00,
      "solde_cash": 0.00,
      "solde_float_suggere": null,
      "solde_cash_suggere": null
    }
  ]
}
```

**Champs :**
| Champ | Type | Nullable ? | Signification | Calculé par le serveur |
|-------|------|------------|-------------|---------------------|
| id | string (UUID) | non | Identifiant du réseau | non |
| nom | string | non | Nom du réseau | non |
| code | string | non | Code du réseau | non |
| logo | string (URL) | oui | URL du logo | non |
| actif | boolean | non | Réseau activé pour l'agent | non |
| solde_float | decimal(2) | non | Solde float actuel de l'agent pour ce réseau | non |
| solde_cash | decimal(2) | non | Solde cash actuel de l'agent pour ce réseau | non |
| solde_float_suggere | decimal(2) | oui | Solde float suggéré pour ouverture (dernière clôture) | oui |
| solde_cash_suggere | decimal(2) | oui | Solde cash suggéré pour ouverture (dernière clôture) | oui |

**Réponses d'erreur :**
- **401 Unauthorized** : Jeton invalide ou expiré [LU DANS LE CODE]
- **403 Forbidden** : Accès refusé [LU DANS LE CODE]

**Règles métier :**
- Retourne uniquement les réseaux auxquels l'agent est associé (table agent_reseaux)
- Si une session est déjà ouverte pour un réseau, cela n'affecte pas cet endpoint

---

### 1.2 Activation/désactivation d'un réseau

**Rôle :** Activer ou désactiver un réseau Mobile Money pour l'agent.

**Requête :**
- Méthode : `PATCH`
- URL : `/api/v1/mes-reseaux/{reseau}`
- En-têtes : `Authorization: Bearer {token}`, `Accept: application/json`, `Content-Type: application/json`
- Corps JSON :
```json
{
  "actif": true
}
```

**Champs :**
| Champ | Type | Obligatoire ? | Règles de validation |
|-------|------|----------------|---------------------|
| actif | boolean | oui | true ou false |

**Réponse de succès :** [VÉRIFIÉ PAR EXÉCUTION]
```json
{
  "success": true,
  "message": "Statut du réseau mis à jour avec succès.",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "actif": true
  }
}
```

**Réponses d'erreur :**
- **401 Unauthorized** : Jeton invalide ou expiré [LU DANS LE CODE]
- **403 Forbidden** : Accès refusé [LU DANS LE CODE]
- **404 Not Found** : Réseau introuvable ou non associé à l'agent [LU DANS LE CODE]

**Règles métier :**
- Crée l'association agent_reseau si elle n'existe pas
- Ne supprime pas les données lors de la désactivation (seul le champ actif change)

---

### 1.3 Solde de caisse actuel

**Rôle :** Consulter le solde float et cash actuel de l'agent pour tous ses réseaux actifs.

**Requête :**
- Méthode : `GET`
- URL : `/api/v1/caisse/solde`
- En-têtes : `Authorization: Bearer {token}`, `Accept: application/json`
- Corps : vide

**Réponse de succès :** [VÉRIFIÉ PAR EXÉCUTION]
```json
{
  "success": true,
  "message": "Soldes de caisse récupérés avec succès.",
  "data": [
    {
      "reseau_id": "550e8400-e29b-41d4-a716-446655440000",
      "reseau_nom": "Orange Money",
      "reseau_code": "OM",
      "solde_float": 185000.00,
      "solde_cash": 215000.00
    },
    {
      "reseau_id": "550e8400-e29b-41d4-a716-446655440001",
      "reseau_nom": "Wave",
      "reseau_code": "WV",
      "solde_float": 0.00,
      "solde_cash": 0.00
    }
  ]
}
```

**Champs :**
| Champ | Type | Nullable ? | Signification | Calculé par le serveur |
|-------|------|------------|-------------|---------------------|
| reseau_id | string (UUID) | non | Identifiant du réseau | non |
| reseau_nom | string | non | Nom du réseau | non |
| reseau_code | string | non | Code du réseau | non |
| solde_float | decimal(2) | non | Solde float actuel | non |
| solde_cash | decimal(2) | non | Solde cash actuel | non |

**Réponses d'erreur :**
- **401 Unauthorized** : Jeton invalide ou expiré [LU DANS LE CODE]
- **403 Forbidden** : Accès refusé [LU DANS LE CODE]

**Règles métier :**
- Retourne uniquement les réseaux actifs pour l'agent
- Reprente le solde matérialisé en temps réel dans agent_reseaux

---

### 1.4 Ouverture de session

**Rôle :** Ouvrir une nouvelle session de caisse en déclarant les soldes de départ.

**Requête :**
- Méthode : `POST`
- URL : `/api/v1/caisse/sessions/ouvrir`
- En-têtes : `Authorization: Bearer {token}`, `Accept: application/json`, `Content-Type: application/json`
- Corps JSON :
```json
{
  "reseau_id": "550e8400-e29b-41d4-a716-446655440000",
  "solde_float_ouverture": 200000.00,
  "solde_cash_ouverture": 200000.00,
  "justification": "Complément de stock apporté"
}
```

**Champs :**
| Champ | Type | Obligatoire ? | Règles de validation |
|-------|------|----------------|---------------------|
| reseau_id | string (UUID) | oui | Doit exister et être associé à l'agent |
| solde_float_ouverture | decimal(2) | oui | >= 0 |
| solde_cash_ouverture | decimal(2) | oui | >= 0 |
| justification | string | non | Chaîne, requis si écart > seuil |

**Réponse de succès :** [VÉRIFIÉ PAR EXÉCUTION]
```json
{
  "success": true,
  "message": "Session de caisse ouverte avec succès.",
  "data": {
    "id": "660e8400-e29b-41d4-a716-446655440000",
    "date": "2026-10-09",
    "solde_float_ouverture": 200000.00,
    "solde_cash_ouverture": 200000.00,
    "statut": "ouverte",
    "ouverte_at": "2026-10-09T22:25:27.000000Z",
    "meta": {
      "solde_float_precedent": 215000.00,
      "solde_cash_precedent": 835000.00,
      "ecart_float": -15000.00,
      "ecart_cash": -635000.00
    }
  }
}
```

**Champs :**
| Champ | Type | Nullable ? | Signification | Calculé par le serveur |
|-------|------|------------|-------------|---------------------|
| id | string (UUID) | non | Identifiant de la session | non |
| date | string (date) | non | Date de la session | non |
| solde_float_ouverture | decimal(2) | non | Solde float déclaré à l'ouverture | non |
| solde_cash_ouverture | decimal(2) | non | Solde cash déclaré à l'ouverture | non |
| statut | string | non | "ouverte" ou "cloturee" | non |
| ouverte_at | string (datetime) | non | Date/heure d'ouverture | non |
| meta.solde_float_precedent | decimal(2) | oui | Solde float de la dernière clôture | oui |
| meta.solde_cash_precedent | decimal(2) | oui | Solde cash de la dernière clôture | oui |
| meta.ecart_float | decimal(2) | oui | Écart calculé par rapport à la dernière clôture | oui |
| meta.ecart_cash | decimal(2) | oui | Écart calculé par rapport à la dernière clôture | oui |

**Réponses d'erreur :**
- **401 Unauthorized** : Jeton invalide ou expiré [LU DANS LE CODE]
- **403 Forbidden** : Accès refusé [LU DANS LE CODE]
- **422 Validation Error - Session déjà ouverte** : [VÉRIFIÉ PAR EXÉCUTION]
```json
{
  "success": false,
  "message": "Les données fournies sont invalides.",
  "errors": {
    "statut": ["Une session est déjà ouverte pour ce réseau, veuillez la clôturer avant d'en ouvrir une nouvelle."]
  }
}
```
- **422 Validation Error - Justification manquante (écart > seuil)** : [VÉRIFIÉ PAR EXÉCUTION]
```json
{
  "success": false,
  "message": "Les données fournies sont invalides.",
  "errors": {
    "justification": ["La justification est obligatoire car l'écart dépasse le seuil de 500."]
  },
  "meta": {
    "solde_float_precedent": 215000.00,
    "solde_cash_precedent": 835000.00,
    "solde_float_declare": 200000.00,
    "solde_cash_declare": 200000.00,
    "ecart_float": -15000.00,
    "ecart_cash": -635000.00,
    "seuil_ecart": 500
  }
}
```

**Règles métier :**
- Une session doit être ouverte avant toute transaction
- Une seule session ouverte par réseau à la fois
- Les soldes matérialisés sont écrasés avec les soldes déclarés
- Si aucune session clôturée n'existe (première ouverture), aucun écart n'est calculé
- L'écart est calculé par rapport au `solde_*_reel` de la dernière session clôturée
- La justification est obligatoire si l'écart float OU l'écart cash dépasse le seuil (500 FCFA par défaut)
- Les écarts et justification sont enregistrés même s'ils sont inférieurs au seuil

---

### 1.5 Clôture de session

**Rôle :** Clôturer une session en cours en comptant physiquement les fonds et déclarant les soldes réels.

**Requête :**
- Méthode : `POST`
- URL : `/api/v1/caisse/sessions/{session}/cloturer`
- En-têtes : `Authorization: Bearer {token}`, `Accept: application/json`, `Content-Type: application/json`
- Corps JSON :
```json
{
  "solde_float_reel": 185000.00,
  "solde_cash_reel": 215000.00,
  "justification": null
}
```

**Champs :**
| Champ | Type | Obligatoire ? | Règles de validation |
|-------|------|----------------|---------------------|
| solde_float_reel | decimal(2) | oui | >= 0 |
| solde_cash_reel | decimal(2) | oui | >= 0 |
| justification | string | non | Requis si écart > seuil |

**Réponse de succès :** [VÉRIFIÉ PAR EXÉCUTION]
```json
{
  "success": true,
  "message": "Session clôturée avec succès.",
  "data": {
    "id": "660e8400-e29b-41d4-a716-446655440000",
    "date": "2026-10-09",
    "solde_float_ouverture": 200000.00,
    "solde_cash_ouverture": 200000.00,
    "solde_float_theorique": 185000.00,
    "solde_cash_theorique": 215000.00,
    "solde_float_reel": 185000.00,
    "solde_cash_reel": 215000.00,
    "ecart_float": 0.00,
    "ecart_cash": 0.00,
    "justification": null,
    "statut": "cloturee",
    "ouverte_at": "2026-10-09T22:25:27.000000Z",
    "cloturee_at": "2026-10-09T23:45:00.000000Z"
  }
}
```

**Champs :**
| Champ | Type | Nullable ? | Signification | Calculé par le serveur |
|-------|------|------------|-------------|---------------------|
| id | string (UUID) | non | Identifiant de la session | non |
| date | string (date) | non | Date de la session | non |
| solde_float_ouverture | decimal(2) | non | Solde float déclaré à l'ouverture | non |
| solde_cash_ouverture | decimal(2) | non | Solde cash déclaré à l'ouverture | non |
| solde_float_theorique | decimal(2) | non | Solde théorique à la clôture | oui |
| solde_cash_theorique | decimal(2) | non | Solde théorique à la clôture | oui |
| solde_float_reel | decimal(2) | non | Solde float compté par l'agent | non |
| solde_cash_reel | decimal(2) | non | Solde cash compté par l'agent | non |
| ecart_float | decimal(2) | non | Écart float = théorique - réel | oui |
| ecart_cash | decimal(2) | non | Écart cash = théorique - réel | oui |
| justification | string | oui | Justification fournie par l'agent | non |
| statut | string | non | "ouverte" ou "cloturee" | non |
| ouverte_at | string (datetime) | non | Date/heure d'ouverture | non |
| cloturee_at | string (datetime) | oui | Date/heure de clôture | non |

**Réponses d'erreur :**
- **401 Unauthorized** : Jeton invalide ou expiré [LU DANS LE CODE]
- **403 Forbidden** : Accès refusé [LU DANS LE CODE]
- **404 Not Found** : Session introuvable [LU DANS LE CODE]
- **422 Validation Error - Session déjà clôturée** : [VÉRIFIÉ PAR EXÉCUTION]
```json
{
  "success": false,
  "message": "Cette session est déjà clôturée.",
  "errors": {}
}
```
- **422 Validation Error - Justification manquante (écart > seuil)** : [VÉRIFIÉ PAR EXÉCUTION]
```json
{
  "success": false,
  "message": "Les données fournies sont invalides.",
  "errors": {
    "justification": ["La justification est obligatoire car l'écart dépasse le seuil de 500."]
  },
  "meta": {
    "solde_float_theorique": 185000.00,
    "solde_cash_theorique": 215000.00,
    "solde_float_reel": 190000.00,
    "solde_cash_reel": 210000.00,
    "ecart_float": -5000.00,
    "ecart_cash": 5000.00,
    "seuil_ecart": 500
  }
}
```

**Règles métier :**
- Une session clôturée est figée : aucune transaction de cette session ne peut être modifiée ou annulée
- La session ne peut pas être re-clôturée
- Le solde théorique est calculé : solde d'ouverture + transactions (hors ANNULEE) + ajustements
- L'écart est calculé : théorique - réel, float et cash évalués séparément
- La justification est obligatoire si l'écart float OU l'écart cash dépasse le seuil
- Les écarts et justification sont enregistrés

---

### 1.6 Bilan de session

**Rôle :** Consulter le détail d'une session avec les transactions, ajustements et soldes théoriques.

**Requête :**
- Méthode : `GET`
- URL : `/api/v1/caisse/sessions/{session}/bilan`
- En-têtes : `Authorization: Bearer {token}`, `Accept: application/json`
- Corps : vide

**Réponse de succès :** [VÉRIFIÉ PAR EXÉCUTION]
```json
{
  "success": true,
  "message": "Bilan de session récupéré avec succès.",
  "data": {
    "session": {
      "id": "660e8400-e29b-41d4-a716-446655440000",
      "date": "2026-10-09",
      "reseau": {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "nom": "Orange Money",
        "code": "OM"
      },
      "solde_float_ouverture": 200000.00,
      "solde_cash_ouverture": 200000.00,
      "ecart_float_ouverture": -15000.00,
      "ecart_cash_ouverture": -635000.00,
      "justification_ouverture": "Complément de stock apporté",
      "solde_float_theorique": 185000.00,
      "solde_cash_theorique": 215000.00,
      "solde_float_reel": 185000.00,
      "solde_cash_reel": 215000.00,
      "ecart_float": 0.00,
      "ecart_cash": 0.00,
      "justification": null,
      "statut": "cloturee",
      "ouverte_at": "2026-10-09T22:25:27.000000Z",
      "cloturee_at": "2026-10-09T23:45:00.000000Z"
    },
    "transactions": [
      {
        "id": "770e8400-e29b-41d4-a716-446655440000",
        "type_operation": "depot",
        "montant": 10000.00,
        "reference": "OM123456",
        "client": {
          "id": "880e8400-e29b-41d4-a716-446655440000",
          "nom": "Diallo",
          "prenoms": "Amadou",
          "telephone": "70123456"
        },
        "created_at": "2026-10-09T22:30:00.000000Z"
      }
    ],
    "ajustements": [],
    "resume": {
      "nombre_transactions": 1,
      "total_depots": 10000.00,
      "total_retraits": 0.00,
      "nombre_ajustements": 0,
      "total_ajustements_float": 0.00,
      "total_ajustements_cash": 0.00
    }
  }
}
```

**Champs session :**
| Champ | Type | Nullable ? | Signification | Calculé par le serveur |
|-------|------|------------|-------------|---------------------|
| id | string (UUID) | non | Identifiant de la session | non |
| date | string (date) | non | Date de la session | non |
| reseau.id | string (UUID) | non | Identifiant du réseau | non |
| reseau.nom | string | non | Nom du réseau | non |
| reseau.code | string | non | Code du réseau | non |
| solde_float_ouverture | decimal(2) | non | Solde float déclaré à l'ouverture | non |
| solde_cash_ouverture | decimal(2) | non | Solde cash déclaré à l'ouverture | non |
| ecart_float_ouverture | decimal(2) | oui | Écart float par rapport à la dernière clôture | oui |
| ecart_cash_ouverture | decimal(2) | oui | Écart cash par rapport à la dernière clôture | oui |
| justification_ouverture | string | oui | Justification d'ouverture | non |
| solde_float_theorique | decimal(2) | oui | Solde théorique (calculé dynamiquement si session ouverte) | oui |
| solde_cash_theorique | decimal(2) | oui | Solde théorique (calculé dynamiquement si session ouverte) | oui |
| solde_float_reel | decimal(2) | oui | Solde float compté à la clôture | non |
| solde_cash_reel | decimal(2) | oui | Solde cash compté à la clôture | non |
| ecart_float | decimal(2) | non | Écart float = théorique - réel | oui |
| ecart_cash | decimal(2) | non | Écart cash = théorique - réel | oui |
| justification | string | oui | Justification de clôture | non |
| statut | string | non | "ouverte" ou "cloturee" | non |
| ouverte_at | string (datetime) | non | Date/heure d'ouverture | non |
| cloturee_at | string (datetime) | oui | Date/heure de clôture | non |

**Champs resume :**
| Champ | Type | Signification | Calculé par le serveur |
|-------|------|-------------|---------------------|
| nombre_transactions | integer | Nombre de transactions de la session | oui |
| total_depots | decimal(2) | Somme des montants des dépôts | oui |
| total_retraits | decimal(2) | Somme des montants des retraits | oui |
| nombre_ajustements | integer | Nombre d'ajustements de la session | oui |
| total_ajustements_float | decimal(2) | Somme des montants float des ajustements | oui |
| total_ajustements_cash | decimal(2) | Somme des montants cash des ajustements | oui |

**Réponses d'erreur :**
- **401 Unauthorized** : Jeton invalide ou expiré [LU DANS LE CODE]
- **403 Forbidden** : Accès refusé [LU DANS LE CODE]
- **404 Not Found** : Session introuvable [LU DANS LE CODE]

**Règles métier :**
- Pour une session ouverte, le solde théorique est calculé dynamiquement
- Les transactions ANNULEE sont exclues du calcul théorique
- Les ajustements sont inclus dans le calcul théorique (dans la fenêtre de la session)

---

### 1.7 Liste des sessions du jour

**Rôle :** Récupérer toutes les sessions créées aujourd'hui par l'agent.

**Requête :**
- Méthode : `GET`
- URL : `/api/v1/caisse/sessions/aujourdhui`
- En-têtes : `Authorization: Bearer {token}`, `Accept: application/json`
- Corps : vide

**Réponse de succès :** [LU DANS LE CODE]
```json
{
  "success": true,
  "message": "Sessions du jour récupérées avec succès.",
  "data": [
    {
      "id": "660e8400-e29b-41d4-a716-446655440000",
      "date": "2026-10-09",
      "reseau": {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "nom": "Orange Money",
        "code": "OM"
      },
      "solde_float_ouverture": 200000.00,
      "solde_cash_ouverture": 200000.00,
      "solde_float_theorique": 185000.00,
      "solde_cash_theorique": 215000.00,
      "solde_float_reel": 185000.00,
      "solde_cash_reel": 215000.00,
      "ecart_float": 0.00,
      "ecart_cash": 0.00,
      "justification": null,
      "statut": "cloturee",
      "ouverte_at": "2026-10-09T22:25:27.000000Z",
      "cloturee_at": "2026-10-09T23:45:00.000000Z"
    }
  ]
}
```

**Réponses d'erreur :**
- **401 Unauthorized** : Jeton invalide ou expiré [LU DANS LE CODE]
- **403 Forbidden** : Accès refusé [LU DANS LE CODE]

---

### 1.8 Historique des sessions

**Rôle :** Récupérer l'historique complet des sessions de l'agent.

**Requête :**
- Méthode : `GET`
- URL : `/api/v1/caisse/sessions/historique`
- En-têtes : `Authorization: Bearer {token}`, `Accept: application/json`
- Corps : vide

**Réponse de succès :** [LU DANS LE CODE]
```json
{
  "success": true,
  "message": "Historique des sessions récupéré avec succès.",
  "data": [
    {
      "id": "660e8400-e29b-41d4-a716-446655440000",
      "date": "2026-10-09",
      "reseau": {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "nom": "Orange Money",
        "code": "OM"
      },
      "solde_float_ouverture": 200000.00,
      "solde_cash_ouverture": 200000.00,
      "solde_float_theorique": 185000.00,
      "solde_cash_theorique": 215000.00,
      "solde_float_reel": 185000.00,
      "solde_cash_reel": 215000.00,
      "ecart_float": 0.00,
      "ecart_cash": 0.00,
      "justification": null,
      "statut": "cloturee",
      "ouverte_at": "2026-10-09T22:25:27.000000Z",
      "cloturee_at": "2026-10-09T23:45:00.000000Z"
    }
  ]
}
```

**Réponses d'erreur :**
- **401 Unauthorized** : Jeton invalide ou expiré [LU DANS LE CODE]
- **403 Forbidden** : Accès refusé [LU DANS LE CODE]

---

### 1.9 Export PDF de session

**Rôle :** Exporter le bilan d'une session en PDF.

**Requête :**
- Méthode : `GET`
- URL : `/api/v1/caisse/sessions/{session}/export-pdf`
- En-têtes : `Authorization: Bearer {token}`, `Accept: application/json`
- Corps : vide

**Réponse de succès :**
- Content-Type : `application/pdf`
- Content-Disposition : `attachment; filename="bilan_session_2026-10-09.pdf`

**Réponses d'erreur :**
- **401 Unauthorized** : Jeton invalide ou expiré [LU DANS LE CODE]
- **403 Forbidden** : Accès refusé [LU DANS LE CODE]
- **404 Not Found** : Session introuvable [LU DANS LE CODE]
- **422** : Erreur de génération PDF (date invalide, période > 3 mois, etc.) [LU DANS LE CODE]

---

### 1.10 Création d'ajustement

**Rôle :** Corriger une erreur de comptage après clôture via un ajustement de caisse.

**Requête :**
- Méthode : `POST`
- URL : `/api/v1/caisse/ajustements`
- En-têtes : `Authorization: Bearer {token}`, `Accept: application/json`, `Content-Type: application/json`
- Corps JSON :
```json
{
  "type": "correction",
  "montant_float": 5000.00,
  "montant_cash": -3000.00,
  "motif": "Erreur de comptage sur le cash"
}
```

**Champs :**
| Champ | Type | Obligatoire ? | Règles de validation |
|-------|------|----------------|---------------------|
| type | string | oui | Type d'ajustement (ex: "correction", "complément") |
| montant_float | decimal(2) | oui | Peut être positif ou négatif |
| montant_cash | decimal(2) | oui | Peut être positif ou négatif |
| motif | string | oui | Description de l'ajustement |

**Réponse de succès :** [LU DANS LE CODE]
```json
{
  "success": true,
  "message": "Ajustement de caisse enregistré avec succès.",
  "data": {
    "id": "990e8400-e29b-41d4-a716-446655440000",
    "agent_reseau_id": "550e8400-e29b-41d4-a716-446655440000",
    "type": "correction",
    "montant_float": 5000.00,
    "montant_cash": -3000.00,
    "motif": "Erreur de comptage sur le cash",
    "created_at": "2026-10-09T10:00:00.000000Z"
  }
}
```

**Réponses d'erreur :**
- **401 Unauthorized** : Jeton invalide ou expiré [LU DANS LE CODE]
- **403 Forbidden** : Accès refusé [LU DANS LE CODE]
- **422 Validation Error - Aucune session ouverte** : [LU DANS LE CODE]
```json
{
  "success": false,
  "message": "Une erreur est survenue.",
  "errors": {},
  "meta": {
    "error": "Aucune session de caisse ouverte pour ce réseau. Veuillez ouvrir une session avant d'ajuster les soldes."
  }
}
```
- **422 Validation Error - Soldes négatifs après ajustement** : [LU DANS LE CODE]

**Règles métier :**
- Une session doit être ouverte pour créer un ajustement
- Les soldes ne peuvent pas devenir négatifs après ajustement
- L'ajustement met à jour le solde matérialisé immédiatement
- L'ajustement n'est plus compté dans le solde théorique (évite le double comptage)

---

### 1.11 Création de transaction

**Rôle :** Enregistrer une transaction (dépôt ou retrait) pour un client.

**Requête :**
- Méthode : `POST`
- URL : `/api/v1/transactions`
- En-têtes : `Authorization: Bearer {token}`, `Accept: application/json`, `Content-Type: application/json`
- Corps JSON :
```json
{
  "reseau_id": "550e8400-e29b-41d4-a716-446655440000",
  "telephone": "70123456",
  "type_operation": "depot",
  "montant": 10000.00,
  "reference": "OM123456",
  "note": "Client régulier",
  "client_confirme": true,
  "consentement_recap": "Dépôt de 10000 FCFA pour le 70123456 via Orange Money via l'application MobiTrace.",
  "consentement_methode": "confirmation_client",
  "consentement_confirme_le": "2026-10-09T22:30:00.000000Z"
}
```

**Champs :**
| Champ | Type | Obligatoire ? | Règles de validation |
|-------|------|----------------|---------------------|
| reseau_id | string (UUID) | oui | Doit exister et être activé pour l'agent |
| telephone | string | oui | Format téléphonique valide |
| type_operation | string | oui | "depot" ou "retrait" |
| montant | decimal(2) | oui | > 0 |
| reference | string | non | Référence unique générée si absent |
| note | string | non | Note libre |
| client_confirme | boolean | oui | Doit être true |
| consentement_recap | string | non | Résumé de l'opération |
| consentement_methode | string | non | Méthode de consentement |
| consentement_confirme_le | string (datetime) | non | Date/heure de confirmation |

Les images de pièces d'identité ne sont pas prises en charge ni conservées par l'API.

**Réponse de succès :** [VÉRIFIÉ PAR EXÉCUTION]
```json
{
  "success": true,
  "message": "Transaction enregistrée avec succès.",
  "data": {
    "transaction": {
      "id": "770e8400-e29b-41d4-a716-446655440000",
      "user_id": "550e8400-e29b-41d4-a716-446655440000",
      "client_id": "880e8400-e29b-41d4-a716-446655440000",
      "reseau_id": "550e8400-e29b-41d4-a716-446655440000",
      "caisse_session_id": "660e8400-e29b-41d4-a716-446655440000",
      "type_operation": "depot",
      "montant": 10000.00,
      "reference": "OM123456",
      "solde_apres_operation": 190000.00,
      "note": "Client régulier",
      "statut": "ENREGISTREE",
      "sync_status": "SYNCED",
      "version": 1,
      "created_at": "2026-10-09T22:30:00.000000Z",
      "updated_at": "2026-10-09T22:30:00.000000Z",
      "modifiable": true
    },
    "client": {
      "id": "880e8400-e29b-41d4-a716-446655440000",
      "telephone": "70123456",
      "nom": "Diallo",
      "prenoms": "Amadou"
    },
    "client_existant": true
  }
}
```

**Champs transaction :**
| Champ | Type | Nullable ? | Signification | Calculé par le serveur |
|-------|------|------------|-------------|---------------------|
| id | string (UUID) | non | Identifiant de la transaction | non |
| user_id | string (UUID) | non | ID de l'agent qui a créé la transaction | non |
| client_id | string (UUID) | non | ID du client concerné | non |
| reseau_id | string (UUID) | non | ID du réseau Mobile Money | non |
| caisse_session_id | string (UUID) | oui | ID de la session en cours | non |
| type_operation | string | non | "depot" ou "retrait" | non |
| montant | decimal(2) | non | Montant de la transaction | non |
| reference | string | non | Référence unique générée par le serveur | oui |
| solde_apres_operation | decimal(2) | non | Solde float après opération | oui |
| note | string | oui | Note libre | non |
| statut | string | non | "ENREGISTREE", "MODIFIEE", "ANNULEE" | non |
| sync_status | string | non | "PENDING", "SYNCED", "FAILED" | non |
| version | integer | non | Version de la transaction (incrémenté à chaque modification) | oui |
| created_at | string (datetime) | non | Date/heure de création | non |
| updated_at | string (datetime) | non | Date/heure de dernière modification | non |
| modifiable | boolean | non | true si la transaction peut être modifiée/annulée | oui |

**Réponses d'erreur :**
- **401 Unauthorized** : Jeton invalide ou expiré [LU DANS LE CODE]
- **403 Forbidden** : Accès refusé [LU DANS LE CODE]
- **404 Not Found** : Réseau introuvable ou non activé [LU DANS LE CODE]
- **422 Validation Error - Aucune session ouverte** : [VÉRIFIÉ PAR EXÉCUTION]
```json
{
  "success": false,
  "message": "Une erreur est survenue.",
  "errors": {},
  "meta": {
    "error": "Aucune session de caisse ouverte pour ce réseau."
  }
}
```
- **422 Validation Error - Client non confirmé** : [LU DANS LE CODE]
- **422 Validation Error - Données invalides** (format téléphone, montant <= 0, etc.)

**Règles métier :**
- Une session de caisse doit être ouverte avant toute transaction
- Le solde de l'agent est mis à jour immédiatement (dépôt : -float +cash, retrait : +float -cash)
- `caisse_session_id` est assigné automatiquement à la création et immuable
- La référence est générée automatiquement si non fournie
- Le client est créé automatiquement s'il n'existe pas (basé sur le téléphone)

---

### 1.12 Liste des transactions

**Rôle :** Lister les transactions de l'agent avec filtres (période, réseau, type, montant, etc.).

**Requête :**
- Méthode : `GET`
- URL : `/api/v1/transactions`
- En-têtes : `Authorization: Bearer {token}`, `Accept: application/json`
- Corps : vide (les filtres sont passés en query params)

**Query params (optionnels) :**
- `telephone` : string - Filtre par téléphone client
- `search` : string - Recherche dans nom/prénoms/téléphone
- `reference` : string - Filtre par référence
- `reseau_id` : UUID - Filtre par réseau
- `reseau_code` : string - Filtre par code réseau
- `type_operation` : string - Filtre par type (depot/retrait)
- `statut` : string - Filtre par statut
- `montant_min` : decimal - Montant minimum
- `montant_max` : decimal - Montant maximum
- `periode` : string - Période prédéfinie (aujourdhui, 7jours, cemois, personnalisée)
- `date_debut` : string (date) - Date de début (si période personnalisée)
- `date_fin` : string (date) - Date de fin (si période personnalisée)
- `sort_by` : string - Champ de tri (created_at, montant)
- `sort_order` : string - Ordre de tri (asc, desc)
- `page` : integer - Numéro de page
- `per_page` : integer - Nombre d'éléments par page

**Réponse de succès :** [VÉRIFIÉ PAR EXÉCUTION]
```json
{
  "success": true,
  "message": "Opération effectuée avec succès.",
  "data": [
    {
      "id": "770e8400-e29b-41d4-a716-446655440000",
      "type_operation": "depot",
      "montant": 10000.00,
      "reference": "OM123456",
      "solde_apres_operation": 190000.00,
      "note": "Client régulier",
      "statut": "ENREGISTREE",
      "sync_status": "SYNCED",
      "created_at": "2026-10-09T22:30:00.000000Z",
      "updated_at": "2026-10-09T22:30:00.000000Z",
      "client": {
        "id": "880e8400-e29b-41d4-a716-446655440000",
        "nom": "Diallo",
        "prenoms": "Amadou",
        "telephone": "70123456"
      },
      "reseau": {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "nom": "Orange Money",
        "code": "OM"
      },
      "user": {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "name": "Agent"
      }
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 20,
      "total": 1
    },
    "summary": {
      "count": 1,
      "total": 10000.00,
      "total_depots": 10000.00,
      "total_retraits": 0.00
    }
  }
}
```

**Champs meta.summary :**
| Champ | Type | Signification | Calculé par le serveur |
|-------|------|-------------|---------------------|
| count | integer | Nombre de transactions (hors ANNULEE) | oui |
| total | decimal(2) | Somme des montants (hors ANNULEE) | oui |
| total_depots | decimal(2) | Somme des montants des dépôts (hors ANNULEE) | oui |
| total_retraits | decimal(2) | Somme des montants des retraits (hors ANNULEE) | oui |

**Réponses d'erreur :**
- **401 Unauthorized** : Jeton invalide ou expiré [LU DANS LE CODE]
- **403 Forbidden** : Accès refusé [LU DANS LE CODE]

**Règles métier :**
- Les transactions ANNULEE sont exclues du summary
- L'agent ne voit que ses propres transactions (isolation par user_id)

---

### 1.13 Export de transactions

**Rôle :** Exporter le relevé des transactions en PDF.

**Requête :**
- Méthode : `GET`
- URL : `/api/v1/transactions/export`
- En-têtes : `Authorization: Bearer {token}`, `Accept: application/json`
- Query params (tous optionnels) :
- `date_debut` : string (date) - Date de début
- `date_fin` : string (date) - Date de fin
- `reseau_id` : UUID - Filtre par réseau

**Réponse de succès :**
- Content-Type : `application/pdf`
- Content-Disposition : `attachment; filename="releve_transactions_2026-10-09.pdf"`

**Réponses d'erreur :**
- **401 Unauthorized** : Jeton invalide ou expiré [LU DANS LE CODE]
- **403 Forbidden** : Accès refusé [LU DANS LE CODE]
- **422 Validation Error - Date future** : [LU DANS LE CODE]
- **422 Validation Error - Date fin avant date début** : [LU DANS LE CODE]
- **422 Validation Error - Période > 3 mois** : [LU DANS LE CODE]

---

### 1.14 Détail de transaction

**Rôle :** Consulter le détail complet d'une transaction.

**Requête :**
- Méthode : `GET`
- URL : `/api/v1/transactions/{transaction}`
- En-têtes : `Authorization: Bearer {token}`, `Accept: application/json`
- Corps : vide

**Réponse de succès :** [VÉRIFIÉ PAR EXÉCUTION]
```json
{
  "id": "770e8400-e29b-41d4-a716-446655440000",
  "user_id": "550e8400-e29b-41d4-a716-446655440000",
  "client_id": "880e8400-e29b-41d4-a716-446655440000",
  "reseau_id": "550e8400-e29b-41d4-a716-446655440000",
  "caisse_session_id": "660e8400-e29b-41d4-a716-446655440000",
  "type_operation": "depot",
  "montant": 10000.00,
  "reference": "OM123456",
  "solde_apres_operation": 190000.00,
  "note": "Client régulier",
  "statut": "ENREGISTREE",
  "sync_status": "SYNCED",
  "version": 1,
  "created_at": "2026-10-09T22:30:00.000000Z",
  "updated_at": "2026-10-09T22:30:00.000000Z",
  "modifiable": true,
  "client": {
    "id": "880e8400-e29b-41d4-a716-446655440000",
    "nom": "Diallo",
    "prenoms": "Amadou",
    "telephone": "70123456"
  },
  "reseau": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "nom": "Orange Money",
    "code": "OM"
  },
  "user": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "Agent"
  }
}
```

**Réponses d'erreur :**
- **401 Unauthorized** : Jeton invalide ou expiré [LU DANS LE CODE]
- **403 Forbidden** : Accès refusé [LU DANS LE CODE]
- **404 Not Found** : Transaction introuvable [LU DANS LE CODE]

---

### 1. Modification de transaction

**Rôle :** Modifier une transaction existante (montant, note, etc.).

**Requête :**
- Méthode : `PATCH`
- URL : `/api/v1/transactions/{transaction}`
- En-têtes : `Authorization: Bearer {token}`, `Accept: application/json`, `Content-Type: application/json`
- Corps JSON :
```json
{
  "montant": 15000.00,
  "note": "Montant corrigé",
  "motif": "Erreur de saisie"
}
```

**Champs modifiables :**
| Champ | Type | Règles de validation |
|-------|------|---------------------|
| montant | decimal(2) | > 0 |
| note | string | Libre |
| motif | string | Requis si montant modifié |

**Réponse de succès :** [VÉRIFIÉ PAR EXÉCUTION]
```json
{
  "id": "770e8400-e29b-41d4-a716-446655440000",
  "user_id": "550e8400-e29b-41d4-a716-446655440000",
  "client_id": "880e8400-e29b-41d4-a716-446655440000",
  "reseau_id": "550e8400-e29b-41d4-a716-446655440000",
  "caisse_session_id": "660e8400-e29b-41d4-a716-446655440000",
  "type_operation": "depot",
  "montant": 15000.00,
  "reference": "OM123456",
  "solde_apres_operation": 195000.00,
  "note": "Montant corrigé",
  "statut": "MODIFIEE",
  "sync_status": "SYNCED",
  "version": 2,
  "created_at": "2026-10-09T22:30:00.000000Z",
  "updated_at": "2026-10-09T22:35:00.000000Z",
  "modifiable": true,
  "client": {...},
  "reseau": {...},
  "user": {...}
}
```

**Réponses d'erreur :**
- **401 Unauthorized** : Jeton invalide ou expiré [LU DANS LE CODE]
- **403 Forbidden** : Accès refusé [LU DANS LE CODE]
- **404 Not Found** : Transaction introuvable [LU DANS LE CODE]
- **422 Validation Error - Transaction de session clôturée** : [VÉRIFIÉ PAR EXÉCUTION]
```json
{
  "success": false,
  "message": "Une erreur est survenue.",
  "errors": {},
  "meta": {
    "error": "Session clôturée : utilisez un ajustement de correction."
  }
}
```

**Règles métier :**
- Une transaction de session clôturée ne peut pas être modifiée
- Le solde est rejeu : l'ancien montant est annulé (sens inverse), le nouveau est appliqué (sens normal)
- La version est incrémentée
- L'historique est enregistré avec ancien/nouveau valeurs et motif

---

### 1. Annulation de transaction

**Rôle :** Annuler une transaction existante.

**Requête :**
- Méthode : `POST`
- URL : `/api/v1/transactions/{transaction}/cancel`
- En-têtes : `Authorization: Bearer {token}`, `Accept: application/json`, `Content-Type: application/json`
- Corps JSON :
```json
{
  "motif": "Client absent"
}
```

**Champs :**
| Champ | Type | Obligatoire ? | Règles de validation |
|-------|------|----------------|---------------------|
| motif | string | oui | Motif de l'annulation |

**Réponse de succès :** [VÉRIFIÉ PAR EXÉCUTION]
```json
{
  "id": "770e8400-e29b-41d4-a716-446655440000",
  "user_id": "550e8400-e29b-41d4-a716-446655440000",
  "client_id": "880e8400-e29b-41d4-a716-446655440000",
  "reseau_id": "550e8400-e29b-41d4-a716-446655440000",
  "caisse_session_id": "660e8400-e29b-41d4-a716-446655440000",
  "type_operation": "depot",
  "montant": 10000.00,
  "reference": "OM123456",
  "solde_apres_operation": 200000.00,
  "note": "Client régulier",
  "statut": "ANNULEE",
  "sync_status": "SYNCED",
  "version": 2,
  "created_at": "2026-10-09T22:30:00.000000Z",
  "updated_at": "2026-10-09T22:40:00.000000Z",
  "modifiable": false,
  "client": {...},
  "reseau": {...},
  "user": {...}
}
```

**Réponses d'erreur :**
- **401 Unauthorized** : Jeton invalide ou expiré [LU DANS LE CODE]
- **403 Forbidden** : Accès refusé [LU DANS LE CODE]
- **404 Not Found** : Transaction introuvable [LU DANS LE CODE]
- **422 Validation Error - Transaction de session clôturée** : [VÉRIFIÉ PAR EXÉCUTION]
```json
{
  "success": false,
  "message": "Une erreur est survenue.",
  "errors": {},
  "meta": {
    "error": "Session clôturée : utilisez un ajustement de correction."
  }
}
```

**Règles métier :**
- Une transaction de session clôturée ne peut pas être annulée
- Le solde est rejeu (sens inverse)
- Le statut passe à "ANNULEE"
- L'historique est enregistré avec motif

---

### 1. Historique de transaction

**Rôle :** Consulter l'historique complet des modifications d'une transaction.

**Requête :**
- Méthode : `GET`
- URL : `/api/v1/transactions/{transaction}/history`
- En-têtes : `Authorization: Bearer {token}`, `Accept: application/json`
- Corps : vide

**Réponse de succès :** [LU DANS LE CODE]
```json
{
  "success": true,
  "message": "Historique de transaction récupéré avec succès.",
  "data": [
    {
      "id": "990e8400-e29b-41d4-a716-446655440000",
      "transaction_id": "770e8400-e29b-41d4-a716-446655440000",
      "type_historique": "MODIFICATION",
      "ancien": {
        "montant": 10000.00,
        "note": "Client régulier"
      },
      "nouveau": {
        "montant": 15000.00,
        "note": "Montant corrigé"
      },
      "motif": "Erreur de saisie",
      "utilisateur_id": "550e8400-e29b-41d4-a716-446655440000",
      "created_at": "2026-10-09T22:35:00.000000Z"
    }
  ]
}
```

**Champs :**
| Champ | Type | Signification |
|-------|------|-------------|
| id | string (UUID) | Identifiant de l'historique |
| transaction_id | string (UUID) | ID de la transaction concernée |
| type_historique | string | "CREATION", "MODIFICATION", "ANNULEE" |
| ancien | object | Anciennes valeurs des champs modifiés |
| nouveau | object | Nouvelles valeurs des champs modifiés |
| motif | string | Motif de l'opération |
| utilisateur_id | string (UUID) | ID de l'utilisateur à l'origine |
| created_at | string (datetime) | Date/heure de l'historique |

**Réponses d'erreur :**
- **401 Unauthorized** : Jeton invalide ou expiré [LU DANS LE CODE]
- **403 Forbidden** : Accès refusé [LU DANS LE CODE]
- **404 Not Found** : Transaction introuvable [LU DANS LE CODE]

---

## 2. Valeurs fournies par le serveur : le mobile ne doit pas les recalculer

| Valeur | Endpoint et champ qui la fournit | Formule appliquée côté serveur | Moment où elle est disponible |
|--------|--------------------------------|------------------------------|-------------------------|
| Solde théorique float et cash | GET /api/v1/caisse/sessions/{session}/bilan → session.solde_float_theorique, session.solde_cash_theorique | solde_*_ouverture + Σ(transactions hors ANNULEE avec signes) + Σ(ajustements dans fenêtre) | Session ouverte ou clôturée |
| Écarts d'ouverture (float et cash) | POST /api/v1/caisse/sessions/ouvrir → meta.ecart_float, meta.ecart_cash ; GET /api/v1/caisse/sessions/{session}/bilan → session.ecart_float_ouverture, session.ecart_cash_ouverture | solde_déclaré - solde_reel dernière clôture | Ouverture (enregistré) |
| Écarts de clôture (float et cash) | POST /api/v1/caisse/sessions/{session}/cloturer → ecart_float, ecart_cash ; GET /api/v1/caisse/sessions/{session}/bilan → session.ecart_float, session.ecart_cash | solde_théorique - solde_reel | Clôture (enregistré) |
| Solde suggéré à l'ouverture | GET /api/v1/mes-reseaux → data[].solde_float_suggere, data[].solde_cash_suggere | solde_reel de la dernière session clôturée du même agent_reseau, null si aucune | À tout moment (lecture réseaux) |
| Seuil de justification | 422 → meta.seuil_ecart | Config (config('caisse seuil_ecart')) ou 500 par défaut | À tout moment (dans les réponses 422) |
| Totaux dépôts/retraits/ajustements | GET /api/v1/transactions → meta.summary.total_depots, meta.summary.total_retraits ; GET /api/v1/caisse/sessions/{session}/bilan → resume.total_depots, resume.total_retraits, resume.total_ajustements_float, resume.total_ajustements_cash | Σ(montant) WHERE type_operation=depot/retrait/ajustement ; tous hors ANNULEE | À tout moment (lecture) |
| Solde actuel d'un réseau | GET /api/v1/caisse/solde → data[].solde_float, data[].solde_cash | agent_reseaux.solde_float, agent_reseaux.solde_cash | À tout moment (lecture) |

---

## 3. Règles de justification et de seuil

### Quand la justification est exigée

**À l'ouverture :**
- La justification est exigée si l'écart float OU l'écart calculé (par rapport à la dernière clôture) dépasse le seuil
- Si aucune session clôturée n'existe (première ouverture), aucun écart n'est calculé et aucune justification n'est demandée
- L'écart est calculé : solde_déclaré - solde_reel_dernière_clôture

**À la clôture :**
- La justification est exigie si l'écart calculé (théorique - compté) dépasse le seuil
- L'écart est calculé : solde_théorique - solde_reel

**Évaluation :**
- Float et cash sont évalués séparément (OU) : si l'un OU l'autre dépasse le seuil, la justification est requise
- Le seuil est strict (> et non >=)

**Source du seuil :**
- Configuration : `config('caisse.seuil_ecart')` ou 500 par défaut [LU DANS LE CODE]

**Exposition au mobile :**
- OUI, le seuil est exposé dans les réponses 422 via `meta.seuil_ecart` [VÉRIFIÉ PAR EXÉCUTION]

**Exemples chiffrés (seuil = 500 FCFA) :**

| Cas | Écart float | Écart cash | Justification requise ? |
|-----|-----------|-----------|------------------------|
| Sans écart | 0 | 0 | Non |
| Écart ≤ seuil | 300 | 200 | Non |
| Écart > seuil sur cash seul | 0 | 600 | OUI |
| Écart > seuil sur float seul | 600 | 0 | OUI |
| Écart > seuil sur les deux avec même signe | 600 | 600 | OUI |
| Écart > seuil sur les deux avec signes opposés | 600 | -600 | OUI (l'un des deux dépasse le seuil) |

---

## 4. Cycle de vie d'une session

### États possibles
- **ouverte** : Session en cours, transactions possibles
- **cloturee** : Session clôturée, figée

### Transitions autorisées
- ouverte → cloturee : si écart accepté (avec justification si > seuil)
- Aucune autre transition

### Transitions refusées
- cloturee → cloturee : re-clôture refusée [VÉRIFIÉ PAR EXÉCUTION]
- ouverte → ouverte : ouverture refusée si session déjà ouverte [VÉRIFIÉ PAR EXÉCUTION]

### Ce qui change à la clôture
- Les champs suivants sont figés : solde_float_ouverture, solde_cash_ouverture, ecart_float_ouverture, ecart_cash_ouverture, justification_ouverture
- Les soldes théoriques et réels sont enregistrés
- Les transactions et ajustements sont figés (ne peuvent plus être modifiés/annulés)

### Comment savoir si une transaction est modifiable
Le mobile doit utiliser le champ `modifiable` fourni dans la ressource de transaction :
- Si `transaction.modifiable === false` → la transaction n'est pas modifiable
- Si `transaction.modifiable === true` → la transaction est modifiable

**Règle :** Une transaction est modifiable si :
- Son statut n'est pas "ANNULEE"
- ET (elle n'a pas de session OU elle est rattachée à une session ouverte)

**Champ à vérifier :** `modifiable` dans toutes les réponses de transaction (liste, détail, création, modification, annulation)

---

## 5. Types TypeScript

### ApiResponse
```typescript
interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data?: T;
  meta?: {
    pagination?: {
      current_page: number;
      last_page: number;
      per_page: number;
      total: number;
    };
    summary?: {
      count: number;
      total: number;
      total_depots: number;
      total_retraits: number;
    };
  };
}
```

### SessionOuverte
```typescript
interface SessionOuverte {
  id: string;
  date: string; // YYYY-MM-DD
  solde_float_ouverture: number;
  solde_cash_ouverture: number;
  statut: "ouverte";
  ouverte_at: string; // ISO 8601
  meta?: {
    solde_float_precedent: number | null;
    solde_cash_precedent: number | null;
    ecart_float: number | null;
    ecart_cash: number | null;
  };
}
```

### SessionCloturee
```typescript
interface SessionCloturee extends SessionOuverte {
  statut: "cloturee";
  cloturee_at: string; // ISO 8601
  solde_float_theorique: number;
  solde_cash_theorique: number;
  solde_float_reel: number;
  solde_cash_reel: number;
  ecart_float: number;
  ecart_cash: number;
  justification: string | null;
}
```

### BilanSession
```typescript
interface BilanSession {
  session: {
    id: string;
    date: string;
    reseau: {
      id: string;
      nom: string;
      code: string;
    };
    solde_float_ouverture: number;
    solde_cash_ouverture: number;
    ecart_float_ouverture: number | null;
    ecart_cash_ouverture: number | null;
    justification_ouverture: string | null;
    solde_float_theorique: number;
    solde_cash_theorique: number;
    solde_float_reel: number | null;
    solde_cash_reel: number | null;
    ecart_float: number | null;
    ecart_cash: number | null;
    justification: string | null;
    statut: "ouverte" | "cloturee";
    ouverte_at: string;
    cloturee_id?: string;
  };
  transactions: Transaction[];
  ajustements: Ajustement[];
  resume: {
    nombre_transactions: number;
    total_depots: number;
    total_retraits: number;
    nombre_ajustements: number;
    total_ajustements_float: number;
    total_ajustements_cash: number;
  };
}
```

### Transaction
```typescript
interface Transaction {
  id: string;
  user_id: string;
  client_id: string;
  reseau_id: string;
  caisse_session_id: string | null;
  type_operation: "depot" | "retrait";
  montant: number;
  reference: string;
  solde_apres_operation: number;
  note: string | null;
  statut: "ENREGISTREE" | "MODIFIEE" | "ANNULEE";
  sync_status: "PENDING" | "SYNCED" | "FAILED";
  version: number;
  created_at: string;
  updated_at: string;
  modifiable: boolean;
  client: {
    id: string;
    nom: string;
    prenoms: string;
    telephone: string;
  };
  reseau: {
    id: string;
    nom: string;
    code: string;
  };
  user: {
    id: string;
    name: string;
  };
}
```

### HistoriqueTransaction
```typescript
interface HistoriqueTransaction {
  id: string;
  transaction_id: string;
  type_historique: "CREATION" | "MODIFICATION" | "ANNULEE";
  ancien: Record<string, any>;
  nouveau: Record<string, any>;
  motif: string;
  utilisateur_id: string;
  created_at: string;
}
```

### AjustementCaisse
```typescript
interface AjustementCaisse {
  id: string;
  agent_reseau_id: string;
  type: string;
  montant_float: number;
  montant_cash: number;
  motif: string;
  created_at: string;
}
```

### ReseauAgent
```typescript
interface ReseauAgent {
  id: string;
  nom: string;
  code: string;
  logo: string | null;
  actif: boolean;
  solde_float: number;
  solde_cash: number;
  solde_float_suggere: number | null;
  solde_cash_suggere: number | null;
}
```

---

## Manques dans le contrat

| Valeur | Pourquoi elle pourrait être attendue | Statut |
|--------|----------------------------------|--------|
| Seuil de justification dans l'endpoint des réseaux | Le mobile pourrait vouloir savoir à l'avance le seuil pour adapter son comportement | NON VÉRIFIÉ - Le seuil n'est pas exposé dans la réponse de /api/v1/mes-reseaux (seul dans les réponses 422) |
| Détail de l'agent dans les réponses transaction | Le mobile pourrait vouloir afficher qui a créé une transaction | NON VÉRIFIÉ - L'endpoint /api/v1/transactions/{transaction} n'inclut pas l'agent mais peut être ajouté facilement si demandé |
| URL de téléchargement du PDF dans la réponse | Le mobile pourrait vouloir avoir l'URL directe du PDF généré | NON VÉRIFIÉ - L'endpoint renvoie directement le fichier en pièce jointe (Content-Disposition: attachment) |
| Liste des réseaux disponibles (pas seulement ceux de l'agent) | Le mobile pourrait vouloir proposer l'activation de nouveaux réseaux | NON VÉRIFIÉ - Il existe un endpoint /api/v1/reseaux pour la liste complète, mais le mobile pourrait ne l'utiliser pas |

---

## Incertitudes et non-vérifié

- Comportement réel du mobile React Native (n'a pas pu inspecter le code mobile)
- Intégration réelle des champs `solde_*_suggere` dans l'interface d'ouverture
- Utilisation effective des stats `total_depots`/`total_retraits` dans l'écran transactions
- Application mobile actuellement déployée et utilisée par des agents réels
- Performance de l'OCR CNIB en conditions réelles
- Capacité du système à gérer un volume élevé de transactions
- Gestion des conflits si l'agent ouvre une session sur plusieurs appareils simultanément
- Test de charge sur le endpoint d'export PDF
- Validation de tous les filtres de l'endpoint transactions en conditions réelles
