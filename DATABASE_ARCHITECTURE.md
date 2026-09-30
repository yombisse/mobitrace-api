# Architecture de la Base de Données MobiTrace API

## Informations Générales

- **SGBD** : PostgreSQL
- **Version Laravel** : 13.17
- **Version PHP** : 8.4.20
- **Type de clés primaires** : UUID (Universally Unique Identifier)
- **Soft Deletes** : Activé sur plusieurs tables
- **Transactions** : Actives

---

## Schéma des Tables

### 1. Table `users`

**Description** : Table des utilisateurs/agents Mobile Money

#### Champs

| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| `id` | UUID | PRIMARY KEY | Identifiant unique de l'utilisateur |
| `name` | string | NOT NULL | Nom complet de l'utilisateur |
| `email` | string | UNIQUE, NULLABLE | Adresse email (nullable depuis migration) |
| `email_verified_at` | timestamp | NULLABLE | Date de vérification de l'email |
| `password` | string | NOT NULL | Mot de passe hashé |
| `code_agent` | string | UNIQUE, NULLABLE | Code unique de l'agent |
| `photo` | string | NULLABLE | URL de la photo de profil |
| `telephone` | string | UNIQUE, NOT NULL | Numéro de téléphone |
| `localisation_point` | string | NULLABLE | Localisation du point de service |
| `remember_token` | string | NULLABLE | Token de "remember me" |
| `created_at` | timestamp | NOT NULL | Date de création |
| `updated_at` | timestamp | NOT NULL | Date de dernière modification |
| `deleted_at` | timestamp | NULLABLE | Date de suppression (soft delete) |

#### Relations
- **hasMany** : `clients` - Un utilisateur peut avoir plusieurs clients
- **hasMany** : `transactions` - Un utilisateur peut effectuer plusieurs transactions
- **hasMany** : `transaction_histories` - Un utilisateur peut avoir des historiques de transactions

---

### 2. Table `reseaux`

**Description** : Table des réseaux de Mobile Money (Orange Money, Moov Money, Wave, etc.)

#### Champs

| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| `id` | UUID | PRIMARY KEY | Identifiant unique du réseau |
| `nom` | string(100) | NOT NULL | Nom du réseau (ex: "Orange Money") |
| `code` | string(50) | UNIQUE, NOT NULL | Code du réseau (ex: "OM") |
| `logo` | string | NULLABLE | URL du logo du réseau |
| `deleted_at` | timestamp | NULLABLE | Date de suppression (soft delete) |

#### Particularités
- Pas de timestamps (`created_at`, `updated_at`)
- Le code peut être généré automatiquement à partir du nom si non fourni

#### Relations
- **hasMany** : `transactions` - Un réseau peut avoir plusieurs transactions
- **hasMany** : `transaction_reference_sequences` - Un réseau peut avoir plusieurs séquences de références

---

### 3. Table `clients`

**Description** : Table des clients des agents Mobile Money

#### Champs

| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| `id` | UUID | PRIMARY KEY | Identifiant unique du client |
| `user_id` | UUID | FOREIGN KEY, NOT NULL | Référence vers l'utilisateur (agent) |
| `telephone` | string(20) | NOT NULL | Numéro de téléphone du client |
| `nom` | string(100) | NULLABLE | Nom de famille du client |
| `prenoms` | string(150) | NULLABLE | Prénoms du client |
| `date_naissance` | date | NULLABLE | Date de naissance du client |
| `nationalite` | string(100) | NULLABLE | Nationalité du client |
| `type_piece` | string(50) | NULLABLE | Type de pièce d'identité (CNIB, CIB-AES) |
| `numero_piece` | string(50) | NULLABLE | Numéro de la pièce d'identité |
| `date_delivrance_piece` | date | NULLABLE | Date de délivrance de la pièce |
| `date_expiration_piece` | date | NULLABLE | Date d'expiration de la pièce |
| `cni_recto` | string | NULLABLE | URL de l'image recto de la CNI |
| `cni_verso` | string | NULLABLE | URL de l'image verso de la CNI |
| `created_at` | timestamp | NOT NULL | Date de création |
| `updated_at` | timestamp | NOT NULL | Date de dernière modification |
| `deleted_at` | timestamp | NULLABLE | Date de suppression (soft delete) |

#### Contraintes
- **UNIQUE** : `user_id` + `telephone` (un agent ne peut pas avoir deux clients avec le même numéro)
- **FOREIGN KEY** : `user_id` → `users.id` (CASCADE ON DELETE)

#### Relations
- **belongsTo** : `user` - Un client appartient à un utilisateur
- **hasMany** : `transactions` - Un client peut avoir plusieurs transactions

---

### 4. Table `transactions`

**Description** : Table des transactions Mobile Money (dépôts et retraits)

#### Champs

| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| `id` | UUID | PRIMARY KEY | Identifiant unique de la transaction |
| `user_id` | UUID | FOREIGN KEY, NOT NULL | Référence vers l'utilisateur (agent) |
| `client_id` | UUID | FOREIGN KEY, NULLABLE | Référence vers le client |
| `reseau_id` | UUID | FOREIGN KEY, NOT NULL | Référence vers le réseau |
| `type_operation` | string(10) | NOT NULL, CHECK | Type d'opération ('depot' ou 'retrait') |
| `montant` | decimal(12,2) | NOT NULL, CHECK (>0) | Montant de la transaction |
| `reference` | string(100) | NULLABLE | Référence unique de la transaction |
| `solde_apres_operation` | decimal(12,2) | NULLABLE | Solde après l'opération |
| `note` | text | NULLABLE | Note ou commentaire |
| `statut` | string(20) | NOT NULL, CHECK, DEFAULT | Statut ('ENREGISTREE', 'MODIFIEE', 'ANNULEE') |
| `sync_status` | string(20) | NOT NULL, CHECK, DEFAULT | Statut de synchronisation ('SYNCED', 'PENDING', 'FAILED') |
| `version` | unsigned int | NOT NULL, DEFAULT 1 | Version pour le suivi des modifications |
| `synced_at` | timestamp | NULLABLE | Date de synchronisation |
| `consentement_recap` | text | NULLABLE | Récapitulatif du consentement |
| `consentement_methode` | string(50) | DEFAULT 'confirmation_client' | Méthode de consentement |
| `consentement_confirme_le` | timestamp | NULLABLE | Date de confirmation du consentement |
| `created_at` | timestamp | NOT NULL | Date de création |
| `updated_at` | timestamp | NOT NULL | Date de dernière modification |

#### Contraintes CHECK
- `type_operation` IN ('depot', 'retrait')
- `montant` > 0
- `statut` IN ('ENREGISTREE', 'MODIFIEE', 'ANNULEE')
- `sync_status` IN ('SYNCED', 'PENDING', 'FAILED')

#### Contraintes FOREIGN KEY
- `user_id` → `users.id` (RESTRICT ON DELETE)
- `client_id` → `clients.id` (RESTRICT ON DELETE)
- `reseau_id` → `reseaux.id` (RESTRICT ON DELETE)

#### Index
- `user_id`
- `client_id`
- `reseau_id`
- `created_at`

#### Relations
- **belongsTo** : `user` - Une transaction appartient à un utilisateur
- **belongsTo** : `client` - Une transaction est liée à un client
- **belongsTo** : `reseau` - Une transaction utilise un réseau
- **hasMany** : `histories` - Une transaction peut avoir plusieurs historiques

---

### 5. Table `transaction_histories`

**Description** : Table d'historique des modifications de transactions

#### Champs

| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| `id` | UUID | PRIMARY KEY | Identifiant unique de l'historique |
| `transaction_id` | UUID | FOREIGN KEY, NOT NULL | Référence vers la transaction |
| `user_id` | UUID | FOREIGN KEY, NOT NULL | Référence vers l'utilisateur |
| `action` | string(20) | NOT NULL, CHECK | Type d'action ('CREATION', 'MODIFICATION', 'ANNULATION') |
| `anciennes_donnees` | jsonb | NULLABLE | Données avant modification |
| `nouvelles_donnees` | jsonb | NULLABLE | Données après modification |
| `motif` | text | NULLABLE | Motif de la modification |
| `created_at` | timestamp | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Date de création |

#### Contraintes CHECK
- `action` IN ('CREATION', 'MODIFICATION', 'ANNULATION')

#### Contraintes FOREIGN KEY
- `transaction_id` → `transactions.id` (CASCADE ON DELETE)
- `user_id` → `users.id` (RESTRICT ON DELETE)

#### Index
- `transaction_id`

#### Particularités
- Pas de `updated_at` (constante définie à null)
- `created_at` utilise `useCurrent()` par défaut

#### Relations
- **belongsTo** : `transaction` - Un historique appartient à une transaction
- **belongsTo** : `user` - Un historique est créé par un utilisateur

---

### 6. Table `transaction_reference_sequences`

**Description** : Table pour la génération séquentielle des références de transactions par réseau et date

#### Champs

| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| `id` | UUID | PRIMARY KEY | Identifiant unique de la séquence |
| `reseau_id` | UUID | FOREIGN KEY, NOT NULL | Référence vers le réseau |
| `reference_date` | date | NOT NULL | Date de référence pour la séquence |
| `last_sequence` | unsigned int | NOT NULL, DEFAULT 0 | Dernier numéro de séquence utilisé |
| `created_at` | timestamp | NOT NULL | Date de création |
| `updated_at` | timestamp | NOT NULL | Date de dernière modification |

#### Contraintes
- **UNIQUE** : `reseau_id` + `reference_date` (une séquence par réseau et par date)

#### Contraintes FOREIGN KEY
- `reseau_id` → `reseaux.id` (RESTRICT ON DELETE)

#### Relations
- **belongsTo** : `reseau` - Une séquence appartient à un réseau

---

### 7. Table `password_reset_codes`

**Description** : Table pour la gestion des codes de réinitialisation de mot de passe

#### Champs

| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| `id` | UUID | PRIMARY KEY | Identifiant unique du code |
| `email` | string | NOT NULL, INDEX | Adresse email de l'utilisateur |
| `code` | string | NOT NULL | Code de réinitialisation |
| `expires_at` | timestamp | NOT NULL, INDEX | Date d'expiration du code |
| `used_at` | timestamp | NULLABLE | Date d'utilisation du code |
| `created_at` | timestamp | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Date de création |

#### Index
- `email`
- `expires_at`
- `email` + `expires_at` (composite)

---

### 8. Tables Système Laravel

#### `cache`
- Table de cache Laravel
- Champs : `key`, `value`, `expiration`

#### `cache_locks`
- Table de verrous de cache Laravel
- Champs : `key`, `owner`, `expiration`

#### `sessions`
- Table de sessions Laravel
- Champs : `id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`

#### `personal_access_tokens`
- Table des tokens d'API Laravel Sanctum
- Champs : `id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`

---

## Diagramme des Relations

```
users (1) ──────< (N) clients
users (1) ──────< (N) transactions
users (1) ──────< (N) transaction_histories

clients (1) ──────< (N) transactions

reseaux (1) ──────< (N) transactions
reseaux (1) ──────< (N) transaction_reference_sequences

transactions (1) ──────< (N) transaction_histories
```

---

## Contraintes d'Intégrité

### Soft Deletes
Les tables suivantes utilisent le soft delete (marquage comme supprimé sans suppression réelle) :
- `users`
- `clients`
- `reseaux`

### Cascade Operations
- **CASCADE ON DELETE** : `clients.user_id` (suppression d'un utilisateur supprime ses clients)
- **RESTRICT ON DELETE** : `transactions.user_id`, `transactions.client_id`, `transactions.reseau_id` (empêche la suppression si des transactions existent)

### Unicité
- `users.email` (unique)
- `users.telephone` (unique)
- `users.code_agent` (unique)
- `reseaux.code` (unique)
- `clients.user_id + clients.telephone` (composite unique)

---

## Contraintes CHECK PostgreSQL

### Table `transactions`
- `type_operation` : uniquement 'depot' ou 'retrait'
- `montant` : doit être supérieur à 0
- `statut` : uniquement 'ENREGISTREE', 'MODIFIEE' ou 'ANNULEE'
- `sync_status` : uniquement 'SYNCED', 'PENDING' ou 'FAILED'

### Table `transaction_histories`
- `action` : uniquement 'CREATION', 'MODIFICATION' ou 'ANNULATION'

---

## Index de Performance

### Index explicites
- `transactions.user_id`
- `transactions.client_id`
- `transactions.reseau_id`
- `transactions.created_at`
- `transaction_histories.transaction_id`
- `password_reset_codes.email`
- `password_reset_codes.expires_at`
- `password_reset_codes.email + expires_at`

### Index implicites (clés étrangères)
- `clients.user_id`
- `transactions.user_id`
- `transactions.client_id`
- `transactions.reseau_id`
- `transaction_histories.transaction_id`
- `transaction_histories.user_id`
- `transaction_reference_sequences.reseau_id`

---

## Types de Données Utilisés

- **UUID** : Identifiants uniques universels
- **string** : Textes de longueur variable
- **string(n)** : Textes avec longueur maximale
- **text** : Textes de longueur illimitée
- **decimal(12,2)** : Montants monétaires (12 chiffres au total, 2 décimales)
- **date** : Dates sans heure
- **timestamp** : Dates et heures
- **jsonb** : Données JSON binaires (PostgreSQL)
- **unsigned int** : Entiers non signés

---

## Particularités Métier

### Gestion des Clients
- Un agent ne peut pas avoir deux clients avec le même numéro de téléphone
- Les données d'identité sont optionnelles (nullable) pour permettre les transactions sans CNIB complète
- Les images CNI (recto/verso) sont stockées comme URLs

### Gestion des Transactions
- Les montants doivent être positifs
- Les transactions peuvent être modifiées (statut 'MODIFIEE') ou annulées ('ANNULEE')
- Le versionning permet de suivre les modifications
- Le consentement client est tracé avec la méthode et la date

### Gestion des Réseaux
- Le code réseau peut être généré automatiquement à partir du nom
- Support des mots composés (ex: "LiguidiCash" → "LC")

### Gestion de la Synchronisation
- Les transactions peuvent être en attente de synchronisation ('PENDING')
- Le statut de synchronisation est tracé indépendamment du statut métier

---

## Sécurité et Confidentialité

### Données Sensibles
- **Mots de passe** : Hashés (non stockés en clair)
- **Tokens d'API** : Gérés par Laravel Sanctum
- **Emails** : Nullable pour permettre l'authentification par téléphone uniquement
- **Données personnelles** : Nom, prénoms, date de naissance, numéro de pièce d'identité
- **Images CNI** : Stockées comme URLs (non directement dans la base)

### Contrôles d'Accès
- Les relations sont protégées par des contraintes RESTRICT pour éviter les suppressions accidentelles
- Le soft delete permet de récupérer des données supprimées par erreur

---

## Recommandations

### Pour l'avenir
1. **Archivage** : Implémenter une politique d'archivage pour les transactions anciennes
2. **Audit** : Étendre le système d'historique pour inclure plus de métadonnées
3. **Performance** : Surveiller les index pour les requêtes fréquentes
4. **Backup** : Mettre en place des sauvegardes régulières avec rotation
5. **Compression** : Considérer la compression des champs jsonb pour les données volumineuses

### Maintenance
- Nettoyage régulier des tokens expirés (`personal_access_tokens`)
- Nettoyage des codes de réinitialisation expirés (`password_reset_codes`)
- Monitoring de l'espace disque pour les images CNI stockées

---

## Date du Document
30 septembre 2026

## Version de l'Audit
1.0 - Architecture complète de la base de données MobiTrace API
