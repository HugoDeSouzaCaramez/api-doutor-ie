<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class LivroTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_should_create_a_book_with_indices()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'titulo' => 'Meu Livro Exemplo',
            'indices' => [
                [
                    'titulo' => 'Capítulo 1',
                    'pagina' => 1,
                    'subindices' => [
                        [
                            'titulo' => 'Seção 1.1',
                            'pagina' => 2,
                            'subindices' => [],
                        ],
                    ],
                ],
                [
                    'titulo' => 'Capítulo 2',
                    'pagina' => 3,
                    'subindices' => [],
                ],
            ],
        ];

        $response = $this->postJson('/v1/livros', $payload);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'titulo',
            'usuario_publicador_id',
            'indices' => [
                [
                    'id',
                    'titulo',
                    'pagina',
                    'subindices' => [
                        [
                            'id',
                            'titulo',
                            'pagina',
                            'subindices',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertDatabaseHas('livros', ['titulo' => 'Meu Livro Exemplo']);
    }

    #[Test]
    public function it_should_validate_required_fields()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [];

        $response = $this->postJson('/v1/livros', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['titulo', 'indices']);
    }
}
