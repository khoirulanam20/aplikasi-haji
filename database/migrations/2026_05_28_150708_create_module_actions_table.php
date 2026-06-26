<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->string('action');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['module_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_actions');
    }
};
