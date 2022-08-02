<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUuidColumnToHh5pContentsTable extends Migration
{
    public function up()
    {
        Schema::table('hh5p_contents', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->nullable();
        });
    }

    public function down()
    {
        Schema::table('hh5p_contents', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
