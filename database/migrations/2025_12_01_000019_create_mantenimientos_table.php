<?php

use App\Enums\EstadoMantenimiento;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\TipoMantenimiento;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activo_id')->constrained('activos');
            $table->foreignId('supervisor_id')->constrained('users');
            $table->foreignId('tecnico_principal_id')->constrained('users');
            $table->string('tipo')->default(TipoMantenimiento::PREVENTIVO->value)
                ->isNotEmpty();
            $table->foreignId('reporte_id')
                ->nullable() // Nullable es vital: Los mantenimientos preventivos NO tienen reporte
                ->constrained()
                ->onDelete('cascade');
            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre');
            $table->string('estado')->default(EstadoMantenimiento::PENDIENTE->value);
            $table->string('descripcion');
            $table->boolean('validado');
            $table->float('costo_total');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
