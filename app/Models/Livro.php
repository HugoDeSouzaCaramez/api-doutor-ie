<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Livro extends Model
{
    protected $fillable = ['usuario_publicador_id', 'titulo'];

    public function indices()
    {
        return $this->hasMany(Indice::class);
    }

    public function usuarioPublicador()
    {
        return $this->belongsTo(User::class, 'usuario_publicador_id');
    }
}