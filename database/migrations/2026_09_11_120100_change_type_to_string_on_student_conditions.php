<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah enum('sakit','izin') -> string agar 'isolasi' bisa disimpan.
        Schema::table('student_conditions', function (Blueprint $table) {
            $table->string('type', 20)->change();
        });
    }

    public function down(): void
    {
        Schema::table('student_conditions', function (Blueprint $table) {
            $table->enum('type', ['sakit', 'izin'])->change();
        });
    }
};
