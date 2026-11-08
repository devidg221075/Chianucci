<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 2);
            $table->string('name');
            $table->string('slug');
            $table->unique(['category_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->decimal('weight_g', 8, 2)->nullable();
            $table->decimal('length_cm', 8, 2)->nullable();
            $table->decimal('link_mm', 8, 2)->nullable();
            $table->json('titles')->nullable();
            $table->timestamps();
        });

        Schema::create('product_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 2);
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->unique(['product_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('sort')->default(0);
        });
    }

    public function down(): void
    {
        foreach (['product_images', 'product_translations', 'products', 'category_translations', 'categories'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
