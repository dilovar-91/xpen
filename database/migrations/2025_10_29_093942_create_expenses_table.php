<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->nullable()->constrained(table: 'users', indexName: 'manager_id')->nullOnDelete();
            $table->foreignId('showroom_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('income', 10, 2);
            $table->decimal('expense', 10, 2);
            $table->decimal('balance', 10, 2);
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
