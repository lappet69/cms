@extends('layouts.app_back')
@section('content-header')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="nav-icon fas fa-boxes"></i> Informasi</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active"><i class="nav-icon fas fa-boxes"></i> Informasi</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <table id="datatable" class="table table-sm  table-hover">
                        <thead>
                            <tr>
                                <th class="no-sort" style="width: 30px">No</th>
                                <th>Title</th>
                                <th>Subtitle</th>
                                <th class="no-sort" style="width: 100px"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#datatable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                autoWidth: false,
                responsive: true,
                processing: true,
                serverSide: true,
                cache: false,
                ajax: {
                    url: "{{ route('administrator.informasi.datatable') }}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: function(d) {
                        return $.extend({}, d, {
                            // name: $("#_name").val(),
                            // email: $("#_email").val(),
                            // role_id: $("#_role_ids").val()
                        });
                    }
                },
                dom: '<"toolbar">lfrtip',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'id'
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'subtitle',
                        name: 'subtitle'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    }
                ],
                columnDefs: [{
                    "targets": 'no-sort',
                    "orderable": false,
                }],
                order: [
                    [1, "asc"]
                ]
            });

            const toolbar = '<div class="dt-buttons btn-group flex-wrap">' +
                '<a href="{{ route('administrator.informasi.create') }}" class="btn btn-sm bg-inventory" title="Tambahkan Informasi Baru"><i class="fas fa-plus"></i> Tambahkan Informasi Baru</a>' +
                '</div>';
            $("div.toolbar").html(toolbar);

            $("#_search").click(() => {
                $('#datatable-all').DataTable().ajax.reload();
            });
        });
    </script>
@endpush
