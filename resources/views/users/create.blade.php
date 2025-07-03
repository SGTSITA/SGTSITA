@extends('layouts.app')

@section('template_title')
    Create Usuarios
@endsection

@section('content')

<div class="container-fluid mt-3">
      <div class="row">
        <div class="col">
          <div class="card">
            <!-- Card header -->
            <div class="card-header">
              <h3 class="mb-3">Crear nuevo usuario</h3>
               <a class="btn" href="{{ route('users.index') }}" style="background: {{$configuracion->color_boton_close}}; color: #ffff"> Regresar</a>
                    @if (count($errors) > 0)
                      <div class="alert alert-danger">
                        <strong>Whoops!</strong> There were some problems with your input.<br><br>
                        <ul>
                           @foreach ($errors->all() as $error)
                             <li>{{ $error }}</li>
                           @endforeach
                        </ul>
                      </div>
                    @endif
            </div>

            <div class="card-body mb-5">

                {!! Form::open(array('route' => 'users.store','method'=>'POST')) !!}
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Nombre:</label>
                            {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                        </div>

                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Email:</label>
                            {!! Form::text('email', null, array('placeholder' => 'Email','class' => 'form-control')) !!}
                        </div>
                    </div>

              @if(auth()->user()->Empresa->id == 1)
                @php $listaEmpresas = $empresas_base; @endphp
                @else
                    @php $listaEmpresas = $empresas; @endphp
                @endif

                <div class="mb-2">
                <label for="empresa_select" class="form-label">Agregar Empresa</label>
                <div class="d-flex gap-2">
                    <select id="empresa_select" class="form-select form-select-sm w-50">
                    <option value="">Selecciona una empresa</option>
                    @foreach($empresas_base as $empresa)
                        <option value="{{ $empresa->id }}" data-nombre="{{ $empresa->nombre }}">
                        {{ $empresa->nombre }}
                        </option>
                    @endforeach
                    </select>

                    <div class="form-check align-self-center">
                    <input type="checkbox" class="form-check-input" id="es_predeterminada">
                    <label class="form-check-label" for="es_predeterminada">Predet.</label>
                    </div>

                    <button type="button" id="agregar_empresa" class="btn btn-sm btn-success">+</button>
                </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-borderless align-middle mb-2" id="tabla_empresas" style="font-size: 0.85rem;">
                        <thead class="table-light">
                        <tr>
                            <th style="width: 60%;">Empresa</th>
                            <th style="width: 20%;" class="text-center">Predet.</th>
                            <th style="width: 20%;" class="text-center">Quitar</th>
                        </tr>
                        </thead>
                        <tbody>
                        {{-- Aquí se agregan dinámicamente las filas --}}
                        </tbody>
                    </table>
                </div>

                <div id="empresas_inputs"></div>

                    <div class="col-xs-12 col-sm-12 col-md-6">
                        <div class="form-group">
                            <label for="">Clientes</label>
                            <select name="id_cliente" id="" class="form-select">
                                <option value="">Seleciona Cliente</option>
                                @foreach ($clientes as  $item)
                                    <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Password:</label>
                            {!! Form::password('password', array('placeholder' => 'Password','class' => 'form-control')) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Confirm Password:</label>
                            {!! Form::password('confirm-password', array('placeholder' => 'Confirm Password','class' => 'form-control')) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <label class="form-control-label">Role:</label>
                            {!! Form::select('roles[]', $roles,[], array('class' => 'form-control','multiple')) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                        <button type="submit" class="btn" style="background: {{$configuracion->color_boton_save}}; color: #ffff">Guardar</button>
                    </div>
                </div>
                {!! Form::close() !!}

            </div>

          </div>
        </div>
      </div>
</div>


@endsection



@push('custom-javascript')
<script>
document.getElementById('agregar_empresa').addEventListener('click', function () {
    const select = document.getElementById('empresa_select');
    const empresaId = select.value;
    const empresaNombre = select.options[select.selectedIndex]?.dataset.nombre;
    const esPredeterminada = document.getElementById('es_predeterminada').checked;

    if (!empresaId) {
        alert('Selecciona una empresa primero');
        return;
    }

    if (document.querySelector(`#empresas_inputs input[name="empresas[]"][value="${empresaId}"]`)) {
        alert('La empresa ya fue agregada');
        return;
    }

    if (esPredeterminada) {
        document.querySelectorAll('.predeterminada').forEach(el => el.innerText = '');
        document.querySelectorAll('input[name="empresa_predeterminada"]').forEach(el => el.remove());
    }

    const tbody = document.querySelector('#tabla_empresas tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>${empresaNombre}</td>
        <td class="text-center predeterminada">${esPredeterminada ? '✅' : ''}</td>
        <td>
            <button type="button" class="btn btn-danger btn-sm eliminar">Eliminar</button>
        </td>
    `;
    tbody.appendChild(tr);

    const wrapper = document.getElementById('empresas_inputs');
    wrapper.innerHTML += `
        <input type="hidden" name="empresas[]" value="${empresaId}">
        ${esPredeterminada ? `<input type="hidden" name="empresa_predeterminada" value="${empresaId}">` : ''}
    `;

    select.selectedIndex = 0;
    document.getElementById('es_predeterminada').checked = false;
});

document.querySelector('#tabla_empresas tbody').addEventListener('click', function (e) {
    if (e.target.classList.contains('eliminar')) {
        const fila = e.target.closest('tr');
        const empresaNombre = fila.children[0].textContent;
        const empresaId = [...document.querySelectorAll('#empresa_select option')]
            .find(opt => opt.textContent.trim() === empresaNombre.trim())?.value;

        document.querySelectorAll(`#empresas_inputs input[value="${empresaId}"]`).forEach(el => el.remove());
        const predInput = document.querySelector(`input[name="empresa_predeterminada"][value="${empresaId}"]`);
        if (predInput) predInput.remove();

        fila.remove();
    }
});
</script>
@endpush
