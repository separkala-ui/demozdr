<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_view_login_form(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertViewIs('backend.auth.login');
    }

    #[Test]
    public function user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'superadmin@example.com',
            'password' => bcrypt('12345678'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'superadmin@example.com',
            'password' => '12345678',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function user_cannot_login_with_incorrect_password(): void
    {
        $response = $this->from('/admin/login')
            ->post('/admin/login', [
                'email' => 'superadmin@example.com',
                'password' => 'wrong-password',
            ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[Test]
    public function user_cannot_login_with_email_that_does_not_exist(): void
    {
        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'nobody@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[Test]
    public function remember_me_functionality_works(): void
    {
        $user = User::factory()->create([
            'email' => 'superadmin@example.com',
            'password' => bcrypt('12345678'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'superadmin@example.com',
            'password' => '12345678',
            'remember' => 'on',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);

        // Check for the remember cookie
        $cookies = $response->headers->getCookies();
        $hasRememberCookie = false;

        foreach ($cookies as $cookie) {
            if (strpos($cookie->getName(), 'remember_web_') === 0) {
                $hasRememberCookie = true;
                break;
            }
        }

        $this->assertTrue($hasRememberCookie);
    }
}
