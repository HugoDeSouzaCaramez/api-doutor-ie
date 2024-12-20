<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    public function test_can_create_user()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ];

        $this->postJson('/api/users', $data)
            ->assertStatus(201)
            ->assertJsonFragment(['email' => $data['email']]);
    }

}
