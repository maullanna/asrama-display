<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mahasiswa vokasi (kelompok A10) — di luar asrama, tanpa kamar/fingerprint.
        Schema::create('vocations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('photo_path')->nullable();
            $table->string('location', 20);          // 'karawang' | 'sunter'
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['location', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vocations');
    }
};
