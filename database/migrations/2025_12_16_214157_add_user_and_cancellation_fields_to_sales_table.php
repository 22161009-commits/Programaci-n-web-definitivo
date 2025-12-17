<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('total')->constrained()->onDelete('cascade');
            $table->boolean('cancelada')->default(false)->after('user_id');
            $table->timestamp('cancelada_at')->nullable()->after('cancelada');
            $table->foreignId('cancelada_por')->nullable()->after('cancelada_at')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['cancelada_por']);
            $table->dropColumn(['user_id', 'cancelada', 'cancelada_at', 'cancelada_por']);
        });
    }
};
