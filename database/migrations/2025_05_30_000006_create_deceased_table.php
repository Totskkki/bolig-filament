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
        Schema::create('deceased', function (Blueprint $table) {
            $table->bigInteger('deceasedID', true);
            //  $table->bigInteger('member_id')->nullable()->index('fk_deceased_member')->index();

            $table->unsignedBigInteger('member_id');
            $table->foreign('member_id')->references('memberID')->on('members')->cascadeOnDelete();
            $table->date('date_of_death');
            $table->string('month');
            $table->string('year');
            $table->text('cause_of_death')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deaths');
    }
};
