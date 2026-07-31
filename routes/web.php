<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\InventarioSucuralLoteController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TipoCambioController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SalidaController;
use App\Http\Controllers\CorreccionCompraController;
use App\Http\Controllers\AlertasController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\GuestLoginController;
// use App\Models\DetalleSalida;
// use App\Models\Salida;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

// use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();
Route::post('/login/invitado', GuestLoginController::class)->name('login.guest');


Route::get('/home', [App\Http\Controllers\AdminController::class, 'index'])->name('home')->middleware('auth');
Route::get('/admin', [AdminController::class, 'index'])->name('admin.index')->middleware('auth');
// Route::get('/alerta-stock', [InventarioSucuralLoteController::class, 'alertaStock'])->name('alerta.stock');
// Cambia de 'password.update' a 'password.change.update' o algo similar
Route::get('/password/change', [App\Http\Controllers\ProfileController::class, 'showChangePasswordForm'])->name('password.change');
Route::post('/password/change', [App\Http\Controllers\ProfileController::class, 'changePassword'])->name('password.change.update');

// ── Categorías ───────────────────────────────────────────────────────────────
Route::get('/admin/categorias', [CategoriaController::class, 'index'])->name('categorias.index')->middleware('auth', 'can:categorias.index');
Route::get('/admin/categorias/create', [CategoriaController::class, 'create'])->name('categorias.create')->middleware('auth', 'can:categorias.create');
Route::post('/admin/categorias/create', [CategoriaController::class, 'store'])->name('categorias.store')->middleware('auth', 'can:categorias.store');
Route::get('/admin/categoria/{id}', [CategoriaController::class, 'show'])->name('categorias.show')->middleware('auth', 'can:categorias.show');
Route::get('/admin/categoria/{id}/edit', [CategoriaController::class, 'edit'])->name('categorias.edit')->middleware('auth', 'can:categorias.edit');
Route::put('/admin/categorias/{id}', [CategoriaController::class, 'update'])->name('categorias.update')->middleware('auth', 'can:categorias.update');
Route::delete('/admin/categoria/{id}', [CategoriaController::class, 'destroy'])->name('categorias.destroy')->middleware('auth', 'can:categorias.destroy');


// ── Sucursales ───────────────────────────────────────────────────────────────
Route::get('/admin/sucursales', [SucursalController::class, 'index'])->name('sucursales.index')->middleware('auth', 'can:sucursales.index');
Route::get('/admin/sucursales/create', [SucursalController::class, 'create'])->name('sucursales.create')->middleware('auth', 'can:sucursales.create');
Route::post('/admin/sucursales/create', [SucursalController::class, 'store'])->name('sucursales.store')->middleware('auth', 'can:sucursales.store');
Route::get('/admin/sucursales/{id}', [SucursalController::class, 'show'])->name('sucursales.show')->middleware('auth', 'can:sucursales.show');
Route::get('/admin/sucursales/{id}/edit', [SucursalController::class, 'edit'])->name('sucursales.edit')->middleware('auth', 'can:sucursales.edit');
Route::put('/admin/sucursales/{id}', [SucursalController::class, 'update'])->name('sucursales.update')->middleware('auth', 'can:sucursales.update');
Route::delete('/admin/sucursales/{id}', [SucursalController::class, 'destroy'])->name('sucursales.destroy')->middleware('auth', 'can:sucursales.destroy');


// ── Productos ────────────────────────────────────────────────────────────────
Route::get('admin/productos', [ProductoController::class, 'index'])->name('productos.index')->middleware('auth', 'can:productos.index');
Route::get('admin/productos/create', [ProductoController::class, 'create'])->name('productos.create')->middleware('auth', 'can:productos.create');
Route::post('admin/productos/create', [ProductoController::class, 'store'])->name('productos.store')->middleware('auth', 'can:productos.store');
Route::get('admin/producto/{id}', [ProductoController::class, 'show'])->name('productos.show')->middleware('auth', 'can:productos.show');
Route::get('admin/producto/{id}/edit', [ProductoController::class, 'edit'])->name('productos.edit')->middleware('auth', 'can:productos.edit');
Route::put('admin/producto/{id}', [ProductoController::class, 'update'])->name('productos.update')->middleware('auth', 'can:productos.update');
Route::delete('admin/producto/{id}', [ProductoController::class, 'destroy'])->name('productos.destroy')->middleware('auth', 'can:productos.destroy');
Route::get('productos/verificar-codigo', [ProductoController::class, 'verificarCodigo'])->name('productos.verificar-codigo')->middleware('auth', 'can:productos.verificar-codigo');
Route::get('productos/ultimo-codigo', [ProductoController::class, 'ultimoCodigo'])->name('productos.ultimo-codigo')->middleware('auth', 'can:productos.ultimo-codigo');
Route::put('/admin/productos/{producto}/desactivar', [ProductoController::class, 'desactivar'])->name('productos.desactivar')->middleware('auth', 'can:productos.desactivar');
// En routes/web.php, dentro del grupo de productos
Route::get('/admin/producto/{producto}/historial-precios', [ProductoController::class, 'historialPrecios'])->name('productos.historial')->middleware('auth', 'can:productos.show');


// ── Proveedores ──────────────────────────────────────────────────────────────
Route::get('/admin/proveedores', [ProveedorController::class, 'index'])->name('proveedores.index')->middleware('auth', 'can:proveedores.index');
Route::post('/admin/proveedor/create', [ProveedorController::class, 'store'])->name('proveedores.store')->middleware('auth', 'can:proveedores.store');
Route::put('/admin/proveedor/{id}', [ProveedorController::class, 'update'])->name('proveedores.update')->middleware('auth', 'can:proveedores.update');
Route::delete('/admin/proveedor/{id}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy')->middleware('auth', 'can:proveedores.destroy');


// ── COMPRAS ──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────
Route::get('/admin/compras', [CompraController::class, 'index'])->name('compras.index')->middleware('auth', 'can:compras.index');
Route::get('/admin/compras/create', [CompraController::class, 'create'])->name('compras.create')->middleware('auth', 'can:compras.create');
Route::post('/admin/compras/create', [CompraController::class, 'store'])->name('compras.store')->middleware('auth', 'can:compras.store');
Route::get('/admin/compra/{id}/edit', [CompraController::class, 'edit'])->name('compras.edit')->middleware('auth', 'can:compras.edit');
Route::get('/admin/compra/{id}', [CompraController::class, 'show'])->name('compras.show')->middleware('auth', 'can:compras.show');
Route::delete('/admin/compra/{id}', [CompraController::class, 'destroy'])->name('compras.destroy')->middleware('auth', 'can:compras.destroy');
Route::get('/admin/compra/{compra}/enviar-correo', [CompraController::class, 'enviarCorreo'])->name('compras.enviarCorreo')->middleware('auth', 'can:compras.enviarCorreo');
Route::get('/admin/compra/{compra}/enviar-whatsapp-pdf', [CompraController::class, 'enviarWhatsappPdf'])->name('compras.enviarWhatsappPdf');
Route::get('/admin/compra/{compra}/descargar-pdf', [CompraController::class, 'generarPdf'])->name('compras.descargarPdf');
Route::get('/admin/compra/{compra}/nota-pdf', [CompraController::class, 'notaCompraPdf'])->name('compras.nota-pdf')->middleware('auth', 'can:compras.show');
Route::get('/admin/compra/{compra}/descargar-nota', [CompraController::class, 'descargarNotaCompra'])->name('compras.descargar-nota')->middleware('auth', 'can:compras.show');


// ── LOTES ──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// ── Salidas de Vencidos
Route::get('/admin/lotes', [LoteController::class, 'index'])->name('lotes.index')->middleware('auth', 'can:lotes.index');
Route::get('/admin/lotes/vencidos', [LoteController::class, 'vencidos_index'])->name('lotes.vencidos')->middleware('auth', 'can:lotes.vencidos');
Route::get('/admin/lotes/vencidos/sucursal/{id}', [LoteController::class, 'vencidos_sucursal'])->name('lotes.vencidos.sucursal')->middleware('auth', 'can:lotes.vencidos.sucursal');
Route::post('/admin/lotes/vencidos/agregar-todos', [LoteController::class, 'agregarTodosASalida'])->name('lotes.vencidos.agregar-todos')->middleware('auth', 'can:lotes.vencidos.agregar');
Route::post('/admin/lotes/vencidos/vaciar-carrito', [LoteController::class, 'vaciarCarrito'])->name('lotes.vencidos.vaciar-carrito')->middleware('auth', 'can:lotes.vencidos.eliminar');
Route::post('/admin/lotes/vencidos/eliminar/{lote_id}', [LoteController::class, 'eliminarDeSalida'])->name('lotes.vencidos.eliminar')->middleware('auth', 'can:lotes.vencidos.eliminar');
Route::post('/admin/lotes/vencidos/finalizar', [LoteController::class, 'finalizarSalidaVencidos'])->name('lotes.vencidos.finalizar')->middleware('auth', 'can:lotes.vencidos.finalizar');
Route::post('/admin/lotes/vencidos/agregar', [LoteController::class, 'agregarASalida'])->name('lotes.vencidos.agregar')->middleware('auth', 'can:lotes.vencidos.agregar');
Route::get('/admin/lotes/pdf', [LoteController::class, 'generarPDF'])->name('lotes.pdf')->middleware('auth', 'can:lotes.index');
Route::get('/alerta/lotes-por-vencer', [LoteController::class, 'alertaLotesPorVencer'])->name('alerta.lotes-por-vencer');


// ── INVENTARIO ───────────────────────────────────────────────────────────────
Route::get('/admin/inventario/sucursales_por_lotes', [InventarioSucuralLoteController::class, 'index'])->name('sucursal_por_lotes.index')->middleware('auth', 'can:sucursal_por_lotes.index');
Route::get('/admin/inventario/inventario_por_sucursal/sucursal/{id}', [InventarioSucuralLoteController::class, 'mostrar_inventario_por_sucursal'])
    ->name('mostrar_inventario_por_sucursal.show')->middleware('auth', 'can:mostrar_inventario_por_sucursal.show');
Route::get('admin/inventario/sucursal/{id}/stock-bajo',
[InventarioSucuralLoteController::class, 'stock_bajo_por_sucursal'])
->name('inventario.stock_bajo_sucursal')
->middleware('auth', 'can:inventario.stock_bajo_sucursal');
// En routes/web.php, agrega el middleware 'can' con el nombre del permiso:
Route::get('/admin/inventario/sucursal/{id}/pdf',[InventarioSucuralLoteController::class, 'generarPDF'])
    ->name('inventario.sucursal.pdf')
    ->middleware('auth', 'can:inventario.sucursal.pdf'); // 👈 AGREGADO EL PERMISO
// ── Movimientos PDF ──────────────────────────────────────────────────────
Route::get('/admin/inventario/movimientos/pdf', [MovimientoInventarioController::class, 'generarPDF'])
    ->name('movimientos.pdf')
    ->middleware('auth', 'can:movimientos.index');
Route::get('/admin/inventario/sucursal/{id}/stock-bajo/pdf', [InventarioSucuralLoteController::class, 'generarPDFStockBajo'])->name('inventario.stock_bajo.pdf')->middleware('auth', 'can:inventario.sucursal.pdf');


// ── Movimientos ──────────────────────────────────────────────────────────────
Route::get('/admin/inventario/movimientos', [MovimientoInventarioController::class, 'index'])->name('movimientos.index')->middleware('auth', 'can:movimientos.index');

// ── Tipo de Cambio ───────────────────────────────────────────────────────────
Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/tipo_cambio', [TipoCambioController::class, 'index'])->name('tipo_cambio.index');
});


// ── Roles ────────────────────────────────────────────────────────────────────
Route::get('/admin/roles', [RoleController::class, 'index'])->name('roles.index')->middleware('auth', 'can:roles.index');
Route::get('/admin/roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('auth', 'can:roles.create');
Route::post('/admin/roles/create', [RoleController::class, 'store'])->name('roles.store')->middleware('auth', 'can:roles.store');
Route::get('/admin/roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware('auth', 'can:roles.edit');
Route::get('/admin/roles/{id}/permisos', [RoleController::class, 'permisos'])->name('roles.permisos')->middleware('auth', 'can:roles.permisos');
Route::post('/admin/roles/{id}', [RoleController::class, 'update_permisos'])->name('roles.update_permisos')->middleware('auth', 'can:roles.update_permisos');
Route::put('/admin/roles/{id}', [RoleController::class, 'update'])->name('roles.update')->middleware('auth', 'can:roles.update');
Route::delete('/admin/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('auth', 'can:roles.destroy');


// ── Usuarios ──────────────────────────────────────────────────────────────────
Route::get('/admin/users', [UserController::class, 'index'])->name('user.index')->middleware('auth', 'can:user.index');
Route::get('/admin/users/create', [UserController::class, 'create'])->name('user.create')->middleware('auth', 'can:user.create');
Route::post('/admin/users/create', [UserController::class, 'store'])->name('user.store')->middleware('auth', 'can:user.store');
Route::get('/admin/users/{id}/edit', [UserController::class, 'edit'])->name('user.edit')->middleware('auth', 'can:user.edit');
Route::put('/admin/users/{id}', [UserController::class, 'update'])->name('user.update')->middleware('auth', 'can:user.update');
Route::delete('/admin/users/{id}', [UserController::class, 'destroy'])->name('user.destroy')->middleware('auth', 'can:user.destroy');
// NUEVAS RUTAS PARA SOFT DELETES
Route::get('/admin/users/restaurar/{id}', [UserController::class, 'restaurar'])->name('user.restaurar')->middleware('auth', 'can:user.edit');
Route::delete('/admin/users/force-delete/{id}', [UserController::class, 'forceDelete'])->name('user.forceDelete')->middleware('auth', 'can:user.destroy');


// ── Salidas ───────────────────────────────────────────────────────────────────
Route::get('/admin/salidas', [SalidaController::class, 'index'])->name('salidas.index')->middleware('auth', 'can:salidas.index');
Route::get('/admin/salidas/create', [SalidaController::class, 'create'])->name('salidas.create')->middleware('auth', 'can:salidas.create');
Route::post('/admin/salidas/create', [SalidaController::class, 'store'])->name('salidas.store')->middleware('auth', 'can:salidas.store');
Route::get('/admin/salida/{id}/edit', [SalidaController::class, 'edit'])->name('salidas.edit')->middleware('auth', 'can:salidas.edit');
Route::get('/admin/salida/{id}', [SalidaController::class, 'show'])->name('salidas.show')->middleware('auth', 'can:salidas.show');
Route::delete('/admin/salida/{id}', [SalidaController::class, 'destroy'])->name('salidas.destroy')->middleware('auth', 'can:salidas.destroy');
Route::get('/admin/salida/{salida}/nota-pdf', [SalidaController::class, 'notaSalidaPdf'])->name('salidas.nota-pdf')->middleware('auth', 'can:salidas.show');
Route::get('/admin/salida/{salida}/descargar-nota', [SalidaController::class, 'descargarNotaSalida'])->name('salidas.descargar-nota')->middleware('auth', 'can:salidas.show');

// Route::post('/admin/salidas/{salida}/finalizar', [SalidaController::class, 'finalizarSalida'])->name('salidas.finalizarSalida')->middleware('auth', 'can:salidas.finalizarSalida');


// ── Migraciones de Lotes ──────────────────────────────────────────────────────
Route::post('compras/{compra}/procesar-carrito', [CompraController::class, 'procesarCarrito'])->name('compras.procesarCarrito')->middleware('auth', 'can:compras.procesarCarrito');
Route::post('/inventario/migrar-lotes', [InventarioSucuralLoteController::class, 'migrarLotesASucursal'])->name('inventario.migrar')->middleware('auth', 'can:inventario.migrar');


// ── Corrección de Compras ───────────────────────────────────────────────────
Route::get('/admin/compra/{compraId}/corregir', [CorreccionCompraController::class, 'edit'])->name('compras.correccion.edit')->middleware('auth', 'can:compras.correccion');
Route::post('/admin/compra/{compraId}/corregir', [CorreccionCompraController::class, 'update'])->name('compras.correccion.update')->middleware('auth', 'can:compras.correccion');
Route::get('/admin/lotes/{loteId}/stock', [CorreccionCompraController::class, 'getStockLote'])->name('lotes.stock')->middleware('auth');

// Ruta para obtener detalles ROP del producto
Route::get('/admin/inventario/producto/{productoId}/detalle-rop', [InventarioSucuralLoteController::class, 'detalleROP'])->name('inventario.detalle.rop')->middleware('auth');

// ── ALERTAS ────────────────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/alertas/stock-bajo', [AlertasController::class, 'verificarStockBajo'])->name('alertas.stock-bajo');
    Route::get('/alertas/lotes-por-vencer', [AlertasController::class, 'verificarLotesPorVencer'])->name('alertas.lotes-por-vencer');
    Route::get('/alertas/lotes-vencidos', [AlertasController::class, 'verificarLotesVencidos'])->name('alertas.lotes-vencidos');  // ← NUEVA RUTA
    Route::get('/alertas/rop', [AlertasController::class, 'verificarROP'])->name('alertas.rop');
});
