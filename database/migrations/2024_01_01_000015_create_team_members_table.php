<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('authors')->onDelete('cascade');
            $table->string('name', 191);
            $table->string('slug', 191)->unique();
            $table->string('role_title', 191)->nullable();
            $table->text('biography')->nullable();
            $table->string('photo')->nullable();
            $table->string('email', 191)->nullable();
            $table->json('social_links')->nullable();
            $table->integer('sort_order')->default(0);
            $table->unsignedTinyInteger('status')->default(1)->comment('1=Active, 0=Inactive, 4=Deleted');
            $table->timestamps();

            $table->index('status');
            $table->index('author_id');
            $table->index('slug');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
