<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Articulo;
use App\Models\Activo;
use App\Models\MaterialArticulo;
use App\Models\Notificacion;
use Spatie\Permission\Models\Role;

class JHdemo extends Seeder
{
    //CREADO NETAMENTE PARA PROBAR SI FUNCIONA LOS CONTROLADORES DEL EQUIPO Juan y Haddan
    public function run(): void
    {
        // 1. LLAMAMOS A LOS ROLES
        $this->call(RolesSeeder::class);
        // 2. CREAMOS UNA UBICACION DE PRUEBA
        $this->call(UbicacionSeeder::class);
        // 3. CREAMOS ARTICULOS DE PRUEBA
        $this->call(ArticuloSeeder::class);
        // 4. CREAMOS ACTIVOS DE PRUEBA
        $this->call(ActivoSeeder::class);
        // 5. CREAMOS NOTIFICACIONES DE PRUEBA
        $this->call(NotificacionSeeder::class);
        // 6. CREAMOS MATERIAL ARTICULO DE PRUEBA
        $this->call(MaterialArticuloSeeder::class);
}
}