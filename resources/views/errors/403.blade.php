@extends('layouts.app')

@section('template_title')
Acceso restringido
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        
        <div class="col-sm-12">
            <div class="card">
              <div class="card-body">
              <div class="container py-5">
                  <div class="p-5 mb-4 bg-light rounded-3">
                    <div class="container-fluid py-5">
                      <h1 class="display-5 fw-bold">
                        <i class="fa fa-triangle-exclamation"></i>
                        Solo personal autorizado
                      </h1>
                      <p class="col-md-8 fs-5">
                        Lo sentimos, acceso restringido. <br>Si tiene un código de acceso ingreselo a continuación:
                      </p>
                      <div class="input-group ">
                        <input type="password" class="form-control form-control-sm" placeholder="Código de acceso" aria-label="Código de acceso" aria-describedby="button-addon2">
                        <button class="btn bg-gradient-primary" style="margin-bottom:0px !important; box-shadow:0 !important" type="button" id="button-addon2">Acceder</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>
@endsection
