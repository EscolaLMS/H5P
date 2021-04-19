<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHH5pContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hh5p_contents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->bigInteger('user_id')->nullable()->unsigned(); // ADD key
            $table->string('title');
            $table->bigInteger('library_id')->unsigned(); // ADD key
            $table->text('parameters'); // TODO this can be JSON type
            $table->string('nonce', 8)->unique(); // used for assiging temporary editor files to content
            

            // TODO: do we need those ?
            $table->text('filtered')->nullable();
            $table->string('slug', 127)->nullable();
            $table->string('embed_type', 127)->nullable();
            $table->bigInteger('disable')->unsigned()->default(0);
            $table->string('content_type', 127)->nullable();
            $table->string('author', 127)->nullable();
            $table->string('license', 7)->nullable();
            $table->text('keywords', 65535)->nullable();
            $table->text('description', 65535)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('hh5p_contents');
    }
}
