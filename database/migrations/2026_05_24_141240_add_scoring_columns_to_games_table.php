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
        Schema::table('games', function (Blueprint $table) {
            $table->unsignedTinyInteger('home_penalty_score')->nullable()->after('away_score');
            $table->unsignedTinyInteger('away_penalty_score')->nullable()->after('home_penalty_score');
            $table->string('penalty_winner', 4)->nullable()->after('away_penalty_score');
            $table->timestamp('scored_at')->nullable()->after('synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn([
                'home_penalty_score',
                'away_penalty_score',
                'penalty_winner',
                'scored_at',
            ]);
        });
    }
};
