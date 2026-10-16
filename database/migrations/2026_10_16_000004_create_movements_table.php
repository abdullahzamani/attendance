<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->foreignId('movement_reason_id')->constrained()->restrictOnDelete();
            $table->text('note')->nullable();
            $table->dateTime('out_at');
            $table->dateTime('expected_back_at')->nullable();
            $table->dateTime('in_at')->nullable();
            $table->foreignId('checked_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['staff_id', 'in_at']);
            $table->index('out_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
