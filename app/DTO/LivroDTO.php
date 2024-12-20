<?php

namespace App\DTO;

class LivroDTO
{
    public function __construct(
        public string $titulo,
        public int $usuarioPublicadorId
    ) {}
}
