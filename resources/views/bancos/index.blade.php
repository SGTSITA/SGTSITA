@extends('layout.main')

@section('title-window')

@endsection

@section('content')
<div class="kt-card rounded-xl mb-4">
        <div class="flex items-center flex-wrap py-3 sm:flex-wrap justify-between grow gap-2 p-5 rtl:[background-position:-30%_41%] [background-position:121%_41%] bg-no-repeat bg-[length:660px_310px] upgrade-bg">
         <div class="flex items-center gap-4">
          <div class="relative size-[50px] shrink-0">
  
           <div class="absolute leading-none ">
            <img src="{{asset('/asset/media/bancos/azteca.jpg')}}" alt="">
           </div>
          </div>
          <div class="flex flex-col gap-1.5">
           <div class="flex items-center flex-wrap gap-2.5">
            <a class="text-base font-medium text-mono hover:text-primary" href="#">
             Catalina Castañeda
            </a>
            <span class="kt-badge kt-badge-sm kt-badge-outline">
             Banco Azteca
            </span>
           </div>
           <div class="text-sm text-foreground">
           Núm Cuenta: 1238469344
            <br>
            Clabe: 072180012384693448
           </div>
          </div>
         </div>
         <div class="flex items-center gap-1.5">
          <button class="kt-btn kt-btn-mono">
           Configuración
          </button>
          <a class="kt-btn kt-btn-ghost" href="#">
           Movimiento bancario
          </a>
         </div>
        </div>
       </div>


        <div class="kt-card mb-4">
         <div class="kt-card-content">
          <div class="flex lg:px-10 py-1.5 gap-2">
           <div class="grid grid-cols-1 place-content-center flex-1 gap-1 text-center">
            <span class="text-mono text-2xl lg:text-2xl leading-none font-semibold">
            $927,986.06
            </span>
            <span class="">
                 <span class="kt-badge rounded-full kt-badge-outline kt-badge-primary gap-1 items-center">
                    <span class="kt-badge-dot size-2.0">
                    </span>
                    Saldo Inicial
                 </span>
            </span>
           </div>
           <span class="not-last:border-e border-e-input my-1">
           </span>
           <div class="grid grid-cols-1 place-content-center flex-1 gap-1 text-center">
            <span class="text-mono text-2xl lg:text-2xl leading-none font-semibold">
             $0.00
            </span>
            <span class="">
                 <span class="kt-badge rounded-full kt-badge-outline kt-badge-danger gap-1 items-center">
                    <span class="kt-badge-dot size-2.0">
                    </span>
                    Ingresos
                 </span>
            </span>
           </div>
           <span class="not-last:border-e border-e-input my-1">
           </span>
           <div class="grid grid-cols-1 place-content-center flex-1 gap-1 text-center">
            <span class="text-mono text-2xl lg:text-2xl sm:text-sm leading-none font-semibold">
             $1,000.00
            </span>
            <span class="">
                 <span class="kt-badge rounded-full kt-badge-outline kt-badge-warning gap-1 items-center">
                    <span class="kt-badge-dot size-2.0">
                    </span>
                    Egresos
                 </span>
            </span>
           </div>
           <span class="not-last:border-e border-e-input my-1">
           </span>
           <div class="grid grid-cols-1 place-content-center flex-1 gap-1 text-center">
            <span class="text-mono text-2xl lg:text-2xl leading-none font-semibold">
            $927,985.06
            </span>
            <span class="">
                 <span class="kt-badge rounded-full kt-badge-outline kt-badge-success gap-1 items-center">
                    <span class="kt-badge-dot size-2.0">
                    </span>
                    Saldo Final
                 </span>
            </span>
           </div>
           <span class="not-last:border-e border-e-input my-1">
           </span>
          </div>
         </div>
        </div>

        <div class="mb-4" id="example"></div>


@endsection

@push('custom')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable/styles/handsontable.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable/styles/ht-theme-main.min.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded',()=>{
        const alto = window.innerHeight;
        const container = document.querySelector('#example');

const hot = new Handsontable(container, {
  // theme name with obligatory ht-theme-* prefix
  themeName: 'ht-theme-main-dark-auto',
  colHeaders: ['FECHA','DESCRIPCIÓN','REFERENCIA',  'INGRESOS', 'EGRESOS'],
  data: [
    
    ['25 AGO 2025', 'LIQUIDACIÓN OPERADOR - RIGOBERTO', 'NOMINA 0200', 0, 250],
    ['30 SEPT 2025', 'DINERO PARA VIAJE - ALIMENTOS', '#MGBU3122560', 0, 250],
    ['10 OCT 2025', 'GASTO DE VIAJE - DIESEL', '#MEDU4532895', 0, 500]
  ],
  rowHeaders: true,
  columns:[{readOnly:false},{readOnly:false },{readOnly:false},
    {
      readOnly:false,
      type: 'numeric',
      numericFormat: {
        pattern: '$ 0,0.00',
        culture: 'en-US'
      }
    },
    {
      readOnly:false,
      type: 'numeric',
      numericFormat: {
        pattern: '$ 0,0.00',
        culture: 'en-US'
      }
    }
],
  height: 'auto',
  stretchH: 'all',
  autoWrapRow: true,
  autoWrapCol: true,
  licenseKey: 'non-commercial-and-evaluation' // for non-commercial use only
});
    })
   

</script>
@endpush
