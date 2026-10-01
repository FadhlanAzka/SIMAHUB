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
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('status', 50)->nullable()->change();
            $table->string('type', 30)->default('WFO')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->enum('status', ['tepat_waktu', 'terlambat'])->nullable()->change();
            $table->enum('type', ['WFO', 'WFH'])->default('WFO')->change();
        });
    }
};
