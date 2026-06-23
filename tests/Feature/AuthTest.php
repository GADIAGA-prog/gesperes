<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function page_connexion_accessible(): void
    {
        $this->get(route('login'))->assertOk();
    }

    #[Test]
    public function redirection_si_non_connecte(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    #[Test]
    public function connexion_avec_identifiants_valides(): void
    {
        $user = User::factory()->create([
            'email'    => 'test@test.bf',
            'password' => Hash::make('password'),
            'actif'    => true,
        ]);

        $this->post(route('login'), [
            'email'    => 'test@test.bf',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function connexion_refusee_si_compte_inactif(): void
    {
        User::factory()->create([
            'email'    => 'inactif@test.bf',
            'password' => Hash::make('password'),
            'actif'    => false,
        ]);

        $this->post(route('login'), [
            'email'    => 'inactif@test.bf',
            'password' => 'password',
        ])->assertSessionHasErrors('email');
    }

    #[Test]
    public function deconnexion_fonctionne(): void
    {
        $user = User::factory()->create(['actif' => true]);
        $this->actingAs($user);

        $this->post(route('logout'))->assertRedirect();
        $this->assertGuest();
    }
}
