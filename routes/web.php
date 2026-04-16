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
        Route::get('/inventario/existencias/almacen/imprimir', \App\Http\Controllers\Inventory\WarehouseStockPrintController::class)->name('inventory.warehouse-stock.print');
        Route::get('/inventario/categorias', \App\Livewire\Inventory\CategoryIndex::class)->name('inventory.categories.index');
        Route::get('/inventario/almacenes', \App\Livewire\Inventory\WarehouseIndex::class)->name('inventory.warehouses.index');
        Route::get('/inventario/movimientos', \App\Livewire\Inventory\StockMovementIndex::class)->name('inventory.movements.index');
        Route::get('/inventario/lotes', \App\Livewire\Inventory\LotIndex::class)->name('inventory.lots.index');
        Route::get('/inventario/lotes/{lot}', \App\Livewire\Inventory\LotDetail::class)->name('inventory.lots.show');
        Route::get('/inventario/kardex', \App\Livewire\Inventory\PepsKardexView::class)->name('inventory.kardex');
    });
    Route::middleware('can:create inventory')->group(function () {
        Route::get('/inventario/productos/crear', \App\Livewire\Inventory\ProductForm::class)->name('inventory.products.create');
        Route::get('/inventario/categorias/crear', \App\Livewire\Inventory\CategoryForm::class)->name('inventory.categories.create');
        Route::get('/inventario/almacenes/crear', \App\Livewire\Inventory\WarehouseForm::class)->name('inventory.warehouses.create');
    });
    Route::middleware('can:adjust inventory')->group(function () {
        Route::get('/inventario/movimientos/crear', \App\Livewire\Inventory\StockMovementForm::class)->name('inventory.movements.create');
    });
    Route::middleware('can:edit inventory')->group(function () {
        Route::get('/inventario/productos/{product}/editar', \App\Livewire\Inventory\ProductForm::class)->name('inventory.products.edit');
        Route::get('/inventario/categorias/{category}/editar', \App\Livewire\Inventory\CategoryForm::class)->name('inventory.categories.edit');
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
        Route::get('/compras/ordenes/imprimir', \App\Http\Controllers\Purchases\PurchaseReportPrintController::class)->name('purchases.orders.report.print');
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
        Route::get('/ventas/listas-precios/comparador', \App\Livewire\Sales\ProductPriceComparison::class)->name('sales.price-lists.comparison');
        Route::get('/ventas/cotizaciones/{quotation}', \App\Livewire\Sales\QuotationShow::class)->name('sales.quotations.show');
        Route::get('/ventas/cotizaciones/{quotation}/imprimir', \App\Http\Controllers\Sales\QuotationPrintController::class)->name('sales.quotations.print');
        Route::get('/ventas/ordenes/{order}', \App\Livewire\Sales\OrderShow::class)->name('sales.orders.show');
        Route::get('/ventas/ordenes/{order}/imprimir', \App\Http\Controllers\Sales\OrderPrintController::class)->name('sales.orders.print');
        Route::get('/ventas/facturas/{invoice}', \App\Livewire\Sales\InvoiceShow::class)->name('sales.invoices.show');
        Route::get('/ventas/facturas/{invoice}/descargar/{type}', \App\Http\Controllers\Sales\InvoiceDownloadController::class)->name('sales.invoices.download');
    });


    // ── Proyectos ─────────────────────────────────────────────────────────────
    Route::middleware('can:create projects')->get('/proyectos/crear', \App\Livewire\Projects\ProjectForm::class)->name('projects.create');
    Route::middleware('can:view projects')->group(function () {
        Route::get('/proyectos', \App\Livewire\Projects\ProjectIndex::class)->name('projects.index');
        Route::get('/proyectos/{project}', \App\Livewire\Projects\ProjectShow::class)->name('projects.show');
        Route::get('/proyectos/{project}/gastos', \App\Livewire\Projects\ProjectExpenseIndex::class)->name('projects.expenses.index');
        Route::get('/proyectos/{project}/tablero', \App\Livewire\Projects\ProjectTaskBoard::class)->name('projects.board');
        Route::get('/proyectos/{project}/hitos', \App\Livewire\Projects\ProjectMilestones::class)->name('projects.milestones');
    });
    Route::middleware('can:edit projects')->get('/proyectos/{project}/editar', \App\Livewire\Projects\ProjectForm::class)->name('projects.edit');

    // ── Recursos Humanos ─────────────────────────────────────────────────────
    // Rutas estáticas primero (antes de los parámetros dinámicos)
    Route::middleware('can:create hr')->group(function () {
        Route::get('/rrhh/empleados/crear', \App\Livewire\HR\EmployeeForm::class)->name('hr.employees.create');
        Route::get('/rrhh/nominas/crear', \App\Livewire\HR\PayrollForm::class)->name('hr.payrolls.create');
    });
    Route::middleware('can:view hr')->group(function () {
        Route::get('/rrhh/empleados', \App\Livewire\HR\EmployeeIndex::class)->name('hr.employees.index');
        Route::get('/rrhh/empleados/{employee}', \App\Livewire\HR\EmployeeShow::class)->name('hr.employees.show');
        Route::get('/rrhh/departamentos', \App\Livewire\HR\DepartmentIndex::class)->name('hr.departments.index');
        Route::get('/rrhh/puestos', \App\Livewire\HR\PositionIndex::class)->name('hr.positions.index');
        Route::get('/rrhh/contratos', \App\Livewire\HR\ContractIndex::class)->name('hr.contracts.index');
        Route::get('/rrhh/asistencias', \App\Livewire\HR\AttendanceIndex::class)->name('hr.attendances.index');
        Route::get('/rrhh/nominas', \App\Livewire\HR\PayrollIndex::class)->name('hr.payrolls.index');
        Route::get('/rrhh/nominas/{payroll}', \App\Livewire\HR\PayrollShow::class)->name('hr.payrolls.show');
        Route::get('/rrhh/permisos', \App\Livewire\HR\LeaveIndex::class)->name('hr.leaves.index');
        Route::get('/rrhh/incidencias', \App\Livewire\HR\IncidentIndex::class)->name('hr.incidents.index');
        Route::get('/rrhh/evaluaciones', \App\Livewire\HR\EvaluationIndex::class)->name('hr.evaluations.index');
    });
    Route::middleware('can:edit hr')->group(function () {
        Route::get('/rrhh/empleados/{employee}/editar', \App\Livewire\HR\EmployeeForm::class)->name('hr.employees.edit');
    });

    // ── Finanzas ──────────────────────────────────────────────────────────────
    Route::middleware('can:view finance')->group(function () {
        Route::get('/finanzas/cuentas', \App\Livewire\Finance\FinanceAccountIndex::class)->name('finance.accounts.index');
        Route::get('/finanzas/transacciones', \App\Livewire\Finance\FinanceTransactionIndex::class)->name('finance.transactions.index');
        Route::get('/finanzas/presupuestos', \App\Livewire\Finance\FinanceBudgetIndex::class)->name('finance.budgets.index');
        Route::get('/finanzas/flujo-caja', \App\Livewire\Finance\FinanceCashflowIndex::class)->name('finance.cashflow.index');
    });
    Route::middleware('can:create finance')->group(function () {
        Route::get('/finanzas/cuentas/crear', \App\Livewire\Finance\FinanceAccountForm::class)->name('finance.accounts.create');
        Route::get('/finanzas/transacciones/crear', \App\Livewire\Finance\FinanceTransactionForm::class)->name('finance.transactions.create');
        Route::get('/finanzas/presupuestos/crear', \App\Livewire\Finance\FinanceBudgetForm::class)->name('finance.budgets.create');
        Route::get('/finanzas/flujo-caja/crear', \App\Livewire\Finance\FinanceCashflowForm::class)->name('finance.cashflow.create');
    });
    Route::middleware('can:edit finance')->group(function () {
        Route::get('/finanzas/cuentas/{account}/editar', \App\Livewire\Finance\FinanceAccountForm::class)->name('finance.accounts.edit');
        Route::get('/finanzas/transacciones/{transaction}/editar', \App\Livewire\Finance\FinanceTransactionForm::class)->name('finance.transactions.edit');
        Route::get('/finanzas/presupuestos/{budget}/editar', \App\Livewire\Finance\FinanceBudgetForm::class)->name('finance.budgets.edit');
        Route::get('/finanzas/flujo-caja/{cashflow}/editar', \App\Livewire\Finance\FinanceCashflowForm::class)->name('finance.cashflow.edit');
    });

});

require __DIR__ . '/auth.php';
