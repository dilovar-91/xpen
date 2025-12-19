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
            $table->unsignedTinyInteger('income_type')
                ->nullable()
                ->comment('1 = Наличка, 2 = Безнал')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedTinyInteger('income_type')
                ->nullable(false)
                ->comment('1 = Наличка, 2 = Безнал')
                ->change();
        });
    }
};
