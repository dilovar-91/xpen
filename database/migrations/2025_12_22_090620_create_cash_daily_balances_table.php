<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cash_daily_balances', function (Blueprint $table) {
            $table->id();

            $table->date('date');

            $table->unsignedBigInteger('showroom_id');

            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->default(0);

            $table->boolean('approved')->default(false);

            $table->timestamps();

            // индексы и ограничения
            $table->unique(['date', 'showroom_id']);
            $table->foreign('showroom_id')
                ->references('id')
                ->on('showrooms')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_daily_balances');
    }
};

