@extends('layouts.admin')
@section('main-content')

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">{{ ('Penerimaan Gudang') }}</h1>

    @if (session('success'))
        <div class="alert alert-success border-left-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger border-left-danger" role="alert">
            <ul class="pl-4 my-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(isset($penerimaanList))
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Daftar Penerimaan') }}</h6>
            <a href="{{ route('terimagudang.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus fa-sm"></i> {{ __('Buat Penerimaan Baru') }}
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nomor Penerimaan</th>
                            <th>Tanggal</th>
                            <th>Gudang Pengirim</th>
                            <th>Gudang Penerima</th>
                            <th>No. Referensi Transfer</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($penerimaanList as $penerimaan)
                            <tr>
                                <td><strong>{{ $penerimaan->Rcv_number }}</strong></td>
                                <td>{{ \Carbon\Carbon::parse($penerimaan->Rcv_Date)->isoFormat('DD MMMM YYYY') }}</td>
                                <td>{{ $penerimaan->Rcv_From ?? '-' }}</td>
                                <td>{{ $penerimaan->Rcv_WareCode ?? '-' }}</td>
                                <td>{{ $penerimaan->transferHeader->trx_number ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $penerimaan->rcv_posting == 'F' ? 'warning' : 'success' }}">
                                        {{ $penerimaan->rcv_posting == 'F' ? 'DRAFT' : 'POSTED' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($penerimaan->rcv_posting == 'F')
                                        <a href="{{ route('terimagudang.edit', $penerimaan->id) }}" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                        <button class="btn btn-sm btn-danger delete-draft-btn" data-id="{{ $penerimaan->id }}" title="Hapus Draft"><i class="fas fa-trash"></i></button>
                                    @else
                                        <a href="{{ route('terimagudang.edit', $penerimaan->id) }}" class="btn btn-sm btn-info" title="Lihat"><i class="fas fa-eye"></i></a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <img src="{{ asset('img/svg/undraw_editable_dywm.svg') }}" alt="Tidak ada data" style="height: 150px; width: auto; opacity: 0.8;" class="mb-4">
                                        <h5 class="font-weight-bold text-gray-800 mb-2">Belum ada Data Penerimaan</h5>
                                        <p class="text-gray-500 mb-3">
                                            Belum ada barang yang diterima dari transfer gudang.<br>
                                            Silakan proses penerimaan baru.
                                        </p>
                                        <a href="{{ route('terimagudang.create') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus"></i> Buat Penerimaan Baru
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($penerimaanList->hasPages())
            <div class="d-flex justify-content-center">
                {{ $penerimaanList->links() }}
            </div>
            @endif
        </div>
    </div>

    @elseif(isset($penerimaan))
    <div class="d-flex justify-content-end mb-2">
        <a href="{{ route('terimagudang.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Kembali ke Daftar</a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">{{ $penerimaan->rcv_posting !== 'T' ? 'Form Penerimaan Barang' : 'Detail Penerimaan Barang' }}</h6>
            @if($penerimaan->id)
                <span class="badge badge-{{ $penerimaan->rcv_posting == 'F' ? 'warning' : 'success' }}">{{ $penerimaan->rcv_posting == 'F' ? 'DRAFT' : 'POSTED' }}</span>
            @endif
        </div>
        <div class="card-body">
            <form id="penerimaanForm" action="{{ $penerimaan->id ? route('terimagudang.update', $penerimaan->id) : route('terimagudang.store') }}" method="POST">
                @csrf
                @if($penerimaan->id)
                    @method('PUT')
                @endif
                <input type="hidden" id="penerimaanId" name="penerimaan_id" value="{{ $penerimaan->id }}">

                <div class="row">
                    <div class="col-md-6 mb-3"><label for="Rcv_number">No. Penerimaan</label><input type="text" id="Rcv_number" class="form-control" value="{{ $penerimaan->Rcv_number ?? 'Otomatis' }}" readonly></div>
                    <div class="col-md-6 mb-3"><label for="Rcv_Date">Tanggal Penerimaan</label><input type="date" id="Rcv_Date" class="form-control" name="Rcv_Date" value="{{ $penerimaan->Rcv_Date ? \Carbon\Carbon::parse($penerimaan->Rcv_Date)->format('Y-m-d') : date('Y-m-d') }}" {{ $penerimaan->rcv_posting == 'T' ? 'readonly' : '' }}></div>

                    @if($penerimaan->rcv_posting !== 'T')
                    <div class="form-group col-12">
                        <label for="transfer_id" class="text-primary"><strong>Ambil Data dari Transfer Gudang</strong></label>
                        <select id="transfer_id" name="ref_trx_auto" class="form-control" {{ $penerimaan->ref_trx_auto ? 'disabled' : '' }}>
                            <option value="">-- Pilih Nomor Transfer yang Sudah di-Posting --</option>
                            @foreach($postedTransfers as $transfer)
                                <option value="{{ $transfer->Trx_Auto }}" {{ $penerimaan->ref_trx_auto == $transfer->Trx_Auto ? 'selected' : '' }}>{{ $transfer->trx_number }} | Dari: {{ $transfer->Trx_WareCode }} -> Ke: {{ $transfer->Trx_RcvNo }} ({{ \Carbon\Carbon::parse($transfer->Trx_Date)->format('d M Y') }})</option>
                            @endforeach
                        </select>
                        @if ($penerimaan->ref_trx_auto)<small class="form-text text-muted">Nomor transfer tidak dapat diubah setelah disimpan.</small>@endif
                    </div>
                    @endif

                    <div class="col-md-6 mb-3"><label for="Rcv_From">Gudang Pengirim (Asal)</label><input type="text" id="Rcv_From" name="Rcv_From" class="form-control" value="{{ $penerimaan->Rcv_From }}" readonly style="background-color: #e9ecef;"></div>
                    <div class="col-md-6 mb-3"><label for="Rcv_WareCode">Gudang Penerima (Tujuan)</label><input type="text" id="Rcv_WareCode" name="Rcv_WareCode" class="form-control" value="{{ $penerimaan->Rcv_WareCode }}" readonly style="background-color: #e9ecef;"></div>
                    
                    <div class="col-12 mb-2"><label for="Rcv_Note">Catatan</label><textarea id="Rcv_Note" class="form-control" name="Rcv_Note" rows="2" {{ $penerimaan->rcv_posting == 'T' ? 'readonly' : '' }}>{{ $penerimaan->Rcv_Note }}</textarea></div>
                </div>

                @if($penerimaan->rcv_posting !== 'T')
                <div class="my-3 d-flex">
                    <button type="submit" name="action" value="save_draft" class="btn btn-primary mr-2"><i class="fas fa-save"></i> Simpan Draft</button>
                    <button type="button" id="btnSubmitPenerimaan" class="btn btn-success mr-2"><i class="fas fa-check-circle"></i> Simpan & Posting</button>
                    @if($penerimaan->id)
                        <button type="button" id="btnCancelDraft" class="btn btn-danger"><i class="fas fa-times"></i> Batalkan & Hapus Draft</button>
                    @endif
                </div>
                @endif

                <div class="card shadow-sm mt-4">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Detail Barang Diterima</h6></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="detailTable" width="100%" cellspacing="0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Kode Produk</th>
                                        <th>Nama Produk</th>
                                        <th>Satuan</th>
                                        <th class="text-end">Qty Dikirim</th>
                                        <th class="text-end bg-light" style="width: 15%;">Qty Diterima</th>
                                        <th class="text-end bg-light" style="width: 15%;">Qty Ditolak</th>
                                        <th class="text-end">Harga/COGS</th>
                                        <th class="text-end">Subtotal Diterima</th>
                                    </tr>
                                </thead>
                                <tbody id="detailTableBody">
                                    @if($penerimaan->details && $penerimaan->details->count() > 0)
                                        @foreach($penerimaan->details as $index => $detail)
                                        <tr>
                                            <td>{{ $detail->Rcv_ProdCode }}</td>
                                            <td>{{ $detail->Rcv_prodname }}
                                                <input type="hidden" name="details[{{$index}}][Rcv_ProdCode]" value="{{ $detail->Rcv_ProdCode }}">
                                                <input type="hidden" name="details[{{$index}}][Rcv_prodname]" value="{{ $detail->Rcv_prodname }}">
                                                <input type="hidden" name="details[{{$index}}][Rcv_cogs]" class="detail-cogs" value="{{ $detail->Rcv_cogs }}">
                                            </td>
                                            <td>{{ $detail->Rcv_uom }}<input type="hidden" name="details[{{$index}}][Rcv_uom]" value="{{ $detail->Rcv_uom }}"></td>
                                            <td class="text-end detail-qty-sent-display">{{ $detail->Rcv_Qty_Sent }}<input type="hidden" name="details[{{$index}}][Rcv_Qty_Sent]" value="{{ $detail->Rcv_Qty_Sent }}"></td>
                                            <td><input type="number" name="details[{{$index}}][Rcv_Qty_Received]" class="form-control text-end detail-calc detail-qty-received" value="{{ $detail->Rcv_Qty_Received }}" min="0" max="{{ $detail->Rcv_Qty_Sent }}" {{ $penerimaan->rcv_posting == 'T' ? 'readonly' : '' }}></td>
                                            <td><input type="number" name="details[{{$index}}][Rcv_Qty_Rejected]" class="form-control text-end detail-calc detail-qty-rejected" value="{{ $detail->Rcv_Qty_Rejected ?? 0 }}" min="0" max="{{ $detail->Rcv_Qty_Sent }}" {{ $penerimaan->rcv_posting == 'T' ? 'readonly' : '' }}></td>
                                            <td class="text-end">{{ number_format($detail->Rcv_cogs, 2) }}</td>
                                            <td class="text-end fw-bold detail-subtotal">{{ number_format(($detail->Rcv_Qty_Received * $detail->Rcv_cogs), 2) }}</td>
                                        </tr>
                                        @endforeach
                                    @else
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center justify-content-center">
                                                <i class="fas fa-dolly-flatbed fa-4x text-gray-300 mb-3"></i>
                                                <h6 class="font-weight-bold text-gray-600">Belum ada barang dimuat</h6>
                                                <p class="text-gray-500 mb-0 small">Silakan pilih <strong>Nomor Transfer</strong> di bagian atas untuk memuat daftar barang.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection


@push('scripts')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function loadAllWarehouses() {
        $.ajax({
            url: '{{ route("warehouse.getAll") }}',
            type: 'GET',
            success: function(response) {
                $('#Trx_WareCode').empty().append('<option value="">-- Pilih Gudang --</option>');
                $('#Trx_RcvNo').empty().append('<option value="">-- Pilih Gudang --</option>');
                
                response.forEach(function(warehouse) {
                    $('#Trx_WareCode').append(`<option value="${warehouse.WARE_Auto}">${warehouse.WARE_Name}</option>`);
                    $('#Trx_RcvNo').append(`<option value="${warehouse.WARE_Auto}">${warehouse.WARE_Name}</option>`);
                });
            },
            error: function() {
                console.error('Gagal memuat data gudang');
            }
        });
    }

    $(document).ready(function() {
        loadAllWarehouses();
    });


    $(document).ready(function() {
        @if(isset($penerimaanList))
            $('#dataTable').DataTable();

            $('#dataTable').on('click', '.delete-draft-btn', function() {
                const penerimaanId = $(this).data('id');
                const baseUrl = '{{ url("/mutasigudang/terimagudang") }}';
                Swal.fire({
                    title: 'Hapus Draft Penerimaan Ini?', text: "Aksi ini tidak dapat dibatalkan.", icon: 'warning',
                    showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, hapus!', cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = $(`<form action="${baseUrl}/${penerimaanId}" method="POST" style="display:none;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="{{ csrf_token() }}"></form>`);
                        $('body').append(form);
                        form.submit();
                    }
                });
            });
        @endif

        @if(isset($penerimaan))
        const baseUrl = '{{ url("/mutasigudang/terimagudang") }}';

        $('#transfer_id').on('change', function() {
            const transferId = $(this).val();
            if (!transferId) {
                $('#Rcv_From, #Rcv_WareCode').val('');
                $('#detailTableBody').html('<tr><td colspan="8" class="text-center">Pilih nomor transfer untuk memuat data barang.</td></tr>');
                return;
            }
            $('#detailTableBody').html('<tr><td colspan="8" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>');

            $.ajax({
                url: `${baseUrl}/get-transfer-details/${transferId}`, type: 'GET',
                success: function(response) {
                    console.log("Transfer Response:", response);
                    const gudangAsal = response.Trx_WareCode_name || response.gudang_pengirim?.WARE_Name || 'N/A';
                    const gudangTujuan = response.Trx_RcvNo_name || response.gudang_penerima?.WARE_Name || 'N/A';
                    
                    $('#Rcv_From').val(gudangAsal);
                    $('#Rcv_WareCode').val(gudangTujuan);
                    
                    const tableBody = $('#detailTableBody');
                    tableBody.empty();
                    
                    if (response.details && response.details.length > 0) {
                        response.details.forEach(function(item, index) {
                            const productName = item.produk?.nama_produk || item.trx_prodname || item.Trx_ProdCode;
                            const productUom = item.trx_uom || item.produk?.satuan || 'PCS';
                            const subtotal = (parseFloat(item.Trx_QtyTrx) * parseFloat(item.trx_cogs)).toFixed(2);
                            
                            const rowHtml = `
                                <tr>
                                    <td>${item.Trx_ProdCode}</td>
                                    <td>${productName}
                                        <input type="hidden" name="details[${index}][Rcv_ProdCode]" value="${item.Trx_ProdCode}">
                                        <input type="hidden" name="details[${index}][Rcv_prodname]" value="${productName}">
                                        <input type="hidden" name="details[${index}][Rcv_cogs]" class="detail-cogs" value="${item.trx_cogs}">
                                    </td>
                                    <td>${productUom}<input type="hidden" name="details[${index}][Rcv_uom]" value="${productUom}"></td>
                                    <td class="text-end detail-qty-sent-display">${item.Trx_QtyTrx}<input type="hidden" name="details[${index}][Rcv_Qty_Sent]" value="${item.Trx_QtyTrx}"></td>
                                    <td><input type="number" name="details[${index}][Rcv_Qty_Received]" class="form-control text-end detail-calc detail-qty-received" value="${item.Trx_QtyTrx}" min="0" max="${item.Trx_QtyTrx}"></td>
                                    <td><input type="number" name="details[${index}][Rcv_Qty_Rejected]" class="form-control text-end detail-calc detail-qty-rejected" value="0" min="0" max="${item.Trx_QtyTrx}"></td>
                                    <td class="text-end">${Number(item.trx_cogs).toLocaleString('id-ID', {minimumFractionDigits: 2})}</td>
                                    <td class="text-end fw-bold detail-subtotal">${Number(subtotal).toLocaleString('id-ID', {minimumFractionDigits: 2})}</td>
                                </tr>`;
                            tableBody.append(rowHtml);
                        });
                    } else {
                        tableBody.html('<tr><td colspan="8" class="text-center">Transfer ini tidak memiliki detail barang.</td></tr>');
                    }
                },
                error: function(xhr) {
                    console.error("AJAX Error:", xhr);
                    $('#detailTableBody').html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat data transfer.</td></tr>');
                    Swal.fire('Error!', xhr.responseJSON?.error || 'Gagal menghubungi server.', 'error');
                }

            });
        });

        function calculateRow(row) {
            const qtySent = parseFloat(row.find('.detail-qty-sent-display').text()) || 0;
            let qtyReceived = parseFloat(row.find('.detail-qty-received').val()) || 0;
            let qtyRejected = parseFloat(row.find('.detail-qty-rejected').val()) || 0;
            const cogs = parseFloat(row.find('.detail-cogs').val()) || 0;

            if (qtyReceived + qtyRejected > qtySent) {
                qtyRejected = qtySent - qtyReceived;
                if (qtyRejected < 0) {
                    qtyRejected = 0;
                    qtyReceived = qtySent;
                    row.find('.detail-qty-received').val(qtyReceived);
                }
                row.find('.detail-qty-rejected').val(qtyRejected);
            }

            const subtotal = qtyReceived * cogs;
            row.find('.detail-subtotal').text(subtotal.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        }
        $('#detailTableBody').on('keyup change', '.detail-calc', function() {
            calculateRow($(this).closest('tr'));
        });

        $('#btnSubmitPenerimaan').click(function(e) {
            e.preventDefault();
            const hasItems = $('#detailTableBody tr').length > 0 && !$('#detailTableBody td[colspan]').length;
            if (!hasItems) {
                Swal.fire('Peringatan!', 'Pilih transfer dan pastikan ada barang yang akan diterima sebelum posting.', 'warning');
                return;
            }
            Swal.fire({
                title: 'Simpan & Posting Penerimaan?', text: "Stok akan diperbarui dan data tidak bisa diubah lagi.",
                icon: 'question', showCancelButton: true, confirmButtonColor: '#28a745',
                confirmButtonText: 'Ya, Posting!', cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#penerimaanForm').append('<input type="hidden" name="action" value="save_post" />').submit();
                }
            });
        });

        $('#btnCancelDraft').click(function() {
            const penerimaanId = $('#penerimaanId').val();
            Swal.fire({
                title: 'Batalkan & Hapus Draft Ini?', text: "Seluruh data akan dihapus permanen.",
                icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus Draft!', cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = $(`<form action="${baseUrl}/${penerimaanId}" method="POST" style="display:none;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="{{ csrf_token() }}"></form>`);
                    $('body').append(form);
                    form.submit();
                }
            });
        });
        @endif
    });
</script>
@endpush