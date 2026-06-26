<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('integration_settings');
    }

    public function down(): void
    {
        // Tabel integration_settings tidak lagi dipakai; tidak dibuat ulang saat rollback.
    }
};
