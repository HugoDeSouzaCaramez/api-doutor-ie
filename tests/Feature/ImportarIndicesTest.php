<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Livro;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ImportarIndicesXML;
use Laravel\Sanctum\Sanctum;

class ImportarIndicesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_it_should_dispatch_import_indices_job()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $livro = Livro::factory()->create([
            'usuario_publicador_id' => $user->id,
        ]);

        $xmlContent = <<<XML
            <indice>
            <item pagina="1" titulo="Capítulo 1">
                <item pagina="2" titulo="Seção 1.1">
                <item pagina="3" titulo="Subseção 1.1.1"/>
                </item>
                <item pagina="4" titulo="Seção 1.2"/>
            </item>
            </indice>
            XML;

        Queue::fake();

        $response = $this->postJson("/v1/livros/{$livro->id}/importar-indices-xml", $xmlContent, [
            'Content-Type' => 'application/xml',
        ]);

        $response->assertStatus(202);
        Queue::assertPushed(ImportarIndicesXML::class, function ($job) use ($livro) {
            return $job->livro->id === $livro->id;
        });
    }

    #[Test]
    public function test_it_should_return_404_if_book_not_found()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $xmlContent = <<<XML
        <indice>
        <item pagina="1" titulo="Capítulo 1">
            <item pagina="2" titulo="Seção 1.1">
            <item pagina="3" titulo="Subseção 1.1.1"/>
            </item>
        </item>
        </indice>
        XML;

        $response = $this->postJson("/v1/livros/999/importar-indices-xml", $xmlContent, [
            'Content-Type' => 'application/xml',
        ]);

        $response->assertStatus(404);
    }
}
