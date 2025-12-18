<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Detail Penjualan - {{ config('app.name') }}</title>

    <!-- Custom fonts for this template-->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <!-- Custom styles for this page-->
    <link href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ Auth::user()->name }}</span>
                                <img class="img-profile rounded-circle" src="{{ asset('img/undraw_profile.svg') }}">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="{{ route('profile') }}">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Detail Penjualan</h1>
                        <div>
                            <a href="{{ route('penjualan.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            @if($jualan->status == 'Draft')
                            <button class="btn btn-success ml-2" id="approveBtn" data-id="{{ $jualan->id }}" data-no_jualan="{{ $jualan->no_jualan }}">
                                <i class="fas fa-check"></i> Approve Penjualan
                            </button>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Informasi Penjualan</h6>
                                    <span class="badge badge-{{ $jualan->status == 'Draft' ? 'secondary' : ($jualan->status == 'Approved' ? 'success' : 'danger') }}">
                                        {{ $jualan->status }}
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label-sm">No. Jualan</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control form-control-sm" value="{{ $jualan->no_jualan }}" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label-sm">Pelanggan</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control form-control-sm" value="{{ $jualan->pelanggan->anggota ?? 'N/A' }}" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label-sm">No. CO</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control form-control-sm" value="{{ $jualan->customerOrder->no_order ?? 'N/A' }}" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label-sm">PO Pelanggan</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control form-control-sm" value="{{ $jualan->po_pelanggan ?? '-' }}" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label-sm">Tgl. Kirim</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control form-control-sm" value="{{ \Carbon\Carbon::parse($jualan->tgl_kirim)->format('d/m/Y') }}" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label-sm">Jatuh Tempo</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control form-control-sm" value="{{ $jualan->jatuh_tempo ? \Carbon\Carbon::parse($jualan->jatuh_tempo)->format('d/m/Y') : '-' }}" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label-sm">Pengguna</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control form-control-sm" value="{{ $jualan->pengguna }}" readonly>
                                                </div>
                                            </div>
                                            @if($jualan->approved_by)
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label-sm">Approved By</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control form-control-sm" value="{{ $jualan->approved_by }}" readonly>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Detail Produk</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" width="100%" cellspacing="0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Produk</th>
                                                    <th class="text-center">Qty</th>
                                                    <th class="text-center">Satuan</th>
                                                    <th class="text-right">Harga</th>
                                                    <th class="text-center">Disc(%)</th>
                                                    <th class="text-center">Pajak (%)</th>
                                                    <th class="text-right">Nominal</th>
                                                    <th>Catatan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($jualan->details as $index => $detail)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $detail->product ? $detail->product->nama_produk : 'Produk tidak ditemukan (ID: ' . $detail->product_id . ')' }}</td>
                                                    <td class="text-center">{{ $detail->qty }}</td>
                                                    <td class="text-center">{{ $detail->satuan ?? 'pcs' }}</td>
                                                    <td class="text-right">Rp{{ number_format($detail->harga, 0, ',', '.') }}</td>
                                                    <td class="text-center">{{ $detail->disc ?? 0 }}</td>
                                                    <td class="text-center">{{ $detail->pajak ?? 0 }}%</td>
                                                    <td class="text-right">Rp{{ number_format($detail->nominal, 0, ',', '.') }}</td>
                                                    <td>{{ $detail->catatan ?? '-' }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="9" class="text-center text-muted">Tidak ada detail produk.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Ringkasan Pembayaran</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td class="font-weight-bold">Bruto</td>
                                                <td class="text-right">Rp{{ number_format($jualan->bruto, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td class="font-weight-bold">Total Disc.</td>
                                                <td class="text-right">Rp{{ number_format($jualan->total_disc, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td class="font-weight-bold">Total Pajak</td>
                                                <td class="text-right">Rp{{ number_format($jualan->total_pajak, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr class="border-top">
                                                <td class="font-weight-bold">Netto</td>
                                                <td class="text-right font-weight-bold">Rp{{ number_format($jualan->netto, 0, ',', '.') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            @if($jualan->status == 'Draft')
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-warning">Status Persetujuan</h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Penjualan ini belum di-approve. Stok barang akan dikurangi setelah penjualan di-approve.
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($jualan->status == 'Approved')
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-success">Status Persetujuan</h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle"></i> Penjualan ini telah di-approve pada {{ $jualan->approved_at ? \Carbon\Carbon::parse($jualan->approved_at)->format('d/m/Y H:i') : '-' }} oleh {{ $jualan->approved_by }}.
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; {{ config('app.name') }} {{ date('Y') }}</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="{{ route('logout') }}">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <!-- Custom scripts for all pages-->
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>

    <!-- Page level plugins -->
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

    <!-- Page level custom scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(document).ready(function() {
        // Event handler untuk approve button
        $('#approveBtn').on('click', function() {
            const btn = $(this);
            const id = btn.data('id');
            const noJualan = btn.data('no_jualan');

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
                    $.ajax({
                        url: `/penjualan/${id}/approve`,
                        method: 'POST',
                        data: {
                            '_token': '{{ csrf_token() }}'
                        },
                        beforeSend: function() {
                            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
                        },
                        success: function(response) {
                            Swal.fire('Berhasil!', response.message, 'success')
                                .then(() => location.reload());
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false).html('<i class="fas fa-check"></i> Approve Penjualan');
                            Swal.fire('Error', xhr.responseJSON?.message || 'Gagal approve penjualan.', 'error');
                        }
                    });
                }
            });
        });
    });
    </script>
</body>

</html>
