<?php

namespace App\Http\Controllers;

use App\Models\Indice;
use App\Models\Livro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\ImportarIndicesXML;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class LivroController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Livro::query();

            if ($request->filled('titulo')) {
                $query->where('titulo', 'like', '%' . $request->titulo . '%');
            }

            if ($request->filled('titulo_do_indice')) {
                $query->whereHas('indices', function ($q) use ($request) {
                    $q->where('titulo', 'like', '%' . $request->titulo_do_indice . '%');
                });
            }

            $livros = $query->with(['usuarioPublicador', 'indices.subindices'])->get();

            return response()->json($livros, 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erro ao listar livros', 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'titulo' => 'required|string',
                'indices' => 'required|array',
                'indices.*.titulo' => 'required|string',
                'indices.*.pagina' => 'required|integer',
                'indices.*.subindices' => 'array',
            ]);

            $livro = DB::transaction(function () use ($validated) {
                $livro = Livro::create([
                    'usuario_publicador_id' => auth()->id(),
                    'titulo' => $validated['titulo'],
                ]);

                $this->saveIndices($livro, $validated['indices']);

                return $livro;
            });

            return response()->json($livro->load('indices.subindices'), 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erro ao cadastrar livro', 'message' => $e->getMessage()], 500);
        }
    }

    public function importarIndicesXml($livroId, Request $request)
    {
        try {
            $livro = Livro::findOrFail($livroId);

            $xml = simplexml_load_string($request->getContent());
            if (!$xml) {
                return response()->json(['error' => 'XML inválido'], 400);
            }

            ImportarIndicesXML::dispatch($livro, $xml->asXML());

            return response()->json(['message' => 'Job de importação disparado'], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Livro não encontrado', 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erro ao importar índices', 'message' => $e->getMessage()], 500);
        }
    }


    private function saveIndices($livro, $indices, $parentId = null)
    {
        try {
            foreach ($indices as $indice) {
                $newIndice = Indice::create([
                    'livro_id' => $livro->id,
                    'indice_pai_id' => $parentId,
                    'titulo' => $indice['titulo'],
                    'pagina' => $indice['pagina'],
                ]);

                if (!empty($indice['subindices'])) {
                    $this->saveIndices($livro, $indice['subindices'], $newIndice->id);
                }
            }
        } catch (Exception $e) {
            throw new Exception('Erro ao salvar índices: ' . $e->getMessage());
        }
    }
}
