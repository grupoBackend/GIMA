<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\TipoMantenimiento;
use App\Enums\EstadoMantenimiento;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() : void
    {
        Schema::create('calendario_mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activo_id')->constrained('activos');
            $table->string('tipo')->default(TipoMantenimiento::PREVENTIVO->value);
            $table->dateTime('fecha_programada');
            $table->foreignId('tecnico_asignado_id')->constrained('users');
            $table->string('estado')->default(EstadoMantenimiento::PENDIENTE->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendario_mantenimientos');
    }
};
