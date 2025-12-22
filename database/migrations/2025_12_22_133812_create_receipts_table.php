<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('showroom_id')->constrained()->cascadeOnDelete(); // связь с салоном
            $table->string('full_name');
            $table->string('phone');
            $table->text('comment')->nullable();
            $table->decimal('full_price', 12, 2); // сумма чека

            $table->date('repayment_date')->nullable(); // дата погашения
            $table->tinyInteger('type_id')->default(1); // 1 - часть, 2 - полная
            $table->timestamp('closed')->nullable(); // когда закрыт чек

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
