<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_requires_unique_email(): void
    {
        $this->post('/register', [
            'name' => 'First',
            'email' => 'a@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect('/');

        $this->post('/register', [
            'name' => 'Second',
            'email' => 'A@GMAIL.COM',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors(['email']);
    }

    public function test_register_rejects_long_name(): void
    {
        $this->post('/register', [
            'name' => str_repeat('a', 51),
            'email' => 'longname@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors(['name']);
    }
}
