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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('fifa_match_id')->unique();
            $table->unsignedSmallInteger('match_number')->nullable();
            $table->string('id_season')->nullable();
            $table->string('id_stage')->nullable();
            $table->string('id_group')->nullable();
            $table->string('stage_name')->nullable();
            $table->string('group_name')->nullable();
            $table->dateTime('scheduled_at');
            $table->dateTime('local_scheduled_at')->nullable();
            $table->string('home_fifa_team_id')->nullable();
            $table->string('home_name')->nullable();
            $table->string('home_abbr', 10)->nullable();
            $table->string('home_placeholder', 10)->nullable();
            $table->string('away_fifa_team_id')->nullable();
            $table->string('away_name')->nullable();
            $table->string('away_abbr', 10)->nullable();
            $table->string('away_placeholder', 10)->nullable();
            $table->string('stadium_name')->nullable();
            $table->string('city_name')->nullable();
            $table->unsignedTinyInteger('match_status');
            $table->unsignedTinyInteger('home_score')->nullable();
            $table->unsignedTinyInteger('away_score')->nullable();
            $table->boolean('time_defined')->default(true);
            $table->boolean('is_final')->default(false);
            $table->json('payload');
            $table->timestamp('synced_at');
            $table->timestamps();

            $table->index('scheduled_at');
            $table->index(['is_final', 'scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
