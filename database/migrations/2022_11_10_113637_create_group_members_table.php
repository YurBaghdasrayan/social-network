<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_members', function (Blueprint $table) {
            $table->id();
            $table->integer('sender_id');
            $table->integer('receiver_id');
//            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->foreignId('group_id')->constrained()->on('group')->onDelete('cascade');
//            $table->integer('group_id');
            $table->string('status')->nullable();
            $table->string('user_status')->nullable();
            $table->string('role')->default('participant');
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
        Schema::dropIfExists('group_members');
    }
};
