@extends('layouts.admin')
@section('main-content')

    <div class="container-fluid">
        <h1 class="h3 mb-2 text-gray-800">Daftar Gudang</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        <div class="mb-3">
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#warehouseModal">
                <i class="fas fa-plus"></i> Tambah Gudang
            </button>
            <a href="{{ route('inventory.stock_report') }}" class="btn btn-success mb-3 ms-2">
                <i class="fas fa-file-alt"></i> Laporan Stok
            </a>
        </div>


        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Gudang</h6>
            </div>
            <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width=5% >No</th>
                            <th>Gudang</th>
                            <th>Alamat</th>
                            <th>Telepon</th>
                            <th>Email</th>
                            <th>Web</th>
                            <th>Catatan 1</th>
                            <th>Catatan 2</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($warehouses as $index => $warehouse)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $warehouse->WARE_Name }}</td>
                        <td>{{ $warehouse->WARE_Address }}</td>
                        <td>{{ $warehouse->WARE_Phone }}</td>
                        <td>{{ $warehouse->WARE_Email }}</td>
                        <td>{{ $warehouse->WARE_Web }}</td>
                        <td>{{ $warehouse->ware_note1 }}</td>
                        <td>{{ $warehouse->ware_note2 }}</td>
                        <td>{{ $warehouse->WARE_EntryDate ? \Carbon\Carbon::parse($warehouse->WARE_EntryDate)->format('d F Y') : '' }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-warning edit-btn"
                                    data-id="{{ $warehouse->WARE_Auto }}"
                                    data-name="{{ $warehouse->WARE_Name }}"
                                    data-address="{{ $warehouse->WARE_Address }}"
                                    data-phone="{{ $warehouse->WARE_Phone }}"
                                    data-email="{{ $warehouse->WARE_Email }}"
                                    data-web="{{ $warehouse->WARE_Web }}"
                                    data-note1="{{ $warehouse->ware_note1 }}"
                                    data-note2="{{ $warehouse->ware_note2 }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-danger delete-btn"
                                    data-id="{{ $warehouse->WARE_Auto }}"
                                    data-name="{{ $warehouse->WARE_Name ?? 'item ini' }}"
                                    data-url="{{ route('warehouse.destroy', $warehouse->WARE_Auto) }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <img src="{{ asset('img/svg/undraw_editable_dywm.svg') }}" alt="Tidak ada data" style="height: 150px; width: auto; opacity: 0.8;" class="mb-4">
                                <h5 class="font-weight-bold text-gray-800 mb-2">Data Gudang Kosong</h5>
                                <p class="text-gray-500 mb-3">
                                    Belum ada master data gudang yang didaftarkan.
                                </p>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#warehouseModal">
                                    <i class="fas fa-plus"></i> Tambah Gudang Pertama
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>
    </div>

    </div>

    <div class="modal fade" id="warehouseModal" tabindex="-1" aria-labelledby="warehouseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" id="warehouseForm">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="warehouseModalLabel">Tambah Gudang</h5>
            </div>
                <div class="modal-body">
                    <fieldset class="border p-3">
                        <legend class="w-auto px-2 fw-bold">Master Warehouse</legend>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Gudang</label>
                            <div class="col-sm-9">
                                <input type="text" name="WARE_Name" class="form-control" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Alamat</label>
                            <div class="col-sm-9">
                                <textarea name="WARE_Address" class="form-control" rows="4"></textarea>

                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Telp</label>
                            <div class="col-sm-3">
                                <input type="text" name="WARE_Phone" class="form-control">
                            </div>
                            <label class="col-sm-2 col-form-label">e-mail</label>
                            <div class="col-sm-4">
                                <input type="email" name="WARE_Email" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Web</label>
                            <div class="col-sm-9">
                                <input type="text" name="WARE_Web" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Catatan 1</label>
                            <div class="col-sm-9">
                                <input type="text" name="ware_note1" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Catatan 2</label>
                            <div class="col-sm-9">
                                <input type="text" name="ware_note2" class="form-control">
                            </div>
                        </div>
                    </fieldset>
                </div>

                <div class="modal-footer">
                    <button type="submit" id="submitbutton" class="btn btn-success">
                        <i class="bi bi-check-circle-fill"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle-fill"></i> Batal
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#dataTable').DataTable();
    $('.btn-primary[data-bs-target="#warehouseModal"]').on('click', function() {
        $('#warehouseModalLabel').text('Tambah Gudang');
        $('#warehouseForm').attr('action', '{{ route('warehouse.store') }}');
        $('#formMethod').val('POST');
        $('#warehouseForm')[0].reset();
    });

    $('.edit-btn').on('click', function() {
        const modal = $('#warehouseModal');
        const form = $('#warehouseForm');
        const id = $(this).data('id');

        $('#warehouseModalLabel').text('Edit Gudang');
        form.attr('action', `/mutasigudang/warehouse/${id}`);
        $('#formMethod').val('PUT');

        form.find('[name="WARE_Name"]').val($(this).data('name'));
        form.find('[name="WARE_Address"]').val($(this).data('address'));
        form.find('[name="WARE_Phone"]').val($(this).data('phone'));
        form.find('[name="WARE_Email"]').val($(this).data('email'));
        form.find('[name="WARE_Web"]').val($(this).data('web'));
        form.find('[name="ware_note1"]').val($(this).data('note1'));
        form.find('[name="ware_note2"]').val($(this).data('note2'));

        modal.modal('show');
    });

    $('#warehouseForm').on('submit', function(event) {
        event.preventDefault();

        const form = $(this);
        const url = form.attr('action');
        const method = form.find('input[name="_method"]').val();
        const data = form.serialize();
        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            success: function(response) {
                $('#warehouseModal').modal('hide'); 
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message || 'Data berhasil disimpan.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                const errors = xhr.responseJSON.errors;
                let errorMessages = '';
                if (errors) {
                    $.each(errors, function(key, value) {
                        errorMessages += `<li>${value[0]}</li>`;
                    });
                    Swal.fire('Gagal!', `<ul>${errorMessages}</ul>`, 'error');
                } else {
                    Swal.fire('Gagal!', xhr.responseJSON.message || 'Terjadi kesalahan.', 'error');
                }
            }
        });
    });


    $('.delete-btn').on('click', function (event) {
        event.preventDefault();

        const button = $(this);
        const itemName = button.data('name') || 'item ini';
        const deleteUrl = button.data('url');
        const csrfToken = '{{ csrf_token() }}';

        Swal.fire({
            title: 'Apakah Anda yakin?',
            html: `Anda akan menghapus: <strong>${itemName}</strong><br><small>Tindakan ini tidak dapat dibatalkan.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrl,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: csrfToken
                    },
                    success: function (response) {
                        Swal.fire('Terhapus!', response.message || 'Data berhasil dihapus.', 'success')
                            .then(() => location.reload());
                    },
                    error: function (xhr) {
                        const message = xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus.';
                        Swal.fire('Gagal!', message, 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush