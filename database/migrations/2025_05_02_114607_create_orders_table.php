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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('content_id')->constrained('contents')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained('payment_methods')->cascadeOnUpdate()->cascadeOnDelete();
            $table->decimal('total', 10, 2)->nullable();
            $table->integer('quantity')->nullable();
            $table->string('payment_status')->default(\App\Enums\PaymentStatusEnum::PENDING->value);
            $table->string('status')->default(\App\Enums\OrderStatusEnum::PENDING->value);
            $table->boolean('is_active')->default(\App\Enums\IsActiveEnum::ACTIVE->value);//0:not_active 1:active 
            $table->date('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
