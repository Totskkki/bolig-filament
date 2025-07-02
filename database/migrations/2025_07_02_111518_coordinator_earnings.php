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
        Schema::create('coordinator_earnings', function (Blueprint $table) {
            $table->id();


            $table->unsignedBigInteger('contribution_id');
            $table->foreign('contribution_id')
                ->references('consid')
                ->on('contributions')
                ->onDelete('cascade');
            $table->unsignedBigInteger('coordinator_id');
            $table->decimal('share_amount', 10, 2);



            $table->foreign('coordinator_id')->references('memberID')->on('members')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
