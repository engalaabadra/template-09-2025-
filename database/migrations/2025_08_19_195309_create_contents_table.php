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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            
            $table->string('lang')->default(defaultLang());
            $table->foreignId('translate_id')->nullable()->constrained('contents', 'id')->cascadeOnUpdate()->cascadeOnDelete();
            
            $table->foreignId('parent_content_id')->nullable()->constrained('contents', 'id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users', 'id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('shelf_id')->constrained('shelves', 'id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories', 'id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('type')->index(); // article, book           
            $table->longText('description')->nullable();
            $table->longText('content_text')->nullable();
            $table->longText('summery')->nullable();
            $table->longText('chapters')->nullable();
            $table->boolean('is_featured')->default(\App\Enums\IsFeaturedEnum::INFEATURED->value);//0:infeatured 1:featured 
            $table->integer('reads_count')->nullable();
            $table->integer('searches_count')->nullable();
            $table->integer('likes_count')->nullable();
            $table->integer('reviews_count')->nullable();
            $table->date('published_at')->nullable();
            
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
        Schema::dropIfExists('contents');
    }
};
