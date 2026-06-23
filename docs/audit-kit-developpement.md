# Audit GesPerES — Kit de développement vs Réalisé

> Comparaison du document `kit_developpement_application_RH_claude_code_vscode.docx`
> et des données `GESPER/` avec l'application développée. Date : 2026-06-20.

---

## 1. Sources analysées
- **Kit de développement** (spec fonctionnelle, stack, modules V1/V2, tables, menu, ordre de dev).
- **Données GESPER** : référentiels (catégories, échelles, classes, échelons, indices, emplois, fonctions, structures niveau 1-4, localités, programmes/actions), et **barèmes d'indemnités** (`salaire/` : astreinte, logement, spécifique harmonisé, technicité).
- **Décret 2014-427** (indemnités) — PDF de référence.

## 2. Conformité stack & architecture — ✅ Conforme
| Recommandation kit | Réalisé |
|---|---|
| Laravel + Blade + Tailwind + MySQL | ✅ |
| Spatie Laravel Permission | ✅ |
| Laravel Excel (exports) | ✅ |
| DomPDF (exports PDF) | ✅ |
| ApexCharts | ✅ |
| Architecture Models/Controllers/Requests/**Services**/Policies/Seeders/Views | ✅ |
| Soft deletes, FK, index, audit logs (spatie/activitylog) | ✅ |

## 3. Couverture des modules

### Kit V1 — MVP sérieux
| Module kit | État | Détail |
|---|---|---|
| Tableau de bord | ✅ | ApexCharts |
| Agents (dossier complet) | ✅ | CRUD, fiche, import/export Excel, **PDF** |
| Structures | ✅ | arborescence + types réformés (Ministère/Direction/Service/Établissement) |
| Référentiels RH | ✅ | CRUD générique + grille indiciaire |
| Affectations | ✅ | historique mutations |
| Présence / absences | ✅ | pointage journalier + fiches A/B/C |
| Documents RH | ✅ | dossier, archivage, recherche globale, export ZIP, multi-upload |
| Utilisateurs | ✅ | comptes + rôles/permissions |
| Statistiques | 🟡 | dashboard OK, **rapports avancés manquants** |
| Exports | ✅ | Excel + PDF |

### Kit V2 — GRH avancée
| Module kit | État | Détail |
|---|---|---|
| Carrière (avancements, promotions, nominations, reclassements) | ✅ | module Carrière + recalcul indice/retraite |
| **Mouvements** (sorties temporaires/définitives) | ✅ | hors kit explicite, ajouté (disponibilité, détachement, retraite, décès…) |
| **Indemnités** (décret 2014-427) | ✅ | référentiel + **barèmes GESPER** (astreinte, logement, spécifique, technicité) + moteur de calcul |
| Retraite (détection + prévisions) | 🟡 | alertes proches-retraite OK ; **prévisions/cohortes manquantes** |
| Notifications | 🟡 | **Alertes RH in-app** (retraite, docs expirés) ; **pas d'emails ni de table notifications** |
| Congés | ✅ | demandes, validation, soldes |
| **Formation** (plan, bénéficiaires, coûts, compétences) | ❌ | non développé |
| **Discipline** (explications, sanctions, recours) | ❌ | non développé |
| **Performance** (objectifs, notation, appréciation) | ❌ | non développé |
| **GPEC** (prévision emplois, compétences, déficits) | ❌ | non développé |
| **Compétences** | ❌ | non développé |

## 4. Données GESPER vs intégrées
| Donnée GESPER | État |
|---|---|
| Catégories, échelles, classes, échelons, indices (grille) | ✅ importées |
| Emplois, fonctions, spécialités, type enseignements | ✅ |
| Structures niveau 1-4 | ✅ (import GESPER) |
| Localités, régions/provinces, zones | ✅ |
| Programmes / actions / budget | ✅ |
| **Barèmes indemnités** (astreinte 312, spécifique 315, logement 15, technicité 18) | ✅ **importés ce jour** |
| `gesper.sql`, PDF-PAR-2026, répartition dépenses 2027-2029 | ⬜ non exploités (paie/prévision budgétaire) |

## 5. Écarts notables
1. **Champ `photo`** de l'agent (cité dans le kit) — absent de la table `agents`.
2. **`docs/specifications-rh.md`** (recommandé par le kit) — absent.
3. **Modules GRH avancée manquants** : Formation, Discipline, Performance, GPEC, Compétences.
4. **Notifications** : pas de système (table + envoi email + file d'attente) ni de **tâche planifiée** pour générer les alertes automatiquement.
5. **Rapports statistiques avancés** : pyramide des âges, effectifs croisés (région × catégorie × sexe), masse salariale, exports dédiés.
6. **Paie / bulletin** : la grille indiciaire (salaire) + les indemnités (barèmes) permettraient un **bulletin de rémunération** (non assemblé).
7. **Couverture de tests** faible (services unitaires + auth) ; manque de tests fonctionnels (feature) sur les contrôleurs.
8. **Bug préexistant** : `AuthTest::connexion` attend une redirection `/` alors que le login redirige vers `/dashboard`.

## 6. Recommandations d'amélioration (priorisées)

**Priorité haute**
- **Attribution automatique des indemnités barème** : bouton « Calculer & figer » sur la fiche agent (crée les `agent_indemnites` à partir du moteur), avec historique des montants.
- **Bulletin de rémunération (PDF)** : salaire indiciaire + indemnités → brut, par agent.
- **Module Formation** (plan annuel, sessions, bénéficiaires, coûts, compétences acquises).
- **Notifications & tâches planifiées** : commande `php artisan schedule` générant alertes (retraite, documents) + notifications in-app/email.

**Priorité moyenne**
- **Module Discipline** (demandes d'explication, sanctions, recours) — table `dossiers_disciplinaires`.
- **Rapports statistiques avancés** (pyramide des âges, effectifs croisés, masse salariale) + exports.
- **Champ photo** agent (+ affichage fiche/PDF).
- **Module Performance** (objectifs, notation, appréciation annuelle).

**Priorité basse / socle**
- **GPEC** (prévision emplois/compétences, déficits/sureffectifs) — s'appuie sur Formation + Performance.
- **Compétences** (référentiel + rattachement agent).
- `docs/specifications-rh.md`, montée en couverture de **tests fonctionnels**, correctif `AuthTest`.

## 7. À valider avec le MESFPTT
- Règles d'**attribution** des indemnités (cumul, conditions d'éligibilité par position/statut).
- Périodicité et déclencheurs des **alertes/notifications**.
- Indicateurs cibles des **rapports** de pilotage.
