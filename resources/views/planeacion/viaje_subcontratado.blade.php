<div class="row mt-4">
  <div class="col-lg-5 col-12">
    <h6 class="mb-0">Proveedor</h6>
    <p class="text-sm">Seleccione el proveedor que transportará el contenedor.</p>
    <div class="border-dashed border-1 border-secondary border-radius-md p-3">
      <p class="text-xs mb-2">
        <span class="font-weight-bolder">Proveedor</span>
      </p>
      <div class="d-flex align-items-center">
        <select class="form-control" name="cmbProveedor" id="cmbProveedor"> 
            @foreach ($proveedores as $item) 
            <option value="{{$item->id}}">{{$item->nombre}}</option> 
            @endforeach 
        </select>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <h6 class="mt-3">Costos</h6>
  <p class="text-sm">Precios de costo del viaje.</p>
  <div class="col-12 col-md-4  mt-2 text-start">
    <label>Costo del viaje</label>
    <input class="form-control fieldsCalculo moneyformat" name="precio_proveedor" id="precio_proveedor" autocomplete="off" placeholder="" oninput="allowOnlyDecimals(event)" type="text">
  </div>
  <div class="col-12 col-md-4  mt-2 text-start">
    <label>Burreo</label>
    <input class="form-control fieldsCalculo moneyformat" name="burreo_proveedor" id="burreo_proveedor" autocomplete="off" placeholder="" oninput="allowOnlyDecimals(event)" type="text">
  </div>
  <div class="col-12 col-md-4  mt-2 text-start">
    <label>Maniobra</label>
    <input class="form-control fieldsCalculo moneyformat" name="maniobra_proveedor" id="maniobra_proveedor" autocomplete="off" placeholder="" oninput="allowOnlyDecimals(event)" type="text">
  </div>
  <div class="col-12 col-md-4  mt-2 text-start">
    <label>Estadía</label>
    <input class="form-control fieldsCalculo moneyformat" name="estadia_proveedor" id="estadia_proveedor" autocomplete="off" placeholder="" oninput="allowOnlyDecimals(event)" type="text">
  </div>
  <div class="col-12 col-md-4  mt-2 text-start">
    <label>Otros</label>
    <input class="form-control fieldsCalculo moneyformat" name="otro_proveedor" id="otro_proveedor" autocomplete="off" placeholder="" oninput="allowOnlyDecimals(event)" type="text">
  </div>
</div>
<div class="row">
  <div class="col-12 col-md-4  mt-2 text-start">
    <label>IVA</label>
    <input class="form-control fieldsCalculo moneyformat" name="iva_proveedor" id="iva_proveedor" autocomplete="off" placeholder="" oninput="allowOnlyDecimals(event)" type="text">
  </div>
  <div class="col-12 col-md-4  mt-2 text-start">
    <label>Retención</label>
    <input class="form-control fieldsCalculo moneyformat" name="retencion_proveedor" id="retencion_proveedor" autocomplete="off" placeholder="" oninput="allowOnlyDecimals(event)" type="text">
  </div>
</div>
<div class="row">
  <div class="col-12 col-md-4  mt-2 text-start">
    <label>Base 1</label>
    <input class="form-control fieldsCalculo moneyformat" name="base_factura" id="base_factura" autocomplete="off" placeholder="" oninput="allowOnlyDecimals(event)" type="text">
  </div>
  <div class="col-12 col-md-4  mt-2 text-start">
    <label>Base 2</label>
    <input class="form-control fieldsCalculo moneyformat" name="base_taref" id="base_taref" autocomplete="off" placeholder="" oninput="allowOnlyDecimals(event)" type="text">
  </div>
</div>
<div class="row">
  <div class="col-12 col-md-4  mt-2 text-start">
    <label>Sobrepeso</label>
    <input class="form-control fieldsCalculo moneyformat" name="sobrepeso_proveedor" id="sobrepeso_proveedor" autocomplete="off" placeholder="" oninput="allowOnlyDecimals(event)" type="text">
  </div>
  <div class="col-12 col-md-4  mt-2 text-start">
    <label>Precio sobrepeso</label>
    <input class="form-control fieldsCalculo moneyformat" name="cantidad_sobrepeso_proveedor" id="cantidad_sobrepeso_proveedor" autocomplete="off" placeholder="" oninput="allowOnlyDecimals(event)" type="text">
  </div>
  <div class="col-12 col-md-4  mt-2 text-start">
    <label>Total</label>
    <input class="form-control fieldsCalculo moneyformat" name="total_proveedor" id="total_proveedor" autocomplete="off" placeholder="" oninput="allowOnlyDecimals(event)" type="text">
  </div>
</div>