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
        Schema::create('assessment_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('candidate_id')->nullable()->constrained('candidates')->nullOnDelete();
            $table->enum('role', ['panelist', 'candidate']);
            $table->timestamps();

            $table->index(['assessment_id', 'role']);
            $table->unique(['assessment_id', 'user_id', 'role'], 'assessment_user_role_unique');
            $table->unique(['assessment_id', 'candidate_id', 'role'], 'assessment_candidate_role_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_assignments');
    }
};
