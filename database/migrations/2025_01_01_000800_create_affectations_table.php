<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affectations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->foreignId('ancienne_structure_id')->nullable()->constrained('structures')->nullOnDelete();
            $table->foreignId('nouvelle_structure_id')->nullable()->constrained('structures')->nullOnDelete();
            $table->foreignId('ancienne_fonction_id')->nullable()->constrained('fonctions')->nullOnDelete();
            $table->foreignId('nouvelle_fonction_id')->nullable()->constrained('fonctions')->nullOnDelete();
            $table->date('date_effet');
            $table->string('reference_acte')->nullable();
            $table->string('motif')->nullable();
            $table->string('document_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['agent_id', 'date_effet']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affectations');
    }
};
