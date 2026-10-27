<?php

namespace Tests\Feature;

use App\Models\Player;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['email' => 'admin@helppro.tech', 'password' => 'Banjersing']);
    }

    public function test_guests_are_sent_to_login(): void
    {
        [$a, $b] = Player::factory()->count(2)->create();

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->getJson(route('api.player-stats'))->assertUnauthorized();
        $this->getJson(route('api.versus'))->assertUnauthorized();

        $this->post(route('players.store'), ['nombre' => 'X'])->assertRedirect(route('login'));
        $this->post(route('matches.store'), [
            'fecha' => '2026-01-01T10:00', 'ganador_id' => $a->id, 'perdedor_id' => $b->id,
            'victorias_ganador' => 3, 'victorias_perdedor' => 1,
        ])->assertRedirect(route('login'));
        $this->put('/matches/1', [])->assertRedirect(route('login'));

        $this->assertDatabaseCount('matches', 0);
        $this->assertDatabaseMissing('players', ['nombre' => 'X']);
    }

    public function test_admin_can_log_in_and_out(): void
    {
        $this->admin();

        $this->post(route('login.store'), ['email' => 'admin@helppro.tech', 'password' => 'Banjersing'])
            ->assertRedirect(route('ranking.index'));
        $this->assertAuthenticated();

        $this->get(route('ranking.index'))->assertOk()->assertSee('Ranking - Season')->assertSee('Salir');
        $this->get(route('dashboard'))->assertOk()->assertSee('data-bs-target="#modalPleitoMovil"', false)->assertSee('Salir');

        $this->post(route('logout'))->assertRedirect(route('ranking.index'));
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_login_opens_ranking_even_after_visiting_a_protected_page(): void
    {
        $this->admin();
        $this->get(route('dashboard'))->assertRedirect(route('login'));

        $this->post(route('login.store'), ['email' => 'admin@helppro.tech', 'password' => 'Banjersing'])
            ->assertRedirect(route('ranking.index'));
    }

    public function test_wrong_credentials_are_rejected(): void
    {
        $this->admin();

        $this->from(route('login'))
            ->post(route('login.store'), ['email' => 'admin@helppro.tech', 'password' => 'incorrecta'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_throttled(): void
    {
        $this->admin();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.store'), ['email' => 'admin@helppro.tech', 'password' => 'mala']);
        }

        $this->post(route('login.store'), ['email' => 'admin@helppro.tech', 'password' => 'Banjersing'])
            ->assertStatus(429);
    }

    public function test_admin_seeder_creates_a_hashed_admin_from_env(): void
    {
        putenv('ADMIN_EMAIL=admin@helppro.tech');
        putenv('ADMIN_PASSWORD=Banjersing');
        $_ENV['ADMIN_EMAIL'] = 'admin@helppro.tech';
        $_ENV['ADMIN_PASSWORD'] = 'Banjersing';

        $this->seed(AdminUserSeeder::class);

        $user = User::where('email', 'admin@helppro.tech')->firstOrFail();
        $this->assertNotSame('Banjersing', $user->password);
        $this->assertTrue(password_verify('Banjersing', $user->password));
    }
}
