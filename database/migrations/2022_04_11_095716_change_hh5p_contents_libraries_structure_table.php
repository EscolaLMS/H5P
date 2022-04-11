<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeHh5pContentsLibrariesStructureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hh5p_contents_libraries', function (Blueprint $table) {
            $table->dropPrimary('hh5p_contents_libraries_pkey');
            $table->unique(['content_id', 'library_id', 'dependency_type'], 'hh5p_contents_libraries_unique_key');
        });
        Schema::table('hh5p_contents_libraries', function (Blueprint $table) {
            $table->id();
        });
        Schema::table('hh5p_libraries_languages', function (Blueprint $table) {
            $table->dropPrimary('hh5p_libraries_languages_pkey');
            $table->unique(['library_id', 'language_code'], 'hh5p_libraries_languages_unique_key');
        });
        Schema::table('hh5p_libraries_languages', function (Blueprint $table) {
            $table->id();
        });
        Schema::table('hh5p_libraries_dependencies', function (Blueprint $table) {
            $table->dropPrimary('hh5p_libraries_dependencies_pkey');
            $table->unique(['library_id', 'required_library_id'], 'hh5p_libraries_dependencies_unique_key');
        });
        Schema::table('hh5p_libraries_dependencies', function (Blueprint $table) {
            $table->id();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hh5p_contents_libraries', function (Blueprint $table) {
            $table->dropColumn('id');
        });
        Schema::table('hh5p_contents_libraries', function (Blueprint $table) {
            $table->dropUnique('hh5p_contents_libraries_unique_key');
        });
        Schema::table('hh5p_libraries_languages', function (Blueprint $table) {
            $table->dropUnique('hh5p_libraries_languages_unique_key');
        });
        Schema::table('hh5p_libraries_dependencies', function (Blueprint $table) {
            $table->dropUnique('hh5p_libraries_dependencies_unique_key');
        });
        Schema::table('hh5p_contents_libraries', function (Blueprint $table) {
            $table->primary(['content_id', 'library_id', 'dependency_type'], 'fk_primary');
        });
        Schema::table('hh5p_libraries_languages', function (Blueprint $table) {
            $table->dropColumn('id');
        });
        Schema::table('hh5p_libraries_languages', function (Blueprint $table) {
            $table->primary(['library_id', 'language_code'], 'fk_primary');
        });
        Schema::table('hh5p_libraries_dependencies', function (Blueprint $table) {
            $table->dropColumn('id');
        });
        Schema::table('hh5p_libraries_dependencies', function (Blueprint $table) {
            $table->primary(['library_id', 'required_library_id'], 'fk_primary');
        });
    }
}
