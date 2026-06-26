<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            $table->char('primary_color', 7)->default('#FF5B37')->after('favicon_path');
            $table->char('secondary_color', 7)->default('#4B5694')->after('primary_color');
        });
    }

    public function down(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            $table->dropColumn(['primary_color', 'secondary_color']);
        });
    }
};
