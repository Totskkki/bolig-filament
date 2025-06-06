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

          //  $table->bigInteger('user_id')->index('fk_member_user')->index();
            $table->bigInteger('address_id')->nullable()->index('fk_member_address')->index();
            $table->bigInteger('names_id')->nullable()->index('fk_member_names')->index();
            $table->date('membership_date');
            $table->enum('membership_status', ['active', 'inactive','deceased'])->nullable()->default('active');
               $table->string('phone')->nullable();
                $table->string('photo')->nullable();
           // $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

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
