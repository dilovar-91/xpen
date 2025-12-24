<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_daily_balances', function (Blueprint $table) {
            $table->boolean('manually_changed')
                ->default(false)
                ->after('closing_balance');
        });
    }

    public function down(): void
    {
        Schema::table('cash_daily_balances', function (Blueprint $table) {
            $table->dropColumn('manually_changed');
        });
    }
};
