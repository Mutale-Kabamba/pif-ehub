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
        Schema::table('assessments', function (Blueprint $table) {
            $table->string('access_key')->nullable()->unique()->after('status');
        });

        Schema::table('assessment_assignments', function (Blueprint $table) {
            $table->string('panel_name', 50)->nullable()->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn('access_key');
        });

        Schema::table('assessment_assignments', function (Blueprint $table) {
            $table->dropColumn('panel_name');
        });
    }
};
