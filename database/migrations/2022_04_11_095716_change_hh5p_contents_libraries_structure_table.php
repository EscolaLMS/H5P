<?php

use Illuminate\Database\Migrations\Migration;
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
            $table->unique(['content_id', 'library_id', 'dependency_type']);
        });
        Schema::table('hh5p_contents_libraries', function (Blueprint $table) {
            $table->id();
        });
        Schema::table('hh5p_libraries_languages', function (Blueprint $table) {
            $table->dropPrimary('hh5p_libraries_languages_pkey');
            $table->unique(['library_id', 'language_code']);
        });
        Schema::table('hh5p_libraries_languages', function (Blueprint $table) {
            $table->id();
        });
        Schema::table('hh5p_libraries_dependencies', function (Blueprint $table) {
            $table->dropPrimary('hh5p_libraries_dependencies_pkey');
            $table->unique(['library_id', 'required_library_id']);
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
            $table->dropUnique('hh5p_contents_libraries_content_id_library_id_dependency_type_u');
        });
        Schema::table('hh5p_contents_libraries', function (Blueprint $table) {
            $table->primary(['content_id', 'library_id', 'dependency_type'], 'fk_primary');
        });
        Schema::table('hh5p_libraries_languages', function (Blueprint $table) {
            $table->dropColumn('id');
        });
        Schema::table('hh5p_libraries_languages', function (Blueprint $table) {
            $table->dropUnique('hh5p_libraries_languages_library_id_language_code_unique');
        });
        Schema::table('hh5p_libraries_languages', function (Blueprint $table) {
            $table->primary(['library_id', 'language_code'], 'fk_primary');
        });
        Schema::table('hh5p_libraries_dependencies', function (Blueprint $table) {
            $table->dropColumn('id');
        });
        Schema::table('hh5p_libraries_dependencies', function (Blueprint $table) {
            $table->dropUnique('hh5p_libraries_dependencies_library_id_required_library_id_uniq');
        });
        Schema::table('hh5p_libraries_dependencies', function (Blueprint $table) {
            $table->primary(['library_id', 'required_library_id'], 'fk_primary');
        });
    }
}
