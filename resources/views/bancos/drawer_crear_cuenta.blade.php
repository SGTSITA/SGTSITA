<div
  class="hidden kt-drawer kt-drawer-end card flex flex-col w-[600px] max-w-[90%] top-5 bottom-5 end-5 rounded-xl border border-border shadow-lg "
  data-kt-drawer="true"
  data-kt-drawer-container="body"
  id="drawers_shop_cart"
>
  <!-- Header -->
  <div class="kt-card-header px-6 py-4 border-b border-border flex items-center justify-between">
    <h3 class="kt-card-title text-lg font-semibold">
      Agregar Cuenta Bancaria
    </h3>
    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost shrink-0" data-kt-drawer-dismiss="true">
      <i class="ki-filled ki-cross text-base"></i>
    </button>
  </div>

  <!-- Content -->
  <div class="kt-card-content p-6 space-y-8 overflow-y-auto flex-1">
    <!-- Titular -->
    <div class="flex flex-wrap justify-between ">
      <div class="flex flex-col">
        <div class="text-base font-semibold text-mono hover:text-primary">
          Titular
        </div>
        <span class="text-secondary-foreground text-sm">
          Persona o empresa propietaria de la cuenta
        </span>
      </div>
      <label class="kt-input sm:max-w-full xl:max-w-96 w-full">
        <i class="ki-solid ki-user"></i>
        <input type="text" name="titular" placeholder="Ej. Juan Pérez" autocomplete="off">
      </label>
    </div>

    <div class="border-b border-border my-6 py-2"></div>

    <!-- Banco -->
    <div class="flex flex-wrap justify-between gap-4 py-2">
      <div class="flex flex-col">
        <div class="text-base font-semibold text-mono hover:text-primary">
          Banco
        </div>
        <span class="text-secondary-foreground text-gray-500 text-sm">
          Institución financiera correspondiente
        </span>
      </div>
      <label class="kt-input sm:max-w-full xl:max-w-96 w-full">
        <i class="ki-solid ki-bank"></i>
        <input type="text" name="banco" placeholder="Ej. BBVA, Banamex, Santander" autocomplete="off">
      </label>
    </div>

    <div class="border-b border-border my-6 py-2"></div>

    <!-- Número de Cuenta -->
    <div class="flex flex-wrap justify-between gap-4 py-2">
      <div class="flex flex-col">
        <div class="text-base font-semibold text-mono hover:text-primary">
          Número de Cuenta
        </div>
        <span class="text-secondary-foreground text-sm">
          Identificador de la cuenta bancaria
        </span>
      </div>
      <label class="kt-input sm:max-w-full xl:max-w-96 w-full">
        <i class="ki-solid ki-hash"></i>
        <input type="text" name="num_cuenta" placeholder="Ej. 1234567890" autocomplete="off">
      </label>
    </div>

    <div class="border-b border-border my-6 py-2"></div>

    <!-- CLABE -->
    <div class="flex flex-wrap justify-between gap-4 py-2">
      <div class="flex flex-col">
        <div class="text-base font-semibold text-mono hover:text-primary">
          CLABE Interbancaria
        </div>
        <span class="text-secondary-foreground text-sm">
          Clave de 18 dígitos para transferencias
        </span>
      </div>
      <label class="kt-input sm:max-w-full xl:max-w-96 w-full">
        <i class="ki-solid ki-code"></i>
        <input type="text" name="clabe" maxlength="18" placeholder="Ej. 002910123456789012" autocomplete="off">
      </label>
    </div>

    <div class="border-b border-border my-6 py-2"></div>

    <!-- Saldo inicial -->
    <div class="flex flex-wrap justify-between gap-4 py-2">
      <div class="flex flex-col">
        <div class="text-base font-semibold text-mono hover:text-primary">
          Saldo Inicial
        </div>
        <span class="text-secondary-foreground text-sm">
          Monto con el que inicia la cuenta
        </span>
      </div>
      <label class="kt-input sm:max-w-full xl:max-w-96 w-full">
        <i class="ki-solid ki-dollar"></i>
        <input type="number" name="saldo_inicial" placeholder="Ej. 15000.00" step="0.01" autocomplete="off">
      </label>
    </div>
  </div>

  <!-- Footer -->
  <div class="kt-card-footer p-5 border-b border-border flex gap-3 bg-background">
    <button class="kt-btn kt-btn-outline flex-1">
      Borrar campos
    </button>
    <button class="kt-btn kt-btn-primary flex-1">
      <i class="ki-filled ki-handcart"></i>
      Crear Cuenta
    </button>
  </div>
</div>
