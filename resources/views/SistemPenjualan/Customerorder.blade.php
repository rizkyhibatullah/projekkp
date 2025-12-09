@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Daftar Pesanan Pelanggan</h1>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <div class="mb-3">
        @php
        $currentRouteName = Route::currentRouteName();
        $currentMenuSlug = Str::beforeLast($currentRouteName, '.');
        @endphp
        @can('tambah', $currentMenuSlug)
        <button type="button" class="btn btn-primary" id="btnAddCustomerOrder">
            <i class="fas fa-plus fa-sm"></i> Tambah Pesanan Baru
        </button>
        @endcan
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center">No</th>
                            <th>Pelanggan</th>
                            <th class="text-center">No# Order</th>
                            <th class="text-center">PO Pelanggan</th>
                            <th class="text-center">Tgl. Kirim</th>
                            <th class="text-right">Netto</th>
                            <th class="text-center">Tgl. Pesan</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customerOrders as $index => $order)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $order->pelanggan->anggota ?? 'N/A' }}</td>
                                <td class="text-center">{{ $order->no_order }}</td>
                                <td>{{ $order->po_pelanggan ?? '-' }}</td>
                                <td class="text-center">{{ $order->tgl_kirim ? \Carbon\Carbon::parse($order->tgl_kirim)->format('d/m/Y') : '-' }}</td>
                                <td class="text-right">Rp{{ number_format($order->netto, 0, ',', '.') }}</td>
                                <td class="text-center">{{ \Carbon\Carbon::parse($order->tanggal_pesan)->format('d/m/Y') }}</td>
                                <td class="text-center">
                                     <span class="badge badge-pill badge-{{ strtolower($order->status) == 'selesai' ? 'success' : (strtolower($order->status) == 'batal' ? 'danger' : 'warning') }}">
                                        {{ $order->status ?? 'Draft' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                @can('ubah', $currentMenuSlug)
                                    <button class="btn btn-sm btn-warning edit-btn" title="Edit Pesanan"
                                        data-id="{{ $order->id }}"
                                        data-pelanggan_id="{{ $order->pelanggan_id }}"
                                        data-no_order="{{ $order->no_order }}"
                                        data-po_pelanggan="{{ $order->po_pelanggan }}"
                                        data-tgl_kirim="{{ $order->tgl_kirim ? \Carbon\Carbon::parse($order->tgl_kirim)->format('Y-m-d') : '' }}"
                                        data-bruto="{{ $order->bruto }}"
                                        data-disc="{{ $order->disc }}"
                                        data-pajak="{{ $order->pajak }}"
                                        data-netto="{{ $order->netto }}"
                                        data-tanggal_pesan="{{ \Carbon\Carbon::parse($order->tanggal_pesan)->format('Y-m-d') }}"
                                        data-status="{{ $order->status }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endcan
                                    @can('hapus', $currentMenuSlug)
                                    <button class="btn btn-sm btn-danger delete-btn" title="Hapus Pesanan"
                                        data-id="{{ $order->id }}"
                                        data-no_order="{{ $order->no_order }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Tidak ada data pesanan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Universal Modal for Add/Edit -->
<div class="modal fade" id="universalModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form id="mainForm" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="_method" value="POST">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Pesanan Pelanggan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Pelanggan <span class="text-danger">*</span></label>
                            <select id="pelanggan_id" name="pelanggan_id" class="form-control" required>
                                <option value="">Pilih Pelanggan</option>
                                @foreach($pelanggans as $pelanggan)
                                    <option value="{{ $pelanggan->id }}">{{ $pelanggan->anggota }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>No# Order</label>
                            <input type="text" id="no_order" name="no_order" class="form-control" value="AUTO" readonly>
                        </div>
                        <div class="form-group">
                            <label>PO Pelanggan</label>
                            <input type="text" id="po_pelanggan" name="po_pelanggan" class="form-control">
                        </div>
                         <div class="form-group">
                            <label>Tanggal Pesan <span class="text-danger">*</span></label>
                            <input type="date" id="tanggal_pesan" name="tanggal_pesan" class="form-control" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tanggal Kirim</label>
                            <input type="date" id="tgl_kirim" name="tgl_kirim" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Bruto <span class="text-danger">*</span></label>
                            <input type="number" id="bruto" name="bruto" class="form-control" required step="any" readonly>
                        </div>
                        <div class="form-group">
                            <label>Disc (%)</label>
                            <input type="number" id="disc" name="disc" class="form-control" min="0" max="100" step="any" readonly>
                        </div>
                        <div class="form-group">
                            <label>Pajak</label>
                            <input type="number" id="pajak" name="pajak" class="form-control" step="any" readonly>
                        </div>
                        <div class="form-group">
                            <label>Netto <span class="text-danger">*</span></label>
                            <input type="number" id="netto" name="netto" class="form-control" required readonly step="any">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="Draft">Draft</option>
                                <option value="Dikirim">Dikirim</option>
                                <option value="Selesai">Selesai</option>
                                <option value="Batal">Batal</option>
                            </select>
                        </div>
                    </div>
                </div>

                <hr>
                <h5 class="mb-3">Detail Item</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="itemsTable">
                        <thead class="thead-light">
                            <tr>
                                <th>Produk</th>
                                <th width="10%">Qty</th>
                                <th width="15%">Harga</th>
                                <th width="10%">Disc(%)</th>
                                <th width="10%">Pajak</th>
                                <th width="15%">Subtotal</th>
                                <th width="5%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="items[0][product_id]" class="form-control product-select" required>
                                        <option value="">Pilih Produk</option>
                                        @foreach($dataproduks ?? [] as $dataproduk)
                                        <option value="{{ $dataproduk->id }}" data-harga="{{ $dataproduk->harga_jual }}">{{ $dataproduk->nama_produk }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="items[0][qty]" class="form-control item-qty" min="0.01" step="0.01" required></td>
                                <td><input type="number" name="items[0][harga]" class="form-control item-harga" min="0" step="0.01" required></td>
                                <td><input type="number" name="items[0][disc]" class="form-control item-disc" min="0" max="100" step="0.01" value="0"></td>
                                <td><input type="number" name="items[0][pajak]" class="form-control item-pajak" min="0" step="0.01" value="0"></td>
                                <td><input type="number" name="items[0][subtotal]" class="form-control item-subtotal" min="0" step="0.01" readonly></td>
                                <td><button type="button" class="btn btn-sm btn-danger remove-item"><i class="fas fa-trash"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-right mb-3">
                    <button type="button" id="addItem" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Tambah Item</button>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="modalSubmit">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
 $(function() {
    // --- Configuration ---
    // Standardized route names. Ensure these are defined in your routes/web.php.
    const storeUrl = "{{ route('customer-orders.store') }}";
    const updateUrlTpl = "{{ route('customer-orders.update', ':id') }}";
    const deleteUrlTpl = "{{ route('customer-orders.destroy', ':id') }}";
    const csrfToken = "{{ csrf_token() }}";

    const modal = $('#universalModal');
    const form = $('#mainForm');

    // Item management
    let itemCount = 1;

    // --- Initialize DataTable ---
    $('#dataTable').DataTable({
        "language": { "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Indonesian.json" }
    });

    // --- Helper Functions ---
    function calculateNetto() {
        calculateTotals();
    }

    // Calculate subtotal for a single item
    function calculateItemSubtotal(row) {
        const qty = parseFloat(row.find('.item-qty').val()) || 0;
        const harga = parseFloat(row.find('.item-harga').val()) || 0;
        const discPercent = parseFloat(row.find('.item-disc').val()) || 0;
        const pajak = parseFloat(row.find('.item-pajak').val()) || 0;

        const total = qty * harga;
        const discAmount = total * (discPercent / 100);
        const subtotal = total - discAmount + pajak;

        row.find('.item-subtotal').val(subtotal.toFixed(2));
    }

    // Calculate totals (bruto, total disc, total pajak, netto)
    function calculateTotals() {
        let bruto = 0;
        let totalDisc = 0;
        let totalPajak = 0;

        $('#itemsTable tbody tr').each(function() {
            const qty = parseFloat($(this).find('.item-qty').val()) || 0;
            const harga = parseFloat($(this).find('.item-harga').val()) || 0;
            const discPercent = parseFloat($(this).find('.item-disc').val()) || 0;
            const pajak = parseFloat($(this).find('.item-pajak').val()) || 0;

            const total = qty * harga;
            const discAmount = total * (discPercent / 100);

            bruto += total;
            totalDisc += discAmount;
            totalPajak += pajak;
        });

        const netto = bruto - totalDisc + totalPajak;

        $('#bruto').val(bruto.toFixed(2));
        $('#disc').val(bruto > 0 ? ((totalDisc / bruto) * 100).toFixed(2) : 0);
        $('#pajak').val(totalPajak.toFixed(2));
        $('#netto').val(netto.toFixed(2));
    }

    // --- Event Handlers ---
    // Add new item row
    $('#addItem').click(function() {
        let newRow = `
            <tr>
                <td>
                    <select name="items[${itemCount}][product_id]" class="form-control product-select" required>
                        <option value="">Pilih Produk</option>
                        @foreach($dataproduks ?? [] as $dataproduk)
                        <option value="{{ $dataproduk->id }}" data-harga="{{ $dataproduk->harga_jual }}">{{ $dataproduk->nama_produk }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" name="items[${itemCount}][qty]" class="form-control item-qty" min="0.01" step="0.01" required></td>
                <td><input type="number" name="items[${itemCount}][harga]" class="form-control item-harga" min="0" step="0.01" required></td>
                <td><input type="number" name="items[${itemCount}][disc]" class="form-control item-disc" min="0" max="100" step="0.01" value="0"></td>
                <td><input type="number" name="items[${itemCount}][pajak]" class="form-control item-pajak" min="0" step="0.01" value="0"></td>
                <td><input type="number" name="items[${itemCount}][subtotal]" class="form-control item-subtotal" min="0" step="0.01" readonly></td>
                <td><button type="button" class="btn btn-sm btn-danger remove-item"><i class="fas fa-trash"></i></button></td>
            </tr>
        `;
        $('#itemsTable tbody').append(newRow);
        itemCount++;
    });

    // Remove item row
    $(document).on('click', '.remove-item', function() {
        if ($('#itemsTable tbody tr').length > 1) {
            $(this).closest('tr').remove();
            calculateTotals();
        } else {
            Swal.fire('Peringatan', 'Minimal harus ada satu item', 'warning');
        }
    });

    // Auto-fill harga when product is selected
    $(document).on('change', '.product-select', function() {
        const selectedOption = $(this).find('option:selected');
        const harga = selectedOption.data('harga') || 0;
        $(this).closest('tr').find('.item-harga').val(harga);
        calculateItemSubtotal($(this).closest('tr'));
        calculateTotals();
    });

    // Calculate item subtotal when qty, harga, disc, or pajak changes
    $(document).on('input', '.item-qty, .item-harga, .item-disc, .item-pajak', function() {
        calculateItemSubtotal($(this).closest('tr'));
        calculateTotals();
    });

    $('#btnAddCustomerOrder').click(function() {
        form.trigger('reset');
        $('#modalTitle').text('Tambah Pesanan Pelanggan Baru');
        $('#modalSubmit').text('Simpan');
        form.attr('action', storeUrl);
        $('input[name="_method"]').val('POST');
        $('#no_order').val('AUTO');
        $('#tanggal_pesan').val(new Date().toISOString().split('T')[0]);

        // Reset items table to single empty row
        $('#itemsTable tbody').html(`
            <tr>
                <td>
                    <select name="items[0][product_id]" class="form-control product-select" required>
                        <option value="">Pilih Produk</option>
                        @foreach($dataproduks ?? [] as $dataproduk)
                        <option value="{{ $dataproduk->id }}" data-harga="{{ $dataproduk->harga_jual }}">{{ $dataproduk->nama_produk }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" name="items[0][qty]" class="form-control item-qty" min="0.01" step="0.01" required></td>
                <td><input type="number" name="items[0][harga]" class="form-control item-harga" min="0" step="0.01" required></td>
                <td><input type="number" name="items[0][disc]" class="form-control item-disc" min="0" max="100" step="0.01" value="0"></td>
                <td><input type="number" name="items[0][pajak]" class="form-control item-pajak" min="0" step="0.01" value="0"></td>
                <td><input type="number" name="items[0][subtotal]" class="form-control item-subtotal" min="0" step="0.01" readonly></td>
                <td><button type="button" class="btn btn-sm btn-danger remove-item"><i class="fas fa-trash"></i></button></td>
            </tr>
        `);
        itemCount = 1;

        calculateNetto();
        modal.modal('show');
    });

    $('#dataTable').on('click', '.edit-btn', function() {
        let btn = $(this);
        let id = btn.data('id');

        form.trigger('reset');
        $('#modalTitle').text('Edit Pesanan Pelanggan');
        $('#modalSubmit').text('Simpan Perubahan');
        let actionUrl = updateUrlTpl.replace(':id', id);
        form.attr('action', actionUrl);
        $('input[name="_method"]').val('PUT');

        // Populate form fields
        $('#pelanggan_id').val(btn.data('pelanggan_id'));
        $('#no_order').val(btn.data('no_order'));
        $('#po_pelanggan').val(btn.data('po_pelanggan'));
        $('#tgl_kirim').val(btn.data('tgl_kirim'));
        $('#bruto').val(btn.data('bruto'));
        $('#disc').val(btn.data('disc'));
        $('#pajak').val(btn.data('pajak'));
        $('#tanggal_pesan').val(btn.data('tanggal_pesan'));
        $('#status').val(btn.data('status'));

        // Load order details for editing
        $.ajax({
            url: `/api/customer-orders/${id}/details`,
            method: 'GET',
            success: function(response) {
                if (response.details && response.details.length > 0) {
                    let itemsHtml = '';
                    response.details.forEach((item, index) => {
                        itemsHtml += `
                            <tr>
                                <td>
                                    <select name="items[${index}][product_id]" class="form-control product-select" required>
                                        <option value="">Pilih Produk</option>
                                        @foreach($dataproduks ?? [] as $dataproduk)
                                        <option value="{{ $dataproduk->id }}" data-harga="{{ $dataproduk->harga_jual }}" ${item.product_id == {{ $dataproduk->id }} ? 'selected' : ''}>{{ $dataproduk->nama_produk }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="items[${index}][qty]" class="form-control item-qty" min="0.01" step="0.01" required value="${item.qty}"></td>
                                <td><input type="number" name="items[${index}][harga]" class="form-control item-harga" min="0" step="0.01" required value="${item.harga}"></td>
                                <td><input type="number" name="items[${index}][disc]" class="form-control item-disc" min="0" max="100" step="0.01" value="${item.disc || 0}"></td>
                                <td><input type="number" name="items[${index}][pajak]" class="form-control item-pajak" min="0" step="0.01" value="${item.pajak || 0}"></td>
                                <td><input type="number" name="items[${index}][subtotal]" class="form-control item-subtotal" min="0" step="0.01" readonly value="${item.subtotal || 0}"></td>
                                <td><button type="button" class="btn btn-sm btn-danger remove-item"><i class="fas fa-trash"></i></button></td>
                            </tr>
                        `;
                    });
                    $('#itemsTable tbody').html(itemsHtml);
                    itemCount = response.details.length;
                } else {
                    // Reset to single empty row if no details
                    $('#itemsTable tbody').html(`
                        <tr>
                            <td>
                                <select name="items[0][product_id]" class="form-control product-select" required>
                                    <option value="">Pilih Produk</option>
                                    @foreach($dataproduks ?? [] as $dataproduk)
                                    <option value="{{ $dataproduk->id }}" data-harga="{{ $dataproduk->harga_jual }}">{{ $dataproduk->nama_produk }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" name="items[0][qty]" class="form-control item-qty" min="0.01" step="0.01" required></td>
                            <td><input type="number" name="items[0][harga]" class="form-control item-harga" min="0" step="0.01" required></td>
                            <td><input type="number" name="items[0][disc]" class="form-control item-disc" min="0" max="100" step="0.01" value="0"></td>
                            <td><input type="number" name="items[0][pajak]" class="form-control item-pajak" min="0" step="0.01" value="0"></td>
                            <td><input type="number" name="items[0][subtotal]" class="form-control item-subtotal" min="0" step="0.01" readonly></td>
                            <td><button type="button" class="btn btn-sm btn-danger remove-item"><i class="fas fa-trash"></i></button></td>
                        </tr>
                    `);
                    itemCount = 1;
                }

                calculateTotals();
            },
            error: function() {
                // Reset to single empty row if error
                $('#itemsTable tbody').html(`
                    <tr>
                        <td>
                            <select name="items[0][product_id]" class="form-control product-select" required>
                                <option value="">Pilih Produk</option>
                                @foreach($dataproduks ?? [] as $dataproduk)
                                <option value="{{ $dataproduk->id }}" data-harga="{{ $dataproduk->harga_jual }}">{{ $dataproduk->nama_produk }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" name="items[0][qty]" class="form-control item-qty" min="0.01" step="0.01" required></td>
                        <td><input type="number" name="items[0][harga]" class="form-control item-harga" min="0" step="0.01" required></td>
                        <td><input type="number" name="items[0][disc]" class="form-control item-disc" min="0" max="100" step="0.01" value="0"></td>
                        <td><input type="number" name="items[0][pajak]" class="form-control item-pajak" min="0" step="0.01" value="0"></td>
                        <td><input type="number" name="items[0][subtotal]" class="form-control item-subtotal" min="0" step="0.01" readonly></td>
                        <td><button type="button" class="btn btn-sm btn-danger remove-item"><i class="fas fa-trash"></i></button></td>
                    </tr>
                `);
                itemCount = 1;
            }
        });

        modal.modal('show');
    });

    form.on('submit', function(e) {
        e.preventDefault();

        // Check if there's at least one item
        let hasItems = false;
        $('#itemsTable tbody tr').each(function() {
            const productId = $(this).find('.product-select').val();
            if (productId) {
                hasItems = true;
                return false; // Exit the loop
            }
        });

        if (!hasItems) {
            Swal.fire('Error', 'Minimal harus ada satu item', 'error');
            return;
        }

        const submitBtn = $('#modalSubmit');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                modal.modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan pada server.';
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('<br>');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire({ icon: 'error', title: 'Error', html: errorMessage });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });

    $('#dataTable').on('click', '.delete-btn', function() {
        let id = $(this).data('id');
        let noOrder = $(this).data('no_order');

        Swal.fire({
            title: 'Konfirmasi Hapus',
            html: `Apakah Anda yakin ingin menghapus pesanan <strong>${noOrder}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrlTpl.replace(':id', id),
                    method: 'POST',
                    data: {
                        '_method': 'DELETE',
                        '_token': csrfToken
                    },
                    success: function(response) {
                        Swal.fire('Terhapus!', response.message, 'success')
                            .then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menghapus pesanan.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
