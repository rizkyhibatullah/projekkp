@extends('layouts.admin')
@section('main-content')

<div class="container-fluid">
    @if(isset($orders))
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Permintaan Gudang</h6>
            <a href="{{ route('gudangorder.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Buat Permintaan Baru
            </a>
        </div>
        <div class="card-body">
            @if ($message = Session::get('success'))
                <div class="alert alert-success">
                    <p>{{ $message }}</p>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>No. Permintaan</th>
                            <th>Tanggal</th>
                            <th>Lokasi Asal</th>
                            <th>Lokasi Tujuan</th>
                            <th>Bruto</th>
                            <th>Diskon</th>
                            <th>Pajak</th>
                            <th>Netto</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->pur_ordernumber }}</td>
                            <td>{{ $order->Pur_Date ? $order->Pur_Date->format('d M Y') : '-' }}</td>
                            <td>{{ $order->gudangPengirim->WARE_Name ?? '-' }}</td>
                            <td>{{ $order->gudangPenerima->WARE_Name ?? '-' }}</td>
                            <td class="text-end">{{ number_format($order->total_bruto, 2) }}</td>
                            <td class="text-end">{{ number_format($order->total_discount, 2) }}</td>
                            <td class="text-end">{{ number_format($order->total_taxes, 2) }}</td>
                            <td class="text-end fw-bold">{{ number_format($order->grand_total, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $order->pur_status === 'draft' ? 'warning' : 'success' }} text-white">
                                    {{ $order->pur_status === 'draft' ? 'DRAFT' : 'SUBMITTED' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('gudangorder.edit', $order->Pur_Auto) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('gudangorder.show', $order->Pur_Auto) }}" class="btn btn-sm btn-info" title="Lihat">
                                    <i class="fas fa-eye"></i>
                                </a>

                            </td>
                        </tr>
                        @empty
                        <tr>
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <img src="{{ asset('img/svg/undraw_editable_dywm.svg') }}" alt="Tidak ada data" style="height: 150px; width: auto; opacity: 0.8;" class="mb-4">
                                        <h5 class="font-weight-bold text-gray-800 mb-2">Belum ada Permintaan Gudang</h5>
                                        <p class="text-gray-500 mb-3">
                                            Saat ini belum ada data permintaan (Order) yang tersedia.
                                        </p>
                                        <a href="{{ route('gudangorder.create') }}" class="btn btn-success btn-sm">
                                            <i class="fas fa-plus"></i> Buat Permintaan Baru
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                 {!! $orders->links() !!}
            </div>
        </div>
    </div>

    @elseif(isset($order))
    <div class="d-flex justify-content-end mb-2">
        <a href="{{ route('gudangorder.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Kembali ke Daftar</a>
    </div>
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Form Permintaan Gudang</h6>
            <span class="badge bg-{{ $order->pur_status === 'draft' ? 'warning' : 'success' }} text-white">
                {{ $order->pur_status === 'draft' ? 'DRAFT' : 'SUBMITTED' }}
            </span>
        </div>
        <div class="card-body">
            <form id="headerForm">
                @csrf
                <input type="hidden" id="orderId" value="{{ $order->Pur_Auto }}">
                <div class="row">
                     <div class="col-md-6 mb-3">
                        <label for="pur_ordernumber" class="form-label">Nomor Permintaan</label>
                        <input type="text" name="pur_ordernumber" class="form-control"  value="{{ $order->pur_ordernumber }}" readonly >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="Pur_Date" class="form-label">Tanggal</label>
                        <input type="date" name="Pur_Date" class="form-control" value="{{ old('Pur_Date', $order->Pur_Date ? $order->Pur_Date->format('Y-m-d') : date('Y-m-d')) }}" required {{ $order->pur_status === 'draft' ? '' : 'readonly' }}>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="pur_warehouse" class="form-label">Lokasi Asal</label>
                        <select name="from_warehouse_id" id="from_warehouse_id" class="form-control" required>
                            <option value="">Pilih Gudang Pengirim</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->WARE_Auto }}">{{ $warehouse->WARE_Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="pur_destination">Lokasi Tujuan</label>
                        <select name="to_warehouse_id" id="to_warehouse_id" class="form-control" required>
                            <option value="">Pilih Gudang Penerima</option>
                            @foreach($allWarehouses as $warehouse) 
                                <option value="{{ $warehouse->WARE_Auto }}" {{ (isset($order) && $order->pur_destination == $warehouse->WARE_Auto) ? 'selected' : '' }}>
                                    {{ $warehouse->WARE_Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mb-2">
                        <label for="Pur_Note" class="form-label">Catatan</label>
                        <textarea class="form-control" name="Pur_Note" {{ $order->pur_status === 'draft' ? '' : 'readonly' }}>{{ $order->Pur_Note }}</textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($order->pur_status === 'draft')
    <div class="mb-3 d-flex">
        <button type="button" class="btn btn-primary mr-2" data-bs-toggle="modal" data-bs-target="#detailModal">
            <i class="fas fa-plus"></i> Tambah Barang
        </button>
        <button id="btnSubmitOrder" class="btn btn-success mr-2">
            <i class="fas fa-save"></i> Simpan & Ajukan
        </button>
        <button id="btnCancelDraft" class="btn btn-danger">
            <i class="fas fa-times"></i> Batalkan Draft
        </button>
    </div>
    @endif

    <div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detail Barang</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="detailTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th>Satuan</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Diskon</th>
                        <th>Pajak</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->details as $detail)
                    <tr>
                        <td>{{ $detail->Pur_ProdCode }}</td>
                        <td>{{ $detail->Pur_prodname }}</td>
                        <td>{{ $detail->Pur_UOM }}</td>
                        <td>{{ $detail->Pur_Qty }}</td>
                        <td>{{ number_format($detail->Pur_GrossPrice, 2) }}</td>
                        <td>{{ number_format($detail->Pur_Discount, 2) }}</td>
                        <td>{{ number_format($detail->Pur_Taxes, 2) }}</td>
                        <td>{{ number_format($detail->Pur_NettPrice, 2) }}</td>
                        <td>
                            @if($order->pur_status === 'draft')
                            <button class="btn btn-sm btn-danger delete-detail-btn" data-id="{{ $detail->Pur_Det_Auto }}" title="Hapus Barang">
                                <i class="fas fa-trash"></i>
                            </button>
                            @else
                            <span class="text-muted">Locked</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <i class="fas fa-shopping-basket fa-4x text-gray-300 mb-3"></i>
                                <h6 class="font-weight-bold text-gray-600">Keranjang Kosong</h6>
                                @if($order->pur_status === 'draft')
                                    <p class="text-gray-500 mb-2 small">Tambahkan barang yang ingin diminta ke dalam list ini.</p>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal">
                                        <i class="fas fa-plus"></i> Tambah Barang
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

    @if($order->pur_status === 'draft')
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="detailForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailModalLabel">Tambah Barang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="Pur_Auto" value="{{ $order->Pur_Auto }}">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="Pur_ProdCode" class="form-label">Kode Produk</label>
                                <select name="Pur_ProdCode" id="Pur_ProdCode" class="form-control product-select" required>
                                    <option value="">Pilih Gudang Pengirim di Halaman Utama</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="pur_prodname" class="form-label">Nama Produk</label>
                                <input type="text" name="pur_prodname" id="pur_prodname" class="form-control" required readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="Pur_UOM" class="form-label">Satuan</label>
                                <select name="Pur_UOM" id="Pur_UOM" class="form-control" required>
                                    <option value="" disabled selected>-- Pilih Satuan --</option>
                                    <option value="PCS">PCS</option>
                                    <option value="BOX">BOX</option>
                                    <option value="KG">KG</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="Pur_Qty" class="form-label">Qty</label>
                                <input type="number" id="Pur_Qty" name="Pur_Qty" class="form-control detail-calc" step="1" min="1" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="Pur_GrossPrice" class="form-label">Harga</label>
                                <input type="number" id="Pur_GrossPrice" name="Pur_GrossPrice" class="form-control detail-calc" step="0.01" min="0" required readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="Pur_Discount" class="form-label">Diskon</label>
                                <input type="number" id="Pur_Discount" name="Pur_Discount" class="form-control detail-calc" step="0.01" min="0" value="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="Pur_Taxes" class="form-label">Pajak</label>
                                <input type="number" id="Pur_Taxes" name="Pur_Taxes" class="form-control detail-calc" step="0.01" min="0" value="0">
                            </div>
                            <div class="col-12 mb-3">
                                <label for="Pur_NettPrice" class="form-label">Nominal (Harga Bersih)</label>
                                <input type="number" id="Pur_NettPrice" name="Pur_NettPrice" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" id="btnSaveDetail" class="btn btn-primary">Simpan Barang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
        @endif

    @endif

</div>
@endsection


@push('scripts')
<script>
$(document).ready(function() {
    $('#dataTable').DataTable();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });

    function showToast(type, message) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
        Toast.fire({
            icon: type,
            title: message
        });
    }

    @if(isset($order))
    
    const orderId = '{{ $order->Pur_Auto }}';
    const baseUrl = `{{ url('/mutasigudang/gudangorder') }}`;
    let availableProducts = []; 

    function filterDestinationOptions() {
        const sourceId = $('#from_warehouse_id').val();
        const $destSelect = $('#to_warehouse_id');
        const currentDestId = $destSelect.val();

        $destSelect.find('option').prop('disabled', false).show();

        if (sourceId) {
            const $targetOption = $destSelect.find('option[value="' + sourceId + '"]');
            $targetOption.prop('disabled', true).hide();
            if (currentDestId == sourceId) {
                $destSelect.val('').trigger('change');
            }
        }
    }

    function calculateNetPrice() {
        const qty = parseFloat($('#Pur_Qty').val()) || 0;
        const price = parseFloat($('#Pur_GrossPrice').val()) || 0;
        const discount = parseFloat($('#Pur_Discount').val()) || 0;
        const taxes = parseFloat($('#Pur_Taxes').val()) || 0;

        if (qty > 0) {
            const subtotal = qty * price;
            const netPrice = (subtotal - discount) + taxes;
            $('#Pur_NettPrice').val(netPrice.toFixed(2));
        } else {
            $('#Pur_NettPrice').val('');
        }
    }

    filterDestinationOptions();

    $('#from_warehouse_id').on('change', function() {
        filterDestinationOptions();
        var warehouseId = $(this).val();
        var productDropdown = $('#Pur_ProdCode'); 

        if (!warehouseId) {
            productDropdown.empty().append('<option value="">Pilih Gudang Pengirim dulu</option>');
            availableProducts = []; 
            return;
        }

        productDropdown.empty().append('<option value="">Memuat produk...</option>');
        
        $.ajax({
            url: '{{ url('mutasigudang/get-products-by-warehouse') }}/' + warehouseId,
            type: 'GET',
            dataType: 'json',
            success: function(products) {
                availableProducts = products;
                
                productDropdown.empty().append('<option value="">Pilih Produk</option>');
                if (products.length === 0) {
                     productDropdown.empty().append('<option value="">Tidak ada produk di gudang ini</option>');
                }

                $.each(products, function(key, product) {
                    productDropdown.append(
                        '<option value="' + product.kode_produk + '" ' + 
                                'data-name="' + product.nama_produk + '" ' +
                                'data-price="' + product.harga_jual + '">' + 
                            product.nama_produk + ' (' + product.kode_produk + ')' +
                        '</option>'
                    );
                });
            },
            error: function(xhr) {
                console.error("AJAX Error:", xhr.responseText);
                productDropdown.empty().append('<option value="">Gagal memuat produk (Error ' + xhr.status + ')</option>');
                availableProducts = []; 
            }
        });
    });

    $('#detailModal').on('keyup change', '.detail-calc', function() {
        calculateNetPrice();
    });

    $('#detailModal').on('hidden.bs.modal', function () {
        $('#detailForm')[0].reset();
        $('#Pur_NettPrice').val('');
        $('#Pur_ProdCode').empty().append('<option value="">Pilih Gudang Pengirim dulu</option>');
    });

    $('button[data-bs-target="#detailModal"]').on('click', function() {
        var productDropdown = $('#Pur_ProdCode');
        if (availableProducts.length === 0) {
            if (!$('#from_warehouse_id').val()) {
                productDropdown.empty().append('<option value="">Pilih Gudang Pengirim dulu</option>');
            } else {
                 productDropdown.empty().append('<option value="">Tidak ada produk di gudang ini</option>');
            }
        } else {
            productDropdown.empty().append('<option value="">Pilih Produk</option>');
            $.each(availableProducts, function(key, product) {
                productDropdown.append(
                    '<option value="' + product.kode_produk + '" ' + 
                            'data-name="' + product.nama_produk + '" ' +
                            'data-price="' + product.harga_jual + '">' +
                        product.nama_produk + ' (' + product.kode_produk + ')' +
                    '</option>'
                );
            });
        }
    });

    $('#detailModal').on('change', '#Pur_ProdCode', function() {
        var selectedOption = $(this).find('option:selected');
        
        var price = selectedOption.data('price') || 0;
        var name = selectedOption.data('name') || '';

        $('#pur_prodname').val(name);
        $('#Pur_GrossPrice').val(price).trigger('change'); 
    });

    $('#headerForm').on('change', 'input, textarea, select', function() {
        const data = $('#headerForm').serialize();
        $.ajax({
            url: `${baseUrl}/${orderId}/update-header`,
            type: 'POST',
            data: data + '&_method=PUT',
            success: function(response) {
                if(response.success) {
                    showToast('success', response.message || 'Perubahan berhasil disimpan.');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal memperbarui data.';
                showToast('error', errorMsg);
            }
        });
    });

    $('#btnSaveDetail').on('click', function() {
        const data = $('#detailForm').serialize();
        $.ajax({
            url: '{{ route("gudangorder.storeDetail") }}',
            method: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    $('#detailModal').modal('hide');
                    $('#detailForm')[0].reset();
                    Swal.fire('Berhasil!', 'Barang berhasil ditambahkan.', 'success')
                    .then(() => {
                        location.reload(); 
                    });
                } else {
                    Swal.fire('Gagal!', response.message || 'Terjadi kesalahan.', 'error');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Terjadi kesalahan pada server.';
                if (xhr.status === 422) { 
                    const errors = xhr.responseJSON.errors;
                    errorMsg = Object.values(errors).map(e => `<li>${e[0]}</li>`).join('');
                    Swal.fire('Error Validasi!', `<ul>${errorMsg}</ul>`, 'error');
                } else {
                     Swal.fire('Error!', (xhr.responseJSON && xhr.responseJSON.message) || errorMsg, 'error');
                }
            }
        });
    });

    $('#detailTable').on('click', '.delete-detail-btn', function() {
        const detailId = $(this).data('id');
        Swal.fire({
            title: 'Hapus Barang Ini?',
            text: "Aksi ini tidak dapat dibatalkan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${baseUrl}/${orderId}/details/${detailId}`,
                    type: 'POST', 
                    data: { _method: 'DELETE' },
                    success: function() {
                        location.reload();
                    },
                    error: function(xhr) {
                        showToast('error', 'Gagal menghapus barang.');
                    }
                });
            }
        });
    });

    $('#btnSubmitOrder').click(function() {
        const hasItems = $('#detailTable tbody tr').length > 0 && !$('#detailTable tbody td[colspan]').length;
        if (!hasItems) {
            Swal.fire('Peringatan!', 'Anda harus menambahkan setidaknya satu barang sebelum menyimpan.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Simpan & Ajukan Permintaan?',
            text: "Setelah diajukan, permintaan tidak bisa diubah lagi.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Ya, Ajukan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${baseUrl}/${orderId}/submit`,
                    type: 'POST',
                    data: { '_method': 'PUT' },
                    success: function(response) {
                        Swal.fire('Berhasil!', response.message || 'Permintaan berhasil diajukan.', 'success')
                        .then(() => {
                            window.location.href = '{{ route("gudangorder.index") }}';
                        });
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Gagal mengajukan permintaan.';
                        showToast('error', errorMsg);
                    }
                });
            }
        });
    });

    $('#btnCancelDraft').click(function() {
        Swal.fire({
            title: 'Batalkan & Hapus Draft Ini?',
            text: "Seluruh data akan dihapus permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Tidak, Kembali'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${baseUrl}/${orderId}`,
                    type: 'POST',
                    data: { '_method': 'DELETE' },
                    success: function(response) {
                        Swal.fire('Dihapus!', 'Draft permintaan telah berhasil dihapus.', 'success')
                        .then(() => {
                           window.location.href = '{{ route("gudangorder.index") }}';
                        });
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Gagal membatalkan draft.';
                        showToast('error', errorMsg);
                    }
                });
            }
        });
    });
    
    @else 
    
    $('#dataTable').on('click', '.delete-order-btn', function() {
        const orderId = $(this).data('id');
        const orderName = $(this).data('name');
        const baseUrl = `{{ url('/mutasigudang/gudangorder') }}`;
        const finalUrl = `${baseUrl}/${orderId}`;

        Swal.fire({
            title: 'Hapus Permintaan Ini?',
            text: `Anda akan menghapus draft ${orderName}. Aksi ini tidak dapat dibatalkan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: finalUrl,
                    type: 'POST', 
                    data: { _method: 'DELETE' },
                    success: function(response) {
                        Swal.fire('Berhasil Dihapus!', response.message, 'success')
                        .then(() => {
                            location.reload(); 
                        });
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Gagal menghapus draft.';
                        showToast('error', errorMsg);
                    }
                });
            }
        });
    });
    
    @endif

});
</script>
@endpush