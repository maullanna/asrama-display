<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            // Tandai kamar sebagai Ruang Isolasi (kapasitas 2 bet).
            $table->boolean('is_isolation')->default(false)->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('is_isolation');
        });
    }
};
