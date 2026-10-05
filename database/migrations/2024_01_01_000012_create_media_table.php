<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('file_name', 191);
            $table->string('file_path', 191);
            $table->string('file_type', 50);
            $table->string('mime_type', 100);
            $table->integer('file_size')->nullable()->comment('in bytes');
            $table->string('disk', 50)->default('public');
            $table->string('alt_text', 191)->nullable();
            $table->string('caption', 191)->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('thumbnail_path', 191)->nullable();
            $table->string('webp_path', 191)->nullable();
            $table->unsignedTinyInteger('status')->default(1)->comment('1=Active, 0=Inactive, 4=Deleted');
            $table->timestamps();

            $table->index('status');
            $table->index('user_id');
            $table->index('file_type');
            $table->index('mime_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
