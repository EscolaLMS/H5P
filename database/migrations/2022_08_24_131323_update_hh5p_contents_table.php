<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (DB::connection() instanceof PostgresConnection) {
            DB::statement('ALTER TABLE hh5p_contents ALTER COLUMN parameters TYPE JSON USING (parameters::json);');
        } else {
            Schema::table('hh5p_contents', function (Blueprint $table) {
                $table->json('parameters')->change();
            });
        }

        Schema::table('hh5p_contents', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }

    public function down()
    {
        Schema::table('hh5p_contents', function (Blueprint $table) {
            $table->string('title');
            $table->mediumText('parameters')->change();
        });
    }
};
