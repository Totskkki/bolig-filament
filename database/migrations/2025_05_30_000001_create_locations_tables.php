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
            $table->id();
            $table->string('region_code')->unique();
            $table->string('region_name');
        });

        // provinces table
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('province_code')->unique();
            $table->string('province_name');
            $table->foreignId('region_id')->constrained()->onDelete('cascade');
        });

        // cities table
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('city_code')->unique();
            $table->string('city_name');
            $table->foreignId('province_id')->constrained()->onDelete('cascade');
        });

        // barangays table
        Schema::create('barangays', function (Blueprint $table) {
            $table->id();
            $table->string('brgy_code')->unique();
            $table->string('brgy_name');
            $table->foreignId('city_id')->constrained()->onDelete('cascade');
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
