<?php

use App\Http\Controllers\ControllerSP\PelangganController;
use App\Http\Controllers\ControllerSP\DaftarPesananController;
use App\Http\Controllers\ControllerSP\PenjualanController;
use App\Http\Controllers\Akuntansi\KodeAkuntingController;
use App\Http\Controllers\Akuntansi\JurnalUmumController;
use App\Http\Controllers\Inventory\SupplierController;
use App\Http\Controllers\Keamanan\RoleController;
use App\Http\Controllers\Presensi\KaryawanController;
use App\Http\Controllers\Retur\ReturPenjualanController;
use App\Http\Controllers\Retur\ReturPembelianController;
use App\Http\Controllers\Comprof\SettingMenuController;
use App\Http\Controllers\Presensi\DivisiController;
use App\Http\Controllers\Presensi\SubDivisiController;
use App\Http\Controllers\Presensi\PosisiController;
use App\Http\Controllers\Presensi\JadwalController;
use App\Http\Controllers\Presensi\ShiftController;
use App\Http\Controllers\Presensi\LiburNasionalController;
use App\Http\Controllers\Presensi\LeaveApprovalController;
use App\Http\Controllers\Presensi\OfficeLocationController;
use App\Http\Controllers\Presensi\RekapController;
use App\Http\Controllers\Presensi\AbsensiController;
use App\Http\Controllers\Comprof\SubMenuController;
use App\Http\Controllers\Comprof\DataStafController;
use App\Http\Controllers\Inventory\KelompokProdukController;
use App\Http\Controllers\Inventory\SatuanProdukController;
use App\Http\Controllers\Inventory\DtprodukController;
use App\Http\Controllers\Inventory\PurchaseOrderController;
use App\Http\Controllers\Inventory\PenerimaanController;
use App\Http\Controllers\Akuntansi\BukuBesarController;
use App\Http\Controllers\Akuntansi\KasMasukController;
use App\Http\Controllers\Akuntansi\KasKeluarController;
use App\Http\Controllers\Keamanan\PermissionController;
use App\Http\Controllers\Keamanan\MemberController;
use App\Http\Controllers\Comprof\SliderController;
use App\Http\Controllers\Comprof\SetPerusahaanController;
use App\Http\Controllers\Comprof\KategoriBeritaController;
use App\Http\Controllers\Comprof\KategoriAlbumController;
use App\Http\Controllers\Comprof\WebsiteContentController;
use App\Http\Controllers\Comprof\BeritaController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\GalleryController;
use App\Http\Controllers\Frontend\NewsController;
use App\Http\Controllers\Frontend\PageController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\MutasiGudang\WarehouseController;
use App\Http\Controllers\MutasiGudang\GudangOrderController;
use App\Http\Controllers\MutasiGudang\TransferGudangController;
use App\Http\Controllers\MutasiGudang\TerimaGudangController;
use App\Http\Controllers\Inventory\StockReportController;
use App\Http\Controllers\TestController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('warehouse/get-all', [WarehouseController::class, 'getAll'])->name('warehouse.getAll');

// Frontend Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/team', [HomeController::class, 'team'])->name('team');
Route::get('/careers', [PageController::class, 'show'])->name('careers');
// Gallery Routes
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('/gallery/{id}', [GalleryController::class, 'show'])->name('gallery.show');
// News Routes
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/category/{id}', [NewsController::class, 'category'])->name('news.category');
Route::get('/news/{slug}', [NewsController::class, 'show'])->name('news.show');
// Dynamic Pages from Website Content
Route::get('/page/{id}', [PageController::class, 'show'])->name('page.show');
Route::get('/submenu/{submenuId}', [PageController::class, 'showBySubmenu'])->name('page.by.submenu');

/* Auth Routes */

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Auth::routes();

Route::middleware(['web'])->group(function () {
    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
});

//route yang tidak perlu acess menu
Route::middleware(['auth'])->group(function () {
    Route::get('/home', 'HomeController@index')->name('home');
    Route::get('/profile', 'ProfileController@index')->name('profile');
    Route::put('/profile', 'ProfileController@update')->name('profile.update');

});

Route::middleware(['auth', 'can.access.menu'])->group(function () {
    // --- Data Presensi Routes ---
    Route::middleware(['auth'])->group(function () { // Pastikan hanya user terautentikasi
    Route::prefix('presensi')->group(function () {
            // Data Karyawan Routes
            Route::get('/employee', [KaryawanController::class, 'index'])->name('employee.index');
            Route::post('/employee', [KaryawanController::class, 'store'])->name('employee.store');
            Route::get('/employee/{Employee}', [KaryawanController::class, 'show'])->name('employee.show');
            Route::put('/employee/{Employee}', [KaryawanController::class, 'update'])->name('employee.update');
            Route::delete('/employee/{Employee}', [KaryawanController::class, 'destroy'])->name('employee.destroy');
            // Divisi Routes
            Route::get('/divisi', [DivisiController::class, 'index'])->name('divisi.index');
            Route::post('/divisi', [DivisiController::class, 'store'])->name('divisi.store');
            Route::get('/divisi/{Divisi}', [DivisiController::class, 'show'])->name('divisi.show');
            Route::put('/divisi/{Divisi}', [DivisiController::class, 'update'])->name('divisi.update');
            Route::delete('/divisi/{Divisi}', [DivisiController::class, 'destroy'])->name('divisi.destroy');
            // Sub-Divisi Routes
            Route::get('/subdivisi', [SubDivisiController::class, 'index'])->name('subdivisi.index');
            Route::post('/subdivisi', [SubDivisiController::class, 'store'])->name('subdivisi.store');
            Route::get('/subdivisi/{SubDivisi}', [SubDivisiController::class, 'show'])->name('subdivisi.show');
            Route::put('/subdivisi/{SubDivisi}', [SubDivisiController::class, 'update'])->name('subdivisi.update');
            Route::delete('/subdivisi/{SubDivisi}', [SubDivisiController::class, 'destroy'])->name('subdivisi.destroy');
            Route::get('/get-subdivisi/{Divisi}', [SubDivisiController::class, 'getByDivision'])->name('subdivisi.getByDivision');
            // Posisi Routes
            Route::get('/posisi', [PosisiController::class, 'index'])->name('posisi.index');
            Route::post('/posisi', [PosisiController::class, 'store'])->name('posisi.store');
            Route::get('/posisi/{Posisi}', [PosisiController::class, 'show'])->name('posisi.show');
            Route::put('/posisi/{Posisi}', [PosisiController::class, 'update'])->name('posisi.update');
            Route::delete('/posisi/{Posisi}', [PosisiController::class, 'destroy'])->name('posisi.destroy');
            // Jadwal
        Route::prefix('jadwal')->name('jadwal.')->group(function () {
            Route::get('/', [JadwalController::class, 'index'])->name('index');
            Route::put('/update/{id}', [JadwalController::class, 'update'])->name('update');
            Route::post('/fetch', [JadwalController::class, 'fetchJadwal'])->name('fetch');
            Route::post('/generate', [JadwalController::class, 'generate'])->name('generate');
            // === TAMBAHKAN ROUTE INI ===
            Route::delete('/destroy', [JadwalController::class, 'destroyJadwal'])->name('destroy');
            Route::post('/check', [JadwalController::class, 'checkJadwal'])->name('check');
        });
        // Shift
        Route::post('/shift', [ShiftController::class,'store'])->name('shift.store');
        Route::put('/shift/{Shift}', [ShiftController::class,'update'])->name('shift.update');
        Route::delete('/shift/{shift}', [ShiftController::class,'destroy'])->name('shift.destroy');
        // Libur Nasional
        Route::post('/holiday', [LiburNasionalController::class,'store'])->name('holiday.store');
        Route::put('/holiday/{LiburNasional}', [LiburNasionalController::class,'update'])->name('holiday.update');
        Route::delete('/holiday/{liburNasional}', [LiburNasionalController::class,'destroy'])->name('holiday.destroy');
        // Lokasi Office
        Route::post('/officelocation', [OfficeLocationController::class, 'store'])->name('officelocation.store');
        Route::put('/officelocation/{officeLocation}', [OfficeLocationController::class, 'update'])->name('officelocation.update');
        Route::delete('/officelocation/{officeLocation}', [OfficeLocationController::class, 'destroy'])->name('officelocation.destroy');
        // Absensi
        Route::resource('absensi', AbsensiController::class);
        // Leave Request
        Route::get('/leave-approvals', [LeaveApprovalController::class, 'index'])->name('leave.approvals.index');
        Route::post('/leave-approvals/{leaveRequest}/approve', [LeaveApprovalController::class, 'approve'])->name('leave.approvals.approve');
        Route::post('/leave-approvals/{leaveRequest}/reject', [LeaveApprovalController::class, 'reject'])->name('leave.approvals.reject');
        Route::delete('/leave-approvals/{leaveRequest}', [LeaveApprovalController::class, 'destroy'])->name('leave.approvals.destroy');
        //Rekap
        Route::match(['get', 'post'], '/rekap', [RekapController::class, 'generateReport'])->name('rekap.generate');
    });
});

    // --- Akuntansi Routes ---
    Route::prefix('akunting')->group(function () {
        // Kode Akuntansi routes
        Route::get('/kodeakunting', [KodeAkuntingController::class, 'index'])->name('kodeakunting.index');
        Route::post('/kodeakunting', [KodeAkuntingController::class, 'store'])->name('kodeakunting.store');
        Route::get('/kodeakunting/{id}/edit', [KodeAkuntingController::class, 'edit'])->name('kodeakunting.edit');
        Route::put('/kodeakunting/{id}', [KodeAkuntingController::class, 'update'])->name('kodeakunting.update');
        Route::delete('/kodeakunting/{id}', [KodeAkuntingController::class, 'destroy'])->name('kodeakunting.destroy');
        Route::get('/kodeakunting/get-subclasses/{classId}', [KodeAkuntingController::class, 'getSubclassesByClass'])->name('kodeakunting.getSubclasses');
        // Jurnal Umum routes
        Route::resource('jurnal-umum', JurnalUmumController::class)->names('jurnalumum');
        Route::get('jurnal-umum/get-nama-perkiraan/{id}', [JurnalUmumController::class, 'getNamaPerkiraan'])->name('jurnalumum.getNamaPerkiraan');
        Route::get('jurnal-umum/get-next-no', [JurnalUmumController::class, 'create'])->name('jurnalumum.getNextNo');
        // Buku Besar routes
        Route::get('buku-besar', [BukuBesarController::class, 'index'])->name('bukubesar.index');
        Route::get('buku-besar/pdf', [BukuBesarController::class, 'generatePDF'])->name('bukubesar.pdf');
        // Kas Masuk routes
        Route::resource('kas-masuk', KasMasukController::class);
        // Kas Keluar routes
        Route::resource('kas-keluar', KasKeluarController::class);
    });

    // --- Inventory Routes ---
    Route::prefix('inventory')->group(function () {
        // Supplier routes
        Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier.index');
        Route::post('/supplier', [SupplierController::class, 'store'])->name('supplier.store');
        Route::put('/supplier/{supplier}', [SupplierController::class, 'update'])->name('supplier.update');
        Route::delete('/supplier/{supplier}', [SupplierController::class, 'destroy'])->name('supplier.destroy');
        // Kelompok Produk routes
        Route::get('/kelompokproduk', [KelompokprodukController::class, 'index'])->name('kelompokproduk.index');
        Route::post('/kelompokproduk', [KelompokprodukController::class, 'store'])->name('kelompokproduk.store');
        Route::put('/kelompokproduk/{kelompokproduk}', [KelompokprodukController::class, 'update'])->name('kelompokproduk.update');
        Route::delete('/kelompokproduk/{kelompokproduk}', [KelompokprodukController::class, 'destroy'])->name('kelompokproduk.destroy');
        // Satuan Produk routes
        Route::get('/satuanproduk', [SatuanProdukController::class, 'index'])->name('satuanproduk.index');
        Route::post('/satuanproduk', [SatuanProdukController::class, 'store'])->name('satuanproduk.store');
        Route::put('/satuanproduk/{satuanproduk}', [SatuanProdukController::class, 'update'])->name('satuanproduk.update');
        Route::delete('/satuanproduk/{satuanproduk}', [SatuanProdukController::class, 'destroy'])->name('satuanproduk.destroy');
        // Data Produk Routes
        Route::get('/dataproduk', [DtprodukController::class, 'index'])->name('dataproduk.index');
        Route::post('/dataproduk', [DtprodukController::class, 'store'])->name('dataproduk.store');
        Route::get('/dataproduk/{produk}/edit', [DtprodukController::class, 'edit'])->name('dataproduk.edit');
        Route::put('/dataproduk/{produk}', [DtprodukController::class, 'update'])->name('dataproduk.update');
        Route::delete('/dataproduk/{produk}', [DtprodukController::class, 'destroy'])->name('dataproduk.destroy');
        // Purchase Order routes
        Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
        Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
        Route::get('/purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
        Route::get('/purchase-orders/{purchase_order}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-orders.edit');
        Route::delete('/purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'destroy'])->name('purchase-orders.destroy');
        // Custom routes for PO operations
        Route::put('/purchase-orders/{id}/update-header', [PurchaseOrderController::class, 'updateHeader'])->name('purchase-orders.update-header');
        Route::post('/purchase-orders/{poId}/details', [PurchaseOrderController::class, 'storeDetail'])->name('purchase-orders.store-detail');
        Route::put('/purchase-orders/{poId}/details/{detailId}', [PurchaseOrderController::class, 'updateDetail'])->name('purchase-orders.update-detail');
        Route::delete('/purchase-orders/{poId}/details/{detailId}', [PurchaseOrderController::class, 'deleteDetail'])->name('purchase-orders.delete-detail');
        Route::post('/purchase-orders/{id}/publish', [PurchaseOrderController::class, 'publish'])->name('purchase-orders.publish');
        Route::delete('/purchase-orders/{id}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
        // Penerimaan routes
        Route::get('/penerimaan', [PenerimaanController::class, 'index'])->name('penerimaan.index');
        Route::get('/penerimaan/create', [PenerimaanController::class, 'create'])->name('penerimaan.create');
        Route::post('/penerimaan', [PenerimaanController::class, 'store'])->name('penerimaan.store');
        Route::get('/penerimaan/{penerimaan}', [PenerimaanController::class, 'show'])->name('penerimaan.show');
        Route::get('/penerimaan/{penerimaan}/edit', [PenerimaanController::class, 'edit'])->name('penerimaan.edit');
        Route::put('/penerimaan/{penerimaan}', [PenerimaanController::class, 'update'])->name('penerimaan.update');
        Route::delete('/penerimaan/{penerimaan}', [PenerimaanController::class, 'destroy'])->name('penerimaan.destroy');
        // Custom routes for Penerimaan operations
        Route::put('/penerimaan/{id}/update-header', [PenerimaanController::class, 'updateHeader'])->name('penerimaan.update-header');
        Route::post('/penerimaan/{penerimaanId}/details', [PenerimaanController::class, 'storeDetail'])->name('penerimaan.store-detail');
        Route::put('/penerimaan/{penerimaanId}/details/{detailId}', [PenerimaanController::class, 'updateDetail'])->name('penerimaan.update-detail');
        Route::delete('/penerimaan/{penerimaanId}/details/{detailId}', [PenerimaanController::class, 'deleteDetail'])->name('penerimaan.delete-detail');
        Route::post('/penerimaan/{id}/publish', [PenerimaanController::class, 'publish'])->name('penerimaan.publish');
        Route::delete('/penerimaan/{id}/cancel', [PenerimaanController::class, 'cancel'])->name('penerimaan.cancel');
        Route::get('/stock-report', [StockReportController::class, 'index'])->name('inventory.stock_report');
    });

    // --- Keamanan Routes ---
    Route::prefix('keamanan')->name('keamanan.')->group(function () {
        // Route for Role (URL: /keamanan/roles)
        Route::resource('roles', RoleController::class);
        // Route for Permission (URL: /keamanan/permission)
        Route::get('permission', [PermissionController::class, 'index'])->name('permission.index');
        Route::post('permission/update-menu-access', [PermissionController::class, 'updateMenuAccess'])->name('permission.updateMenuAccess');
        // Route for Member (User) (URL: /keamanan/member)
        Route::get('member', [MemberController::class, 'index'])->name('member.index');
        Route::post('member', [MemberController::class, 'store'])->name('member.store');
        Route::get('member/{id}/edit', [MemberController::class, 'edit'])->name('member.edit');
        Route::put('member/{id}', [MemberController::class, 'update'])->name('member.update');
        Route::delete('member/{id}', [MemberController::class, 'destroy'])->name('member.destroy');
        Route::get('member/search-employees', [MemberController::class, 'searchEmployees'])->name('member.searchEmployees');
        Route::get('member/get-role-menus-by-role/{roleId?}', [MemberController::class, 'getRoleMenusByRoleId'])->name('member.getRoleMenusByRoleId');
    });

    // --- Gudang Routes ---

    Route::prefix('mutasigudang')->middleware(['auth'])->group(function () {

        // Gudang
        Route::resource('warehouse', WarehouseController::class);

        // Optional JSON endpoint (untuk AJAX edit)
        Route::get('/{id}/json', [WarehouseController::class, 'json'])->name('json');

        // Permintaan Gudang (Gudang Order)
        Route::get('gudangorder', [GudangOrderController::class, 'index'])->name('gudangorder.index');
        Route::get('gudangorder/create', [GudangOrderController::class, 'create'])->name('gudangorder.create');
        Route::get('gudangorder/{id}/edit', [GudangOrderController::class, 'edit'])->name('gudangorder.edit');
        Route::get('gudangorder/{id}', [GudangOrderController::class, 'show'])->name('gudangorder.show');
        Route::post('gudangorder', [GudangOrderController::class, 'store'])->name('gudangorder.store');
        Route::delete('gudangorder/{id}', [GudangOrderController::class, 'destroy'])->name('gudangorder.destroy');
        Route::get('get-products-by-warehouse/{warehouse_id}', [GudangOrderController::class, 'getProductsByWarehouse'])->name('gudangorder.getProducts');
        Route::post('gudangorder/store-detail', [GudangOrderController::class, 'storeDetail'])->name('gudangorder.storeDetail');
        Route::put('gudangorder/{id}/update-header', [GudangOrderController::class, 'updateHeader'])->name('gudangorder.updateHeader');
        Route::delete('gudangorder/{orderId}/details/{detailId}', [GudangOrderController::class, 'destroyDetail'])->name('gudangorder.destroyDetail');
        Route::put('gudangorder/{id}/submit', [GudangOrderController::class, 'submit'])->name('gudangorder.submit');

        // Transfer Gudang
        Route::get('transfergudang/in-transit', [TransferGudangController::class, 'showInTransit'])->name('transfergudang.inTransit');
        Route::get('transfergudang', [TransferGudangController::class, 'index'])->name('transfergudang.index');
        Route::get('transfergudang/create', [TransferGudangController::class, 'create'])->name('transfergudang.create');
        Route::get('transfergudang/{id}/edit', [TransferGudangController::class, 'edit'])->name('transfergudang.edit');
        Route::get('transfergudang/{id}', [TransferGudangController::class, 'show'])->name('transfergudang.show');
        Route::delete('transfergudang/{id}', [TransferGudangController::class, 'destroy'])->name('transfergudang.destroy');
        Route::put('transfergudang/{id}/update-header', [TransferGudangController::class, 'updateHeader'])->name('transfergudang.updateHeader');
        Route::put('transfergudang/{id}/submit', [TransferGudangController::class, 'submit'])->name('transfergudang.submit');
        Route::post('transfergudang/detail/store', [TransferGudangController::class, 'storeDetail'])->name('transfergudang.storeDetail');
        Route::delete('transfergudang/{id}/details/{detailId}', [TransferGudangController::class, 'destroyDetail'])->name('transfergudang.destroyDetail');
        Route::get('transfergudang/fetch-details/{permintaanId}', [TransferGudangController::class, 'fetchPermintaanDetails'])->name('transfergudang.fetchDetails');
        Route::post('transfergudang/{id}/sync-details', [TransferGudangController::class, 'syncDetailsFromPermintaan'])->name('transfergudang.syncDetails');

        //PenerimaanGudang
        Route::get('terimagudang', [TerimaGudangController::class, 'index'])->name('terimagudang.index');
        Route::get('terimagudang/create', [TerimaGudangController::class, 'create'])->name('terimagudang.create');
        Route::post('terimagudang/store', [TerimaGudangController::class, 'store'])->name('terimagudang.store');
        Route::get('terimagudang/{id}/edit', [TerimaGudangController::class, 'edit'])->name('terimagudang.edit');
        Route::put('terimagudang/{id}', [TerimaGudangController::class, 'update'])->name('terimagudang.update');
        Route::delete('terimagudang/{id}', [TerimaGudangController::class, 'destroy'])->name('terimagudang.destroy');
        Route::get('terimagudang/get-transfer-details/{id}', [TerimaGudangController::class, 'getTransferDetails'])->name('terimagudang.getTransferDetails');

    });

    // --- Data Retur Routes ---
    Route::prefix('retur')->name('retur.')->group(function () {
        // RETUR PENJUALAN
        Route::get('penjualan', [ReturPenjualanController::class, 'index'])->name('penjualan.index');
        Route::get('penjualan/customers', [ReturPenjualanController::class, 'getCustomers'])->name('penjualan.customers');
        Route::get('penjualan/warehouses', [ReturPenjualanController::class, 'getWarehouses'])->name('penjualan.warehouses');
        Route::get('penjualan/product-data', [ReturPenjualanController::class, 'getProductData'])->name('penjualan.product-data');
        Route::get('penjualan/uom-options', [ReturPenjualanController::class, 'getUoms'])->name('penjualan.uom-options');
        Route::get('penjualan/data', [ReturPenjualanController::class, 'dataJson'])->name('penjualan.data');
        Route::get('penjualan/create', [ReturPenjualanController::class, 'create'])->name('penjualan.create');
        Route::get('penjualan/{id}/edit', [ReturPenjualanController::class, 'edit'])->name('penjualan.edit');
        Route::put('penjualan/{id}', [ReturPenjualanController::class, 'updateHeader'])->name('penjualan.update');
        // DETAIL
        Route::get('penjualan/{id}/details', [ReturPenjualanController::class, 'detailsJson'])->name('penjualan.details.data');
        Route::post('penjualan/{id}/details', [ReturPenjualanController::class, 'storeDetail'])->name('penjualan.details.store');
        Route::put('penjualan/{id}/details/{detailId}', [ReturPenjualanController::class, 'updateDetail'])->name('penjualan.details.update');
        Route::delete('penjualan/{id}/details/{detailId}', [ReturPenjualanController::class, 'destroyDetail'])->name('penjualan.details.destroy');
        // DRAFT
        Route::delete('penjualan/{id}', [ReturPenjualanController::class, 'destroyHeader'])->name('penjualan.destroy');
        Route::put('penjualan/{id}/publish', [ReturPenjualanController::class, 'publish'])->name('penjualan.publish');
        Route::put('penjualan/{id}/publish-edit', [ReturPenjualanController::class, 'publishEdit'])->name('penjualan.publishEdit');
        // APPROVE
        Route::post('penjualan/approve-all', [ReturPenjualanController::class, 'approveAll'])->name('penjualan.approveAll');
        Route::post('penjualan/{id}/approve', [ReturPenjualanController::class, 'approve'])->name('penjualan.approve');
        // PRINT
        Route::get('penjualan/print-all', [ReturPenjualanController::class, 'printAll'])->name('penjualan.printAll');
        Route::get('penjualan/{id}/print', [ReturPenjualanController::class, 'print'])->name('penjualan.print');

        // RETUR PEMBELIAN
        Route::get('pembelian', [ReturPembelianController::class, 'index'])->name('pembelian.index');
        Route::get('pembelian/suppliers', [ReturPembelianController::class, 'getSuppliers'])->name('pembelian.suppliers');
        Route::get('pembelian/warehouses', [ReturPembelianController::class, 'getWarehouses'])->name('pembelian.warehouses');
        Route::get('pembelian/product-data', [ReturPembelianController::class, 'getProductData'])->name('pembelian.product-data');
        Route::get('pembelian/uom-options', [ReturPembelianController::class, 'getUoms'])->name('pembelian.uom-options');
        Route::get('pembelian/data', [ReturPembelianController::class, 'dataJson'])->name('pembelian.data');
        Route::get('pembelian/create', [ReturPembelianController::class, 'create'])->name('pembelian.create');
        Route::get('pembelian/{id}/edit', [ReturPembelianController::class, 'edit'])->name('pembelian.edit');
        Route::put('pembelian/{id}', [ReturPembelianController::class, 'updateHeader'])->name('pembelian.update');
        // DETAIL
        Route::get('pembelian/{id}/details', [ReturPembelianController::class, 'detailsJson'])->name('pembelian.details.data');
        Route::post('pembelian/{id}/details', [ReturPembelianController::class, 'storeDetail'])->name('pembelian.details.store');
        Route::put('pembelian/{id}/details/{detailId}', [ReturPembelianController::class, 'updateDetail'])->name('pembelian.details.update');
        Route::delete('pembelian/{id}/details/{detailId}', [ReturPembelianController::class, 'destroyDetail'])->name('pembelian.details.destroy');
        // DRAFT
        Route::delete('pembelian/{id}', [ReturPembelianController::class, 'destroyHeader'])->name('pembelian.destroy');
        Route::put('pembelian/{id}/publish', [ReturPembelianController::class, 'publish'])->name('pembelian.publish');
        Route::put('pembelian/{id}/publish-edit', [ReturPembelianController::class, 'publishEdit'])->name('pembelian.publishEdit');
        // APPROVE
        Route::post('pembelian/approve-all', [ReturPembelianController::class, 'approveAll'])->name('pembelian.approveAll');
        Route::post('pembelian/{id}/approve', [ReturPembelianController::class, 'approve'])->name('pembelian.approve');
        // PRINT
        Route::get('pembelian/print-all', [ReturPembelianController::class, 'printAll'])->name('pembelian.printAll');
        Route::get('pembelian/{id}/print', [ReturPembelianController::class, 'print'])->name('pembelian.print');
    });

    // --- Company Profile routes group ---
    Route::prefix('comprof')->name('comprof.')->group(function () {
        // Setting Menu Routes
        Route::get('/settingmenu', [SettingMenuController::class, 'index'])->name('settingmenu.index');
        Route::post('/settingmenu', [SettingMenuController::class, 'store'])->name('settingmenu.store');
        Route::put('/settingmenu/{settingmenu}', [SettingMenuController::class, 'update'])->name('settingmenu.update');
        Route::delete('/settingmenu/{settingmenu}', [SettingMenuController::class, 'destroy'])->name('settingmenu.destroy');
        // Sub Menu Routes
        Route::get('/settingsubmenu', [SubMenuController::class, 'index'])->name('settingsubmenu.index');
        Route::post('/settingsubmenu', [SubMenuController::class, 'store'])->name('settingsubmenu.store');
        Route::put('/settingsubmenu/{submenu}', [SubMenuController::class, 'update'])->name('settingsubmenu.update');
        Route::delete('/settingsubmenu/{submenu}', [SubMenuController::class, 'destroy'])->name('settingsubmenu.destroy');
        // Data Staf
        Route::get('/datastaf', [DataStafController::class, 'index'])->name('datastaf.index');
        Route::post('/datastaf', [DataStafController::class, 'store'])->name('datastaf.store');
        Route::put('/datastaf/{datastaf}', [DataStafController::class, 'update'])->name('datastaf.update');
        Route::delete('/datastaf/{datastaf}', [DataStafController::class, 'destroy'])->name('datastaf.destroy');
        // Slider Routes
        Route::get('/slider', [SliderController::class, 'index'])->name('slider.index');
        Route::post('/slider', [SliderController::class, 'store'])->name('slider.store');
        Route::put('/slider/{slider}', [SliderController::class, 'update'])->name('slider.update');
        Route::delete('/slider/{slider}', [SliderController::class, 'destroy'])->name('slider.destroy');
        Route::post('/slider/upload-image', [SliderController::class, 'uploadImage'])->name('slider.upload-image');

        // Set Perusahaan Routes
        Route::get('/setperusahaan', [SetPerusahaanController::class, 'index'])->name('setperusahaan.index');
        Route::post('/setperusahaan', [SetPerusahaanController::class, 'store'])->name('setperusahaan.store');
        Route::post('/setperusahaan/upload-image', [SetPerusahaanController::class, 'uploadImage'])->name('setperusahaan.upload-image');
        // Kategori Berita Routes
        Route::get('/kategoriberita', [KategoriBeritaController::class, 'index'])->name('kategoriberita.index');
        Route::post('/kategoriberita', [KategoriBeritaController::class, 'store'])->name('kategoriberita.store');
        Route::put('/kategoriberita/{kategoriberita}', [KategoriBeritaController::class, 'update'])->name('kategoriberita.update');
        Route::delete('/kategoriberita/{kategoriberita}', [KategoriBeritaController::class, 'destroy'])->name('kategoriberita.destroy');
        // Kategori Album Routes
        Route::get('/kategorialbum', [KategoriAlbumController::class, 'index'])->name('kategorialbum.index');
        Route::post('/kategorialbum', [KategoriAlbumController::class, 'store'])->name('kategorialbum.store');
        Route::put('/kategorialbum/{kategorialbum}', [KategoriAlbumController::class, 'update'])->name('kategorialbum.update');
        Route::delete('/kategorialbum/{kategorialbum}', [KategoriAlbumController::class, 'destroy'])->name('kategorialbum.destroy');
        // Website Content Routes
        Route::get('/websitecontent', [WebsiteContentController::class, 'index'])->name('websitecontent.index');
        Route::post('/websitecontent', [WebsiteContentController::class, 'store'])->name('websitecontent.store');
        Route::put('/websitecontent/{id}', [WebsiteContentController::class, 'update'])->name('websitecontent.update');
        Route::delete('/websitecontent/{id}', [WebsiteContentController::class, 'destroy'])->name('websitecontent.destroy');
        Route::post('/websitecontent/upload-image', [WebsiteContentController::class, 'uploadImage'])->name('websitecontent.upload-image');

        // Berita Routes
        Route::get('/berita', [BeritaController::class, 'index'])->name('berita.index');
        Route::get('/berita/create', [BeritaController::class, 'create'])->name('berita.create');
        Route::post('/berita', [BeritaController::class, 'store'])->name('berita.store');
        Route::get('/berita/{berita}/edit', [BeritaController::class, 'edit'])->name('berita.edit');
        Route::put('/berita/{berita}', [BeritaController::class, 'update'])->name('berita.update');
        Route::delete('/berita/{berita}', [BeritaController::class, 'destroy'])->name('berita.destroy');
    });

// --- Routes Penjualan ---
    Route::resource('pelanggan', PelangganController::class);
    Route::resource('customer-orders', DaftarPesananController::class);
    Route::resource('penjualan', PenjualanController::class)->only(['index', 'store']);
    Route::post('penjualan/{id}/approve', [\App\Http\Controllers\ControllerSP\PenjualanController::class, 'approve'])->name('penjualan.approve');
    Route::get('penjualan/{id}', [\App\Http\Controllers\ControllerSP\PenjualanController::class, 'show'])->name('penjualan.show');
    Route::delete('penjualan/{penjualan}', [PenjualanController::class, 'destroy'])->name('penjualan.destroy');

        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/customer-orders/{id}/details', [\App\Http\Controllers\ControllerSP\DaftarPesananController::class, 'getOrderDetails']);
        Route::prefix('jualan')->name('jualan.')->group(function () {
            Route::get('/outstanding-orders/{pelanggan}', [PenjualanController::class, 'getOutstandingOrders'])->name('outstanding-orders');
            // Route to get the details of a specific customer order
            Route::get('/order-details/{customerOrder}', [PenjualanController::class, 'getOrderDetails'])->name('order-details');
        });
    });

});
