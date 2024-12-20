<?php

namespace App\Http\Controllers;

use App\Models\Indice;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class IndiceController extends Controller
{
    public function show($indiceId)
    {
        try {
            $indice = Indice::with('subindices')->findOrFail($indiceId);
            return response()->json($indice);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Índice não encontrado'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Ocorreu um erro ao buscar o índice'], 500);
        }
    }

    public function update(Request $request, $indiceId)
    {
        try {
            $validated = $request->validate([
                'titulo' => 'required|string',
                'pagina' => 'required|integer',
            ]);

            $indice = Indice::findOrFail($indiceId);
            $indice->update($validated);

            return response()->json($indice);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Índice não encontrado'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Ocorreu um erro ao atualizar o índice'], 500);
        }
    }

    public function destroy($indiceId)
    {
        try {
            $indice = Indice::findOrFail($indiceId);
            $indice->delete();

            return response()->json(['message' => 'Índice deletado com sucesso']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Índice não encontrado'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Ocorreu um erro ao deletar o índice'], 500);
        }
    }
}