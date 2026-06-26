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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('domain');
            $table->string('label');
            $table->string('route_name');
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('permission_name')->nullable();
            $table->timestamps();

            $table->index(['domain', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
