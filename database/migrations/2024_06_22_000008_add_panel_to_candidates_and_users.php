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
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('panel', 1)->nullable()->after('name'); // 'A' or 'B'
            $table->string('gender', 10)->nullable()->after('panel');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('panel', 10)->nullable()->after('panelist_name'); // 'A', 'B', or 'cover'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn(['panel', 'gender']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('panel');
        });
    }
};
