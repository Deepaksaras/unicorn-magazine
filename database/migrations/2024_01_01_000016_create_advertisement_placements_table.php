<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisement_placements', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('slug', 191)->unique();
            $table->string('description', 191)->nullable();
            $table->string('dimensions', 50)->nullable()->comment('e.g., 728x90, 300x250');
            $table->string('location', 100)->nullable()->comment('header, sidebar, footer, in-article');
            $table->integer('max_ads')->default(1);
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('status')->default(1)->comment('1=Active, 0=Inactive, 4=Deleted');
            $table->timestamps();

            $table->index('status');
            $table->index('slug');
            $table->index('location');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisement_placements');
    }
};
