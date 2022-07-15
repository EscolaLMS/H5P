<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateH5pLibrariesHubCacheTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hh5p_libraries_hub_cache', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('machine_name', 127);
            $table->bigInteger('major_version')->unsigned();
            $table->bigInteger('minor_version')->unsigned();
            $table->bigInteger('patch_version')->unsigned();
            $table->bigInteger('h5p_major_version')->unsigned()->nullable();
            $table->bigInteger('h5p_minor_version')->unsigned()->nullable();
            $table->string('title');
            $table->text('summary', 65535);
            $table->text('description', 65535);
            $table->string('icon', 511);
            $table->bigInteger('created_at')->unsigned();
            $table->bigInteger('updated_at')->unsigned();
            $table->bigInteger('is_recommended')->unsigned();
            $table->bigInteger('popularity')->unsigned();
            $table->text('screenshots', 65535)->nullable();
            $table->string('license', 511)->nullable();
            $table->string('example', 511);
            $table->string('tutorial', 511)->nullable();
            $table->text('keywords', 65535)->nullable();
            $table->text('categories', 65535)->nullable();
            $table->string('owner', 511)->nullable();
            $table->index(['machine_name', 'major_version', 'minor_version', 'patch_version'], 'hub_name_version');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('hh5p_libraries_hub_cache');
    }
}
