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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('country_id')->nullable()->constrained('countries')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('phone_no')->nullable();
            $table->string('email')->nullable();
            $table->text('message');
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
        Schema::dropIfExists('contacts');
    }
};
