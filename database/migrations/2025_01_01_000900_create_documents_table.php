<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->string('type_document');             // App\Enums\TypeDocument
            $table->string('reference')->nullable();
            $table->date('date_document')->nullable();
            $table->date('date_expiration')->nullable();
            $table->string('chemin');                    // chemin sur le disque "documents"
            $table->string('nom_original')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('taille')->nullable();
            $table->string('statut')->default('valide'); // valide / expire / archive
            $table->text('commentaire')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['agent_id', 'type_document']);
            $table->index('date_expiration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
