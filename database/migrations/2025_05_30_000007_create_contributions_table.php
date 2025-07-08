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
            $table->id('consid');

            $table->unsignedBigInteger('payer_memberID');
            $table->foreign('payer_memberID')->references('memberID')->on('members')->cascadeOnDelete();
            $table->unsignedBigInteger('deceased_id')->nullable();
            $table->foreign('deceased_id')->references('deceasedID')->on('deceased')->nullOnDelete();
            $table->decimal('amount', 15);
            $table->decimal('adjusted_amount')->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->string('payment_batch')->nullable();
            $table->string('month')->nullable();
            $table->string('year')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0=unpaid, 1=paid');
            $table->tinyInteger('release_status')->default(0)->comment('0=pending, 1=released', '2=partial');
            $table->unsignedBigInteger('released_by')->nullable();
            $table->foreign('released_by')->references('userid')->on('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable();
            $table->string('release_receipt_path')->nullable();
            $table->string('signature_path')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('coordinator_id')->nullable();
            $table->foreign('coordinator_id')->references('memberID')->on('members')->onDelete('set null');
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
