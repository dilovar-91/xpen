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
        Schema::table('expenses', function (Blueprint $table) {
            $table->decimal('income', 15, 2)->default(0)->nullable()->change();
            $table->decimal('expense', 15, 2)->default(0)->nullable()->change();
            $table->decimal('balance', 15, 2)->default(0)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->decimal('income', 10, 2)->default(0)->nullable()->change();
            $table->decimal('expense', 10, 2)->default(0)->nullable()->change();
            $table->decimal('balance', 10, 2)->default(0)->nullable()->change();
        });
    }
};
