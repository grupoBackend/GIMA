<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Direccion;
use App\Models\Proveedor;
use App\Models\Articulo;
use App\Models\Ubicacion;
use App\Models\Repuesto;
use App\Models\Activo;
use App\Models\MaterialArticulo;
use App\Models\Reporte;
use App\Models\CalendarioMantenimiento;
use App\Models\Mantenimiento;
use App\Models\SesionesMantenimiento;
use App\Models\RepuestoUsado;
use App\Models\Auditoria;
use App\Models\Notificacion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Roles (assuming RolesSeeder is handled elsewhere or can be integrated here)
        $this->call(RolesSeeder::class);

        // 2. Create independent entities first
        // Create an admin user
        $adminRole = Role::findByName('admin');
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'estado' => \App\Enums\UserStatusEnum::ACTIVO,
            'fecha_aprobacion' => now(),
        ]);
        $adminUser->assignRole($adminRole);

        // Create some regular users (tecnicos, supervisores)
        $tecnicoRole = Role::findByName('tecnico');
        $supervisorRole = Role::findByName('supervisor');

        User::factory(10)->create()->each(function ($user) use ($tecnicoRole, $supervisorRole) {
            $user->assignRole(collect([$tecnicoRole, $supervisorRole])->random());
        });

        // Other independent entities
        Direccion::factory(5)->create();
        Proveedor::factory(10)->create();
        Articulo::factory(15)->create();

        // 3. Create entities that depend on the above
        // Ubicacion depends on Direccion
        Ubicacion::factory(20)->create();

        // Repuesto depends on Proveedor and Direccion
        Repuesto::factory(30)->create();

        // Activo depends on Articulo and Ubicacion
        Activo::factory(50)->create();

        // MaterialArticulo depends on Articulo
        MaterialArticulo::factory(40)->create();

        // Reporte depends on User and Activo
        Reporte::factory(25)->create();

        // CalendarioMantenimiento depends on Activo and User
        CalendarioMantenimiento::factory(30)->create();

        // Mantenimiento depends on Activo, User (supervisor, tecnico), and optionally Reporte
        // Create some preventive (default) and some corrective
        Mantenimiento::factory(20)->create();
        Mantenimiento::factory(10)->correctivo()->create();

        // SesionesMantenimiento depends on Mantenimiento and User
        SesionesMantenimiento::factory(50)->create();

        // RepuestoUsado depends on SesionesMantenimiento and Repuesto
        RepuestoUsado::factory(100)->create();

        // Auditoria depends on User
        Auditoria::factory(50)->create();

        // Notificacion depends on User
        Notificacion::factory(70)->create();
    }
}
