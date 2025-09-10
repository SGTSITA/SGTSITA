@extends('layouts.app')

@section('template_title')
    Editar Usuario
@endsection

@push('custom-css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <style>
        .card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.06);
        }

        .card-header {
            background: #f8f9fb;
            border-top-left-radius: 18px;
            border-top-right-radius: 18px;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-label {
            font-weight: 500;
        }

        .form-group-ios {
            position: relative;
        }

        .form-group-ios i {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            color: #aaa;
            z-index: 1;
        }

        .form-group-ios input,
        .form-group-ios select {
            padding-left: 2.5rem !important;
            border-radius: 12px;
            height: 45px;
        }

        .radio-group-ios {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .radio-ios {
            appearance: none;
            -webkit-appearance: none;
            background-color: #f0f0f5;
            border: none;
            padding: 10px 20px;
            border-radius: 999px;
            font-weight: 600;
            color: #333;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .radio-ios:checked {
            background-color: #007aff;
            color: #fff;
        }

        .radio-ios:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 122, 255, 0.4);
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h3>Editar Usuario</h3>
                        <a class="btn btn-ios" href="{{ route('users.index') }}">Regresar</a>
                    </div>

                    <div class="card-body">
                        {!! Form::model($user, [
                            'route' => ['users.update', $user->id],
                            'method' => 'PATCH',
                            'id' => 'formEditarUsuario',
                        ]) !!}
                        <div class="row g-3">
                            <div class="col-md-12 form-group-ios">
                                <label class="form-label">Nombre</label>
                                <i class="fas fa-user"></i>
                                {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Nombre']) !!}
                            </div>

                            <div class="col-md-12 form-group-ios">
                                <label class="form-label">Email</label>
                                <i class="fas fa-envelope"></i>
                                {!! Form::email('email', null, ['class' => 'form-control', 'placeholder' => 'Email']) !!}
                            </div>

                            <div class="col-md-12 form-group-ios">
                                <label class="form-label">Empresa</label>
                                <i class="fas fa-building"></i>
                                <select name="id_empresa" class="form-select">
                                    <option value="">Selecciona una empresa</option>
                                    @foreach ($listaEmpresas as $empresa)
                                        <option value="{{ $empresa->id }}"
                                            @if ($empresa->id == $user->id_empresa) selected @endif>
                                            {{ $empresa->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12 form-group-ios">
                                <label class="form-label">Contraseña (opcional)</label>
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" id="password" class="form-control"
                                    placeholder="Nueva contraseña">
                            </div>

                            <div class="col-md-12 form-group-ios">
                                <label class="form-label">Confirmar Contraseña</label>
                                <i class="fas fa-lock"></i>
                                <input type="password" name="confirm-password" id="confirm-password" class="form-control"
                                    placeholder="Confirmar nueva contraseña">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label d-block">Roles</label>
                                <div class="radio-group-ios">
                                    @foreach ($roles as $key => $rol)
                                        <label>
                                            <input type="radio" name="roles[]" value="{{ $key }}"
                                                class="radio-ios" @if (in_array($key, $userRole)) checked @endif>
                                            {{ $rol }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-md-6" id="clienteGroup" style="display: none;">
                                <label class="form-label">Selecciona el Cliente</label>
                                <select name="id_cliente" id="id_cliente" class="form-select">
                                    <option value="0">Sin cliente</option>
                                    @foreach ($clientes as $cliente)
                                        <option value="{{ $cliente->id }}"
                                            @if ($cliente->id == $user->id_cliente) selected @endif>
                                            {{ $cliente->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 text-center mt-3">
                                <button type="submit" class="btn btn-ios">Actualizar</button>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('formEditarUsuario').addEventListener('submit', function(e) {
            const password = document.getElementById('password')?.value.trim();
            const confirmPassword = document.getElementById('confirm-password')?.value.trim();

            if (password || confirmPassword) {
                if (password !== confirmPassword) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Contraseñas no coinciden',
                        text: 'La nueva contraseña y su confirmación deben ser iguales.',
                        confirmButtonColor: '#007aff'
                    });
                    return;
                }
            }
        });

        function toggleClienteField() {
            const selected = document.querySelector('input[name="roles[]"]:checked');
            const clienteGroup = document.getElementById('clienteGroup');
            const clienteSelect = document.getElementById('id_cliente');

            const selectedLabel = selected?.parentElement?.innerText?.trim().toUpperCase() || "";
            if (selectedLabel.includes("CLIENTE")) {
                clienteGroup.style.display = 'block';
            } else {
                clienteGroup.style.display = 'none';
                if (clienteSelect) clienteSelect.value = '0';
            }
        }


        document.addEventListener('DOMContentLoaded', function() {
            const radios = document.querySelectorAll('input[name="roles[]"]');
            toggleClienteField();
            radios.forEach(radio => {
                radio.addEventListener('change', toggleClienteField);
            });
        });
    </script>
@endpush
