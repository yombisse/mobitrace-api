# Prompt Intégration Mobile - Module Caisse

## CONTEXTE
Backend Laravel MobiTrace v13.31 avec module caisse amélioré. Ce prompt décrit les changements backend à intégrer dans l'application React Native mobile pour offrir une expérience utilisateur riche et alignée.

---

## CHANGEMENTS BACKEND À INTÉGRER

### 1. Contrôle de continuité à l'ouverture de session

**Nouveau comportement backend :**
- Lors de l'ouverture d'une session, le backend compare les soldes déclarés avec `solde_*_reel` de la dernière session clôturée
- Si l'écart (float OU cash) dépasse le seuil configuré (500 FCFA par défaut), le champ `justification` devient obligatoire
- Sans justification → 422 avec meta contenant les détails de l'écart
- Avec justification → 200 et enregistrement des écarts/justification

**Champs API concernés :**
- `POST /api/v1/caisse/sessions` (ouverture)
- Champs requis : `reseau_id`, `solde_float_ouverture`, `solde_cash_ouverture`
- Champ optionnel : `justification` (string, nullable)

**Réponse succès (200) :**
```json
{
  "success": true,
  "message": "Session de caisse ouverte avec succès.",
  "data": {
    "id": "uuid",
    "date": "2026-10-09",
    "solde_float_ouverture": 200000,
    "solde_cash_ouverture": 200000,
    "statut": "ouverte",
    "ouverte_at": "2026-10-09T22:25:27",
    "meta": {
      "solde_float_precedent": 215000,
      "solde_cash_precedent": 835000,
      "ecart_float": -15000,
      "ecart_cash": -635000
    }
  }
}
```

**Réponse erreur (422) - justification manquante :**
```json
{
  "success": false,
  "message": "Les données fournies sont invalides.",
  "errors": {
    "justification": ["La justification est obligatoire car l'écart dépasse le seuil de 500."]
  },
  "meta": {
    "solde_float_precedent": 215000,
    "solde_cash_precedent": 835000,
    "solde_float_declare": 200000,
    "solde_cash_declare": 200000,
    "ecart_float": -15000,
    "ecart_cash": -635000,
    "seuil_ecart": 500
  }
}
```

---

### 2. Préremplissage des soldes à l'ouverture

**Nouveau comportement backend :**
- L'endpoint `GET /api/v1/reseaux` retourne maintenant `solde_float_suggere` et `solde_cash_suggere`
- Ces valeurs correspondent au `solde_*_reel` de la dernière session clôturée pour chaque réseau
- Si aucune session clôturée n'existe, ces valeurs sont `null` (au lieu de 0)

**Réponse GET /api/v1/reseaux :**
```json
{
  "success": true,
  "message": "Réseaux récupérés avec succès.",
  "data": [
    {
      "id": "uuid",
      "nom": "Telecel Money",
      "code": "TM",
      "logo": "url",
      "actif": true,
      "solde_float": 185000,
      "solde_cash": 215000,
      "solde_float_suggere": 215000,  // Dernière clôture
      "solde_cash_suggere": 835000   // Dernière clôture
    },
    {
      "id": "uuid",
      "nom": "Orange Money",
      "code": "OM",
      "logo": "url",
      "actif": false,
      "solde_float": 0,
      "solde_cash": 0,
      "solde_float_suggere": null,  // Pas de clôture
      "solde_cash_suggere": null
    }
  ]
}
```

---

### 3. Stats par type d'opération dans l'endpoint transactions

**Nouveau comportement backend :**
- `GET /api/v1/transactions` retourne maintenant `total_depots` et `total_retraits` dans le summary
- Ces valeurs excluent les transactions ANNULEE

**Réponse GET /api/v1/transactions :**
```json
{
  "success": true,
  "message": "Opération effectuée avec succès.",
  "data": [...],
  "meta": {
    "pagination": {...},
    "summary": {
      "count": 10,
      "total": 250000,
      "total_depots": 150000,
      "total_retraits": 100000
    }
  }
}
```

---

### 4. Bilan de session avec champs d'ouverture

**Nouveau comportement backend :**
- `GET /api/v1/caisse/sessions/{id}/bilan` expose maintenant les champs d'ouverture
- Pour une session ouverte, `solde_*_theorique` est calculé dynamiquement

**Réponse GET /api/v1/caisse/sessions/{id}/bilan :**
```json
{
  "success": true,
  "message": "Bilan de session récupéré avec succès.",
  "data": {
    "session": {
      "id": "uuid",
      "solde_float_ouverture": 200000,
      "solde_cash_ouverture": 200000,
      "ecart_float_ouverture": -15000,
      "ecart_cash_ouverture": -635000,
      "justification_ouverture": "Nouvelle déclaration de stock",
      "solde_float_theorique": 185000,
      "solde_cash_theorique": 215000,
      "solde_float_reel": null,
      "solde_cash_reel": null,
      "ecart_float": null,
      "ecart_cash": null,
      "justification": null,
      "statut": "ouverte",
      "ouverte_at": "2026-10-09T22:25:27",
      "cloturee_at": null
    },
    "transactions": [...],
    "ajustements": [...],
    "resume": {...}
  }
}
```

---

## SPÉCIFICATIONS MOBILE

### 1. Écran d'ouverture de session

**Flux utilisateur :**
1. L'utilisateur sélectionne un réseau dans la liste des réseaux
2. L'écran préremplit les champs solde_float et solde_cash avec `solde_*_suggere` de l'API
3. Si `solde_*_suggere` est null, les champs sont vides (ou préremplis avec 0 selon choix UX)
4. L'utilisateur modifie si nécessaire et valide

**Validation côté mobile :**
- Avant envoi : calculer l'écart local avec `solde_*_suggere` (si disponible)
- Si l'écart dépasse 500 FCFA, afficher un message invitant à ajouter une justification
- Rendre le champ justification visible/obligatoire dans ce cas
- Envoyer `justification` dans le payload si l'écart dépasse le seuil

**Gestion de la réponse 422 :**
- Si l'API retourne 422 avec `meta`, afficher une alerte claire avec :
  - Soldes précédents
  - Soldes déclarés
  - Écarts calculés
  - Seuil
- Permettre à l'utilisateur de modifier les soldes ou ajouter une justification
- Afficher le champ justification déjà rempli si l'utilisateur l'avait saisi

**Implémentation React Native :**
```typescript
interface OpeningSessionData {
  reseau_id: string;
  solde_float_ouverture: number;
  solde_cash_ouverture: number;
  justification?: string;
}

// Après récupération des réseaux
const soldeFloatSuggere = reseau.solde_float_suggere || 0;
const soldeCashSuggere = reseau.solde_cash_suggere || 0;

// Préremplissage
const [soldeFloat, setSoldeFloat] = useState(soldeFloatSuggere);
const [soldeCash, setSoldeCash] = useState(soldeCashSuggere);
const [justification, setJustification] = useState('');

// Détection écart
const ecartFloat = soldeFloat - soldeFloatSuggere;
const ecartCash = soldeCash - soldeCashSuggere;
const seuil = 500;
const justificationRequise = Math.abs(ecartFloat) > seuil || Math.abs(ecartCash) > seuil;
```

---

### 2. Écran de liste des transactions (stats)

**Flux utilisateur :**
1. L'utilisateur consulte l'écran des transactions
2. Le mobile récupère les transactions via `GET /api/v1/transactions`
3. Le mobile affiche les stats dans un composant récapitulatif

**Affichage des stats :**
- Total des dépôts : afficher `meta.summary.total_depots`
- Total des retraits : afficher `meta.summary.total_retraits`
- Nombre de transactions : afficher `meta.summary.count`
- Solde net (optionnel) : `total_depots - total_retraits`

**Implémentation React Native :**
```typescript
interface TransactionSummary {
  count: number;
  total: number;
  total_depots: number;
  total_retraits: number;
}

// Composant de stats
const TransactionStats = ({ summary }: { summary: TransactionSummary }) => (
  <View style={styles.statsContainer}>
    <StatCard 
      label="Dépôts" 
      value={formatCurrency(summary.total_depots)} 
      icon="arrow-down" 
      color="green" 
    />
    <StatCard 
      label="Retraits" 
      value={formatCurrency(summary.total_retraits)} 
      icon="arrow-up" 
      color="red" 
    />
    <StatCard 
      label="Net" 
      value={formatCurrency(summary.total_depots - summary.total_retraits)} 
      icon="trending-up" 
      color="blue" 
    />
    <StatCard 
      label="Transactions" 
      value={summary.count.toString()} 
      icon="list" 
      color="gray" 
    />
  </View>
);
```

---

### 3. Écran de détail de session (bilan)

**Flux utilisateur :**
1. L'utilisateur consulte le détail d'une session
2. Le mobile récupère le bilan via `GET /api/v1/caisse/sessions/{id}/bilan`
3. Le mobile affiche les informations de la session, y compris les écarts d'ouverture

**Affichage des champs d'ouverture :**
- Si `ecart_float_ouverture` ou `ecart_cash_ouverture` != null, afficher une section "Écarts d'ouverture"
- Afficher les soldes d'ouverture et les écarts calculés
- Afficher la justification si présente

**Implémentation React Native :**
```typescript
interface SessionBilan {
  session: {
    solde_float_ouverture: number;
    solde_cash_ouverture: number;
    ecart_float_ouverture: number | null;
    ecart_cash_ouverture: number | null;
    justification_ouverture: string | null;
    solde_float_theorique: number;
    solde_cash_theorique: number;
    solde_float_reel: number | null;
    solde_cash_reel: number | null;
    ...
  };
}

// Composant d'affichage des écarts d'ouverture
const OpeningEcartSection = ({ session }: { session: SessionBilan['session'] }) => {
  if (session.ecart_float_ouverture === null && session.ecart_cash_ouverture === null) {
    return null;
  }

  return (
    <View style={styles.ecartSection}>
      <Text style={styles.sectionTitle}>Écarts d'ouverture</Text>
      <Row label="Float précédent" value={formatCurrency(session.solde_float_ouverture - session.ecart_float_ouverture)} />
      <Row label="Cash précédent" value={formatCurrency(session.solde_cash_ouverture - session.ecart_cash_ouverture)} />
      <Row label="Écart Float" value={formatCurrency(session.ecart_float_ouverture)} color={session.ecart_float_ouverture < 0 ? 'red' : 'green'} />
      <Row label="Écart Cash" value={formatCurrency(session.ecart_cash_ouverture)} color={session.ecart_cash_ouverture < 0 ? 'red' : 'green'} />
      {session.justification_ouverture && (
        <Row label="Justification" value={session.justification_ouverture} multiline />
      )}
    </View>
  );
};
```

---

## CAS PARTICULIERS À GÉRER

### 1. Première ouverture (pas de session précédente)
- `solde_*_suggere` = null
- Pas de comparaison d'écart
- Justification non requise
- Mobile : préremplir avec 0 ou laisser vide

### 2. Écart inférieur ou égal au seuil
- Justification non requise
- Les écarts sont quand même enregistrés en base
- Mobile : pas de demande de justification, mais peut afficher un avertissement informatif

### 3. Écart sur un seul solde (float OU cash)
- La justification est requise si l'un des deux dépasse le seuil
- Mobile : détecter les deux écarts et demander justification si nécessaire

### 4. Session sans transactions
- `total_depots` = 0
- `total_retraits` = 0
- Mobile : afficher 0 dans les stats

---

## RECOMMANDATIONS UX

### 1. Ouverture de session
- Afficher clairement les soldes suggérés en gris/placeholder
- Calculer et afficher l'écart en temps réel pendant la saisie
- Masquer le champ justification par défaut, l'afficher si écart > seuil
- Utiliser des couleurs pour indiquer un écart positif (vert) ou négatif (rouge)

### 2. Stats transactions
- Placer le composant de stats en haut de l'écran (sticky ou fixe)
- Utiliser des icônes distinctives pour dépôts/retraits
- Permettre de filtrer par type d'opération et voir les stats se mettre à jour
- Afficher le solde net comme métrique principale

### 3. Bilan session
- Regrouper les écarts d'ouverture dans une section collapsible
- Mettre en évidence la justification si elle existe
- Colorer les écarts pour une lecture rapide

---

## TESTS À IMPLÉMENTER (MOBILE)

### 1. Tests d'ouverture de session
- [ ] Ouverture sans écart → validation réussie sans justification
- [ ] Ouverture avec écart > seuil sans justification → message d'erreur avec détails
- [ ] Ouverture avec écart > seuil avec justification → validation réussie
- [ ] Ouverture avec écart ≤ seuil → validation réussie sans justification
- [ ] Première ouverture (pas de suggestion) → champs vides
- [ ] Écart sur cash seul → justification demandée
- [ ] Écart sur float seul → justification demandée

### 2. Tests de stats transactions
- [ ] Affichage correct des total_depots et total_retraits
- [ ] Exclusion des transactions ANNULEE des stats
- [ ] Mise à jour des stats lors du filtrage
- [ ] Calcul correct du solde net

### 3. Tests de bilan session
- [ ] Affichage des écarts d'ouverture
- [ ] Affichage de la justification d'ouverture
- [ ] Cas sans écarts → section masquée
- [ ] Solde théorique dynamique pour session ouverte

---

## SÉCURITÉ

- Ne jamais exposer le seuil côté mobile (il peut changer côté backend)
- Valider toujours la réponse 422 côté mobile avant d'afficher
- Ne jamais cacher les erreurs API sans informer l'utilisateur
- Toujours afficher les soldes suggérés en clair pour transparence
