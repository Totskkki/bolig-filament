<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // regions table
        Schema::create('regions', function (Blueprint $table) {
            $table->string('region_code')->primary(); // ex. '01'
            $table->string('region_name');
        });

        // provinces table
        Schema::create('provinces', function (Blueprint $table) {
            $table->string('province_code')->primary(); // ex. '0128'
            $table->string('province_name');
            $table->string('region_code'); // foreign key
            $table->foreign('region_code')->references('region_code')->on('regions');
        });

        // cities table
        Schema::create('cities', function (Blueprint $table) {
            $table->string('city_code')->primary();
            $table->string('city_name');
            $table->string('province_code');
            $table->foreign('province_code')->references('province_code')->on('provinces');
        });

        // barangays table
        Schema::create('barangays', function (Blueprint $table) {
            $table->string('brgy_code')->primary();
            $table->string('brgy_name');
            $table->string('city_code');
            $table->foreign('city_code')->references('city_code')->on('cities');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('barangays');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('countries');
    }
};
