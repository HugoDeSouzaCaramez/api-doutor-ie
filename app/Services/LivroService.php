<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\DTO\LivroDTO;

class LivroService
{
    public function listarLivros(array $filters)
    {
        return Livro::with('indices')
            ->when($filters['titulo'], fn($query) => $query->where('titulo', 'like', "%{$filters['titulo']}%"))
            ->get();
    }
}
