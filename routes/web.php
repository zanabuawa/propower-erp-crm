<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::post('/broadcasting/auth', function () {
    return Broadcast::auth(request());
})->middleware(['web', 'auth']);

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', \App\Livewire\Dashboard::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Empresas ─────────────────────────────────────────────────────────────
    Route::middleware('can:create companies')->get('/empresas/crear', \App\Livewire\Companies\CompanyForm::class)->name('companies.create');
    Route::middleware('can:edit companies')->get('/empresas/{company}/editar', \App\Livewire\Companies\CompanyForm::class)->name('companies.edit');
    Route::middleware('can:view companies')->get('/empresas', \App\Livewire\Companies\CompanyIndex::class)->name('companies.index');

    // ── Usuarios ──────────────────────────────────────────────────────────────
    Route::middleware('can:create users')->get('/usuarios/crear', \App\Livewire\Users\UserForm::class)->name('users.create');
    Route::middleware('can:edit users')->get('/usuarios/{user}/editar', \App\Livewire\Users\UserForm::class)->name('users.edit');
    Route::middleware('can:view users')->get('/usuarios', \App\Livewire\Users\UserIndex::class)->name('users.index');

    // ── Sucursales ────────────────────────────────────────────────────────────
    Route::middleware('can:create branches')->get('/sucursales/crear', \App\Livewire\Branches\BranchForm::class)->name('branches.create');
    Route::middleware('can:edit branches')->get('/sucursales/{branch}/editar', \App\Livewire\Branches\BranchForm::class)->name('branches.edit');
    Route::middleware('can:view branches')->get('/sucursales', \App\Livewire\Branches\BranchIndex::class)->name('branches.index');

    // ── Inventario ────────────────────────────────────────────────────────────
    Route::middleware('can:view inventory')->group(function () {
        Route::get('/inventario', \App\Livewire\Inventory\ProductIndex::class)->name('inventory.index');
        Route::get('/inventario/existencias', \App\Livewire\Inventory\InventoryGeneral::class)->name('inventory.general');
        Route::get('/inventario/existencias/almacen', \App\Livewire\Inventory\InventoryByWarehouse::class)->name('inventory.warehouse-stock');
        Route::get('/inventario/categorias', \App\Livewire\Inventory\CategoryIndex::class)->name('inventory.categories.index');
        Route::get('/inventario/unidades', \App\Livewire\Inventory\UnitIndex::class)->name('inventory.units.index');
        Route::get('/inventario/almacenes', \App\Livewire\Inventory\WarehouseIndex::class)->name('inventory.warehouses.index');
        Route::get('/inventario/movimientos', \App\Livewire\Inventory\StockMovementIndex::class)->name('inventory.movements.index');
    });
    Route::middleware('can:create inventory')->group(function () {
        Route::get('/inventario/productos/crear', \App\Livewire\Inventory\ProductForm::class)->name('inventory.products.create');
        Route::get('/inventario/categorias/crear', \App\Livewire\Inventory\CategoryForm::class)->name('inventory.categories.create');
        Route::get('/inventario/unidades/crear', \App\Livewire\Inventory\UnitForm::class)->name('inventory.units.create');
        Route::get('/inventario/almacenes/crear', \App\Livewire\Inventory\WarehouseForm::class)->name('inventory.warehouses.create');
    });
    Route::middleware('can:adjust inventory')->group(function () {
        Route::get('/inventario/movimientos/crear', \App\Livewire\Inventory\StockMovementForm::class)->name('inventory.movements.create');
    });
    Route::middleware('can:edit inventory')->group(function () {
        Route::get('/inventario/productos/{product}/editar', \App\Livewire\Inventory\ProductForm::class)->name('inventory.products.edit');
        Route::get('/inventario/categorias/{category}/editar', \App\Livewire\Inventory\CategoryForm::class)->name('inventory.categories.edit');
        Route::get('/inventario/unidades/{unitOfMeasure}/editar', \App\Livewire\Inventory\UnitForm::class)->name('inventory.units.edit');
        Route::get('/inventario/almacenes/{warehouse}/editar', \App\Livewire\Inventory\WarehouseForm::class)->name('inventory.warehouses.edit');
        Route::get('/inventario/movimientos/{stockMovement}', \App\Livewire\Inventory\StockMovementForm::class)->name('inventory.movements.show');
    });
    Route::middleware('can:adjust inventory')->get('/inventario/transferencias/crear', \App\Livewire\Inventory\InventoryTransferForm::class)->name('inventory.transfers.create');
    Route::middleware('can:view inventory')->group(function () {
        Route::get('/inventario/transferencias', \App\Livewire\Inventory\InventoryTransferIndex::class)->name('inventory.transfers.index');
        Route::get('/inventario/transferencias/{stockMovement}', \App\Livewire\Inventory\InventoryTransferForm::class)->name('inventory.transfers.show');
    });

    // ── Activos Fijos ─────────────────────────────────────────────────────────
    Route::middleware('can:view assets')->group(function () {
        Route::get('/activos', \App\Livewire\Assets\AssetIndex::class)->name('assets.index');
        Route::get('/activos/inventario', \App\Livewire\Assets\AssetInventoryView::class)->name('assets.inventory');
        Route::get('/activos/transferencias', \App\Livewire\Assets\AssetTransferIndex::class)->name('assets.transfers.index');
    });
    Route::middleware('can:create assets')->get('/activos/crear', \App\Livewire\Assets\AssetForm::class)->name('assets.create');
    Route::middleware('can:edit assets')->get('/activos/{asset}/editar', \App\Livewire\Assets\AssetForm::class)->name('assets.edit');
    Route::middleware('can:transfer assets')->get('/activos/transferencias/nueva', \App\Livewire\Assets\AssetTransferForm::class)->name('assets.transfers.create');

    // ── Clientes ──────────────────────────────────────────────────────────────
    Route::middleware('can:create contacts')->get('/clientes/crear', \App\Livewire\Customers\CustomerForm::class)->name('contacts.create');
    Route::middleware('can:edit contacts')->get('/clientes/{customer}/editar', \App\Livewire\Customers\CustomerForm::class)->name('contacts.edit');
    Route::middleware('can:view contacts')->group(function () {
        Route::get('/clientes', \App\Livewire\Customers\CustomerIndex::class)->name('contacts.index');
        Route::get('/clientes/{customer}', \App\Livewire\Customers\CustomerShow::class)->name('contacts.show');
    });

    // ── Proveedores ───────────────────────────────────────────────────────────
    Route::middleware('can:create suppliers')->get('/proveedores/crear', \App\Livewire\Suppliers\SupplierForm::class)->name('suppliers.create');
    Route::middleware('can:edit suppliers')->get('/proveedores/{supplier}/editar', \App\Livewire\Suppliers\SupplierForm::class)->name('suppliers.edit');
    Route::middleware('can:view suppliers')->group(function () {
        Route::get('/proveedores', \App\Livewire\Suppliers\SupplierIndex::class)->name('suppliers.index');
        Route::get('/proveedores/{supplier}', \App\Livewire\Suppliers\SupplierShow::class)->name('suppliers.show');
    });

    // ── Compras ───────────────────────────────────────────────────────────────
    // Rutas estáticas primero para evitar que wildcards capturen "crear"
    Route::middleware('can:create purchases')->group(function () {
        Route::get('/compras/requisiciones/crear', \App\Livewire\Purchases\RequisitionForm::class)->name('purchases.requisitions.create');
        Route::get('/compras/ordenes/crear', \App\Livewire\Purchases\OrderForm::class)->name('purchases.orders.create');
    });
    Route::middleware('can:receive goods')->group(function () {
        Route::get('/compras/recepciones/nueva', \App\Livewire\Purchases\GoodsReceiptForm::class)->name('purchases.goods-receipts.create');
        Route::get('/compras/ordenes/{order}/recibir', \App\Livewire\Purchases\ReceiptForm::class)->name('purchases.receipts.create');
    });
    Route::middleware('can:view purchases')->group(function () {
        Route::get('/compras/requisiciones', \App\Livewire\Purchases\RequisitionIndex::class)->name('purchases.index');
        Route::get('/compras/ordenes', \App\Livewire\Purchases\OrderIndex::class)->name('purchases.orders.index');
        Route::get('/compras/recepciones', \App\Livewire\Purchases\GoodsReceiptIndex::class)->name('purchases.goods-receipts.index');
        Route::get('/compras/reporte', \App\Livewire\Purchases\OrderReport::class)->name('purchases.report');
        Route::get('/compras/requisiciones/{requisition}', \App\Livewire\Purchases\RequisitionShow::class)->name('purchases.requisitions.show');
        Route::get('/compras/ordenes/{order}', \App\Livewire\Purchases\OrderShow::class)->name('purchases.orders.show');
        Route::get('/compras/ordenes/{order}/imprimir', \App\Http\Controllers\Purchases\OrderPrintController::class)->name('purchases.orders.print');
        Route::get('/compras/recepciones/{receipt}/imprimir', \App\Http\Controllers\Purchases\ReceiptPrintController::class)->name('purchases.receipts.print');
        Route::get('/compras/requisiciones/{requisition}/imprimir', \App\Http\Controllers\Purchases\RequisitionPrintController::class)->name('purchases.requisitions.print');
    });

    // ── Ventas ────────────────────────────────────────────────────────────────
    // Rutas estáticas primero
    Route::middleware('can:create sales')->group(function () {
        Route::get('/ventas/cotizaciones/crear', \App\Livewire\Sales\QuotationForm::class)->name('sales.quotations.create');
        Route::get('/ventas/ordenes/crear', \App\Livewire\Sales\OrderForm::class)->name('sales.orders.create');
        Route::get('/ventas/facturas/crear', \App\Livewire\Sales\InvoiceForm::class)->name('sales.invoices.create');
        Route::get('/ventas/ordenes/{order}/remision', \App\Livewire\Sales\DeliveryForm::class)->name('sales.deliveries.create');
    });
    Route::middleware('can:manage price lists')->group(function () {
        Route::get('/ventas/listas-precios/crear', \App\Livewire\Sales\PriceListForm::class)->name('sales.price-lists.create');
    });
    Route::middleware('can:manage price lists')->get('/ventas/listas-precios/{priceList}/editar', \App\Livewire\Sales\PriceListForm::class)->name('sales.price-lists.edit');
    Route::middleware('can:view sales')->group(function () {
        Route::get('/ventas/cotizaciones', \App\Livewire\Sales\QuotationIndex::class)->name('sales.index');
        Route::get('/ventas/ordenes', \App\Livewire\Sales\OrderIndex::class)->name('sales.orders.index');
        Route::get('/ventas/facturas', \App\Livewire\Sales\InvoiceIndex::class)->name('sales.invoices.index');
        Route::get('/ventas/listas-precios', \App\Livewire\Sales\PriceListIndex::class)->name('sales.price-lists.index');
        Route::get('/ventas/cotizaciones/{quotation}', \App\Livewire\Sales\QuotationShow::class)->name('sales.quotations.show');
        Route::get('/ventas/cotizaciones/{quotation}/imprimir', \App\Http\Controllers\Sales\QuotationPrintController::class)->name('sales.quotations.print');
        Route::get('/ventas/ordenes/{order}', \App\Livewire\Sales\OrderShow::class)->name('sales.orders.show');
        Route::get('/ventas/ordenes/{order}/imprimir', \App\Http\Controllers\Sales\OrderPrintController::class)->name('sales.orders.print');
        Route::get('/ventas/facturas/{invoice}', \App\Livewire\Sales\InvoiceShow::class)->name('sales.invoices.show');
        Route::get('/ventas/facturas/{invoice}/descargar/{type}', \App\Http\Controllers\Sales\InvoiceDownloadController::class)->name('sales.invoices.download');
    });

});

require __DIR__ . '/auth.php';
