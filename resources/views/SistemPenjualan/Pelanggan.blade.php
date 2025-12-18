@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Daftar Pelanggan</h1>
    <p class="mb-4">Manajemen daftar pelanggan yang terdaftar di sistem.</p>

    {{-- Session Success/Error Alerts --}}
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
        <button type="button" class="btn btn-primary" id="addPelangganButton">
            <i class="fas fa-plus fa-sm"></i> Tambah Pelanggan Baru
        </button>
        @endcan
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Pelanggan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Anggota</th>
                            <th>Alamat</th>
                            <th>Telp</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pelanggans as $index => $pelanggan)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="text-center">{{ $pelanggan->kode }}</td>
                                <td>{{ $pelanggan->anggota }}</td>
                                <td>{!! nl2br(e($pelanggan->alamat)) !!}</td>
                                <td>{{ $pelanggan->telp }}</td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $pelanggan->status == 'Aktif' ? 'success' : 'danger' }}">
                                        {{ $pelanggan->status }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @can('ubah', $currentMenuSlug)
                                    <button class="btn btn-sm btn-warning edit-btn"
                                        title="Edit Pelanggan"
                                        data-id="{{ $pelanggan->id }}"
                                        data-kode="{{ $pelanggan->kode }}"
                                        data-anggota="{{ $pelanggan->anggota }}"
                                        data-alamat="{{ $pelanggan->alamat }}"
                                        data-telp="{{ $pelanggan->telp }}"
                                        data-email="{{ $pelanggan->email }}"
                                        data-cara_bayar="{{ $pelanggan->cara_bayar }}"
                                        data-lama_bayar="{{ $pelanggan->lama_bayar }}"
                                        data-potongan="{{ $pelanggan->potongan }}"
                                        data-nominal_plafon="{{ $pelanggan->nominal_plafon }}"
                                        data-status="{{ $pelanggan->status }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endcan
                                    @can('hapus', $currentMenuSlug)
                                    <button class="btn btn-sm btn-danger delete-btn"
                                        title="Hapus Pelanggan"
                                        data-id="{{ $pelanggan->id }}"
                                        data-anggota="{{ $pelanggan->anggota }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                   @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Tidak ada data pelanggan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit Pelanggan -->
<div class="modal fade" id="pelangganModal" tabindex="-1" role="dialog" aria-labelledby="pelangganModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="pelangganForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="modal-header">
                    <h5 class="modal-title" id="pelangganModalLabel">Tambah Pelanggan Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" id="form_group_kode" style="display: none;">
                                <label>Kode</label>
                                <input type="text" class="form-control" id="modal_kode" name="kode" readonly>
                            </div>
                            <div class="form-group">
                                <label>Nama Pelanggan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="modal_anggota" name="anggota" required>
                            </div>
                            <div class="form-group">
                                <label>Alamat <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="modal_alamat" name="alamat" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Telp <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="modal_telp" name="telp" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" id="modal_email" name="email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cara Bayar</label>
                                <select class="form-control" id="modal_cara_bayar" name="cara_bayar">
                                    <option value="TUNAI">TUNAI</option>
                                    <option value="KREDIT">KREDIT</option>
                                    <option value="KONSINYASI">KONSINYASI</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Lama Bayar (hari)</label>
                                <input type="number" class="form-control" id="modal_lama_bayar" name="lama_bayar" min="0">
                            </div>
                            <div class="form-group">
                                <label>Potongan (%)</label>
                                <input type="number" step="0.01" class="form-control" id="modal_potongan" name="potongan" min="0" max="100">
                            </div>
                            <div class="form-group">
                                <label>Nominal Plafon</label>
                                <input type="number" step="0.01" class="form-control" id="modal_nominal_plafon" name="nominal_plafon" min="0">
                            </div>
                             <div class="form-group">
                                <label>Status</label>
                                <select class="form-control" id="modal_status" name="status">
                                    <option value="Aktif">Aktif</option>
                                    <option value="Tidak Aktif">Tidak Aktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="savePelangganButton">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(function() {
        $(document).on('click', '[data-dismiss="modal"]', function() {
        $(this).closest('.modal').modal('hide');
    });
    // --- Konfigurasi ---
    const storeUrl = "{{ route('pelanggan.store') }}";
    const updateUrlTpl = "{{ route('pelanggan.update', ':id') }}";
    const deleteUrlTpl = "{{ route('pelanggan.destroy', ':id') }}";
    const csrfToken = "{{ csrf_token() }}";

    const modal = $('#pelangganModal');
    const form = $('#pelangganForm');

    // --- Inisialisasi DataTable ---
    $('#dataTable').DataTable({
        "language": { "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Indonesian.json" }
    });

    // --- Event Handler ---

    // Buka modal untuk menambah pelanggan baru
    $('#addPelangganButton').on('click', function() {
        form.trigger('reset');
        form.attr('action', storeUrl);
        $('#formMethod').val('POST');
        $('#pelangganModalLabel').text('Tambah Pelanggan Baru');
        $('#savePelangganButton').text('Simpan');
        $('#form_group_kode').hide();
        modal.modal('show');
    });

    // Buka modal untuk mengedit pelanggan
    $('#dataTable').on('click', '.edit-btn', function() {
        const btn = $(this);
        const id = btn.data('id');

        form.trigger('reset');
        form.attr('action', updateUrlTpl.replace(':id', id));
        $('#formMethod').val('PUT');
        $('#pelangganModalLabel').text('Edit Data Pelanggan');
        $('#savePelangganButton').text('Simpan Perubahan');

        // Isi form dari data-attributes
        $('#modal_kode').val(btn.data('kode'));
        $('#modal_anggota').val(btn.data('anggota'));
        $('#modal_alamat').val(btn.data('alamat'));
        $('#modal_telp').val(btn.data('telp'));
        $('#modal_email').val(btn.data('email'));
        $('#modal_cara_bayar').val(btn.data('cara_bayar'));
        $('#modal_lama_bayar').val(btn.data('lama_bayar'));
        $('#modal_potongan').val(btn.data('potongan'));
        $('#modal_nominal_plafon').val(btn.data('nominal_plafon'));
        $('#modal_status').val(btn.data('status'));

        $('#form_group_kode').show();
        modal.modal('show');
    });

    // Tangani submit form untuk tambah dan edit
    form.on('submit', function(e) {
        e.preventDefault();
        const submitBtn = $('#savePelangganButton');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST', // Selalu POST, method di-spoof oleh field _method
            data: form.serialize(),
            success: function(response) {
                modal.modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message, // Gunakan pesan dari respons JSON
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            },
            error: function(xhr) {
                let errorMsg = 'Terjadi kesalahan. Silakan coba lagi.';
                if (xhr.status === 422) { // Error validasi
                    const errors = xhr.responseJSON.errors || {};
                    errorMsg = Object.values(errors).flat().join('<br>');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    html: errorMsg // Gunakan html agar <br> dirender
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });

    // Tangani klik tombol hapus
    $('#dataTable').on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        const anggota = $(this).data('anggota');

        Swal.fire({
            title: 'Konfirmasi Hapus',
            html: `Apakah Anda yakin ingin menghapus pelanggan <strong>${anggota}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
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
                        const errorMsg = xhr.responseJSON?.message || 'Gagal menghapus data.';
                        Swal.fire('Error!', errorMsg, 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
