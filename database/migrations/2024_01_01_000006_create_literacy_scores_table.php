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
        Schema::create('literacy_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->date('assessment_date');

            // 10 task scores (0-2 scale)
            $table->unsignedTinyInteger('task1_directory')->default(0);
            $table->unsignedTinyInteger('task2_wordproc')->default(0);
            $table->unsignedTinyInteger('task3_research')->default(0);
            $table->unsignedTinyInteger('task4_formatting')->default(0);
            $table->unsignedTinyInteger('task5_saving')->default(0);
            $table->unsignedTinyInteger('task6_spreadsheet')->default(0);
            $table->unsignedTinyInteger('task7_screenshot')->default(0);
            $table->unsignedTinyInteger('task8_zip')->default(0);
            $table->unsignedTinyInteger('task9_sysspecs')->default(0);
            $table->unsignedTinyInteger('task10_notepad')->default(0);

            $table->decimal('total_score', 4, 1)->default(0.0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('literacy_scores');
    }
};
