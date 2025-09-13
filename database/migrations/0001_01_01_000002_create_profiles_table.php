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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('lang')->default(defaultLang());
            $table->foreignId('translate_id')->nullable()->constrained('profiles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('username');
            $table->string('full_name')->nullable();
            $table->string('nick_name')->nullable();
            $table->string('gender')->nullable();
            $table->string('address')->nullable();
            $table->date('birth_date')->nullable();

            //for author
            $table->integer('searches_count')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
