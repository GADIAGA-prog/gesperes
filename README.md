# GesPerES — Gestion du Personnel Enseignant du Secondaire

Plateforme RH moderne pour la **DRH-MESFPT / Burkina Faso**.

---

## 🧰 Stack technique

| Composant | Version |
|---|---|
| PHP | ^8.2 |
| Laravel | ^12 |
| Base de données | MySQL 8+ |
| Frontend | Blade + Tailwind CSS 3 + Alpine.js |
| Auth | Laravel Breeze (Blade) |
| RBAC | spatie/laravel-permission ^6 |
| Audit | spatie/laravel-activitylog ^4 |
| Import/Export | maatwebsite/excel ^3 |
| PDF | barryvdh/laravel-dompdf ^3 |
| Graphiques | ApexCharts (CDN) |
| Build | Vite + npm |

---

## 🚀 Installation locale (VS Code / Laragon / Herd)

```bash
# 1. Cloner le projet
git clone https://github.com/VOTRE-COMPTE/gesperes.git
cd gesperes

# 2. Installer les dépendances PHP
composer install

# 3. Installer les dépendances JS et compiler
npm install
npm run build

# 4. Copier et configurer le .env
cp .env.example .env
php artisan key:generate

# 5. Configurer la base de données dans .env
# DB_DATABASE=gesperes
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Créer la base de données MySQL
mysql -u root -e "CREATE DATABASE gesperes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 7. Lancer les migrations et les seeders
php artisan migrate --seed

# 8. Créer le lien symbolique storage
php artisan storage:link

# 9. Lancer le serveur de développement
php artisan serve
```

### Compte par défaut

| Champ | Valeur |
|---|---|
| E-mail | admin@gesperes.bf |
| Mot de passe | GesPerES@2025! |
| Rôle | Super Admin |

> **⚠ Changez ce mot de passe immédiatement après la première connexion.**

---

## ⚙️ Configuration métier (`config/gesperes.php`)

Avant la mise en production, validez ces paramètres avec le service RH :

```php
// Âge légal de retraite par défaut
GESPERES_AGE_RETRAITE=60   # dans .env

// Montant allocation familiale par enfant (FCFA)
gesperes.allocation_familiale.montant_par_enfant = 2000

// Nombre maximum d'enfants pour l'allocation
gesperes.allocation_familiale.nombre_max_enfants = 6

// Volume horaire hebdomadaire enseignant par défaut
gesperes.volume_horaire_defaut = 18

// Délai d'alerte avant retraite (mois)
gesperes.retraite.alerte_mois_avant = 24
```

---

## 🧪 Tests

```bash
# Tous les tests (SQLite en mémoire)
php artisan test

# Tests unitaires uniquement
php artisan test --testsuite=Unit

# Tests fonctionnels
php artisan test --testsuite=Feature

# Avec couverture (nécessite Xdebug ou PCOV)
php artisan test --coverage
```

---

## 🗂️ Architecture principale

```
app/
├── Enums/          # Sexe, StatutDossier, TypeDocument, RoleName…
├── Exports/        # AgentsExport (maatwebsite/excel)
├── Http/
│   ├── Controllers/
│   │   ├── Auth/           # Connexion, réinitialisation
│   │   ├── AgentController
│   │   ├── AgentImportExportController
│   │   ├── AffectationController
│   │   ├── AuditController
│   │   ├── DashboardController
│   │   ├── DocumentController
│   │   ├── ReferentielController  # CRUD générique
│   │   ├── RoleController
│   │   ├── StructureController
│   │   └── UserController
│   └── Requests/   # FormRequests (validation)
├── Imports/        # AgentsImport (maatwebsite/excel)
├── Models/         # Eloquent + SoftDeletes + Auditable
├── Policies/       # Agent, Structure, Document, User
├── Providers/      # AppServiceProvider, AuthServiceProvider
├── Services/       # AgentService, RetraiteService, AllocationFamilialeService, DashboardService
└── Support/        # Permissions, ReferentielRegistry, Auditable (trait)

database/
├── factories/      # UserFactory, AgentFactory
├── migrations/     # 12 fichiers ordonnés
└── seeders/        # RoleSeeder, ReferentielSeeder, UserSeeder, DatabaseSeeder

resources/views/
├── layouts/        # app.blade.php, guest.blade.php
├── components/     # form/input, form/select, form/textarea
├── partials/       # sidebar, flash
├── auth/           # login, forgot-password, reset-password
├── dashboard/      # index (ApexCharts)
├── agents/         # index, show, create, edit, _form, import
├── affectations/   # index, create, show
├── audit/          # index
├── documents/      # index (upload + liste)
├── profile/        # edit
├── referentiels/   # index, show (générique)
├── roles/          # index, edit
├── structures/     # index, show, create, edit, _form, _noeud
└── users/          # index, create, edit, _form
```

---

## 📦 Déploiement (Laravel Cloud / production)

```bash
composer install --no-dev --optimize-autoloader
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan db:seed --class=RoleSeeder --force
```

> En production : configurez un disque privé pour `storage/app/private/documents`
> (S3 ou volume serveur) et définissez `FILESYSTEM_DISK` en conséquence.

---

## 🔐 Rôles et permissions

| Rôle | Accès |
|---|---|
| `super-admin` | Tout (Gate::before) |
| `admin-national` | Toutes les permissions |
| `admin-regional` | Agents, structures, affectations, documents (périmètre régional) |
| `agent-rh` | CRUD agents, import/export, documents |
| `responsable-structure` | Lecture agents/structures, documents (lecture) |
| `consultation` | Lecture seule |
| `agent-individuel` | Ses propres documents |

> Les permissions sont gérées depuis l'interface **Rôles & droits**.

---

## 📋 Modules MVP inclus

- [x] Authentification (connexion, déconnexion, profil, réinitialisation)
- [x] Utilisateurs, rôles & permissions (Spatie)
- [x] Agents (CRUD complet, fiche multi-onglets)
- [x] Structures (arborescence hiérarchique)
- [x] Affectations & mutations (historique transactionnel)
- [x] Documents RH (upload sécurisé, téléchargement contrôlé)
- [x] Référentiels (CRUD générique : catégories, emplois, zones…)
- [x] Tableau de bord (cartes + ApexCharts)
- [x] Import Excel / Export Excel (maatwebsite)
- [x] Audit & traçabilité (spatie/activitylog)

## 🔄 Modules prévus (Phase 2+)

- [ ] Gestion de carrière avancée (avancements, nominations)
- [ ] Présence, absences et congés
- [ ] Formation
- [ ] Recrutement
- [ ] Performance
- [ ] Notifications email/SMS
- [ ] Export PDF (DomPDF)
- [ ] Rapports statistiques avancés

---

*GesPerES v1.0 — DRH-MESFPT / Burkina Faso*
