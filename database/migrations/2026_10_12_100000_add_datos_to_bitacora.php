<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bitacora_cambios', function (Blueprint $table) {
            $table->json('datos_previos')->nullable()->after('descripcion');
            $table->json('datos_nuevos')->nullable()->after('datos_previos');
        });
    }

    public function down(): void
    {
        Schema::table('bitacora_cambios', function (Blueprint $table) {
            $table->dropColumn(['datos_previos', 'datos_nuevos']);
        });
    }
};
