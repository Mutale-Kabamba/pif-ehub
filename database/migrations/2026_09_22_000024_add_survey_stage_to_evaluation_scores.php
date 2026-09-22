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
        Schema::table('evaluation_scores', function (Blueprint $table) {
            if (!Schema::hasColumn('evaluation_scores', 'survey_stage')) {
                $table->string('survey_stage')->default('baseline')->nullable()->after('score');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluation_scores', function (Blueprint $table) {
            if (Schema::hasColumn('evaluation_scores', 'survey_stage')) {
                $table->dropColumn('survey_stage');
            }
        });
    }
};
