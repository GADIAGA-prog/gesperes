<?php

namespace Database\Seeders;

use App\Enums\CategoriePosition;
use App\Models\Categorie;
use App\Models\Classe;
use App\Models\Echelon;
use App\Models\Emploi;
use App\Models\Fonction;
use App\Models\PositionAdministrative;
use App\Models\TypeEnseignement;
use App\Models\Zone;
use Illuminate\Database\Seeder;

/**
 * Référentiels de base pour GesPerES.
 * ⚠  Ces données sont des exemples. À valider et compléter avec le métier.
 */
class ReferentielSeeder extends Seeder
{
    public function run(): void
    {
        // ── Catégories ─────────────────────────────────────────────────
        $cats = [
            ['code' => 'A',  'libelle' => 'Catégorie A'],
            ['code' => 'B',  'libelle' => 'Catégorie B'],
            ['code' => 'C',  'libelle' => 'Catégorie C'],
            ['code' => 'D',  'libelle' => 'Catégorie D'],
            ['code' => 'E',  'libelle' => 'Catégorie E'],
            ['code' => 'P7', 'libelle' => 'Corps enseignant P7'],
        ];
        foreach ($cats as $c) {
            Categorie::firstOrCreate(['code' => $c['code']], ['libelle' => $c['libelle'], 'actif' => true]);
        }
        $this->command->info('✓ Catégories');

        // ── Classes ─────────────────────────────────────────────────────
        foreach (range(1, 10) as $i) {
            Classe::firstOrCreate(['code' => "CL{$i}"], ['libelle' => "Classe {$i}", 'actif' => true]);
        }
        $this->command->info('✓ Classes');

        // ── Échelons ────────────────────────────────────────────────────
        foreach (range(1, 12) as $i) {
            Echelon::firstOrCreate(['code' => "ECH{$i}"], ['libelle' => "Échelon {$i}", 'rang' => $i, 'actif' => true]);
        }
        $this->command->info('✓ Échelons');

        // ── Emplois ─────────────────────────────────────────────────────
        $catA = Categorie::where('code', 'A')->first();
        $catP7 = Categorie::where('code', 'P7')->first();
        $emplois = [
            ['code' => 'PROF', 'libelle' => 'Professeur', 'enseignant' => true, 'volume_horaire_defaut' => 18, 'categorie_id' => $catP7?->id],
            ['code' => 'CPE',  'libelle' => 'Conseiller pédagogique', 'enseignant' => true, 'volume_horaire_defaut' => 12, 'categorie_id' => $catA?->id],
            ['code' => 'ATT',  'libelle' => 'Attaché d\'administration scolaire', 'enseignant' => false, 'volume_horaire_defaut' => null, 'categorie_id' => $catA?->id],
            ['code' => 'ADM',  'libelle' => 'Administrateur des affaires scolaires', 'enseignant' => false, 'volume_horaire_defaut' => null, 'categorie_id' => $catA?->id],
            ['code' => 'INTD', 'libelle' => 'Intendant', 'enseignant' => false, 'volume_horaire_defaut' => null, 'categorie_id' => $catA?->id],
        ];
        foreach ($emplois as $e) {
            Emploi::firstOrCreate(['code' => $e['code']], array_merge($e, ['actif' => true]));
        }
        $this->command->info('✓ Emplois');

        // ── Fonctions ────────────────────────────────────────────────────
        $fonctions = [
            ['code' => 'PROV',  'libelle' => 'Proviseur'],
            ['code' => 'CENS',  'libelle' => 'Censeur'],
            ['code' => 'SG',    'libelle' => 'Secrétaire général'],
            ['code' => 'CPED',  'libelle' => 'Conseiller pédagogique'],
            ['code' => 'INSP',  'libelle' => 'Inspecteur pédagogique'],
            ['code' => 'DIR',   'libelle' => 'Directeur provincial'],
            ['code' => 'DIREG', 'libelle' => 'Directeur régional'],
        ];
        foreach ($fonctions as $f) {
            Fonction::firstOrCreate(['code' => $f['code']], ['libelle' => $f['libelle'], 'actif' => true]);
        }
        $this->command->info('✓ Fonctions');

        // ── Positions administratives ─────────────────────────────────────
        $positions = [
            // Activité
            ['code' => 'ENPOSTE', 'libelle' => 'En poste', 'categorie' => CategoriePosition::ACTIVITE->value],
            // Sorties temporaires
            ['code' => 'DISPO',   'libelle' => 'Disponibilité', 'categorie' => CategoriePosition::SORTIE_TEMPORAIRE->value],
            ['code' => 'DETACH',  'libelle' => 'Détachement', 'categorie' => CategoriePosition::SORTIE_TEMPORAIRE->value],
            ['code' => 'MAD',     'libelle' => 'Mise à disposition', 'categorie' => CategoriePosition::SORTIE_TEMPORAIRE->value],
            ['code' => 'SUSPEN',  'libelle' => 'Suspension', 'categorie' => CategoriePosition::SORTIE_TEMPORAIRE->value],
            // Sorties définitives
            ['code' => 'RETR',    'libelle' => 'Retraite', 'categorie' => CategoriePosition::SORTIE_DEFINITIVE->value],
            ['code' => 'RADI',    'libelle' => 'Radiation', 'categorie' => CategoriePosition::SORTIE_DEFINITIVE->value],
            ['code' => 'DECES',   'libelle' => 'Décès', 'categorie' => CategoriePosition::SORTIE_DEFINITIVE->value],
            ['code' => 'DEMIS',   'libelle' => 'Démission', 'categorie' => CategoriePosition::SORTIE_DEFINITIVE->value],
            ['code' => 'REVOC',   'libelle' => 'Révocation', 'categorie' => CategoriePosition::SORTIE_DEFINITIVE->value],
        ];
        foreach ($positions as $p) {
            PositionAdministrative::firstOrCreate(['code' => $p['code']], array_merge($p, ['actif' => true]));
        }
        $this->command->info('✓ Positions administratives');

        // ── Zones (décret 2014-427) ──────────────────────────────────────
        $zones = [
            ['code' => 'urbaine',      'libelle' => 'Zone urbaine (Ouagadougou, Bobo-Dioulasso)'],
            ['code' => 'semi_urbaine', 'libelle' => 'Zone semi-urbaine'],
            ['code' => 'rurale',       'libelle' => 'Zone rurale'],
        ];
        foreach ($zones as $z) {
            Zone::firstOrCreate(['code' => $z['code']], ['libelle' => $z['libelle'], 'actif' => true]);
        }
        $this->command->info('✓ Zones');

        // ── Types d'enseignement ──────────────────────────────────────────
        $typesEns = [
            ['code' => 'GEN',  'libelle' => 'Enseignement général'],
            ['code' => 'TECH', 'libelle' => 'Enseignement technique'],
            ['code' => 'PROF', 'libelle' => 'Enseignement professionnel'],
        ];
        foreach ($typesEns as $t) {
            TypeEnseignement::firstOrCreate(['code' => $t['code']], ['libelle' => $t['libelle'], 'actif' => true]);
        }
        $this->command->info('✓ Types d\'enseignement');

        $this->command->warn('  ℹ  Complétez les échelles, indices, spécialités et localités depuis l\'interface Référentiels.');
    }
}
