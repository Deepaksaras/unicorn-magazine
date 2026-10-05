<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertising_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertisement_id')->constrained('advertisements')->onDelete('cascade');
            $table->date('stat_date');
            $table->integer('impressions')->default(0);
            $table->integer('clicks')->default(0);
            $table->decimal('ctr', 5, 2)->default(0)->comment('Click-through rate');
            $table->decimal('revenue', 10, 2)->default(0);
            $table->string('currency', 3)->default('INR');
            $table->unsignedTinyInteger('status')->default(1)->comment('1=Active, 0=Inactive, 4=Deleted');
            $table->timestamps();

            $table->index('status');
            $table->index('advertisement_id');
            $table->index('stat_date');
            $table->unique(['advertisement_id', 'stat_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertising_stats');
    }
};
