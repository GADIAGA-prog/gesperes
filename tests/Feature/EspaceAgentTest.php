<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Enums\TypeDocument;
use App\Models\Agent;
use App\Models\Document;
use App\Models\NotificationRh;
use App\Models\User;
use App\Services\NotificationAgentService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EspaceAgentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    /** Crée un agent rattaché à un compte agent-individuel actif (mot de passe connu). */
    private function agentAvecCompte(string $motDePasse = 'MotDePasse#2026'): array
    {
        $user = User::factory()->create(['actif' => true, 'password' => Hash::make($motDePasse)]);
        $user->assignRole(RoleName::AGENT_INDIVIDUEL->value);
        $agent = Agent::factory()->create(['user_id' => $user->id]);

        return [$user, $agent];
    }

    // ── Inscription (matricule + téléphone + date de naissance) ────

    #[Test]
    public function inscription_avec_trois_facteurs_active_et_connecte(): void
    {
        $agent = Agent::factory()->create([
            'matricule'      => 'M12345',
            'telephone'      => '70010203',
            'date_naissance' => '1985-09-21',
            'user_id'        => null,
        ]);

        $this->post(route('espace-agent.inscription.store'), [
            'matricule'             => 'm12345',          // casse différente
            'telephone'             => '70 01 02 03',     // espaces tolérés
            'date_naissance'        => '1985-09-21',
            'password'              => 'MotDePasse#2026',
            'password_confirmation' => 'MotDePasse#2026',
        ])->assertRedirect(route('espace-agent.dashboard'));

        $agent->refresh();
        $this->assertNotNull($agent->user_id);
        $user = $agent->user;
        $this->assertTrue($user->actif);
        $this->assertTrue($user->hasRole(RoleName::AGENT_INDIVIDUEL->value));
        $this->assertTrue(Hash::check('MotDePasse#2026', $user->password));
        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function inscription_refusee_si_un_facteur_ne_correspond_pas(): void
    {
        Agent::factory()->create([
            'matricule' => 'M999', 'telephone' => '70010203',
            'date_naissance' => '1990-01-01', 'user_id' => null,
        ]);

        $this->post(route('espace-agent.inscription.store'), [
            'matricule'             => 'M999',
            'telephone'             => '70010203',
            'date_naissance'        => '1991-01-01', // mauvaise date
            'password'              => 'MotDePasse#2026',
            'password_confirmation' => 'MotDePasse#2026',
        ])->assertSessionHasErrors('matricule');

        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    #[Test]
    public function inscription_refusee_si_compte_deja_actif(): void
    {
        [, $agent] = $this->agentAvecCompte();
        $agent->update(['matricule' => 'M77', 'telephone' => '70010203', 'date_naissance' => '1980-05-05']);

        $this->post(route('espace-agent.inscription.store'), [
            'matricule'             => 'M77',
            'telephone'             => '70010203',
            'date_naissance'        => '1980-05-05',
            'password'              => 'Nouveau#2026',
            'password_confirmation' => 'Nouveau#2026',
        ])->assertSessionHasErrors('matricule');
    }

    // ── Connexion par matricule ───────────────────────────────────

    #[Test]
    public function connexion_agent_par_matricule(): void
    {
        [$user, $agent] = $this->agentAvecCompte('Secret#2026');
        $agent->update(['matricule' => 'CONNEX1']);

        $this->post(route('espace-agent.connexion.store'), [
            'matricule' => 'CONNEX1',
            'password'  => 'Secret#2026',
        ])->assertRedirect(route('espace-agent.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function connexion_agent_refusee_si_mauvais_mot_de_passe(): void
    {
        [, $agent] = $this->agentAvecCompte('Secret#2026');
        $agent->update(['matricule' => 'CONNEX2']);

        $this->post(route('espace-agent.connexion.store'), [
            'matricule' => 'CONNEX2',
            'password'  => 'mauvais',
        ])->assertSessionHasErrors('matricule');

        $this->assertGuest();
    }

    // ── Accès / cloisonnement ─────────────────────────────────────

    #[Test]
    public function espace_agent_inaccessible_aux_invites(): void
    {
        // Les invités de l'espace agent sont dirigés vers LEUR connexion (par matricule).
        $this->get(route('espace-agent.dashboard'))->assertRedirect(route('espace-agent.connexion'));
    }

    #[Test]
    public function espace_agent_interdit_a_un_compte_sans_role_agent(): void
    {
        $user = User::factory()->create(['actif' => true]); // pas de rôle agent-individuel
        $this->actingAs($user)->get(route('espace-agent.dashboard'))->assertForbidden();
    }

    #[Test]
    public function agent_individuel_accede_a_son_espace(): void
    {
        [$user] = $this->agentAvecCompte();

        $this->actingAs($user)->get(route('espace-agent.dashboard'))->assertOk();
        $this->actingAs($user)->get(route('espace-agent.profil'))->assertOk();
        $this->actingAs($user)->get(route('espace-agent.actes'))->assertOk();
        $this->actingAs($user)->get(route('espace-agent.notifications'))->assertOk();
    }

    // ── Actes / téléchargement ────────────────────────────────────

    #[Test]
    public function agent_telecharge_son_propre_acte_mais_pas_celui_dun_autre(): void
    {
        Storage::fake('documents');
        [$user, $agent] = $this->agentAvecCompte();

        Storage::disk('documents')->put("agents/{$agent->id}/acte.pdf", 'contenu');
        $mien = Document::create([
            'agent_id' => $agent->id, 'type_document' => TypeDocument::ARRETE->value,
            'chemin' => "agents/{$agent->id}/acte.pdf", 'nom_original' => 'acte.pdf',
            'mime' => 'application/pdf', 'taille' => 7, 'archive' => false,
        ]);

        $autre = Agent::factory()->create();
        Storage::disk('documents')->put("agents/{$autre->id}/x.pdf", 'x');
        $autreDoc = Document::create([
            'agent_id' => $autre->id, 'type_document' => TypeDocument::ARRETE->value,
            'chemin' => "agents/{$autre->id}/x.pdf", 'nom_original' => 'x.pdf',
            'mime' => 'application/pdf', 'taille' => 1, 'archive' => false,
        ]);

        $this->actingAs($user)->get(route('espace-agent.actes.telecharger', $mien))->assertOk();
        $this->actingAs($user)->get(route('espace-agent.actes.telecharger', $autreDoc))->assertForbidden();
    }

    // ── Notifications ─────────────────────────────────────────────

    #[Test]
    public function agent_marque_sa_notification_comme_lue(): void
    {
        [$user, $agent] = $this->agentAvecCompte();
        $notif = NotificationRh::create([
            'type' => 'acte', 'cle' => 'acte-1', 'agent_id' => $agent->id,
            'titre' => 'Nouvel acte', 'message' => 'Un arrêté', 'niveau' => 'info', 'lu' => false,
        ]);

        $this->actingAs($user)->post(route('espace-agent.notifications.lue', $notif))->assertRedirect();
        $this->assertTrue($notif->fresh()->lu);
    }

    // ── Digital Asset Links (TWA / Play Store) ────────────────────

    #[Test]
    public function assetlinks_vide_tant_que_lempreinte_nest_pas_configuree(): void
    {
        config(['gesperes.android.sha256_fingerprint' => null]);

        $this->getJson('/.well-known/assetlinks.json')->assertOk()->assertExactJson([]);
    }

    #[Test]
    public function assetlinks_expose_le_lien_app_site_quand_configure(): void
    {
        config([
            'gesperes.android.package' => 'bf.gov.mesfpt.gesperes',
            'gesperes.android.sha256_fingerprint' => 'AA:BB:CC, DD:EE:FF',
        ]);

        $this->getJson('/.well-known/assetlinks.json')
            ->assertOk()
            ->assertJsonPath('0.relation.0', 'delegate_permission/common.handle_all_urls')
            ->assertJsonPath('0.target.package_name', 'bf.gov.mesfpt.gesperes')
            ->assertJsonPath('0.target.sha256_cert_fingerprints', ['AA:BB:CC', 'DD:EE:FF']);
    }

    #[Test]
    public function notification_acte_creee_pour_agent_rattache(): void
    {
        Storage::fake('documents');
        [, $agent] = $this->agentAvecCompte();

        $document = Document::create([
            'agent_id' => $agent->id, 'type_document' => TypeDocument::ACTE_NOMINATION->value,
            'reference' => 'A-2026/001', 'chemin' => 'x', 'nom_original' => 'x.pdf',
            'mime' => 'application/pdf', 'taille' => 1, 'archive' => false,
        ]);

        app(NotificationAgentService::class)->notifierNouvelActe($document);

        $this->assertDatabaseHas('notifications_rh', [
            'agent_id' => $agent->id,
            'type'     => 'acte',
            'cle'      => 'acte-' . $document->id,
        ]);
    }
}
