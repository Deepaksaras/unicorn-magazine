<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title', 191);
            $table->string('slug', 191)->unique();
            $table->text('content')->nullable();
            $table->string('template', 100)->default('default');
            $table->string('featured_image')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedTinyInteger('status')->default(0)->comment('1=Published, 0=Draft, 4=Deleted');
            $table->timestamps();

            $table->index('status');
            $table->index('user_id');
            $table->index('slug');
            $table->index('template');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
