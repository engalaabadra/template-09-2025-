<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     */
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('lang')->default(defaultLang());
            $table->foreignId('translate_id')->nullable()->constrained('countries', 'id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name',225);
            $table->string('flag')->nullable();
            $table->string('code');
            $table->string('code2')->nullable();
            $table->string('numcode')->nullable();
            $table->string('phone_code');
            $table->boolean('is_active')->default(\App\Enums\IsActiveEnum::ACTIVE->value);//0:not_active 1:active 
            $table->date('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
