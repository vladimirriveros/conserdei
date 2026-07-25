<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role1 = Role::firstOrCreate(['name' => 'admin']);
        $role2 = Role::firstOrCreate(['name' => 'vendedor']);

        // ── Categorías ───────────────────────────────────────────────────────
        Permission::firstOrCreate(['name' => 'categorias.index', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'categorias.create', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'categorias.store', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'categorias.show', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'categorias.edit', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'categorias.update', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'categorias.destroy', 'guard_name' => 'web'])->syncRoles([$role1]);

        // ── Sucursales ───────────────────────────────────────────────────────
        Permission::firstOrCreate(['name' => 'sucursales.index', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'sucursales.create', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'sucursales.store', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'sucursales.show', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'sucursales.edit', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'sucursales.update', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'sucursales.destroy', 'guard_name' => 'web'])->syncRoles([$role1]);

        // ── Productos ────────────────────────────────────────────────────────
        Permission::firstOrCreate(['name' => 'productos.index', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'productos.create', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'productos.store', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'productos.show', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'productos.edit', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'productos.update', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'productos.destroy', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'productos.verificar-codigo', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'productos.ultimo-codigo', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'productos.desactivar', 'guard_name' => 'web'])->syncRoles([$role1]);

        // ── Proveedores ──────────────────────────────────────────────────────
        Permission::firstOrCreate(['name' => 'proveedores.index', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'proveedores.store', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'proveedores.update', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'proveedores.destroy', 'guard_name' => 'web'])->syncRoles([$role1]);

        // ── Compras ──────────────────────────────────────────────────────────
        Permission::firstOrCreate(['name' => 'compras.index', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'compras.create', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'compras.store', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'compras.show', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'compras.edit', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'compras.destroy', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'compras.enviarCorreo', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'compras.finalizarCompra', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'compras.procesarCarrito', 'guard_name' => 'web'])->syncRoles([$role1]);
        // 👇 PERMISO PARA CORRECCIONES (AHORA CON firstOrCreate)
        Permission::firstOrCreate(['name' => 'compras.correccion', 'guard_name' => 'web'])->syncRoles([$role1]);

        // ── Lotes ────────────────────────────────────────────────────────────
        Permission::firstOrCreate(['name' => 'lotes.index', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'lotes.create', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'lotes.store', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'lotes.show', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'lotes.edit', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'lotes.update', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'lotes.destroy', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'lotes.vencidos', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'lotes.vencidos.sucursal', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'lotes.vencidos.agregar', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'lotes.vencidos.eliminar', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'lotes.vencidos.finalizar', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.lote.update', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'lotes.pdf', 'guard_name' => 'web'])->syncRoles([$role1]);

        // ── Inventario ───────────────────────────────────────────────────────
        Permission::firstOrCreate(['name' => 'sucursal_por_lotes.index', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'mostrar_inventario_por_sucursal.show', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'inventario.stock_bajo_sucursal', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'inventario.migrar', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'inventario.sucursal.pdf', 'guard_name' => 'web'])->syncRoles([$role1]);
        // ── Movimientos ──────────────────────────────────────────────────────
Permission::firstOrCreate(['name' => 'movimientos.index', 'guard_name' => 'web'])->syncRoles([$role1]);
// El PDF usa el mismo permiso que el index
Permission::firstOrCreate(['name' => 'inventario.stock_bajo.pdf', 'guard_name' => 'web'])->syncRoles([$role1]);


        // ── Movimientos ──────────────────────────────────────────────────────
        Permission::firstOrCreate(['name' => 'movimientos.index', 'guard_name' => 'web'])->syncRoles([$role1]);

        // ── Tipo de Cambio ───────────────────────────────────────────────────
        Permission::firstOrCreate(['name' => 'tipo_cambio.index', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'tipo_cambio.store', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'tipo_cambio.update', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'tipo_cambio.destroy', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'tipo_cambio.bs-a-usd', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'tipo_cambio.usd-a-bs', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'tipo_cambio.recalcular-venta', 'guard_name' => 'web'])->syncRoles([$role1]);

        // ── Roles ────────────────────────────────────────────────────────────
        Permission::firstOrCreate(['name' => 'roles.index', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'roles.create', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'roles.store', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'roles.edit', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'roles.permisos', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'roles.update_permisos', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'roles.update', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'roles.destroy', 'guard_name' => 'web'])->syncRoles([$role1]);

        // ── Configuración ────────────────────────────────────────────────────
        Permission::firstOrCreate(['name' => 'configuracion.index', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'configuracion.store', 'guard_name' => 'web'])->syncRoles([$role1]);

        // ── Usuarios ─────────────────────────────────────────────────────────
        Permission::firstOrCreate(['name' => 'user.index', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'user.create', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'user.store', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'user.edit', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'user.update', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'user.destroy', 'guard_name' => 'web'])->syncRoles([$role1]);

        // ── Salidas ──────────────────────────────────────────────────────────
        Permission::firstOrCreate(['name' => 'salidas.index', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'salidas.create', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'salidas.store', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'salidas.show', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'salidas.edit', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'salidas.destroy', 'guard_name' => 'web'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'salidas.finalizarSalida', 'guard_name' => 'web'])->syncRoles([$role1]);
    }
}
