@extends('layouts.app_back')
@push('css')
    <!-- summernote -->
    <link rel="stylesheet" href="{{ asset('back/adminlte/plugins/summernote/summernote-bs4.min.css') }}">
@endpush
@section('content-header')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        @if ($model->exists)
                            <i class="nav-icon fas fa-eidt"></i> Edit Testimoni
                        @else
                            <i class="nav-icon fas fa-plus"></i> Tambah Testimoni
                        @endif
                    </h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><i class="nav-icon fas fa-boxes"></i> Testimoni</li>
                        @if ($model->exists)
                            <li class="breadcrumb-item active"><i class="nav-icon fas fa-edit"></i> Edit Testimoni</li>
                        @else
                            <li class="breadcrumb-item active"><i class="nav-icon fas fa-plus"></i> Tambah Testimoni</li>
                        @endif
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
            <div class="card ">
                <div class="card-body">
                    <form class="form-horizontal"
                        action="{{ $model->exists ? route('administrator.testimoni.update', base64_encode($model->id)) : route('administrator.testimoni.store') }}"
                        method="POST" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        @if ($model->exists)
                            <input type="hidden" name="_method" value="PUT">
                        @endif
                        <div class="form-group">
                            <label for="title" class="col-sm-12 col-form-label">Judul Konten<span
                                    class="text-red">*</span></label>
                            <div class="col-sm-12">
                                <input type="text" name="title" id="title"
                                    class="form-control @error('title') is-invalid @enderror" placeholder="Judul Konten"
                                    value="{{ $model->exists ? $model->title : old('title') }}">
                                @error('title')
                                    <small class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </small>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="subtitle" class="col-sm-12 col-form-label">Sub Judul Konten</label>
                            <div class="col-sm-12">
                                <input type="text" name="subtitle" id="subtitle"
                                    class="form-control @error('subtitle') is-invalid @enderror"
                                    placeholder="Sub Judul Konten"
                                    value="{{ $model->exists ? $model->subtitle : old('subtitle') }}">
                                @error('subtitle')
                                    <small class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </small>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="short_description" class="col-sm-12 col-form-label">Deskripsi Singkat</label>
                            <div class="col-sm-12">
                                <textarea name="short_description" placeholder="Deskripsi Singkat Konten" rows="5"
                                    class="form-control @error('short_description') is-invalid @enderror">{{ $model->exists ? $model->short_description : old('short_description') }}</textarea>
                                @error('short_description')
                                    <small class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </small>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="content" class="col-sm-12 col-form-label">Konten</label>
                            <div class="col-sm-12">
                                <textarea name="content" class="summernote @error('content') is-invalid @enderror" rows="5">
                                    {{ $model->exists ? $model->content : old('content') }}
                                </textarea>
                                @error('content')
                                    <small class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </small>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="gambar" class="col-sm-12 col-form-label">Gambar<span class="text-red">*</span>
                                <a href="javascript:void(0)" class="btn btn-xs btn-inventory" id="tambahGambar"><i
                                        class="fas fa-plus"></i></a></label>

                            @if ($model->exists)
                                <div id="list_gambar">

                                </div>

                                @php
                                    $asset = \App\Models\Asset::where('content_id', $model->id)->get();
                                @endphp

                                @foreach ($asset as $a)
                                    <div>
                                        <b>Keterangan Foto Saat ini : </b> {{ $a->keterangan }}<br>
                                        <img class="img-fluid" src="{{ asset('front/assets/img/' . $a->thumbnail) }}"
                                            alt=""><br><br>
                                    </div>
                                @endforeach
                            @else
                                <div id="list_gambar">

                                </div>
                            @endif
                        </div>

                        {{-- <div class="form-group">
                            <label for="thumbnail" class="col-sm-12 col-form-label">Foto Alumni <span
                                    class="text-red">*</span></label>
                            <div class="col-sm-12">
                                <input type="file" name="thumbnail" id="thumbnail" class="form-control">
                                @error('thumbnail')
                                    <small class="text-red">
                                        <strong>{{ $message }}</strong>
                                    </small>
                                @enderror

                                @php
                                    if ($model->exists) {
                                        echo 'Thumbnail saat ini: <br>';
                                        echo '<img class="img-fluid" src="' . asset('front/assets/img/' . $model->thumbnail) . '">';
                                    }
                                @endphp
                            </div>
                        </div> --}}

                        <div class="form-group">
                            <label for="active" class="col-sm-12 col-form-label">Active <span
                                    class="text-red">*</span></label>
                            <select name="active" id="active"
                                class="form-control @error('active') is-invalid @enderror">
                                <option value="1"
                                    {{ $model->exists ? ($model->active == 1 ? 'selected' : '') : (old('active') == 1 ? 'selected' : '') }}>
                                    Ya</option>
                                <option value="0"
                                    {{ $model->exists ? ($model->active == 0 ? 'selected' : '') : (old('active') == 0 ? 'selected' : '') }}>
                                    Tidak</option>
                            </select>

                            @error('active')
                                <small class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </small>
                            @enderror
                        </div>

                        <div class="float-right">
                            <button type="submit" id="save" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <!-- Summernote -->
    <script src="{{ asset('back/adminlte/plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script>
        function deleteGambar(btn, norow) {
            var row = btn.parentNode;
            row.parentNode.removeChild(row);
        }

        $(function() {
            // Summernote
            $('.summernote').summernote()

            $("#tambahGambar").click(function(e) {
                e.preventDefault();
                const d = new Date();
                let norow = d.getTime();
                var list_gambar = '<div class="row mt-1" id="' + norow + '">' +
                    '               <input type="file" name="gambar[]" class="form-control col-4" id="gambar' +
                    norow + '">&nbsp;' +
                    '<select name="keterangan[]" class="form-control col-2">' +
                    '<option value="thumbnail">Thumbnail</option>' +
                    '<option value="background_image">Gambar Belakang</option>' +
                    '</select>&nbsp;' +
                    '<button class="btn btn-xs btn-danger" onclick="deleteGambar(this, ' +
                    norow + ')">Hapus</button>' +
                    '            </div>';
                $("#list_gambar").append(list_gambar);
            })

        });
    </script>
@endpush
