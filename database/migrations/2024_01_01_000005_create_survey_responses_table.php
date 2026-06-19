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
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->enum('survey_type', ['baseline', 'endline']);

            // 11 quantitative questions (1-5 scale)
            $table->unsignedTinyInteger('q1_os_filemgmt')->default(3);
            $table->unsignedTinyInteger('q2_spreadsheets')->default(3);
            $table->unsignedTinyInteger('q3_ux_design')->default(3);
            $table->unsignedTinyInteger('q4_frontend')->default(3);
            $table->unsignedTinyInteger('q5_js_logic')->default(3);
            $table->unsignedTinyInteger('q6_fullstack')->default(3);
            $table->unsignedTinyInteger('q7_resilience')->default(3);
            $table->unsignedTinyInteger('q8_troubleshooting')->default(3);
            $table->unsignedTinyInteger('q9_freelance')->default(3);
            $table->unsignedTinyInteger('q10_livingstone_tourism')->default(3);
            $table->unsignedTinyInteger('q11_career_efficacy')->default(3);

            // 4 qualitative questions
            $table->text('qual1_why_join')->nullable();
            $table->text('qual2_skills_hoped')->nullable();
            $table->text('qual3_success_criteria')->nullable();
            $table->text('qual4_challenges')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};
