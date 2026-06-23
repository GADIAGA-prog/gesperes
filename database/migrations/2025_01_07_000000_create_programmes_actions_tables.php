<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nomenclature budgétaire : Programme → Action (plusieurs actions par programme).
 * Les structures opérationnelles sont rattachées à une action.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programmes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::create('actions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->foreignId('programme_id')->nullable()->constrained('programmes')->nullOnDelete();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::table('structures', function (Blueprint $table) {
            $table->foreignId('action_id')->nullable()->after('parent_id')->constrained('actions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('structures', function (Blueprint $table) {
            $table->dropConstrainedForeignId('action_id');
        });

        Schema::dropIfExists('actions');
        Schema::dropIfExists('programmes');
    }
};
