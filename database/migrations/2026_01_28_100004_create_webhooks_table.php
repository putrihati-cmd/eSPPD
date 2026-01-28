<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Webhooks for external integrations
        if (!Schema::hasTable('webhooks')) {
            Schema::create('webhooks', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('url');
                $table->json('events'); // array of event types
                $table->string('secret');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Webhook delivery logs
        if (!Schema::hasTable('webhook_logs')) {
            Schema::create('webhook_logs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('webhook_id')->constrained()->cascadeOnDelete();
                $table->string('event');
                $table->json('payload');
                $table->integer('response_code')->nullable();
                $table->text('response_body')->nullable();
                $table->boolean('success')->default(false);
                $table->timestamps();
            });
        }
    }


    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('webhooks');
    }
};
