<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHH5pLibrariesLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hh5p_libraries_languages', function (Blueprint $table) {
            $table->bigInteger('library_id')->unsigned();
            $table->string('language_code', 31);
            // TODO: this should be json
            $table->text('translation', 65535);
            $table->primary(['library_id', 'language_code'], 'fk_primary');
            // TODO: add foreign keys
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('hh5p_libraries_languages');
    }
}
