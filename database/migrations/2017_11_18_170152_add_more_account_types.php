<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddMoreAccountTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->enum('type', ['regular', 'noncurrent', 'sink'])->default('regular')->after('is_sink')->nullable(false);
        });
        DB::table('accounts')
            ->where('is_sink', 1)
            ->update(['type' => 'sink'])
        ;
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('is_sink');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->boolean('is_sink')->after('type')->nullable(false);
        });
        DB::table('accounts')
            ->where('type', 'sink')
            ->update(['is_sink' => 1])
        ;
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
