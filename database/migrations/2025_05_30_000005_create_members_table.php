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
        Schema::create('members', function (Blueprint $table) {
            $table->bigIncrements('memberID');

            $table->unsignedBigInteger('names_id');
            $table->foreign('names_id')->references('namesid')->on('names')->cascadeOnDelete();

            $table->unsignedBigInteger('address_id');
            $table->foreign('address_id')->references('addressid')->on('addresses')->cascadeOnDelete();

            $table->date('membership_date');
            $table->tinyInteger('membership_status')->default(0)->nullable()->comment('0=active, 1=inactive, 2=deceased');
            $table->string('phone')->nullable();
            $table->string('image_photo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
