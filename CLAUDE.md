# CLAUDE.md — GesPerES

> Ce fichier est lu automatiquement par Claude Code à chaque session.
> Il contient tout le contexte nécessaire pour travailler sur ce projet sans explication répétée.

---

## 🎯 Présentation du projet

**GesPerES** = Gestion du Personnel Enseignant du Secondaire
**Maître d'ouvrage** : MESFPTT — Ministère de l'Enseignement Secondaire, de la Formation Professionnelle et Technique / Burkina Faso
**Objectif** : Application RH administrative pour gérer les agents enseignants du secondaire (dossiers, affectations, carrière, documents, statistiques).

---

## 🧰 Stack technique

| Élément | Valeur |
|---|---|
| Framework | Laravel 12 |
| PHP | ^8.2 |
| Base de données | MySQL 8 |
| Frontend | Blade + Tailwind CSS 3 + Alpine.js |
| Auth | Laravel Breeze (Blade) |
| RBAC | spatie/laravel-permission ^6.9 |
| Audit | spatie/laravel-activitylog ^4.8 |
| Import/Export | maatwebsite/excel ^3.1 |
| PDF | barryvdh/laravel-dompdf ^3.0 |
| Graphiques | ApexCharts (CDN, pas npm) |
| Build | Vite + npm |
| Tests | PHPUnit ^11 |
| Environnement dev | Laragon (Windows) |

---

## 📁 Architecture des dossiers

```
app/
├── Enums/              # Constantes typées (Sexe, StatutDossier, RoleName…)
├── Exports/            # Classes maatwebsite (AgentsExport)
├── Imports/            # Classes maatwebsite (AgentsImport)
├── Http/
│   ├── Controllers/    # Un contrôleur par module + Auth/
│   └── Requests/       # FormRequest pour chaque action (Store/Update)
├── Models/             # Eloquent, SoftDeletes, Auditable
├── Policies/           # Autorisation par modèle
├── Providers/          # AppServiceProvider, AuthServiceProvider
├── Services/           # Logique métier (AgentService, RetraiteService…)
└── Support/            # Traits et classes utilitaires (Permissions, ReferentielRegistry)

database/
├── factories/          # UserFactory, AgentFactory
├── migrations/         # 12 fichiers ordonnés (000100 à 000900)
└── seeders/            # RoleSeeder → ReferentielSeeder → UserSeeder

resources/views/
├── layouts/            # app.blade.php (sidebar + topbar), guest.blade.php
├── components/form/    # input, select, textarea (composants réutilisables)
├── partials/           # sidebar.blade.php, flash.blade.php
├── auth/               # login, forgot-password, reset-password
├── dashboard/          # index.blade.php (cartes + ApexCharts)
├── agents/             # index, show, create, edit, _form, import
├── affectations/       # index, create, show
├── audit/              # index
├── documents/          # index (liste + upload inline)
├── profile/            # edit
├── referentiels/       # index, show (générique config-driven)
├── roles/              # index, edit
├── structures/         # index, show, create, edit, _form, _noeud
└── users/              # index, create, edit, _form

routes/
├── web.php             # Toutes les routes protégées
└── auth.php            # Connexion / déconnexion / reset password
```

---

## 🗄️ Modèle de données — Tables principales

```
users                   → comptes applicatifs (roles via Spatie)
agents                  → dossier RH complet de chaque agent
structures              → arborescence hiérarchique (ministère → établissement)
affectations            → historique des mouvements agents
documents               → pièces jointes par agent (disk privé)
activity_log            → journal d'audit (spatie)

-- Suivi des dossiers (circuit administratif) --
natures_dossier         → référentiel des natures (+ délai par défaut), dérivé du MPP GRH (mpp_procedure_id)
suivi_dossiers          → dossier suivi (réf. bordereau, étape, statut, service/agent courant, délai)
suivi_dossier_etapes    → historique des transmissions (mouvements du dossier)

-- Plan de formation (planification pluriannuelle, d'après le Plan Triennal MFPTPS) --
plans_formation         → plan pluriannuel (vision, finalité, objectifs)
programmes_formation    → déclinaison annuelle (objectif stratégique, budget prévisionnel)
actions_formation       → actions planifiées (synthèse 1→7 : thème, modalité, public cible…)
besoins_formation       → recueil des besoins par agent (fiche Annexe 1)
-- Lien réalisation : formations.action_formation_id → suivi prévu vs réalisé

-- TPEE (Tableau Prévisionnel des Effectifs et des Emplois) --
previsions_effectifs    → hypothèses par emploi × année (entrées prévues + effectif cible),
                          portée structure_id nullable (NULL = national). Départs (retraite)
                          et effectif courant dérivés des agents (TpeeService), non stockés.

-- Référentiels rémunération --
categories, echelles, classes, echelons, indices

-- Référentiels emplois --
emplois, fonctions, postes, positions_administratives

-- Référentiels géo/enseignement --
regions, provinces, zones, localites, type_enseignements, specialites
-- Découpage : Région → Province → Localité (commune). Réforme MESFPTT :
-- « Kadiogo » (ex-Centre) et « Guiriko » (ex-Hauts-Bassins) n'ont pas de provinces
-- mais des Circonscriptions d'éducation (CESFPT OUAGA 1-5 / BOBO 1-4) stockées
-- dans la table provinces. L'agent porte region_id/province_id (FK) + localite_id,
-- et conserve region/province/commune en texte (synchronisés par AgentService).

-- Spatie --
roles, permissions, model_has_roles, model_has_permissions, role_has_permissions
```

---

## 👥 Rôles et permissions

### Rôles (App\Enums\RoleName)
| Valeur | Label |
|---|---|
| `super-admin` | Super Admin (tout via Gate::before) |
| `admin-national` | Administrateur national |
| `admin-regional` | Administrateur régional |
| `agent-rh` | Agent RH |
| `responsable-structure` | Responsable structure |
| `consultation` | Consultation |
| `agent-individuel` | Agent individuel |

### Catalogue des permissions (App\Support\Permissions)
```
dashboard.view
agents.view / create / update / delete / import / export
structures.view / create / update / delete
affectations.view / create / update / delete
carriere.view / carriere.manage
mouvements.view / mouvements.manage
indemnites.view / indemnites.manage
formations.view / formations.manage
competences.view / competences.manage
performance.view / performance.manage
discipline.view / discipline.manage
gpec.view
tpee.manage
suivi.view / suivi.manage
alertes.view
documents.view / upload / download / delete
users.view / create / update / delete
settings.view / settings.manage
audit.view
reports.view / reports.export
```

---

## ⚙️ Règles métier critiques

Ces règles sont dans `app/Services/AgentService.php` et ne doivent JAMAIS être contournées :

| # | Règle |
|---|---|
| R2 | La clé d'un agent est alphabétique, transformée en MAJUSCULE |
| R3 | Le sexe est strictement M ou F |
| R7 | La date de retraite est calculée automatiquement (date_naissance + âge légal par catégorie) |
| R8 | Le lieu d'exercice (En classe / Au bureau) est déduit de l'emploi (enseignant oui/non) |
| R9 | L'allocation familiale est calculée automatiquement (nombre_enfants × montant, plafonné) |
| R10 | Les volumes horaires et type d'enseignement ne concernent QUE les emplois enseignants |

Paramètres métier configurables dans `config/gesperes.php` et `.env` :
- `GESPERES_AGE_RETRAITE` (défaut : 60)
- `gesperes.retraite.alerte_mois_avant` (défaut : 24)
- `gesperes.allocation_familiale.montant_par_enfant` (défaut : 2000 FCFA)
- `gesperes.allocation_familiale.nombre_max_enfants` (défaut : 6)
- `gesperes.volume_horaire_defaut` (défaut : 18h)

---

## 🎨 Conventions UI/UX

- **Couleurs** : `institution` (bleu, défini dans tailwind.config.js) + `administ` (vert)
- **Classes CSS custom** (dans `resources/css/app.css`) :
  - `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-danger`
  - `.card` (bloc blanc avec ombre)
  - `.input`, `.label`
  - `.badge`
  - `.table-head`
- **Composants Blade réutilisables** : `<x-form.input>`, `<x-form.select>`, `<x-form.textarea>`
- **Layout principal** : `@extends('layouts.app')` + `@section('header', '...')` + `@section('content')`
- **Messages flash** : `session('success')` et `session('error')` via `@include('partials.flash')`
- **Formulaires** : toujours `@csrf`, méthode PUT/DELETE via `@method('PUT')`
- **Alpine.js** : disponible globalement pour les interactions légères (sidebar, dropdowns, tabs)
- **ApexCharts** : chargé via CDN uniquement dans les vues qui en ont besoin (`@push('scripts')`)

---

## 📏 Conventions de code

### Contrôleurs
- Un contrôleur = un module
- Méthodes : `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`
- Toujours injecter les services via le constructeur
- Toujours appeler `$this->authorize()` en première ligne
- Ne jamais mettre de logique métier dans un contrôleur → déléguer aux Services

### Services
- `app/Services/` pour toute logique métier non triviale
- Injectables via le constructeur (Laravel IoC)
- Testables unitairement sans base de données si possible

### FormRequests
- Un `StoreXxxRequest` et un `UpdateXxxRequest` par module
- La méthode `authorize()` vérifie la permission Spatie
- Toujours définir `messages()` ou `attributes()` pour des erreurs en français

### Modèles
- Toujours utiliser `SoftDeletes` pour les données sensibles
- Toujours utiliser le trait `Auditable` (app/Support/Auditable.php) pour la traçabilité
- Les enums PHP 8.1+ sont castés via `$casts`
- Les relations sont déclarées avec des noms explicites en camelCase français

### Migrations
- Nommage : `YYYY_MM_DD_HHMMNN_description.php`
- Toujours ajouter des index sur les colonnes de recherche fréquente
- Toujours utiliser `->nullOnDelete()` ou `->cascadeOnDelete()` explicitement
- Ne jamais modifier une migration existante → créer une nouvelle

### Routes
- Nommage : `module.action` (ex: `agents.index`, `agents.store`)
- Routes en français pour les URLs (ex: `/agents/creer`, `/structures`)
- Toutes les routes dans `routes/web.php` groupées par module

### Vues Blade
- Toujours `@extends('layouts.app')`
- Formulaires partiels dans `_form.blade.php`
- Composants réutilisables dans `resources/views/components/`
- Pas de PHP complexe dans les vues → passer les données depuis le contrôleur

---

## 🔒 Règles de sécurité ABSOLUES

1. **Ne jamais supprimer le dernier super-admin** → contrôle dans `UserPolicy::delete()`
2. **Ne jamais s'auto-supprimer** → contrôle dans `UserPolicy::delete()`
3. **Les documents sont sur un disque privé** (`documents` dans `config/filesystems.php`) → téléchargement via `DocumentController::download()` uniquement, jamais d'URL directe
4. **Toutes les routes sont protégées** par `middleware(['auth', 'verified'])`
5. **Les permissions sont vérifiées** via `$this->authorize()` ou `@can()` dans les vues
6. **Le super-admin a tout** via `Gate::before()` dans `AuthServiceProvider`
7. **Les mots de passe sont hashés** via `password` cast sur le modèle User
8. **L'import Excel ne sauvegarde jamais de données invalides** → validation ligne par ligne dans `AgentsImport`

---

## 🧪 Tests

```bash
# Lancer tous les tests
php artisan test

# Tests unitaires uniquement
php artisan test --testsuite=Unit

# Tests fonctionnels
php artisan test --testsuite=Feature

# Un test spécifique
php artisan test --filter=RetraiteServiceTest
```

Tests existants :
- `tests/Unit/RetraiteServiceTest.php`
- `tests/Unit/AllocationFamilialeServiceTest.php`
- `tests/Feature/AuthTest.php`

**Environnement de test** : SQLite en mémoire (voir `phpunit.xml`)

---

## 🚀 Commandes quotidiennes

```bash
# Développement JS (watch)
npm run dev

# Build production
npm run build

# Migrations
php artisan migrate
php artisan migrate:rollback
php artisan migrate:fresh --seed   # ⚠ efface tout

# Seeders
php artisan db:seed
php artisan db:seed --class=RoleSeeder

# Caches
php artisan optimize:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Debug
php artisan route:list
php artisan about
php artisan tinker
```

---

## 📋 Modules MVP — État d'avancement

| Module | Statut |
|---|---|
| Authentification | ✅ Complet |
| Utilisateurs & rôles | ✅ Complet |
| Référentiels (CRUD générique) | ✅ Complet |
| Agents (CRUD + fiche) | ✅ Complet |
| Structures (arborescence) | ✅ Complet |
| Affectations & mutations | ✅ Complet |
| Documents RH (upload/download) | ✅ Complet |
| Tableau de bord (ApexCharts) | ✅ Complet |
| Import Excel agents | ✅ Complet |
| Export Excel agents | ✅ Complet |
| Export PDF (fiche agent + liste filtrée) | ✅ Complet |
| Carrière (avancements, promotions, nominations) | ✅ Complet |
| Mouvements du personnel (sorties temporaires/définitives) | ✅ Complet |
| Gestion documentaire (dossier, archivage, recherche, ZIP) | ✅ Complet |
| Suivi des dossiers (circuit, étapes, délais, natures) | ✅ Complet |
| Plan de formation (plan triennal, programmes annuels, actions, besoins, PDF) | ✅ Complet |
| Indemnités (référentiel + barèmes GESPER + bulletin PDF) | ✅ Complet |
| Alertes RH + notifications persistantes (tâche planifiée) | ✅ Complet |
| Congés & absences (pointage + congés + soldes) | ✅ Complet |
| Formation (sessions + bénéficiaires) | ✅ Complet |
| Rapports statistiques avancés (effectifs, pyramide, masse salariale) | ✅ Complet |
| Discipline (demandes d'explication, sanctions, recours) | ✅ Complet |
| Performance (objectifs, notation, appréciation) | ✅ Complet |
| Compétences (référentiel + rattachement agent) | ✅ Complet |
| GPEC (départs retraite, besoins par emploi, cartographie compétences) | ✅ Complet |
| Notifications e-mail (digest, en plus du in-app) | ✅ Complet |
| Audit & traçabilité | ✅ Complet |
| Profil utilisateur | ✅ Complet |
| Espace agent (self-service : inscription, infos, actes, notifications) | ✅ Complet |

## 📋 Modules Phase 3 (GRH avancée) — Tous livrés ✅

Les modules du kit de développement (V1 MVP + V2 GRH avancée) sont tous implémentés.

**Espace agent (self-service)** — ✅ livré. Inscription **sans email** : l'agent prouve son
identité par la concordance de 3 informations de son dossier — **matricule + téléphone +
date de naissance** — puis définit immédiatement son mot de passe (activation instantanée).
Il se **connecte ensuite avec son matricule** (`/espace-agent/connexion`) ; l'adresse e-mail
du compte `User` est synthétique (`agent-{matricule}@gesperes.local`) et n'est jamais un
identifiant de connexion. Espace cloisonné (`middleware agent.individuel` + périmètre par
`agents.user_id`) : consultation des informations personnelles (lecture seule),
téléchargement de ses actes, notifications des actes le concernant. Voir
`app/Http/Controllers/EspaceAgent/` (Inscription, AgentSession, EspaceAgent),
routes `espace-agent.*`, `app/Services/NotificationAgentService.php`. Le rôle
`agent-individuel` n'a AUCUNE permission d'administration (cf. `RoleSeeder`).

Interface **mobile-first / PWA installable sur Android** : layout dédié
`layouts/agent.blade.php` (en-tête dégradé, barre d'onglets basse, bouton
d'installation) + `layouts/agent-auth.blade.php`. PWA : `public/manifest.webmanifest`,
`public/sw.js` (les pages authentifiées ne sont jamais mises en cache ; coquille
hors-ligne `public/offline.html`), icônes `public/images/icons/*` régénérables via
`php scripts/generer-icones-pwa.php`. Les invités de l'espace agent sont redirigés
vers `espace-agent.connexion` via `redirectGuestsTo` (bootstrap/app.php).
Empaquetage Play Store (TWA/Bubblewrap) : piste future, la base PWA est prête.

Pistes d'évolution futures : intégration paie complète, workflow de validation
multi-niveaux, éléments de salaire dans l'espace agent, API mobile.

> Voir [docs/audit-kit-developpement.md](docs/audit-kit-developpement.md) pour l'audit complet kit vs réalisé.

---

## ⚠️ Points métier à valider avec le MESFPTT

Ces paramètres sont des valeurs par défaut à confirmer officiellement :

- [ ] Âge légal de retraite par catégorie (A=63 ? B=58 ? C=58 ?)
- [ ] Montant exact allocation familiale par enfant
- [ ] Nombre maximum d'enfants pris en compte
- [ ] Volume horaire réglementaire par type d'enseignement
- [ ] Liste complète des spécialités d'enseignement
- [ ] Liste complète des localités et leurs zones (décret 2014-427)
- [x] Taux d'indemnités — barèmes du décret 2014-427 importés depuis GESPER
      (astreinte, logement, spécifique harmonisé, technicité) via `IndemniteBaremeSeeder`.
      Reste à valider : règles de cumul et d'éligibilité par position/statut.

---

## 💬 Instructions pour Claude Code

Quand je te demande de développer un module :

1. **Lis d'abord** les fichiers existants concernés avant d'écrire quoi que ce soit
2. **Respecte** exactement les conventions de ce fichier CLAUDE.md
3. **Ne modifie jamais** une migration existante → crée-en une nouvelle
4. **Toujours** utiliser le trait `Auditable` sur les nouveaux modèles
5. **Toujours** créer le FormRequest correspondant (Store + Update)
6. **Toujours** ajouter `$this->authorize()` en première ligne de chaque méthode de contrôleur
7. **Toujours** déléguer la logique métier à un Service dans `app/Services/`
8. **Toujours** écrire au moins un test unitaire pour chaque Service créé
9. **Utilise** les composants Blade existants (`<x-form.input>`, `<x-form.select>`, etc.)
10. **Respecte** le style visuel : layout `layouts.app`, classes CSS custom, couleur `institution`
11. **Demande confirmation** avant de créer un module qui n'est pas dans la liste Phase 2
12. **Ne jamais inventer** de règles métier — demande si une règle n'est pas documentée ici
