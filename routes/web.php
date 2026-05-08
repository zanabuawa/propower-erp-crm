<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::post('/broadcasting/auth', function () {
    return Broadcast::auth(request());
})->middleware(['web', 'auth']);

Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::get('/galeria', function () {
    return view('gallery');
})->name('gallery');

Route::post('/contacto', [\App\Http\Controllers\Landing\ContactoController::class, 'send'])->name('landing.contacto');

Route::get('/dashboard', \App\Livewire\Dashboard::class)->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/admin/landing', \App\Livewire\Landing\LandingEditor::class)->middleware(['auth'])->name('landing.editor');
Route::get('/admin/galeria', \App\Livewire\Landing\GalleryEditor::class)->middleware(['auth'])->name('gallery.editor');
Route::get('/ejecutivo', \App\Livewire\Reports\ExecutiveDashboard::class)->middleware(['auth', 'verified'])->name('reports.executive');

Route::middleware('auth')->group(function () {
    Route::get('/mi-portal', \App\Livewire\HR\EmployeePortal::class)->name('hr.portal');
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
        Route::get('/inventario/existencias/imprimir', \App\Http\Controllers\Inventory\InventoryGeneralPrintController::class)->name('inventory.general.print');
        Route::get('/inventario/existencias/almacen', \App\Livewire\Inventory\InventoryByWarehouse::class)->name('inventory.warehouse-stock');
        Route::get('/inventario/existencias/almacen/imprimir', \App\Http\Controllers\Inventory\WarehouseStockPrintController::class)->name('inventory.warehouse-stock.print');
        Route::get('/inventario/categorias', \App\Livewire\Inventory\CategoryIndex::class)->name('inventory.categories.index');
        Route::get('/inventario/almacenes', \App\Livewire\Inventory\WarehouseIndex::class)->name('inventory.warehouses.index');
        Route::get('/inventario/almacenes/{warehouse}/plano', \App\Livewire\Inventory\WarehouseLayoutEditor::class)->name('inventory.warehouses.layout');
        Route::get('/inventario/movimientos', \App\Livewire\Inventory\StockMovementIndex::class)->name('inventory.movements.index');
        Route::get('/inventario/lotes', \App\Livewire\Inventory\LotIndex::class)->name('inventory.lots.index');
        Route::get('/inventario/lotes/{lot}', \App\Livewire\Inventory\LotDetail::class)->name('inventory.lots.show');
        Route::get('/inventario/kardex', \App\Livewire\Inventory\PepsKardexView::class)->name('inventory.kardex');
        Route::get('/inventario/reabastecimiento', \App\Livewire\Inventory\ReorderRecommendations::class)->name('inventory.reorder');
        Route::get('/inventario/rotacion', \App\Livewire\Inventory\InventoryTurnover::class)->name('inventory.turnover');
        Route::get('/inventario/demanda', \App\Livewire\Inventory\DemandAnalysis::class)->name('inventory.demand');
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
        Route::get('/inventario/almacenes/{warehouse}/ubicaciones', \App\Livewire\Inventory\WarehouseLocationAssignment::class)->name('inventory.warehouses.locations');
        Route::get('/inventario/movimientos/{stockMovement}', \App\Livewire\Inventory\StockMovementForm::class)->name('inventory.movements.show');
    });
    Route::middleware('can:adjust inventory')->get('/inventario/transferencias/crear', \App\Livewire\Inventory\InventoryTransferForm::class)->name('inventory.transfers.create');
    Route::middleware('can:view inventory')->group(function () {
        Route::get('/inventario/transferencias', \App\Livewire\Inventory\InventoryTransferIndex::class)->name('inventory.transfers.index');
        Route::get('/inventario/transferencias/{stockMovement}', \App\Livewire\Inventory\InventoryTransferShow::class)->name('inventory.transfers.show');
        Route::get('/inventario/transferencias/{stockMovement}/accion', \App\Livewire\Inventory\InventoryTransferForm::class)->name('inventory.transfers.action');
    });

    // ── Activos Fijos ─────────────────────────────────────────────────────────
    Route::middleware('can:view assets')->group(function () {
        Route::get('/activos', \App\Livewire\Assets\AssetIndex::class)->name('assets.index');
        Route::get('/activos/inventario', \App\Livewire\Assets\AssetInventoryView::class)->name('assets.inventory');
        Route::get('/activos/transferencias', \App\Livewire\Assets\AssetTransferIndex::class)->name('assets.transfers.index');
        Route::get('/activos/depreciacion', \App\Livewire\Assets\AssetDepreciationIndex::class)->name('assets.depreciation.index');
        Route::get('/activos/prestamos', \App\Livewire\Assets\AssetLoanIndex::class)->name('assets.loans.index');
        Route::get('/activos/mantenimientos', \App\Livewire\Assets\AssetMaintenanceIndex::class)->name('assets.maintenance.index');
    });
    Route::middleware('can:create assets')->get('/activos/crear', \App\Livewire\Assets\AssetForm::class)->name('assets.create');
    Route::middleware('can:edit assets')->get('/activos/{asset}/editar', \App\Livewire\Assets\AssetForm::class)->name('assets.edit');
    Route::middleware('can:transfer assets')->get('/activos/transferencias/nueva', \App\Livewire\Assets\AssetTransferForm::class)->name('assets.transfers.create');
    Route::middleware('can:create assets')->get('/activos/prestamos/nuevo', \App\Livewire\Assets\AssetLoanForm::class)->name('assets.loans.create');
    Route::middleware('can:create assets')->get('/activos/mantenimientos/nuevo', \App\Livewire\Assets\AssetMaintenanceForm::class)->name('assets.maintenance.create');
    Route::middleware('can:edit assets')->get('/activos/mantenimientos/{maintenance}/editar', \App\Livewire\Assets\AssetMaintenanceForm::class)->name('assets.maintenance.edit');

    // ── Clientes ──────────────────────────────────────────────────────────────
    Route::middleware('can:create contacts')->get('/clientes/crear', \App\Livewire\Customers\CustomerForm::class)->name('contacts.create');
    Route::middleware('can:edit contacts')->get('/clientes/{customer}/editar', \App\Livewire\Customers\CustomerForm::class)->name('contacts.edit');
    Route::middleware('can:view contacts')->group(function () {
        Route::get('/clientes', \App\Livewire\Customers\CustomerIndex::class)->name('contacts.index');
        Route::get('/clientes/imprimir', \App\Http\Controllers\Customers\CustomersReportPrintController::class)->name('contacts.report.print');
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
        Route::get('/compras/analytics', \App\Livewire\Purchases\PurchasesAnalytics::class)->name('purchases.analytics');
        Route::get('/compras/requisiciones/{requisition}', \App\Livewire\Purchases\RequisitionShow::class)->name('purchases.requisitions.show');
        Route::get('/compras/ordenes/imprimir', \App\Http\Controllers\Purchases\PurchaseReportPrintController::class)->name('purchases.orders.report.print');
        Route::get('/compras/ordenes/{order}', \App\Livewire\Purchases\OrderShow::class)->name('purchases.orders.show');
        Route::get('/compras/ordenes/{order}/imprimir', \App\Http\Controllers\Purchases\OrderPrintController::class)->name('purchases.orders.print');
        Route::get('/compras/recepciones/{receipt}/imprimir', \App\Http\Controllers\Purchases\ReceiptPrintController::class)->name('purchases.receipts.print');
        Route::get('/compras/requisiciones/{requisition}/imprimir', \App\Http\Controllers\Purchases\RequisitionPrintController::class)->name('purchases.requisitions.print');
    });

    // ── Facturas de proveedor ─────────────────────────────────────────────────
    Route::middleware('can:create purchases')->get('/compras/facturas/crear', \App\Livewire\Purchases\PurchaseInvoiceForm::class)->name('purchases.invoices.create');
    Route::middleware('can:view purchases')->group(function () {
        Route::get('/compras/facturas', \App\Livewire\Purchases\PurchaseInvoiceIndex::class)->name('purchases.invoices.index');
        Route::get('/compras/facturas/{invoice}', \App\Livewire\Purchases\PurchaseInvoiceShow::class)->name('purchases.invoices.show');
        Route::get('/compras/facturas/{invoice}/descargar/{type}', \App\Http\Controllers\Purchases\PurchaseInvoiceDownloadController::class)->name('purchases.invoices.download');
    });

    // ── Notas de crédito de proveedor ─────────────────────────────────────────
    Route::middleware('can:create purchases')->get('/compras/notas-credito/crear', \App\Livewire\Purchases\SupplierCreditNoteForm::class)->name('purchases.credit-notes.create');
    Route::middleware('can:view purchases')->group(function () {
        Route::get('/compras/notas-credito', \App\Livewire\Purchases\SupplierCreditNoteIndex::class)->name('purchases.credit-notes.index');
        Route::get('/compras/notas-credito/{creditNote}', \App\Livewire\Purchases\SupplierCreditNoteShow::class)->name('purchases.credit-notes.show');
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
    Route::middleware('can:create sales')->group(function () {
        Route::get('/ventas/notas-credito/crear', \App\Livewire\Sales\CreditNoteForm::class)->name('sales.credit-notes.create');
    });
    Route::middleware('can:view sales')->group(function () {
        Route::get('/ventas/dashboard', \App\Livewire\Sales\SalesDashboard::class)->name('sales.dashboard');
        Route::get('/ventas/reporte', \App\Livewire\Sales\SalesReport::class)->name('sales.report');
        Route::get('/ventas/cotizaciones', \App\Livewire\Sales\QuotationIndex::class)->name('sales.index');
        Route::get('/ventas/ordenes', \App\Livewire\Sales\OrderIndex::class)->name('sales.orders.index');
        Route::get('/ventas/facturas', \App\Livewire\Sales\InvoiceIndex::class)->name('sales.invoices.index');
        Route::get('/ventas/notas-credito', \App\Livewire\Sales\CreditNoteIndex::class)->name('sales.credit-notes.index');
        Route::get('/ventas/notas-credito/{creditNote}', \App\Livewire\Sales\CreditNoteShow::class)->name('sales.credit-notes.show');
        Route::get('/ventas/listas-precios', \App\Livewire\Sales\PriceListIndex::class)->name('sales.price-lists.index');
        Route::get('/ventas/listas-precios/comparador', \App\Livewire\Sales\ProductPriceComparison::class)->name('sales.price-lists.comparison');
        Route::get('/ventas/autorizaciones', \App\Livewire\Sales\DiscountApprovalIndex::class)->name('sales.discount-approvals.index');
        Route::get('/ventas/cotizaciones/{quotation}', \App\Livewire\Sales\QuotationShow::class)->name('sales.quotations.show');
        Route::get('/ventas/cotizaciones/{quotation}/imprimir', \App\Http\Controllers\Sales\QuotationPrintController::class)->name('sales.quotations.print');
        Route::get('/ventas/ordenes/{order}', \App\Livewire\Sales\OrderShow::class)->name('sales.orders.show');
        Route::get('/ventas/ordenes/{order}/imprimir', \App\Http\Controllers\Sales\OrderPrintController::class)->name('sales.orders.print');
        Route::get('/ventas/facturas/{invoice}', \App\Livewire\Sales\InvoiceShow::class)->name('sales.invoices.show');
        Route::get('/ventas/facturas/{invoice}/descargar/{type}', \App\Http\Controllers\Sales\InvoiceDownloadController::class)->name('sales.invoices.download');
    });


    // ── CRM (Prospectos, Pipeline, Agenda) ───────────────────────────────────
    Route::middleware('can:view sales')->group(function () {
        Route::get('/ventas/crm/prospectos', \App\Livewire\Sales\CrmProspectIndex::class)->name('sales.crm.prospects.index');
        Route::get('/ventas/crm/prospectos/{prospect}', \App\Livewire\Sales\CrmProspectShow::class)->name('sales.crm.prospects.show');
        Route::get('/ventas/crm/pipeline', \App\Livewire\Sales\CrmPipelineIndex::class)->name('sales.crm.pipeline');
        Route::get('/ventas/crm/agenda', \App\Livewire\Sales\CrmAgendaIndex::class)->name('sales.crm.agenda');
        Route::get('/ventas/crm/analytics', \App\Livewire\Sales\CrmAnalytics::class)->name('sales.crm.analytics');
        Route::get('/ventas/clientes/analytics', \App\Livewire\Sales\CustomerAnalytics::class)->name('sales.customers.analytics');
    });
    Route::middleware('can:create sales')->group(function () {
        Route::get('/ventas/crm/prospectos/crear', \App\Livewire\Sales\CrmProspectForm::class)->name('sales.crm.prospects.create');
        Route::get('/ventas/crm/prospectos/{prospect}/editar', \App\Livewire\Sales\CrmProspectForm::class)->name('sales.crm.prospects.edit');
        Route::get('/ventas/crm/oportunidades/crear', \App\Livewire\Sales\CrmOpportunityForm::class)->name('sales.crm.opportunities.create');
        Route::get('/ventas/crm/oportunidades/{opportunity}/editar', \App\Livewire\Sales\CrmOpportunityForm::class)->name('sales.crm.opportunities.edit');
    });

    // ── Proyectos ─────────────────────────────────────────────────────────────
    Route::middleware('can:create projects')->get('/proyectos/crear', \App\Livewire\Projects\ProjectForm::class)->name('projects.create');
    Route::middleware('can:view projects')->group(function () {
        Route::get('/proyectos', \App\Livewire\Projects\ProjectIndex::class)->name('projects.index');
        Route::get('/proyectos/analytics', \App\Livewire\Projects\ProjectsAnalytics::class)->name('projects.analytics');
        Route::get('/proyectos/imprimir', \App\Http\Controllers\Projects\ProjectsReportPrintController::class)->name('projects.report.print');
        Route::get('/proyectos/{project}', \App\Livewire\Projects\ProjectShow::class)->name('projects.show');
        Route::get('/proyectos/{project}/gastos', \App\Livewire\Projects\ProjectExpenseIndex::class)->name('projects.expenses.index');
        Route::get('/proyectos/{project}/tablero', \App\Livewire\Projects\ProjectTaskBoard::class)->name('projects.board');
        Route::get('/proyectos/{project}/hitos', \App\Livewire\Projects\ProjectMilestones::class)->name('projects.milestones');
        Route::get('/proyectos/{project}/gantt', \App\Livewire\Projects\ProjectGantt::class)->name('projects.gantt');
        Route::get('/proyectos/{project}/presupuesto', \App\Livewire\Projects\ProjectBudget::class)->name('projects.budget');
        Route::get('/proyectos/{project}/recursos', \App\Livewire\Projects\ProjectMaterials::class)->name('projects.materials');
        Route::get('/proyectos/{project}/tiempos', \App\Livewire\Projects\ProjectTimeTracking::class)->name('projects.time-tracking');
        Route::get('/proyectos/{project}/financiero', \App\Livewire\Projects\ProjectFinancial::class)->name('projects.financial');
    });
    Route::middleware('can:edit projects')->get('/proyectos/{project}/editar', \App\Livewire\Projects\ProjectForm::class)->name('projects.edit');

    // ── Licitaciones ─────────────────────────────────────────────────────────
    Route::middleware('can:view tenders')->get('/licitaciones', \App\Livewire\Tenders\TenderIndex::class)->name('tenders.index');
    Route::middleware('can:view tenders')->get('/licitaciones/imprimir', \App\Http\Controllers\Tenders\TendersReportPrintController::class)->name('tenders.report.print');
    Route::middleware('can:create tenders')->get('/licitaciones/crear', \App\Livewire\Tenders\TenderForm::class)->name('tenders.create');
    Route::middleware('can:edit tenders')->get('/licitaciones/{tender}/editar', \App\Livewire\Tenders\TenderForm::class)->name('tenders.edit');
    Route::middleware('can:view tenders')->group(function () {
        Route::get('/licitaciones/{tender}', \App\Livewire\Tenders\TenderShow::class)->name('tenders.show');
        Route::get('/licitaciones/{tender}/cotizacion/crear', \App\Livewire\Tenders\QuotationForm::class)->name('tenders.quotations.create');
    });

    // ── Control de Obras ─────────────────────────────────────────────────────
    Route::middleware('can:view tenders')->group(function () {
        Route::get('/obras/permisos', \App\Livewire\Tenders\WorkPermitIndex::class)->name('works.permits.index');
        Route::get('/obras/reportes-semanales', \App\Livewire\Tenders\WorkReportIndex::class)->name('works.reports.index');
        Route::get('/obras/reportes-fotograficos', \App\Livewire\Tenders\WorkPhotoReportIndex::class)->name('works.photo-reports.index');
        Route::get('/obras/libranzas', \App\Livewire\Tenders\WorkLibranzaIndex::class)->name('works.libranzas.index');
        Route::get('/obras/programa/{project}', \App\Livewire\Tenders\WorkProgramIndex::class)->name('works.program.index');
    });

    // ── Visitas de Campo ──────────────────────────────────────────────────────
    Route::middleware('can:view tenders')->group(function () {
        Route::get('/visitas', \App\Livewire\Tenders\SiteVisitIndex::class)->name('tenders.visits.index');
        Route::get('/visitas/crear', \App\Livewire\Tenders\SiteVisitForm::class)->name('tenders.visits.create');
        Route::get('/visitas/{siteVisit}/editar', \App\Livewire\Tenders\SiteVisitForm::class)->name('tenders.visits.edit');
    });

    // ── Recursos Humanos ─────────────────────────────────────────────────────
    // Rutas estáticas primero (antes de los parámetros dinámicos)
    Route::middleware('can:create hr')->group(function () {
        Route::get('/rrhh/empleados/crear', \App\Livewire\HR\EmployeeForm::class)->name('hr.employees.create');
        Route::get('/rrhh/prospectos/crear', \App\Livewire\HR\ProspectForm::class)->name('hr.prospects.create');
        Route::get('/rrhh/nominas/crear', \App\Livewire\HR\PayrollForm::class)->name('hr.payrolls.create');
    });
    Route::middleware('can:view hr')->group(function () {
        Route::get('/rrhh/indicadores', \App\Livewire\HR\HrAnalytics::class)->name('hr.analytics');
        Route::get('/rrhh/empleados', \App\Livewire\HR\EmployeeIndex::class)->name('hr.employees.index');
        Route::get('/rrhh/empleados/imprimir', \App\Http\Controllers\HR\EmployeesPrintController::class)->name('hr.employees.print');
        Route::get('/rrhh/empleados/{employee}', \App\Livewire\HR\EmployeeShow::class)->name('hr.employees.show');
        Route::get('/rrhh/prospectos', \App\Livewire\HR\ProspectIndex::class)->name('hr.prospects.index');
        Route::get('/rrhh/prospectos/agenda', \App\Livewire\HR\ProspectAgenda::class)->name('hr.prospects.agenda');
        Route::get('/rrhh/prospectos/{prospect}', \App\Livewire\HR\ProspectShow::class)->name('hr.prospects.show');
        Route::get('/rrhh/departamentos', \App\Livewire\HR\DepartmentIndex::class)->name('hr.departments.index');
        Route::get('/rrhh/departamentos/crear', \App\Livewire\HR\DepartmentForm::class)->name('hr.departments.create');
        Route::get('/rrhh/departamentos/{department}/editar', \App\Livewire\HR\DepartmentForm::class)->name('hr.departments.edit');
        Route::get('/rrhh/puestos', \App\Livewire\HR\PositionIndex::class)->name('hr.positions.index');
        Route::get('/rrhh/puestos/crear', \App\Livewire\HR\PositionForm::class)->name('hr.positions.create');
        Route::get('/rrhh/puestos/{position}/editar', \App\Livewire\HR\PositionForm::class)->name('hr.positions.edit');
        Route::get('/rrhh/contratos', \App\Livewire\HR\ContractIndex::class)->name('hr.contracts.index');
        Route::get('/rrhh/contratos/crear', \App\Livewire\HR\ContractForm::class)->name('hr.contracts.create');
        Route::get('/rrhh/contratos/{contract}/editar', \App\Livewire\HR\ContractForm::class)->name('hr.contracts.edit');
        Route::get('/rrhh/asistencias', \App\Livewire\HR\AttendanceIndex::class)->name('hr.attendances.index');
        Route::get('/rrhh/asistencias/imprimir', \App\Http\Controllers\HR\AttendancePrintController::class)->name('hr.attendances.print');
        Route::get('/rrhh/asistencias/crear', \App\Livewire\HR\AttendanceForm::class)->name('hr.attendances.create');
        Route::get('/rrhh/asistencias/{attendance}/editar', \App\Livewire\HR\AttendanceForm::class)->name('hr.attendances.edit');
        Route::get('/rrhh/nominas', \App\Livewire\HR\PayrollIndex::class)->name('hr.payrolls.index');
        Route::get('/rrhh/nominas/imprimir', \App\Http\Controllers\HR\PayrollsPrintController::class)->name('hr.payrolls.print');
        Route::get('/rrhh/nominas/{payroll}', \App\Livewire\HR\PayrollShow::class)->name('hr.payrolls.show');
        Route::get('/rrhh/permisos', \App\Livewire\HR\LeaveIndex::class)->name('hr.leaves.index');
        Route::get('/rrhh/permisos/crear', \App\Livewire\HR\LeaveForm::class)->name('hr.leaves.create');
        Route::get('/rrhh/permisos/{leave}/editar', \App\Livewire\HR\LeaveForm::class)->name('hr.leaves.edit');
        Route::get('/rrhh/incidencias', \App\Livewire\HR\IncidentIndex::class)->name('hr.incidents.index');
        Route::get('/rrhh/incidencias/crear', \App\Livewire\HR\IncidentForm::class)->name('hr.incidents.create');
        Route::get('/rrhh/incidencias/{incident}/editar', \App\Livewire\HR\IncidentForm::class)->name('hr.incidents.edit');
        Route::get('/rrhh/evaluaciones', \App\Livewire\HR\EvaluationIndex::class)->name('hr.evaluations.index');
        Route::get('/rrhh/evaluaciones/crear', \App\Livewire\HR\EvaluationForm::class)->name('hr.evaluations.create');
        Route::get('/rrhh/evaluaciones/{evaluation}/editar', \App\Livewire\HR\EvaluationForm::class)->name('hr.evaluations.edit');
        Route::get('/rrhh/capacitacion/costos', \App\Livewire\HR\TrainingCostIndex::class)->name('hr.training.costs');
        Route::get('/rrhh/conceptos-nomina', \App\Livewire\HR\PayrollConceptIndex::class)->name('hr.payroll.concepts');
        Route::get('/rrhh/conceptos-nomina/crear', \App\Livewire\HR\PayrollConceptForm::class)->name('hr.payroll.concepts.create');
        Route::get('/rrhh/conceptos-nomina/{payrollConcept}/editar', \App\Livewire\HR\PayrollConceptForm::class)->name('hr.payroll.concepts.edit');
        Route::get('/rrhh/zonas-asistencia', \App\Livewire\HR\AttendanceLocationIndex::class)->name('hr.attendance.locations');
        Route::get('/rrhh/zonas-asistencia/crear', \App\Livewire\HR\AttendanceLocationForm::class)->name('hr.attendance.locations.create');
        Route::get('/rrhh/zonas-asistencia/{attendanceLocation}/editar', \App\Livewire\HR\AttendanceLocationForm::class)->name('hr.attendance.locations.edit');
        Route::get('/rrhh/checkin', \App\Livewire\HR\AttendanceCheckin::class)->name('hr.attendance.checkin');
        Route::get('/rrhh/vacantes', \App\Livewire\HR\JobOpeningIndex::class)->name('hr.job-openings.index');
        Route::get('/rrhh/vacantes/crear', \App\Livewire\HR\JobOpeningForm::class)->name('hr.job-openings.create');
        Route::get('/rrhh/vacantes/{jobOpening}/editar', \App\Livewire\HR\JobOpeningForm::class)->name('hr.job-openings.edit');
        Route::get('/rrhh/organigrama', \App\Livewire\HR\OrgChart::class)->name('hr.org-chart');
        Route::get('/rrhh/plantilla', \App\Livewire\HR\WorkforcePlanning::class)->name('hr.workforce-planning');
    });
    Route::middleware('can:edit hr')->group(function () {
        Route::get('/rrhh/empleados/{employee}/editar', \App\Livewire\HR\EmployeeForm::class)->name('hr.employees.edit');
        Route::get('/rrhh/prospectos/{prospect}/editar', \App\Livewire\HR\ProspectForm::class)->name('hr.prospects.edit');
    });

    // ── Finanzas ──────────────────────────────────────────────────────────────
    Route::middleware('can:view finance')->group(function () {
        Route::get('/finanzas/cuentas', \App\Livewire\Finance\FinanceAccountIndex::class)->name('finance.accounts.index');
        Route::get('/finanzas/transacciones', \App\Livewire\Finance\FinanceTransactionIndex::class)->name('finance.transactions.index');
        Route::get('/finanzas/transacciones/imprimir', \App\Http\Controllers\Finance\TransactionsPrintController::class)->name('finance.transactions.print');
        Route::get('/finanzas/presupuestos', \App\Livewire\Finance\FinanceBudgetIndex::class)->name('finance.budgets.index');
        Route::get('/finanzas/flujo-caja', \App\Livewire\Finance\FinanceCashflowIndex::class)->name('finance.cashflow.index');
        Route::get('/finanzas/flujo-caja/imprimir', \App\Http\Controllers\Finance\CashflowPrintController::class)->name('finance.cashflow.print');
        Route::get('/finanzas/antigüedad-saldos', \App\Livewire\Finance\AccountsReceivableAging::class)->name('finance.aging.index');
        Route::get('/finanzas/antigüedad-cxp', \App\Livewire\Finance\AccountsPayableAging::class)->name('finance.ap-aging.index');
        Route::get('/finanzas/conciliacion-proveedores', \App\Livewire\Finance\SupplierPaymentReconciliation::class)->name('finance.ap-reconciliation.index');
        Route::get('/finanzas/pagos-programados', \App\Livewire\Finance\ScheduledPaymentIndex::class)->name('finance.scheduled-payments.index');
        Route::get('/finanzas/cierre-mensual', \App\Livewire\Finance\PeriodCloseIndex::class)->name('finance.period-close.index');
        Route::get('/finanzas/gestion', \App\Livewire\Finance\FinanceDashboard::class)->name('finance.dashboard');
        Route::get('/finanzas/reportes', \App\Livewire\Finance\FinanceReports::class)->name('finance.reports.index');
        Route::get('/finanzas/extracto-bancario', \App\Livewire\Finance\BankStatement::class)->name('finance.bank-statement.index');
        Route::get('/finanzas/conciliacion-bancaria', \App\Livewire\Finance\BankReconciliationIndex::class)->name('finance.bank-reconciliation.index');
        Route::get('/finanzas/recordatorios-pago', \App\Livewire\Finance\PaymentReminders::class)->name('finance.reminders.index');
        Route::get('/finanzas/dashboard-cobranza', \App\Livewire\Finance\CollectionsDashboard::class)->name('finance.collections.dashboard');
        Route::get('/finanzas/conciliacion', \App\Livewire\Finance\PaymentReconciliation::class)->name('finance.reconciliation.index');
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

        // Viáticos
        Route::get('/finanzas/viaticos', \App\Livewire\Finance\TravelExpenseIndex::class)->name('finance.travel-expenses.index');
        Route::get('/finanzas/viaticos/crear', \App\Livewire\Finance\TravelExpenseForm::class)->name('finance.travel-expenses.create');
        Route::get('/finanzas/viaticos/{travel}/editar', \App\Livewire\Finance\TravelExpenseForm::class)->name('finance.travel-expenses.edit');
    });

});

require __DIR__ . '/auth.php';
