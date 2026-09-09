<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('year_start');
            $table->date('year_end');
            $table->decimal('total_days', 8, 2)->default(0);
            $table->decimal('used_days', 8, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'year_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};