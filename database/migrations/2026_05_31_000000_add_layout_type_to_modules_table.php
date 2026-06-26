<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->string('layout_type', 20)->default('table_base')->after('code');
        });

        DB::table('modules')
            ->where('code', 'websetting')
            ->update(['layout_type' => 'form_base']);
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('layout_type');
        });
    }
};
