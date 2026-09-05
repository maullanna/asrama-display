<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                  // "Lantai 1"
            $table->string('slug', 30)->unique();                    // "lantai-1", dipakai di URL kiosk
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('floor_id')->constrained()->cascadeOnDelete();
            $table->string('room_number', 20);                       // "101"
            $table->unsignedTinyInteger('capacity')->default(8);
            $table->string('access_pin', 10)->nullable();            // PIN ketua kamar untuk form izin
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['floor_id', 'room_number']);
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->string('student_code', 30)->unique();            // NIM
            $table->string('device_pin', 20)->nullable()->unique();  // PIN di mesin fingerprint
            $table->string('name');
            $table->string('photo_path')->nullable();
            $table->boolean('is_room_leader')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('device_pin');
            $table->index(['room_id', 'is_active']);
        });

        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->string('device_pin', 20);                        // apa adanya dari mesin
            $table->string('device_sn', 40)->nullable();             // serial number mesin
            $table->timestamp('scanned_at');
            $table->enum('direction', ['ci', 'co'])->default('ci');
            $table->string('method', 20)->default('fingerprint');    // fingerprint | qr | manual
            $table->timestamps();

            $table->index(['student_id', 'scanned_at']);
            $table->index('scanned_at');
            $table->index('device_pin');
        });

        Schema::create('student_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['sakit', 'izin']);
            $table->enum('direction', ['ci', 'co'])->nullable();      // hanya diisi kalau type = izin
            $table->date('start_date');
            $table->date('end_date')->nullable();                     // null = masih berlangsung
            $table->string('note')->nullable();
            $table->foreignId('reported_by_student_id')->nullable()
                  ->constrained('students')->nullOnDelete();          // ketua kamar yang input
            $table->timestamps();

            $table->index(['student_id', 'start_date']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_conditions');
        Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('students');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('floors');
    }
};
