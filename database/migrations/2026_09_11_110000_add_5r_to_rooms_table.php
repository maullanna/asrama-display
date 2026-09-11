<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            // Penilaian 5R oleh super admin: hijau (baik) / kuning (progres) / merah (kurang).
            $table->string('status_color', 10)->nullable()->after('access_pin');
            $table->string('keterangan')->nullable()->after('status_color');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['status_color', 'keterangan']);
        });
    }
};
