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
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_title')->default('Encuentra perfiles destacados cerca de ti');
            $table->string('site_subtitle')->default('Explora anuncios verificados con filtros rápidos, experiencia limpia y una navegación diseñada para inspirar confianza.');
            $table->string('cover_image_url')->nullable();
            $table->string('primary_color', 16)->default('#E91E63');
            $table->string('primary_hover_color', 16)->default('#D81B60');
            $table->string('text_color', 16)->default('#222222');
            $table->string('muted_color', 16)->default('#6B7280');
            $table->string('background_color', 16)->default('#F8F8F8');
            $table->string('admin_ink_color', 16)->default('#1f2937');
            $table->string('admin_ink_hover_color', 16)->default('#374151');
            $table->string('admin_muted_color', 16)->default('#4b5563');
            $table->string('admin_danger_color', 16)->default('#dc2626');
            $table->string('admin_focus_color', 16)->default('#6366f1');
            $table->string('server_country')->default('Perú');
            $table->string('server_country_code', 8)->default('PE');
            $table->string('server_utc_offset', 8)->default('-05:00');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
