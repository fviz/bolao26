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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('missing_prediction_reminders_enabled')->default(true);
            $table->boolean('game_result_notifications_enabled')->default(true);
            $table->boolean('daily_summary_enabled')->default(true);
            $table->boolean('tournament_deadline_enabled')->default(true);
            $table->boolean('browser_notifications_enabled')->default(false);
            $table->unsignedSmallInteger('game_reminder_minutes')->default(60);
            $table->time('daily_summary_time')->default('09:00');
            $table->string('daily_summary_timezone')->default('UTC');
            $table->timestamps();

            $table->unique('user_id');
            $table->index(['daily_summary_enabled', 'daily_summary_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
