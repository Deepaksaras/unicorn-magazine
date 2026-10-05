<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->onDelete('cascade');
            $table->string('title', 191);
            $table->string('url', 191)->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('target', 50)->default('_self');
            $table->integer('position')->default(0);
            $table->string('css_class', 191)->nullable();
            $table->unsignedTinyInteger('status')->default(1)->comment('1=Active, 0=Inactive, 4=Deleted');
            $table->timestamps();

            $table->index('status');
            $table->index('menu_id');
            $table->index('parent_id');
            $table->index('position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
