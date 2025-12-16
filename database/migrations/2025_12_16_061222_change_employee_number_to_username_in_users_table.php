<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Para SQLite, necesitamos eliminar el índice único primero
        if (DB::getDriverName() === 'sqlite') {
            try {
                DB::statement('DROP INDEX IF EXISTS users_employee_number_unique');
            } catch (\Exception $e) {
                // El índice puede no existir o tener otro nombre
            }
        }

        // Primero agregar username como nullable
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
        });

        // Actualizar los usuarios existentes con un username basado en employee_number
        DB::table('users')->get()->each(function ($user) {
            $username = 'user_' . $user->id;
            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        });

        // Hacer username NOT NULL y único
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable(false)->unique()->change();
        });

        // Eliminar employee_number
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('employee_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar username y su índice único
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });

        Schema::table('users', function (Blueprint $table) {
            // Restaurar employee_number
            $table->string('employee_number')->unique()->after('name');
        });
    }
};
