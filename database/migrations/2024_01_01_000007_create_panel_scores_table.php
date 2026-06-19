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
        Schema::create('panel_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->foreignId('panelist_id')->constrained('users')->onDelete('cascade');

            // 4 criteria (1-5 scale)
            $table->unsignedTinyInteger('crit1_motivation')->default(3);
            $table->unsignedTinyInteger('crit2_availability')->default(3);
            $table->unsignedTinyInteger('crit3_resilience')->default(3);
            $table->unsignedTinyInteger('crit4_communication')->default(3);

            $table->text('comments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panel_scores');
    }
};
