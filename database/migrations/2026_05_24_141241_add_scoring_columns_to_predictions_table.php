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
        Schema::table('predictions', function (Blueprint $table) {
            $table->string('penalty_winner', 4)->nullable()->after('away_score');
            $table->unsignedSmallInteger('points')->nullable()->after('penalty_winner');
            $table->timestamp('scored_at')->nullable()->after('points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropColumn(['penalty_winner', 'points', 'scored_at']);
        });
    }
};
