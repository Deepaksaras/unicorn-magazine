<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_key', 100)->unique();
            $table->string('title', 191);
            $table->string('big_text', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('link_text', 100)->nullable();
            $table->string('link_url', 191)->nullable();
            $table->integer('position')->default(0);
            $table->unsignedTinyInteger('status')->default(1)->comment('1=Active, 0=Inactive, 4=Deleted');
            $table->timestamps();

            $table->index('status');
            $table->index('section_key');
            $table->index('position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_sections');
    }
};
