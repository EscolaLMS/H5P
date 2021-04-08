<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHH5pLibrariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hh5p_libraries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('name', 127);
            $table->string('title');
            $table->bigInteger('major_version')->unsigned();
            $table->bigInteger('minor_version')->unsigned();
            $table->bigInteger('patch_version')->unsigned();
            $table->bigInteger('runnable')->unsigned()->index('runnable');
            $table->integer('restricted')->unsigned()->default(0);
            $table->bigInteger('fullscreen')->unsigned();
            $table->string('embed_types');
            $table->text('preloaded_js', 65535)->nullable();
            $table->text('preloaded_css', 65535)->nullable();
            $table->text('drop_library_css', 65535)->nullable();
            $table->text('semantics', 65535);
            $table->string('tutorial_url', 1023);
            $table->integer('has_icon')->unsigned()->default(0);
            $table->index(['name', 'major_version', 'minor_version', 'patch_version'], 'name_version');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('hh5p_libraries');
    }
}
