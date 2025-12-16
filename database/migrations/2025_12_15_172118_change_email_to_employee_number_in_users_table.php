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
            // Eliminar el índice único de email si existe
            try {
                DB::statement('DROP INDEX IF EXISTS users_email_unique');
            } catch (\Exception $e) {
                // El índice puede no existir o tener otro nombre
            }
        }

        Schema::table('users', function (Blueprint $table) {
            // Eliminar email y email_verified_at
            $table->dropColumn(['email', 'email_verified_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            // Agregar employee_number único
            $table->string('employee_number')->unique()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar employee_number y su índice único
            $table->dropUnique(['employee_number']);
            $table->dropColumn('employee_number');
        });

        Schema::table('users', function (Blueprint $table) {
            // Restaurar email y email_verified_at
            $table->string('email')->after('name');
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });

        Schema::table('users', function (Blueprint $table) {
            // Agregar índice único a email
            $table->unique('email');
        });
    }
};
