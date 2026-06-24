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
        Schema::create('age_gate_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(true);
            $table->string('storage_key')->default('gatitas_hot_age_confirmed');
            $table->string('badge', 24)->default('+18');
            $table->string('title')->default('Contenido sensible para adultos');
            $table->text('description');
            $table->string('confirm_label')->default('Soy mayor de edad');
            $table->string('exit_label')->default('Salir');
            $table->string('exit_href')->default('https://www.google.com');
            $table->text('legal_text');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('age_gate_settings');
    }
};
