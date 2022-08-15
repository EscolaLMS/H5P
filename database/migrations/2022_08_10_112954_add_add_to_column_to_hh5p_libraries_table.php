<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('hh5p_libraries', function (Blueprint $table) {
            $table->text('add_to')->nullable();
        });
    }

    public function down()
    {
        Schema::table('hh5p_libraries', function (Blueprint $table) {
            $table->dropColumn('add_to');
        });
    }
};
