<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('indices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('livro_id')->constrained('livros')->onDelete('cascade');
            $table->foreignId('indice_pai_id')->nullable()->constrained('indices')->onDelete('cascade');
            $table->string('titulo');
            $table->integer('pagina');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('indices');
    }
};
