<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InitialSetup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->rememberToken();
            $table->string('api_token')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uid', 8);
            $table->integer('user_id')->unsigned();
            $table->string('name');
            $table->decimal('initial_balance', 15, 2);
            $table->boolean('is_sink');
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uid', 8);
            $table->integer('account_id')->unsigned();
            $table->integer('target_id')->unsigned()->nullable();
            $table->enum('type', ['i', 'e', 'x']);
            $table->decimal('amount', 15, 2);
            $table->text('note');
            $table->dateTime('date');
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('transactions__tags', function (Blueprint $table) {
            $table->integer('transaction_id')->unsigned();
            $table->integer('tag_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->primary(['transaction_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('transactions__tags');
    }
}
