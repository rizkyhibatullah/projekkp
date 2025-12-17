@extends('layouts.admin')

@section('main-content')
    <div class="container-fluid">
        <h1 class="h3 mb-2 text-gray-800">Manajemen Penjualan</h1>
        <p class="mb-4">Daftar transaksi penjualan dan pembuatan transaksi baru.</p>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
        @endif

        <div class="mb-3">
            @php
                $currentRouteName = Route::currentRouteName();
                $currentMenuSlug = Str::beforeLast($currentRouteName, '.');
            @endphp
            @can('tambah', $currentMenuSlug)
                <button type="button" class="btn btn-primary" id="btnTambahJualan">
                    <i class="fas fa-plus fa-sm"></i> Buat Penjualan Baru
                </button>
            @endcan
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Data Transaksi Penjualan</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">No. Jualan</th>
                                <th>Pelanggan</th>
                                <th class="text-center">No. CO</th>
                                <th class="text-center">Tgl. Kirim</th>
                                <th class="text-center">Jatuh Tempo</th>
                                <th class="text-right">Netto</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jualans as $index => $jualan)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="text-center">{{ $jualan->no_jualan }}</td>
                                    <td>{{ $jualan->pelanggan->anggota ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $jualan->customerOrder->no_order ?? 'N/A' }}</td>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($jualan->tgl_kirim)->format('d/m/Y') }}
                                    </td>
                                    <td class="text-center">
                                        {{ $jualan->jatuh_tempo ? \Carbon\Carbon::parse($jualan->jatuh_tempo)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="text-right">Rp{{ number_format($jualan->netto, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <span
                                            class="badge badge-{{ $jualan->status == 'Draft' ? 'secondary' : ($jualan->status == 'Approved' ? 'success' : 'danger') }}">
                                            {{ $jualan->status }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="#" class="btn btn-sm btn-info view-btn" title="Lihat Detail"
                                            data-id="{{ $jualan->id }}"><i class="fas fa-eye"></i></a>
                                        @if ($jualan->status == 'Draft')
                                            @can('approve', $currentMenuSlug)
                                                <button class="btn btn-sm btn-success approve-btn" title="Approve Penjualan"
                                                    data-id="{{ $jualan->id }}" data-no_jualan="{{ $jualan->no_jualan }}">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endcan
                                            @can('hapus', $currentMenuSlug)
                                                <!-- TAMBAHKAN PERMISI CHECK JIKA DIPERLUKAN -->
                                                <button class="btn btn-sm btn-danger delete-btn" title="Hapus Penjualan"
                                                    data-id="{{ $jualan->id }}" data-no_jualan="{{ $jualan->no_jualan }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        @endif
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Belum ada data jualan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for creating a new sale -->
    <div class="modal fade" id="jualanModal" tabindex="-1" role="dialog" aria-labelledby="jualanModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="jualanModalLabel">Buat Jualan Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formJualan" action="{{ route('penjualan.store') }}" method="POST">
                        @csrf
                        <!-- Tambahkan field tersembunyi untuk pengguna -->
                        <input type="hidden" id="pengguna" name="pengguna" value="{{ Auth::user()->name ?? 'System' }}">

                        {{-- Form Header --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group row"><label class="col-sm-4 col-form-label-sm">NO# Jualan</label>
                                    <div class="col-sm-8"><input type="text" class="form-control form-control-sm"
                                            value="AUTO" readonly></div>
                                </div>
                                <div class="form-group row"><label class="col-sm-4 col-form-label-sm">Pelanggan <span
                                            class="text-danger">*</span></label>
                                    <div class="col-sm-8"><select class="form-control form-control-sm" id="pelanggan_id"
                                            name="pelanggan_id" required>
                                            <option value="" data-lama_bayar="0">--- Pilih Pelanggan ---</option>
                                            @foreach ($pelanggans as $p)
                                                <option value="{{ $p->id }}"
                                                    data-lama_bayar="{{ $p->lama_bayar ?? 0 }}">{{ $p->anggota }}
                                                </option>
                                            @endforeach
                                        </select></div>
                                </div>
                                <div class="form-group row"><label class="col-sm-4 col-form-label-sm">No# CO <span
                                            class="text-danger">*</span></label>
                                    <div class="col-sm-8"><select class="form-control form-control-sm"
                                            id="customer_order_id" name="customer_order_id" required disabled>
                                            <option>--- Pilih Pelanggan ---</option>
                                        </select></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row"><label class="col-sm-4 col-form-label-sm">Tgl. Kirim <span
                                            class="text-danger">*</span></label>
                                    <div class="col-sm-8"><input type="date" class="form-control form-control-sm"
                                            id="tgl_kirim" name="tgl_kirim" value="{{ date('Y-m-d') }}" required></div>
                                </div>
                                <div class="form-group row"><label class="col-sm-4 col-form-label-sm">Jatuh Tempo</label>
                                    <div class="col-sm-8"><input type="date" class="form-control form-control-sm"
                                            id="jatuh_tempo" name="jatuh_tempo" readonly></div>
                                </div>
                                <div class="form-group row"><label class="col-sm-4 col-form-label-sm">PO Pelanggan</label>
                                    <div class="col-sm-8"><input type="text" class="form-control form-control-sm"
                                            id="po_pelanggan" name="po_pelanggan" readonly></div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        {{-- Item Details Table --}}
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="jualanDetailTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th width="10%">Qty</th>
                                        <th>Satuan</th>
                                        <th>Harga</th>
                                        <th width="8%">Disc(%)</th>
                                        <!-- REVISI: Ubah header Pajak menjadi persentase -->
                                        <th>Pajak (%)</th>
                                        <th>Nominal</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Pilih Customer Order untuk
                                            menampilkan data.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <hr>
                        {{-- Totals --}}
                        <div class="row justify-content-end">
                            <div class="col-md-5">
                                <div class="form-group row align-items-center"><label
                                        class="col-sm-4 col-form-label">Bruto</label>
                                    <div class="col-sm-8"><input type="text" class="form-control text-right"
                                            id="bruto" readonly></div>
                                </div>
                                <div class="form-group row align-items-center"><label
                                        class="col-sm-4 col-form-label">Total
                                        Disc.</label>
                                    <div class="col-sm-8"><input type="text" class="form-control text-right"
                                            id="total_disc" readonly></div>
                                </div>
                                <div class="form-group row align-items-center"><label
                                        class="col-sm-4 col-form-label">Total
                                        Pajak</label>
                                    <div class="col-sm-8"><input type="text" class="form-control text-right"
                                            id="total_pajak" readonly></div>
                                </div>
                                <div class="form-group row font-weight-bold align-items-center"><label
                                        class="col-sm-4 col-form-label">Netto</label>
                                    <div class="col-sm-8"><input type="text" class="form-control text-right"
                                            id="netto" readonly></div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSimpanJualan"><i class="fas fa-save mr-1"></i>
                        Simpan Jualan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for viewing sale details -->
    <div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="viewModalLabel">Detail Penjualan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="saleDetails">
                        <!-- Details will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // --- Config ---
            const modal = $('#jualanModal');
            const form = $('#formJualan');
            const viewModal = $('#viewModal');

            // Correctly define API routes using Laravel's route() helper
            const outstandingOrdersUrlTpl = "{{ route('api.jualan.outstanding-orders', ':pelanggan') }}";
            const orderDetailsUrlTpl = "{{ route('api.jualan.order-details', ':customerOrder') }}";
            const approveUrlTpl = "{{ route('penjualan.approve', ':id') }}";
            const viewUrlTpl = "{{ route('penjualan.show', ':id') }}";
            const deleteUrlTpl = "{{ route('penjualan.destroy', ':id') }}";
            const csrfToken = "{{ csrf_token() }}";

            // --- Initialize DataTable ---
            $('#dataTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Indonesian.json"
                }
            });

            // --- Helper Functions ---
            function formatCurrency(num) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(num);
            }

            function calculateDueDate() {
                const tglKirim = $('#tgl_kirim').val();
                const lamaBayar = parseInt($('#pelanggan_id').find('option:selected').data('lama_bayar')) || 0;
                if (tglKirim) {
                    let dueDate = new Date(tglKirim);
                    dueDate.setDate(dueDate.getDate() + lamaBayar);
                    $('#jatuh_tempo').val(dueDate.toISOString().split('T')[0]);
                } else {
                    $('#jatuh_tempo').val('');
                }
            }

            // *** REVISI: Fungsi updateTotals untuk menghitung pajak sebagai persentase ***
            function updateTotals() {
                let bruto = 0,
                    totalDisc = 0,
                    totalPajakAmount = 0; // Ubah variabel untuk menyimpan jumlah pajak nominal

                $('#jualanDetailTable tbody tr').each(function() {
                    if ($(this).find('td').length <= 1) return;
                    const row = $(this);
                    const qty = parseFloat(row.find('.input-qty').val()) || 0;
                    const harga = parseFloat(row.find('.input-harga').val()) || 0;
                    const discPercent = parseFloat(row.find('.input-disc').val()) || 0;
                    const pajakPercent = parseFloat(row.find('.input-pajak').val()) || 0; // Ubah variabel

                    const totalHarga = qty * harga;
                    const discAmount = totalHarga * (discPercent / 100);
                    const subtotalAfterDiscount = totalHarga - discAmount;

                    // Hitung pajak berdasarkan persentase dari subtotal setelah diskon
                    const pajakAmount = subtotalAfterDiscount * (pajakPercent / 100);

                    const nominal = subtotalAfterDiscount + pajakAmount;

                    row.find('.input-nominal').val(nominal.toFixed(2));
                    bruto += totalHarga;
                    totalDisc += discAmount;
                    totalPajakAmount += pajakAmount; // Jumlahkan nominal pajak
                });
                const netto = bruto - totalDisc + totalPajakAmount;
                $('#bruto').val(formatCurrency(bruto));
                $('#total_disc').val(formatCurrency(totalDisc));
                $('#total_pajak').val(formatCurrency(totalPajakAmount)); // Tampilkan total nominal pajak
                $('#netto').val(formatCurrency(netto));
            }

            // --- Event Handlers ---
            $('#btnTambahJualan').on('click', function() {
                form.trigger('reset');
                $('#jualanModalLabel').text('Buat Jualan Baru');
                $('#customer_order_id').html('<option>--- Pilih Pelanggan ---</option>').prop('disabled',
                    true);
                $('#jualanDetailTable tbody').html(
                    '<tr><td colspan="8" class="text-center text-muted">Pilih Customer Order.</td></tr>'
                );
                $('#tgl_kirim').val(new Date().toISOString().split('T')[0]);

                // Set nilai pengguna saat modal dibuka
                $('#pengguna').val('{{ Auth::user()->name ?? 'System' }}');

                calculateDueDate();
                updateTotals();
                modal.modal('show');
            });

            $('#btnSimpanJualan').on('click', () => form.submit());

            form.on('change', '#pelanggan_id, #tgl_kirim', calculateDueDate);

            form.on('input', '.input-qty, .input-disc, .input-pajak', updateTotals);

            form.on('change', '#pelanggan_id', function() {
                const customerId = $(this).val();
                const coSelect = $('#customer_order_id');
                $('#jualanDetailTable tbody').html(
                    '<tr><td colspan="8" class="text-center text-muted">Pilih Customer Order.</td></tr>'
                );
                updateTotals();

                if (!customerId) {
                    coSelect.html('<option>--- Pilih Pelanggan ---</option>').prop('disabled', true);
                    return;
                }

                coSelect.html('<option>Memuat...</option>').prop('disabled', true);
                $.get(outstandingOrdersUrlTpl.replace(':pelanggan', customerId), function(orders) {
                    coSelect.html('<option value="">--- Pilih CO ---</option>');
                    if (orders.length > 0) {
                        orders.forEach(o => coSelect.append(
                            // PERBAIKAN: Tambahkan data-disc dan data-pajak
                            `<option value="${o.id}" data-po="${o.po_pelanggan || ''}" data-disc="${o.disc || 0}" data-pajak="${o.pajak || 0}">${o.no_order}</option>`
                        ));
                        coSelect.prop('disabled', false);
                    } else {
                        coSelect.html('<option value="">--- Tidak ada CO ---</option>');
                    }
                }).fail(() => coSelect.html('<option value="">--- Error ---</option>'));
            });

            form.on('change', '#customer_order_id', function() {
                const coId = $(this).val();
                const selectedOption = $(this).find('option:selected');
                const globalDisc = parseFloat(selectedOption.data('disc')) || 0;
                const globalPajak = parseFloat(selectedOption.data('pajak')) || 0;
                const po = $(this).find('option:selected').data('po');
                const tableBody = $('#jualanDetailTable tbody');
                $('#po_pelanggan').val(po);

                if (!coId) {
                    tableBody.html(
                        '<tr><td colspan="8" class="text-center text-muted">Pilih Customer Order.</td></tr>'
                    );
                    updateTotals();
                    return;
                }

                const url = orderDetailsUrlTpl.replace(':customerOrder', coId);
                console.log('Fetching order details from:', url);

                $.ajax({
                    url: url,
                    method: 'GET',
                    beforeSend: () => {
                        console.log('Sending request to:', url);
                        tableBody.html(
                            '<tr><td colspan="8" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat...</td></tr>'
                        );
                    },
                    success: function(response) {
                        console.log('Response received:', response);
                        tableBody.empty();
                        if (response.length > 0) {
                            response.forEach(item => {
                                const productName = item.product ? item.product
                                    .nama_produk : 'Produk tidak ditemukan';
                                const productSatuan = 'pcs';

                                // REVISI: Tambahkan atribut min dan max pada input pajak
                                tableBody.append(`
                        <tr>
                            <td><input type="text" value="${productName}" class="form-control form-control-sm" readonly></td>
                            <td><input type="number" name="items[${item.id}][qty]" value="${item.qty}" class="form-control form-control-sm text-right input-qty" step="any"></td>
                            <td><input type="text" value="${productSatuan}" class="form-control form-control-sm" readonly></td>
                            <td><input type="number" value="${item.harga}" class="form-control form-control-sm text-right input-harga" readonly step="any"></td>
                            <td><input type="number" name="items[${item.id}][disc]" value="${item.disc || 0}" class="form-control form-control-sm text-right input-disc" step="any" min="0" max="100"></td>
                            <td><input type="number" name="items[${item.id}][pajak]" value="${item.pajak || 0}" class="form-control form-control-sm text-right input-pajak" step="any" min="0" max="100"></td>
                            <td><input type="number" name="items[${item.id}][nominal]" class="form-control form-control-sm text-right input-nominal" readonly step="any"></td>
                            <td><input type="text" name="items[${item.id}][catatan]" value="${item.catatan || ''}" class="form-control form-control-sm"></td>
                        </tr>
                    `);
                            });
                            updateTotals();
                        } else {
                            tableBody.html(
                                '<tr><td colspan="8" class="text-center text-muted">Tidak ada item detail di CO ini.</td></tr>'
                            );
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading order details:', xhr);
                        console.error('Status:', xhr.status);
                        console.error('Response Text:', xhr.responseText);
                        tableBody.html(
                            '<tr><td colspan="8" class="text-center text-danger">Gagal memuat data. Status: ' +
                            xhr.status + '</td></tr>'
                        );
                    }
                });
            });

            form.on('submit', function(e) {
                e.preventDefault();
                const submitBtn = $('#btnSimpanJualan');
                const originalHtml = submitBtn.html();

                if (!$('#pelanggan_id').val() || !$('#customer_order_id').val()) {
                    Swal.fire('Error!', 'Pelanggan dan Customer Order wajib diisi.', 'error');
                    return;
                }

                if ($('#pengguna').length === 0) {
                    form.append(
                        '<input type="hidden" name="pengguna" value="{{ Auth::user()->name ?? 'System' }}">'
                    );
                }

                const formData = form.serialize();
                console.log('Form data being sent:', formData);
                console.log('Pengguna field value:', $('#pengguna').val());

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    beforeSend: () => submitBtn.prop('disabled', true).html(
                        '<i class="fas fa-spinner fa-spin"></i> Menyimpan...'),
                    success: function(response) {
                        modal.modal('hide');
                        Swal.fire('Berhasil!', response.message, 'success').then(() => location
                            .reload());
                    },
                    error: function(xhr) {
                        let errorMsg = 'Terjadi kesalahan.';
                        if (xhr.responseJSON) {
                            errorMsg = xhr.responseJSON.message || (xhr.responseJSON.errors ?
                                Object
                                .values(xhr.responseJSON.errors).flat().join('<br>') :
                                errorMsg);
                        }
                        Swal.fire('Error!', errorMsg, 'error');
                    },
                    complete: () => submitBtn.prop('disabled', false).html(originalHtml)
                });
            });

            // Event handler untuk approve button
            $(document).on('click', '.approve-btn', function(e) {
                e.preventDefault();

                const btn = $(this);
                const id = btn.data('id');
                const noJualan = btn.data('no_jualan');

                console.log('Approve button clicked for ID:', id, 'No Jualan:', noJualan);

                Swal.fire({
                    title: 'Konfirmasi Approve',
                    html: `Apakah Anda yakin ingin approve penjualan <strong>${noJualan}</strong>?<br>Stok barang akan dikurangi setelah approve.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Approve!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#28a745'
                }).then((result) => {
                    if (result.isConfirmed) {
                        console.log('Sending approve request to:', approveUrlTpl.replace(':id',
                            id));

                        $.ajax({
                            url: approveUrlTpl.replace(':id', id),
                            method: 'POST',
                            data: {
                                '_token': csrfToken
                            },
                            beforeSend: function() {
                                btn.prop('disabled', true).html(
                                    '<i class="fas fa-spinner fa-spin"></i>');
                            },
                            success: function(response) {
                                console.log('Approve success:', response);
                                Swal.fire('Berhasil!', response.message, 'success')
                                    .then(() => location.reload());
                            },
                            error: function(xhr) {
                                console.error('Approve error:', xhr);
                                btn.prop('disabled', false).html(
                                    '<i class="fas fa-check"></i>');
                                Swal.fire('Error', xhr.responseJSON?.message ||
                                    'Gagal approve penjualan.', 'error');
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault();

                const btn = $(this);
                const id = btn.data('id');
                const noJualan = btn.data('no_jualan');

                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    html: `Apakah Anda yakin ingin menghapus penjualan <strong>${noJualan}</strong>?<br><small class="text-danger">Tindakan ini tidak dapat dibatalkan.</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d33' // Merah untuk aksi berbahaya
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            // Laravel membutuhkan method spoofing untuk DELETE
                            url: deleteUrlTpl.replace(':id', id),
                            method: 'POST',
                            data: {
                                '_method': 'DELETE',
                                '_token': csrfToken
                            },
                            beforeSend: function() {
                                // Nonaktifkan tombol dan tampilkan spinner
                                btn.prop('disabled', true).html(
                                    '<i class="fas fa-spinner fa-spin"></i>');
                            },
                            success: function(response) {
                                Swal.fire('Terhapus!', response.message, 'success')
                                    .then(() => location
                                        .reload()
                                        ); // Reload halaman untuk memperbarui tabel
                            },
                            error: function(xhr) {
                                // Aktifkan kembali tombol jika terjadi error
                                btn.prop('disabled', false).html(
                                    '<i class="fas fa-trash"></i>');
                                Swal.fire('Error', xhr.responseJSON?.message ||
                                    'Gagal menghapus penjualan.', 'error');
                            }
                        });
                    }
                });
            });

            // Event handler untuk view button
            $(document).on('click', '.view-btn', function(e) {
                e.preventDefault();

                const btn = $(this);
                const id = btn.data('id');

                console.log('View button clicked for ID:', id);

                $.ajax({
                    url: viewUrlTpl.replace(':id', id),
                    method: 'GET',
                    beforeSend: function() {
                        $('#saleDetails').html(
                            '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>'
                        );
                        viewModal.modal('show');
                    },
                    success: function(response) {
                        $('#saleDetails').html(response);
                    },
                    error: function(xhr) {
                        console.error('View error:', xhr);
                        $('#saleDetails').html(
                            '<div class="alert alert-danger">Gagal memuat detail penjualan.</div>'
                        );
                    }
                });
            });
        });
    </script>
@endpush
