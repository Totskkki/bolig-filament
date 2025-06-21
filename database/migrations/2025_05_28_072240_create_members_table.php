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
            $table->id();
            $table->bigInteger('address_id')->nullable()->index('fk_member_address')->index();
            $table->bigInteger('names_id')->nullable()->index('fk_member_names')->index();
            $table->date('membership_date');
            $table->tinyInteger('membership_status')->default(0)->comment('0=active, 1=inactive,2=deceased')->nullable();
            $table->string('phone')->nullable();
            $table->string('image_photo')->nullable();


            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
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
