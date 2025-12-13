<div class="flex-grow-1">
    <div class="fv-row row mb-3">
        <div class="col-12 min-w-450px">
            <div class="border border-gray-300 border-dashed rounded min-w-450px py-3 px-4 me-6 mb-3">
                <div class="d-flex align-items-center">
                    <div class="fs-4 fw-bold" id="proveedorName">Sub Cliente:</div>
                </div>
                <select class="form-select subcliente d-inline-block" id="id_subcliente" name="id_subcliente">
                    <option value="">Seleccionar subcliente</option>
                </select>
            </div>
        </div>
         <div class="col-12 min-w-450px">
                <div class="border border-gray-300 border-dashed rounded min-w-450px py-3 px-4 me-6 mb-3">
         <div class="row">
                        <div class="col-12 mb-5">
                            <div class="d-flex align-items-center">
                                <div class="fs-4 fw-bold" id="proveedorName">Proveedor:</div>
                            </div>
                            <select class="form-select subcliente d-inline-block" id="id_proveedor" name="id_proveedor">
                                <option value="">Seleccionar proveedor</option>
                                @foreach ($proveedores as $p)

                                    <option value="{{ $p->id }}"
                                       @if ($action == 'editar' && $cotizacion?->empresa_local == $p->id) selected @endif>
                                        {{ $p->nombre }}
                                    </option>
                                @endforeach
                            </select>
                           </div>

                    </div>
                    </div>
                    </div>
                     <div class="col-12 min-w-450px">
                <div class="border border-gray-300 border-dashed rounded min-w-450px py-3 px-4 me-6 mb-3">


                    <div class="row">
                        <div class="col-12 mb-5">
                            <div class="d-flex align-items-center">
                                <div class="fs-4 fw-bold" id="proveedorName">Transportista:</div>
                            </div>
                            <select class="form-select subcliente d-inline-block" id="id_transportista"
                                name="id_transportista">
                                <option value="">Seleccionar transportista</option>
                                @foreach ($transportista as $tr)
                                    <option value="{{ $tr->id }}"
                                        @if ($action == 'editar' && $cotizacion?->transportista_local == $tr->id) selected @endif>
                                        {{ $tr->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>
            </div>
        @can('mec-elegir-proveedor')

        @endcan
    </div>
</div>

<div class="mb-2"></div>
    <div class="text-muted fs-7">Ingrese los datos del contenedor que se solicitan a continuación</div>
    <div class="mb-2"></div>
    <div class="fv-row row mb-3">
        <div class="col-3">

            <div class="input-group  mb-5">
                <span class="input-group-text" id="basic-addon1">
                    <i class="ki-duotone ki-logistic fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                        <span class="path5"></span>
                        <span class="path6"></span>
                        <span class="path7"></span>
                    </i>
                </span>
                <div class="form-floating">
                    <input type="text" class="form-control" autocomplete="off" id="num_contenedor" placeholder=""
                        @if ($action == 'editar') value="{{ $cotizacion->DocCotizacion->num_contenedor }}" @endif
                        oninput="changeTag('tagContenedor',this.value)" />
                    <label for="num_contenedor" class="text-gray-700">Número de Contenedor</label>
                </div>
            </div>


        </div>

        <div class="col-2">
            <div class="input-group  mb-5">
                <span class="input-group-text" id="basic-addon1">
                    <i class="ki-duotone ki-map fs-1 text-gray-650 active ">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                </span>
                <div class="form-floating">
                    <input @if ($action == 'editar') value="{{ $cotizacion->origen_local }}" @endif type="text"
                        class="form-control" id="origen" autocomplete="off" placeholder="" />
                    <label for="origen" class="text-gray-700">Origen</label>
                </div>
            </div>
        </div>
           <div class="col-2">
            <div class="input-group  mb-5">
                <span class="input-group-text" id="basic-addon1">
                    <i class="ki-duotone ki-route fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                    </i>
                </span>
                <div class="form-floating">
                    <input @if ($action == 'editar') value="{{ $cotizacion->destino_local }}" @endif type="text"
                        class="form-control" id="destino" autocomplete="off" placeholder="" />
                    <label for="destino" class="text-gray-700">Destino</label>
                </div>
            </div>
        </div>
                <div class="col-2">
            <div class="input-group  mb-5">
                <span class="input-group-text" id="basic-addon1">
                    <i class="ki-duotone ki-delivery-3 fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                </span>
                <div class="form-floating">
                    <input @if ($action == 'editar') value="{{ $cotizacion->tamano }}" @endif type="text"
                        class="form-control" autocomplete="off" id="tamano" placeholder=""
                        oninput="allowOnlyDecimals(event)" />
                    <label for="tamano" class="text-gray-700">Tamaño de Contenedor</label>
                </div>
            </div>
        </div>

       <div class="col-2">
    <div class="input-group mb-5">
        <span class="input-group-text">
            <i class="ki-duotone ki-dollar fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </span>

        <div class="form-floating flex-grow-1">
            <select id="estado_contenedor" name="estado_contenedor" class="form-select color-select">
                <option value="">Seleccione...</option>
                            @foreach($opcionesColores as $color)
                                <option value="{{ $color }}"
                                @if ($action == 'editar' && $cotizacion->estado_contenedor == $color) selected @endif>
                                {{ $color }}
                            </option>
                        @endforeach

        </select>
            <label for="estado_contenedor" class="form-label">Estado del Contenedor</label>
        </div>
    </div>

</div>

        <div class="col-4 d-none">
            <div class="input-group mb-5">
                <span class="input-group-text" id="basic-addon1">
                    <i class="ki-duotone ki-delivery-2 fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                        <span class="path5"></span>
                        <span class="path6"></span>
                        <span class="path7"></span>
                        <span class="path8"></span>
                        <span class="path9"></span>
                    </i>
                </span>
                <div class="form-floating">
                    <input type="text" class="form-control" autocomplete="off" value="22"
                        id="peso_reglamentario" placeholder="" oninput="allowOnlyDecimals(event)" />
                    <label for="peso_reglamentario" class="text-gray-700">Peso de Reglamentario</label>
                    <input type="text" class="form-control" autocomplete="off" id="sobrepeso" placeholder=""
                        value="0" oninput="allowOnlyDecimals(event)" />
                    <input type="text" class="form-control" autocomplete="off" id="precio_sobre_peso"
                        placeholder="" value="0" oninput="allowOnlyDecimals(event)" />
                    <input type="text" class="form-control" autocomplete="off" id="precio_tonelada"
                        placeholder="" value="0" oninput="allowOnlyDecimals(event)" />
                </div>
            </div>
        </div>



    </div>
    <div class="fv-row row mb-3">

        <div class="col-3">
            <div class="input-group mb-5">
                <span class="input-group-text" id="basic-addon1">
                    <i class="ki-duotone ki-delivery-2 fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                        <span class="path5"></span>
                        <span class="path6"></span>
                        <span class="path7"></span>
                        <span class="path8"></span>
                        <span class="path9"></span>
                    </i>
                </span>
                <div class="form-floating">
                    <input type="text"
                        @if ($action == 'editar') value="{{ $cotizacion->peso_contenedor }}" @endif
                        class="form-control" autocomplete="off" id="peso_contenedor" placeholder=""
                        oninput="allowOnlyDecimals(event)" />
                    <label for="peso_contenedor" class="text-gray-700">Peso de Contenedor</label>
                </div>
            </div>
        </div>




        <div class="col-2">
            <div class="input-group mb-5">
                <span class="input-group-text" id="basic-addon1">
                    <i class="ki-duotone ki-calendar-8 fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                        <span class="path5"></span>
                        <span class="path6"></span>
                    </i>
                </span>
               <div class="form-floating">
                    <input @if ($action == 'editar') value="{{ $cotizacion->fecha_modulacion_local }}" @endif
                        type="text" class="form-control fechas" autocomplete="off" id="fecha_modulacion"
                        name="fecha_modulacion" placeholder="" oninput="allowOnlyDecimals(event)" />
                    <label for="fecha_modulacion" class="text-gray-700">Fecha Modulación</label>
                </div>
            </div>
        </div>
       <div class="col-2">
            <div class="input-group mb-5">

                <!-- Icono izquierdo -->
                <span class="input-group-text bg-light">
                    <i class="ki-duotone ki-black-right fs-3 text-gray-600"></i>
                </span>

                <!-- Input con floating label -->
                <div class="form-floating flex-grow-1">
                    <input
                        @if ($action == 'editar')
                            value="{{ $cotizacion->cp_pedimento }}"
                        @endif
                        type="text"
                        class="form-control"
                        autocomplete="off"
                        id="num_pedimento"
                        name="num_pedimento"
                        placeholder=""
                    />
                    <label for="num_pedimento" class="text-gray-700">Num. Pedimento</label>
                </div>

            </div>
        </div>
        <div class="col-2">
            <div class="input-group mb-5">
                <span class="input-group-text" id="basic-addon1">
                    <i class="ki-duotone ki-information fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </span>
                @php
                    $valorActual = $action == 'editar' ? $cotizacion->cp_clase_ped : '';
                @endphp
                <div class="form-floating flex-grow-1">
                    <select class="form-control" id="cp_clase_ped" name="mi_select">
                        <option value="">Seleccione una opción</option>
                            @foreach($opciones as $op)
                                <option value="{{ $op }}" {{ $valorActual == $op ? 'selected' : '' }}>
                                {{ $op }}
                            </option>
                        @endforeach
                    </select>
                    <label for="mi_select" class="text-gray-700">Tipo</label>
                </div>


            </div>
        </div>
        <div class="col-2 d-none" id="campo_confirmacion_sello">
            <div class="input-group mb-5">
                <span class="input-group-text">
                    <i class="ki-duotone ki-shield-tick fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </span>

                <div class="form-floating flex-grow-1 d-flex align-items-center ps-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox"
                            id="confirmacion_sello"
                            name="confirmacion_sello"
                           {{ $action == 'editar' && $cotizacion->confirmacion_sello ? 'checked' : ''}} >
                        <label class="form-check-label ms-2 text-gray-700" for="confirmacion_sello">
                            Tiene sello validado
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-2 d-none" id="campo_nuevo_sello">
            <div class="input-group mb-5">
                <span class="input-group-text">
                    <i class="ki-duotone ki-lock fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </span>

                <div class="form-floating flex-grow-1">
                   <input type="hidden" name="nuevo_sello" id="nuevo_sello" value="1">
                    <label class="text-gray-700">Nuevo sello</label>
                </div>
            </div>
        </div>



    </div>
     <div class="fv-row row mb-3"> <!--fila 3 -->
        <div class="col-3">
            <div class="input-group mb-5">
               <span class="input-group-text" id="basic-addon1">
                    <i class="ki-duotone ki-dollar fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </span>

                <div class="form-floating flex-grow-1">
                      <input type="text" id="Costomaniobra" name="Costomaniobra" class="form-control fw-bold"
                        @if ($action == 'editar')
                            value="{{ $cotizacion->costo_maniobra_local }}"
                        @endif
                        oninput="allowOnlyDecimals(event)" >
                    <label for="Costomaniobra" class="text-gray-700">Costo Maniobra</label>
                </div>
            </div>
        </div>
        <div class="col-8">
            <div class="input-group mb-5">
                <span class="input-group-text">
                    <i class="ki-duotone ki-notepad fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </span>

                <div class="form-floating flex-grow-1">
                    <textarea class="form-control" id="observaciones" name="observaciones"
                       >{{ old('observaciones', $cotizacion->observaciones ?? '') }}</textarea>
                    <label for="observaciones" class="text-gray-700">Observaciones</label>
                </div>
            </div>
        </div>
     </div>
    <input type="hidden" value="MEC-local" id="origen_captura" name="origen_captura" />

<div class="separator my-5"></div>
<h5 class="text-gray-700 mb-3">⛴️ Estadía y Pernocta en Puerto</h5>

<div class="container mt-3">
  <div class="card shadow-sm p-3">
    <div class="row mb-2">
      <div class="col-md-4">
        <label>Estadía ($)</label>
        <input type="number" id="tarifa_estadia" name="tarifa_estadia" class="form-control"

          @if ($action == 'editar')
                            value="{{ $cotizacion->tarifa_estadia }}"
                        @endif
                        >
      </div>
      <div class="col-md-4">
        <label>Días</label>
        <input type="number" id="dias_estadia" name="dias_estadia" class="form-control"
          @if ($action == 'editar')
                            value="{{ $cotizacion->dias_estadia }}"
                        @endif
                        >
      </div>
      <div class="col-md-4">
        <label>Total Estadia ($)</label>
        <input type="text" id="total_estadia" name="total_estadia" class="form-control fw-bold" readonly
          @if ($action == 'editar')
                            value="{{ $cotizacion->total_estadia }}"
                        @endif
                        >
      </div>
    </div>

    <div class="row mb-2">
      <div class="col-md-4">
        <label>Pernocta ($)</label>
        <input type="number" id="tarifa_pernocta" name="tarifa_pernocta" class="form-control"
          @if ($action == 'editar')
                            value="{{ $cotizacion->tarifa_pernocta }}"
                        @endif
                        >
      </div>
      <div class="col-md-4">
        <label>Noches</label>
        <input type="number" id="dias_pernocta" name="dias_pernocta" class="form-control"
          @if ($action == 'editar')
                            value="{{ $cotizacion->dias_pernocta }}"
                        @endif
                        >
      </div>
      <div class="col-md-4">
        <label>Total Pernocta ($)</label>
        <input type="text" id="total_pernocta" name="total_pernocta" class="form-control fw-bold" readonly
          @if ($action == 'editar')
                            value="{{ $cotizacion->total_pernocta }}"
                        @endif
                        >
      </div>
    </div>



    <!-- 🔹 Fila combinada para TOTAL GENERAL -->
    <div class="row mt-3 border-top pt-3 bg-light rounded">
      <div class="col-12 text-center">
        <label class="fw-bold text-success fs-5">TOTAL GENERAL ($)</label>
        <input type="text" id="total_general" name="total_general"
        class="form-control d-inline-block w-auto text-center fw-bold fs-5 border-success"
          @if ($action == 'editar')
                            value="{{ $cotizacion->total_general }}"
                        @endif
         readonly>
      </div>
    </div>
  </div>
</div>

  {{-- <div class="col-md-3">
        <div class="form-floating">
            <input type="datetime-local" class="form-control" id="fecha_liberacion" name="fecha_liberacion">
           <label for="fecha_liberacion" class="text-gray-700">Fecha Liberación</label>
        </div>
    </div>  --}}
{{-- 🔹 Motivo / Responsable / Observaciones
<div class="fv-row row g-3 mb-3">
    <div class="col-md-6">
        <div class="form-floating">
            <textarea class="form-control" id="motivo_demora" name="motivo_demora" rows="2"></textarea>
            <label for="motivo_demora" class="text-gray-700">Motivo de Demora</label>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="responsable" name="responsable"
                placeholder="Nombre del responsable o burrero local">
            <label for="responsable" class="text-gray-700">Responsable (Burrero Local)</label>
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-floating">
            <textarea class="form-control" id="observaciones" name="observaciones" rows="2"></textarea>
            <label for="observaciones" class="text-gray-700">Observaciones</label>
        </div>
    </div>
</div> --}}
