<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->timestamps();
        });

        DB::table('roles')->insert(array( 'name' => 'Master' , 'description' => 'Master' ));
        DB::table('roles')->insert(array( 'name' => 'Alumno' , 'description' => 'Alumno' ));
        DB::table('roles')->insert(array( 'name' => 'Entrenador' , 'description' => 'Entrenador' ));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
}
