<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('backraundPhoto')->nullable();
            $table->string('name')->nullable();
            $table->string('surname')->nullable();
            $table->string('number')->unique()->nullable();
            $table->Date('date_of_birth')->nullable();
            $table->string('patronymic')->nullable();
            $table->integer('role_id')->nullable();
            $table->string('city')->nullable();
            $table->string('verify_code')->nullable();
            $table->string('username')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('day')->nullable();
            $table->integer('mount')->nullable();
            $table->integer('avatar')->default('default.png');
            $table->rememberToken();
            $table->timestamps();
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
    }
}
