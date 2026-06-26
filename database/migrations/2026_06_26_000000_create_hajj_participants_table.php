<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hajj_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun_haji');
            $table->string('nomor_porsi', 20)->nullable();
            $table->string('nama');
            $table->text('alamat')->nullable();
            $table->string('desa')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('telepon', 30)->nullable();
            $table->string('kloter', 20)->nullable();
            $table->string('rombongan', 20)->nullable();
            $table->string('regu', 20)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tahun_haji', 'nomor_porsi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hajj_participants');
    }
};
