<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();
            $table->morphs('seoable');
            $table->string('meta_title', 191)->nullable();
            $table->string('meta_description', 191)->nullable();
            $table->string('meta_keywords', 191)->nullable();
            $table->string('canonical_url', 191)->nullable();
            $table->string('og_title', 191)->nullable();
            $table->string('og_description', 191)->nullable();
            $table->string('og_image', 191)->nullable();
            $table->string('og_type', 50)->nullable();
            $table->string('twitter_card', 50)->nullable();
            $table->string('twitter_title', 191)->nullable();
            $table->string('twitter_description', 191)->nullable();
            $table->string('twitter_image', 191)->nullable();
            $table->json('schema_markup')->nullable();
            $table->string('robots', 50)->nullable()->comment('index, noindex, follow, nofollow');
            $table->unsignedTinyInteger('status')->default(1)->comment('1=Active, 0=Inactive, 4=Deleted');
            $table->timestamps();

            $table->index('status');
            $table->index(['seoable_id', 'seoable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_meta');
    }
};
