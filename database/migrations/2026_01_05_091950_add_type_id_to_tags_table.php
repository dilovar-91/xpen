<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->unsignedBigInteger('type_id')->nullable()->after('id');

            // если есть таблица types
            // $table->foreign('type_id')
            //       ->references('id')
            //       ->on('types')
            //       ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            // если добавлял foreign key
            // $table->dropForeign(['type_id']);

            $table->dropColumn('type_id');
        });
    }
};
