<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_settlements', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->unique();
            $table->string('title')->nullable();
            $table->decimal('total_days', 8, 2)->default(20);
            $table->decimal('probation_cap', 8, 2)->default(2);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_settlements');
    }
};