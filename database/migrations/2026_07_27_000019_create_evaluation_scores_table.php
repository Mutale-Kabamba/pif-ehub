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
        Schema::create('evaluation_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->foreignId('candidate_id')->nullable()->constrained('candidates')->nullOnDelete();
            $table->foreignId('evaluator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->decimal('score', 10, 2)->nullable();
            $table->text('text_response')->nullable();
            $table->timestamps();

            $table->index(['assessment_id', 'candidate_id']);
            $table->index(['assessment_id', 'evaluator_id']);
            $table->unique(
                ['assessment_id', 'candidate_id', 'evaluator_id', 'question_id'],
                'evaluation_unique_entry'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_scores');
    }
};
