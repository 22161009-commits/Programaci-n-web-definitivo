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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('impuesto', 5, 2)->default(16.00)->after('precio')->comment('Porcentaje de impuesto (IVA)');
            $table->decimal('impuesto_calculado', 8, 2)->default(0)->after('impuesto')->comment('Monto del impuesto calculado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['impuesto', 'impuesto_calculado']);
        });
    }
};
