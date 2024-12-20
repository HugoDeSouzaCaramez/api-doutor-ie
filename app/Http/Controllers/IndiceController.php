<?php

namespace App\Http\Controllers;

use App\Models\Indice;
use Illuminate\Http\Request;

class IndiceController extends Controller
{
    public function show($indiceId)
    {
        $indice = Indice::with('subindices')->findOrFail($indiceId);
        return response()->json($indice);
    }

    public function update(Request $request, $indiceId)
    {
        $validated = $request->validate([
            'titulo' => 'required|string',
            'pagina' => 'required|integer',
        ]);

        $indice = Indice::findOrFail($indiceId);
        $indice->update($validated);

        return response()->json($indice);
    }

    public function destroy($indiceId)
    {
        $indice = Indice::findOrFail($indiceId);
        $indice->delete();

        return response()->json(['message' => 'Índice deletado com sucesso']);
    }
}