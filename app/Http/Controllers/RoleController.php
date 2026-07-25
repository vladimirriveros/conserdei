<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::all();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles|max:255'
        ]);

        $rol = new Role();
        $rol->name = $request->name;
        $rol->guard_name = 'web';
        $rol->save();

        return redirect()->route('roles.index')
            ->with('mensaje', 'El rol se ha creado exitosamente.')
            ->with('icono', 'success');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $rol = Role::findOrFail($id);
        return view('admin.roles.edit', compact('rol'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $rol = Role::findOrFail($id);

        // 🔒 PROTECCIÓN: No permitir cambiar el nombre del rol admin
        if ($rol->name === 'admin' && $request->name !== 'admin') {
            return redirect()->route('roles.index')
                ->with('mensaje', '❌ No se puede cambiar el nombre del rol "admin".')
                ->with('icono', 'error');
        }

        $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
        ]);

        $rol->name = $request->name;
        $rol->guard_name = 'web';
        $rol->save();

        return redirect()->route('roles.index')
            ->with('mensaje', 'El rol se ha actualizado exitosamente.')
            ->with('icono', 'success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $rol = Role::findOrFail($id);

            // 🔒 PROTECCIÓN: No permitir eliminar el rol admin
            if ($rol->name === 'admin') {
                return redirect()->route('roles.index')
                    ->with('mensaje', '❌ No se puede eliminar el rol "admin".')
                    ->with('icono', 'error');
            }

            // Verificar si hay usuarios con este rol
            $usuariosConRol = $rol->users()->count();
            if ($usuariosConRol > 0) {
                return redirect()->route('roles.index')
                    ->with('mensaje', "❌ No se puede eliminar el rol porque tiene {$usuariosConRol} usuario(s) asignado(s).")
                    ->with('icono', 'error');
            }

            $rol->delete();

            return redirect()->route('roles.index')
                ->with('mensaje', 'El rol se ha eliminado exitosamente.')
                ->with('icono', 'success');

        } catch (\Exception $e) {
            return redirect()->route('roles.index')
                ->with('mensaje', 'Error al eliminar el rol: ' . $e->getMessage())
                ->with('icono', 'error');
        }
    }

    /**
     * Mostrar formulario de permisos
     */
    public function permisos($id)
{
    $rol = Role::findOrFail($id);

    // 🔒 PROTECCIÓN: Solo Super Admin puede ver permisos del rol admin
    if ($rol->name === 'admin' && !auth()->user()->hasRole('admin')) {
        abort(403, 'No tienes permisos para gestionar el Super Administrador.');
    }

    // Obtener TODOS los permisos del sistema
    $todosLosPermisos = Permission::all();

    // Agrupar permisos por módulo (extraer nombre del módulo del permiso)
    $permisosPorModulo = $todosLosPermisos->groupBy(function($permiso) {
        $partes = explode('.', $permiso->name);
        $modulo = $partes[0];

        // Mapear nombres de módulos a nombres legibles
        $mapaModulos = [
            'categorias' => 'Categorías',
            'sucursales' => 'Sucursales',
            'productos' => 'Productos',
            'proveedores' => 'Proveedores',
            'compras' => 'Compras',
            'inventario' => 'Inventario',
            'tipo_cambio' => 'Tipo de Cambio',
            'roles' => 'Roles',
            'user' => 'Usuarios',
            'salidas' => 'Salidas',
            'lotes' => 'Lotes',
            'sucursal_por_lotes' => 'Inventario',
            'mostrar_inventario_por_sucursal' => 'Inventario',
        ];

        return $mapaModulos[$modulo] ?? ucfirst($modulo);
    });

    // 🔄 NUEVA LÓGICA: Mostrar TODOS los módulos a TODOS los roles
    // El admin decide qué permisos asignar, no el sistema
    $permisos = $permisosPorModulo;

    return view('admin.roles.permisos', compact('rol', 'permisos'));
}

    /**
     * Actualizar permisos del rol
     */
    public function update_permisos(Request $request, $id)
{
    $rol = Role::findOrFail($id);

    // 🔒 PROTECCIÓN: Solo Super Admin puede modificar el rol admin
    if ($rol->name === 'admin' && !auth()->user()->hasRole('admin')) {
        return redirect()->route('roles.index')
            ->with('mensaje', '❌ No puedes modificar los permisos del Super Administrador.')
            ->with('icono', 'error');
    }

    // 🔒 PROTECCIÓN: Evitar que un admin se quite permisos a sí mismo o a roles superiores
    $currentUser = auth()->user();

    // Si el usuario actual NO es super admin, no puede asignar permisos que él no tenga
    if (!$currentUser->hasRole('admin')) {
        $misPermisos = $currentUser->getAllPermissions()->pluck('id')->toArray();
        $permisosSeleccionados = $request->input('permisos', []);

        // Verificar que todos los permisos seleccionados estén en sus permisos
        $permisosInvalidos = array_diff($permisosSeleccionados, $misPermisos);
        if (!empty($permisosInvalidos)) {
            return back()->withErrors([
                'permisos' => 'No puedes asignar permisos que no posees.'
            ]);
        }
    }

    // Sincronizar permisos
    $rol->permissions()->sync($request->input('permisos', []));

    return redirect()->route('roles.index')
        ->with('mensaje', '✅ Los permisos se han actualizado correctamente.')
        ->with('icono', 'success');
}


}
