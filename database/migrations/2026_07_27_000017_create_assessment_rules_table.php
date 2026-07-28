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
        Schema::create('assessment_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->unsignedInteger('max_panelists')->nullable();
            $table->decimal('score_cap', 10, 2)->nullable();
            $table->decimal('passing_threshold', 10, 2)->nullable();
            $table->json('rules_json')->nullable();
            $table->timestamps();

            $table->unique('assessment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_rules');
    }
};
