<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFriendlyValidationMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_initial_setup_explains_every_missing_password_requirement_in_spanish(): void
    {
        $response = $this->from('/install')->post('/install', [
            'email' => 'admin@example.com',
            'password' => 'debil',
            'password_confirmation' => 'debil',
        ]);

        $response->assertRedirect('/install');
        $response->assertSessionHasErrors([
            'password' => 'La contraseña debe tener al menos 12 caracteres.',
        ]);
    }

    public function test_password_confirmation_error_is_understandable(): void
    {
        $response = $this->from('/install')->post('/install', [
            'email' => 'admin@example.com',
            'password' => 'Segura-Password-123!',
            'password_confirmation' => 'Otra-Password-123!',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'La confirmación de contraseña no coincide.',
        ]);
    }

    public function test_invalid_login_message_does_not_reveal_technical_details(): void
    {
        $this->post('/login', [
            'email' => 'nadie@example.com',
            'password' => 'Incorrecta-123!',
        ])->assertSessionHasErrors([
            'email' => 'El correo o la contraseña no son correctos.',
        ]);
    }
}
