@extends('layouts.master')
@section('title', '- Daftar Kelas')
@section('bg-header', 'bg-primary')
@section('header-body')
<div class="row align-items-center py-4">
    <div class="col-lg-6 col-7">
        <h6 class="h2 text-white d-inline-block mb-0">Kelas Saya</h6>
        <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
            <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fas fa-home"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ url('user') }}">Beranda</a></li>
                <li class="breadcrumb-item active" aria-current="page">Kelas Saya</li>
            </ol>
        </nav>
    </div>
</div>
@endsection
@section('content')
<div class="row">
    @if($listClasses->total() == 0)
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-body">
                <h3 class="text-center">Belum ada kelas</h3>
            </div>
        </div>
    </div>
    @else
    @foreach($listClasses as $class)
    <div class="col-md-4">
        <div class="card shadow">
            <img class="card-img-top" src="{{ url('cover/' . $class->class->cover) }}" loading="lazy"
                alt="{{ $class->class->name }}">
            <div class="card-body">
                <h3 class="card-title" style="margin-bottom: 10px;">{{ $class->class->name }} <span
                        class="badge badge-warning float-right">{{ ucfirst($class->class->type) }}</span></h3>
                {{ $class->class->category->name }}
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-4">
                        <span class="badge badge-secondary">{{ ucfirst($class->class->speaker->name) }}</span>
                    </div>
                    <div class="col-8">
                        <div class="btn-group float-right">
                            @if($class->class->type == 'premium')
                            <button data-classid="{{ $class->class->id }}"
                                class="btn btn-sm btn-success float-right downloadSertificate"><i
                                    class="ni ni-paper-diploma"></i></button>
                            @endif
                            @if($class->class->is_draft == 0)
                            <a href="{{ url('user/roll/' . $class->class->id) }}"
                                class="btn btn-sm btn-primary float-right"><i class="ni ni-button-play"></i></a>
                            @else
                            <a href="" class="btn btn-sm btn-danger float-right disabled">Diarsipkan</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    @endif
</div>
<div class="row ">
    <div class="col-md-12 d-flex align-items-center justify-content-center">
        {{ $listClasses->links() }}
    </div>
</div>
@endsection
@push('js')
<script>
    $('#category_id').on('change', function () {
        var cat_id = $(this).val();
        if (cat_id != 0) {
            window.location.href = "{{ url('user/myclass') }}" + '/' + cat_id;
        } else {
            window.location.href = "{{ url('user/myclass') }}";
        }
    });

    $('.downloadSertificate').on('click', function () {
        var class_id = $(this).data('classid');
        window.location.href = "{{ url('user/sertificate/get') }}" + "?class_id=" + class_id +
            '&template=basic_template';
    });

</script>
@endpush
