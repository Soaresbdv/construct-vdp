<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');          // Nome da Obra (ex: Edifício Horizon)
            $table->string('location');       // Localização (ex: Curitiba, PR)
            $table->string('status')->default('in_progress'); // status: planning, in_progress, finished
            $table->string('image_url')->nullable(); // Foto da capa
            $table->date('start_date');       // Data de início
            $table->date('end_date')->nullable(); // Previsão de entrega
            $table->timestamps();             // created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
