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

    // Clientes
    Route::get('/clientes', \App\Livewire\Customers\CustomerIndex::class)->name('contacts.index');
    Route::get('/clientes/crear', \App\Livewire\Customers\CustomerForm::class)->name('contacts.create');
    Route::get('/clientes/{customer}/editar', \App\Livewire\Customers\CustomerForm::class)->name('contacts.edit');
    Route::get('/clientes/{customer}', \App\Livewire\Customers\CustomerShow::class)->name('contacts.show');

    // Proveedores
    Route::get('/proveedores', \App\Livewire\Suppliers\SupplierIndex::class)->name('suppliers.index');
    Route::get('/proveedores/crear', \App\Livewire\Suppliers\SupplierForm::class)->name('suppliers.create');
    Route::get('/proveedores/{supplier}/editar', \App\Livewire\Suppliers\SupplierForm::class)->name('suppliers.edit');
    Route::get('/proveedores/{supplier}', \App\Livewire\Suppliers\SupplierShow::class)->name('suppliers.show');

    // Compras
    Route::get('/compras/requisiciones', \App\Livewire\Purchases\RequisitionIndex::class)->name('purchases.index');
    Route::get('/compras/requisiciones/crear', \App\Livewire\Purchases\RequisitionForm::class)->name('purchases.requisitions.create');
    Route::get('/compras/requisiciones/{requisition}', \App\Livewire\Purchases\RequisitionShow::class)->name('purchases.requisitions.show');

    Route::get('/compras/ordenes', \App\Livewire\Purchases\OrderIndex::class)->name('purchases.orders.index');
    Route::get('/compras/ordenes/crear', \App\Livewire\Purchases\OrderForm::class)->name('purchases.orders.create');
    Route::get('/compras/ordenes/{order}', \App\Livewire\Purchases\OrderShow::class)->name('purchases.orders.show');

    Route::get('/compras/ordenes/{order}/recibir', \App\Livewire\Purchases\ReceiptForm::class)->name('purchases.receipts.create');

    // Ventas
    Route::get('/ventas/cotizaciones', \App\Livewire\Sales\QuotationIndex::class)->name('sales.index');
    Route::get('/ventas/cotizaciones/crear', \App\Livewire\Sales\QuotationForm::class)->name('sales.quotations.create');
    Route::get('/ventas/cotizaciones/{quotation}', \App\Livewire\Sales\QuotationShow::class)->name('sales.quotations.show');

    Route::get('/ventas/ordenes', \App\Livewire\Sales\OrderIndex::class)->name('sales.orders.index');
    Route::get('/ventas/ordenes/crear', \App\Livewire\Sales\OrderForm::class)->name('sales.orders.create');
    Route::get('/ventas/ordenes/{order}', \App\Livewire\Sales\OrderShow::class)->name('sales.orders.show');
    Route::get('/ventas/ordenes/{order}/remision', \App\Livewire\Sales\DeliveryForm::class)->name('sales.deliveries.create');

    Route::get('/ventas/facturas', \App\Livewire\Sales\InvoiceIndex::class)->name('sales.invoices.index');
    Route::get('/ventas/facturas/crear', \App\Livewire\Sales\InvoiceForm::class)->name('sales.invoices.create');
    Route::get('/ventas/facturas/{invoice}', \App\Livewire\Sales\InvoiceShow::class)->name('sales.invoices.show');

    Route::get('/ventas/listas-precios', \App\Livewire\Sales\PriceListIndex::class)->name('sales.price-lists.index');
    Route::get('/ventas/listas-precios/crear', \App\Livewire\Sales\PriceListForm::class)->name('sales.price-lists.create');
    Route::get('/ventas/listas-precios/{priceList}/editar', \App\Livewire\Sales\PriceListForm::class)->name('sales.price-lists.edit');

    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

});

require __DIR__ . '/auth.php';
