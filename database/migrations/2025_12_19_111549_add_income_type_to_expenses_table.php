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
                ->comment('1 = Наличка, 2 = Безнал');

            $table->unsignedTinyInteger('accepted')->nullable()->default(0);

            $table->decimal('remaining_cash', 15, 2)->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('income_type');
            $table->dropColumn('remaining_cash');
            $table->dropColumn('accepted');
        });
    }
};
