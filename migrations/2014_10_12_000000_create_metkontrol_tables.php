<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMetkontrolTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         $tableNames = config('metkontrol.table_names');
     
         //** CREATE ROLE TABLE */
         Schema::create($tableNames['roles'] ?? 'roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->string('slug');
            $table->smallInteger('level');
            $table->text('description')->nullable();
            $table->timestamps();
         });

         Schema::create($tableNames['permissions'] ?? 'permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->timestamps();
         });

         Schema::create($tableNames['rollables'] ?? 'rollables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('role_id');
            $table->morphs('rollable');
            $table->timestamps();
        });

         Schema::create($tableNames['permissionables'] ?? 'permissionables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('permission_id');
            $table->morphs('permissionable');
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
        $tableNames = config('metkontrol.table_names');

        Schema::dropIfExists($tableNames['roles'] ?? 'roles' );
        Schema::dropIfExists($tableNames['permisssions'] ?? 'permissions');
        Schema::dropIfExists($tableNames['role_user'] ?? 'role_user');
        Schema::dropIfExists($tableNames['permisssion_role'] ?? 'permission_role');
    }
}
