<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Indice extends Model
{
    protected $fillable = ['livro_id', 'indice_pai_id', 'titulo', 'pagina'];

    public function subindices()
    {
        return $this->hasMany(self::class, 'indice_pai_id');
    }

    public function livro()
    {
        return $this->belongsTo(Livro::class);
    }
}