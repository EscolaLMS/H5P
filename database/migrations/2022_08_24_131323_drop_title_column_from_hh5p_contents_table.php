<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('hh5p_contents', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }

    public function down()
    {
        Schema::table('hh5p_contents', function (Blueprint $table) {
            $table->string('title');
        });
    }
};
