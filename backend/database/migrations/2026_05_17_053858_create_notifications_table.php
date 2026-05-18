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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('batch_id');
            $table->unsignedBigInteger('recipient_id');
            $table->string('status')
                ->default('queued');
            $table->unsignedInteger('retry_count')
                ->default(0);
            $table->text('provider_response')
                ->nullable();
            $table->timestamp('sent_at')
                ->nullable();
            $table->timestamp('delivered_at')
                ->nullable();
            $table->timestamps();
            $table->foreign('batch_id')
                ->references('id')
                ->on('notification_batches')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
