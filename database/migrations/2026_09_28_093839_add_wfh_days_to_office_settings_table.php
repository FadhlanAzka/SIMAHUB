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
        Schema::table('office_settings', function (Blueprint $table) {
            $table->json('wfh_days')->nullable()->after('work_end_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('office_settings', function (Blueprint $table) {
            $table->dropColumn('wfh_days');
        });
    }
};
