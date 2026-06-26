<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_configs', function (Blueprint $table) {
            $table->boolean('registration_enabled')->nullable();
            $table->boolean('two_factor_enabled')->nullable();
            $table->boolean('security_csp_enabled')->nullable();
            $table->unsignedSmallInteger('impersonation_timeout_minutes')->nullable();
            $table->string('hajj_participant_role')->nullable();
            $table->string('hajj_participant_email_domain')->nullable();
            $table->text('sentry_dsn')->nullable();
            $table->string('sentry_environment')->nullable();
            $table->decimal('sentry_traces_sample_rate', 4, 3)->nullable();
            $table->text('postmark_api_key')->nullable();
            $table->text('resend_api_key')->nullable();
            $table->text('slack_bot_token')->nullable();
            $table->string('slack_bot_channel')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('app_configs', function (Blueprint $table) {
            $table->dropColumn([
                'registration_enabled',
                'two_factor_enabled',
                'security_csp_enabled',
                'impersonation_timeout_minutes',
                'hajj_participant_role',
                'hajj_participant_email_domain',
                'sentry_dsn',
                'sentry_environment',
                'sentry_traces_sample_rate',
                'postmark_api_key',
                'resend_api_key',
                'slack_bot_token',
                'slack_bot_channel',
            ]);
        });
    }
};
