<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Livro;
use App\Models\Indice;
use Laravel\Sanctum\Sanctum;

class LivroListTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_it_should_list_books_without_filters()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Livro::factory()->count(3)->create(['usuario_publicador_id' => $user->id]);

        $response = $this->getJson('/v1/livros');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    #[Test]
    public function test_it_should_filter_books_by_title()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Livro::factory()->create(['titulo' => 'Livro A', 'usuario_publicador_id' => $user->id]);
        Livro::factory()->create(['titulo' => 'Livro B', 'usuario_publicador_id' => $user->id]);

        $response = $this->getJson('/v1/livros?titulo=Livro A');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['titulo' => 'Livro A']);
    }

    #[Test]
    public function test_it_should_filter_books_by_index_title()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $livro = Livro::factory()->create(['titulo' => 'Livro com Índice', 'usuario_publicador_id' => $user->id]);
        Indice::factory()->create(['livro_id' => $livro->id, 'titulo' => 'Capítulo Especial']);

        $response = $this->getJson('/v1/livros?titulo_do_indice=Capítulo Especial');

        $response->assertStatus(200);
        $response->assertJsonFragment(['titulo' => 'Livro com Índice']);
    }
}
