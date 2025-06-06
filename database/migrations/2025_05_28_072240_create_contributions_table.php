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
        Schema::create('contributions', function (Blueprint $table) {
            $table->bigInteger('consid', true);
            $table->bigInteger('payer_memberID')->index('fk_contribution_member')->index();
            $table->bigInteger('deceasedID')->index('fk_deceased')->index();
            $table->decimal('amount', 15);
            $table->decimal('adjusted_amount')->nullable();
            $table->date('payment_date')->nullable();
             $table->tinyInteger('status')->default(0)->comment('0=unpaid, 1=paid');
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};
