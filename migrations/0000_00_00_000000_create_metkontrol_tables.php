
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMetkontrolTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $tableNames = config('metkontrol.table_names');
        $fieldsNames = config('metkontrol.fields');

        //** CREATE ROLE TABLE */
        Schema::create($tableNames['roles'] ?? 'roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->string('slug');
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

        Schema::create($tableNames['rollables'] ?? 'rollables', function (Blueprint $table) use ($tableNames, $fieldsNames) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('role_id')->index();
            $table->morphs($fieldsNames['rollable'] ?? 'rollable', 'rollable');
            $table->timestamps();

            $table->foreign('role_id')
                ->references('id')
                ->on($tableNames['roles'] ?? 'roles')
                ->onDelete('cascade');
        });

        Schema::create($tableNames['permissionables'] ?? 'permissionables', function (Blueprint $table) use ($tableNames, $fieldsNames) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('permission_id')->index();
            $table->morphs($fieldsNames['permissionable'] ?? 'permissionable', 'permissionable');
            $table->timestamps();

            $table->foreign('permission_id')
                ->references('id')
                ->on($tableNames['permissions'] ?? 'permissions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $tableNames = config('metkontrol.table_names');

        Schema::dropIfExists($tableNames['roles'] ?? 'roles');
        Schema::dropIfExists($tableNames['permisssions'] ?? 'permissions');
        Schema::dropIfExists($tableNames['permissionables'] ?? 'permissionables');
        Schema::dropIfExists($tableNames['rollables'] ?? 'rollables');
    }
}
