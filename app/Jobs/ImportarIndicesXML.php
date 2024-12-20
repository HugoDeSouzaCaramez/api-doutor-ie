<?php

namespace App\Jobs;

use App\Models\Indice;
use App\Models\Livro;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportarIndicesXML implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    protected $livro;
    protected $xmlString;

    public function __construct(Livro $livro, string $xmlString)
    {
        $this->livro = $livro;
        $this->xmlString = $xmlString;
    }

    public function handle(): void
    {
        try {
            $xml = simplexml_load_string($this->xmlString);
            if (!$xml) {
                throw new \Exception('XML inválido ou malformado.');
            }

            $this->importarIndices($this->livro, $xml);

            \Log::info('Importação concluída com sucesso.', ['livro_id' => $this->livro->id]);
        } catch (\Exception $e) {
            \Log::error('Erro no job ImportarIndicesXML', [
                'error' => $e->getMessage(),
                'livro_id' => $this->livro->id ?? null,
            ]);

            throw $e;
        }
    }

    private function importarIndices(Livro $livro, \SimpleXMLElement $element, ?int $parentId = null)
    {
        foreach ($element->item as $item) {
            $novoIndice = Indice::create([
                'livro_id' => $livro->id,
                'indice_pai_id' => $parentId,
                'titulo' => (string) $item['titulo'],
                'pagina' => (int) $item['pagina'],
            ]);

            if ($item->count() > 0) {
                $this->importarIndices($livro, $item, $novoIndice->id);
            }
        }
    }
}