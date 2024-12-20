<?php

namespace App\Http\Controllers;

use App\Models\Indice;
use App\Models\Livro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\jobs\importarIndicesXml;

class LivroController extends Controller
{
    public function index(Request $request)
    {
        $query = Livro::query();

        if ($request->filled('titulo')) {
            $query->where('titulo', 'like', '%' . $request->titulo . '%');
        }

        if ($request->filled('titulo_do_indice')) {
            $query->whereHas('indices', function ($q) use ($request) {
                $q->where('titulo', 'like', '%' . $request->titulo_do_indice . '%');
            });
        }

        return $query->with(['usuarioPublicador', 'indices.subindices'])->get();
    }

    public function store(Request $request)
    {
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
    }

    public function importarIndicesXml($livroId, Request $request)
    {
        $livro = Livro::findOrFail($livroId);
        $xml = simplexml_load_string($request->getContent());
        ImportarIndicesXML::dispatch($livro, $xml);
        return response()->json(['message' => 'Job de importação disparado'], 202);
    }

    private function saveIndices($livro, $indices, $parentId = null)
    {
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
    }
}
