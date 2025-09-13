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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            
            $table->string('lang')->default(defaultLang());
            $table->foreignId('translate_id')->nullable()->constrained('settings', 'id')->cascadeOnUpdate()->cascadeOnDelete();
                
            $table->string('key');
            $table->text('value');
            $table->string('type');
            $table->string('group');

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
        Schema::dropIfExists('settings');
    }
};
