<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Empresas
    Route::get('/empresas', \App\Livewire\Companies\CompanyIndex::class)->name('companies.index');
    Route::get('/empresas/crear', \App\Livewire\Companies\CompanyForm::class)->name('companies.create');
    Route::get('/empresas/{company}/editar', \App\Livewire\Companies\CompanyForm::class)->name('companies.edit');

    // Usuarios
    Route::get('/usuarios', \App\Livewire\Users\UserIndex::class)->name('users.index');
    Route::get('/usuarios/crear', \App\Livewire\Users\UserForm::class)->name('users.create');
    Route::get('/usuarios/{user}/editar', \App\Livewire\Users\UserForm::class)->name('users.edit');

    // Sucursales
    Route::get('/sucursales', \App\Livewire\Branches\BranchIndex::class)->name('branches.index');
    Route::get('/sucursales/crear', \App\Livewire\Branches\BranchForm::class)->name('branches.create');
    Route::get('/sucursales/{branch}/editar', \App\Livewire\Branches\BranchForm::class)->name('branches.edit');

    // Inventario
    Route::get('/inventario', \App\Livewire\Inventory\ProductIndex::class)->name('inventory.index');
    Route::get('/inventario/productos/crear', \App\Livewire\Inventory\ProductForm::class)->name('inventory.products.create');
    Route::get('/inventario/productos/{product}/editar', \App\Livewire\Inventory\ProductForm::class)->name('inventory.products.edit');

    Route::get('/inventario/categorias', \App\Livewire\Inventory\CategoryIndex::class)->name('inventory.categories.index');
    Route::get('/inventario/categorias/crear', \App\Livewire\Inventory\CategoryForm::class)->name('inventory.categories.create');
    Route::get('/inventario/categorias/{category}/editar', \App\Livewire\Inventory\CategoryForm::class)->name('inventory.categories.edit');

    Route::get('/inventario/unidades', \App\Livewire\Inventory\UnitIndex::class)->name('inventory.units.index');
    Route::get('/inventario/unidades/crear', \App\Livewire\Inventory\UnitForm::class)->name('inventory.units.create');
    Route::get('/inventario/unidades/{unitOfMeasure}/editar', \App\Livewire\Inventory\UnitForm::class)->name('inventory.units.edit');

    Route::get('/inventario/almacenes', \App\Livewire\Inventory\WarehouseIndex::class)->name('inventory.warehouses.index');
    Route::get('/inventario/almacenes/crear', \App\Livewire\Inventory\WarehouseForm::class)->name('inventory.warehouses.create');
    Route::get('/inventario/almacenes/{warehouse}/editar', \App\Livewire\Inventory\WarehouseForm::class)->name('inventory.warehouses.edit');

    Route::get('/inventario/movimientos', \App\Livewire\Inventory\StockMovementIndex::class)->name('inventory.movements.index');
    Route::get('/inventario/movimientos/crear', \App\Livewire\Inventory\StockMovementForm::class)->name('inventory.movements.create');
    Route::get('/inventario/movimientos/{stockMovement}', \App\Livewire\Inventory\StockMovementForm::class)->name('inventory.movements.show');

    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

});

require __DIR__ . '/auth.php';
