<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_configs', function (Blueprint $table) {
            $table->id();
            $table->string('google_client_id')->nullable();
            $table->text('google_client_secret')->nullable();
            $table->string('google_redirect_uri')->nullable();
            $table->string('mail_mailer')->nullable()->default('smtp');
            $table->string('mail_host')->nullable();
            $table->unsignedSmallInteger('mail_port')->nullable();
            $table->string('mail_username')->nullable();
            $table->text('mail_password')->nullable();
            $table->string('mail_encryption')->nullable();
            $table->string('mail_from_address')->nullable();
            $table->string('mail_from_name')->nullable();
            $table->string('ai_provider')->nullable();
            $table->text('ai_api_key')->nullable();
            $table->string('ai_base_url')->nullable();
            $table->string('ai_model')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_configs');
    }
};
