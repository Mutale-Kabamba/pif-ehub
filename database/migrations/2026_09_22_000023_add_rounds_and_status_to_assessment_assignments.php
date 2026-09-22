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
        Schema::table('assessment_assignments', function (Blueprint $table) {
            $table->unsignedInteger('round')->default(1)->after('panel_name');
            $table->string('selection_status', 50)->default('pending')->after('round'); // pending, selected, reserve, pulled_out, rejected
            $table->text('selection_notes')->nullable()->after('selection_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_assignments', function (Blueprint $table) {
            $table->dropColumn(['round', 'selection_status', 'selection_notes']);
        });
    }
};
