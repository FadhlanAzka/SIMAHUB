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
        Schema::create('office_settings', function (Blueprint $table) {
            $table->id();
            $table->string('office_name')->default('Kantor Instansi MagangHub');
            $table->decimal('latitude', 10, 8)->default(-6.17539240); // Default koordinat pusat
            $table->decimal('longitude', 11, 8)->default(106.82715280);
            $table->integer('radius_meters')->default(100); // 100 meter radius instansi
            $table->time('work_start_time')->default('07:30:00'); // Batas telat masuk
            $table->time('work_end_time')->default('16:00:00'); // Batas minimal pulang
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_settings');
    }
};
