<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('structures', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->string('type');                  // App\Enums\TypeStructure
            $table->foreignId('parent_id')->nullable()->constrained('structures')->nullOnDelete();
            $table->string('region')->nullable();
            $table->string('province')->nullable();
            $table->foreignId('localite_id')->nullable()->constrained('localites')->nullOnDelete();
            // Responsable : référence souple vers agents (FK ajoutée après la table agents)
            $table->unsignedBigInteger('responsable_agent_id')->nullable()->index();
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['type', 'region']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('structures');
    }
};
