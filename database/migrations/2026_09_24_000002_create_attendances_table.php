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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('date')->index();
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->enum('status', ['tepat_waktu', 'terlambat'])->nullable();
            $table->enum('type', ['WFO', 'WFH'])->default('WFO');
            $table->decimal('check_in_lat', 10, 8)->nullable();
            $table->decimal('check_in_long', 11, 8)->nullable();
            $table->integer('check_in_distance')->nullable(); // dalam meter
            $table->decimal('check_out_lat', 10, 8)->nullable();
            $table->decimal('check_out_long', 11, 8)->nullable();
            $table->integer('check_out_distance')->nullable(); // dalam meter
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
