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
        Schema::table('receipts', function (Blueprint $table) {
            // если closed существовал
            if (Schema::hasColumn('receipts', 'closed')) {
                $table->dropColumn('closed');
            }

            $table->date('closed_date')->nullable()->after('full_price');
            $table->boolean('close')->default(false)->after('closed_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropColumn(['closed_date', 'close']);
            $table->boolean('closed')->default(false);
        });
    }
};
