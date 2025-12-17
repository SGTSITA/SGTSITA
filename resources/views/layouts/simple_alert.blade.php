{{-- Message --}}
@if (Session::has('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <span class="alert-text">
            <strong>Éxito!</strong>
            {{ session('success') }}
        </span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if (Session::has('edit'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <span class="alert-text">
            <strong>Éxito!</strong>
            {{ session('edit') }}
        </span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if (Session::has('delete'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <span class="alert-text" style="color: #fff">
            <strong>Éxito!</strong>
            {{ session('delete') }}
        </span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if (Session::has('error'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <span class="alert-text" style="color: #fff">
            <strong>Error!</strong>
            {{ session('error') }}
        </span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
