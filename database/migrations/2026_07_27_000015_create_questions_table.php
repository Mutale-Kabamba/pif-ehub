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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->text('question_text');
            $table->enum('type', ['scale', 'text', 'multiple_choice', 'boolean'])->default('scale');
            $table->decimal('weight', 8, 2)->default(1.00);
            $table->unsignedInteger('order')->default(1);
            $table->timestamps();

            $table->index(['assessment_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
