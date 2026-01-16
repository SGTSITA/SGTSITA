<?php

namespace App\Http\Controllers;

use App\Models\DocumCotizacionAcceso;
use App\Models\DocumCotizacion;
use App\Models\Cotizaciones;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Traits\CommonTrait;
use Illuminate\Http\Request;
use ZipArchive;
use Illuminate\Support\Facades\File;

class DocsController extends Controller
{
    private function accesoValido($token)
    {
        return DocumCotizacionAcceso::where('token', $token)
            ->where('activo', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    public function formPassword($token)
    {
        $acceso = $this->accesoValido($token);
        if (!$acceso) {
            return redirect()->route('externos.acceso.revocado');

        }

        return view('mep.docs.password', compact('token'));
    }



    public function validarPassword($token)
    {
        $acceso = $this->accesoValido($token);

        if (!$acceso) {
            $titmesage = 'Acceso revocado';
            $messag = 'El acceso a estos documentos ya no es válido.';
            $submessag = 'La contraseña fue revocada o el enlace ha expirado.';
            return redirect()->route('externos.acceso.revocado', compact('titmesage', 'messag', 'submessag'));
        }

        if (!Hash::check(request('password'), $acceso->password_hash)) {
            return back()->withErrors(['password' => 'Contraseña incorrecta']);
        }

        session(['acceso_autorizado_'.$token => true]);

        //dd($acceso);
        $columns = $acceso->shared_files;
        if (is_string($columns)) {
            $columns = json_decode($columns, true);
        }

        //$DocDocumento = DocumCotizacion::with('Cotizacion')->where('id', '=', $acceso->documento_id)->first();

        $DocDocumento = $this->documentosGet($acceso->documento_id);

        $documentos = $this->getFilesByColumns(
            $acceso->documento_id,
            $columns
        );

        //dd($DocDocumento);
        return view("mep.docs.documentos", compact('token', 'documentos', 'DocDocumento'));
    }

    public function documentosGet($documento_id)
    {
        $DocDocumento = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
          ->join('estatus_maniobras as estat', 'estat.id', '=', 'cotizaciones.estatus_maniobra_id')
          ->join('subclientes', 'subclientes.id', '=', 'cotizaciones.sub_cliente_local')
          ->join('clients', 'clients.id', '=', 'subclientes.id_cliente')
          ->join('empresas', 'empresas.id', '=', 'cotizaciones.empresa_local')
          ->join('proveedores', 'proveedores.id', '=', 'cotizaciones.transportista_local')
           ->where('d.id', '=', $documento_id)
          ->whereIn('tipo_viaje_seleccion', ['local', 'local_to_foraneo'])
          ->where('jerarquia', '!=', 'Secundario')
             ->select([
              'cotizaciones.*',

              // docum
              'd.num_contenedor',
              'd.doc_eir',
              'd.doc_ccp',
              'd.boleta_liberacion',
              'd.doda',
              'd.foto_patio',
              'd.boleta_patio',
              'd.terminal',
              'd.num_autorizacion',

              // estatus
              'estat.nombre as estatus_maniobra',

              // NUEVOS
              'empresas.nombre as empresa',
              'proveedores.nombre as proveedor',
              'clients.nombre as cliente',
              'subclientes.nombre as subcliente',
          ])
          ->first();

        if (!$DocDocumento) { //buscar en cotizaciones de manera normal , tipo foraneo
            $DocDocumento = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
            ->join('subclientes', 'subclientes.id', '=', 'cotizaciones.id_subcliente')
            ->join('clients', 'clients.id', '=', 'subclientes.id_cliente')
            ->join('empresas', 'empresas.id', '=', 'cotizaciones.id_empresa')
            ->join('proveedores', 'proveedores.id', '=', 'cotizaciones.id_proveedor')
             ->where('d.id', '=', $documento_id)
            ->whereIn('tipo_viaje_seleccion', ['foraneo', 'foraneo_to_local'])
            ->where('jerarquia', '!=', 'Secundario')
               ->select([
                'cotizaciones.*',

                // docum
                'd.num_contenedor',
                'd.doc_eir',
                'd.doc_ccp',
                'd.boleta_liberacion',
                'd.doda',
                'd.foto_patio',
                'd.boleta_patio',
                'd.terminal',
                'd.num_autorizacion',



                // NUEVOS
                'empresas.nombre as empresa',
                'proveedores.nombre as proveedor',
                'clients.nombre as cliente',
                'subclientes.nombre as subcliente',
            ])
            ->first();



        }

        return $DocDocumento;

    }

    public function documentos($token)
    {
        $acceso = $this->accesoValido($token);

        $acceso->update([
            'last_access_at' => now(),
            'last_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return view('proveedor.documentos', compact('token'));
    }

    public function download($token, $archivo)
    {
        $acceso = $this->accesoValido($token);
        if (!$acceso) {
            $titmesage = 'Acceso revocado';
            $messag = 'El acceso a estos documentos ya no es válido.';
            $submessag = 'La contraseña fue revocada o el enlace ha expirado.';
            return redirect()->route('externos.acceso.revocado', compact('titmesage', 'messag', 'submessag'));
        }

        $file = $archivo;

        // $columns = $acceso->shared_files;
        // if (is_string($columns)) {
        //     $columns = json_decode($columns, true);
        // }

        // if (!in_array($file, $columns)) {
        //     $titmesage = 'Archivo no autorizado';
        //     $messag = 'No tienes permiso para descargar este archivo.';
        //     $submessag = '';
        //     return redirect()->route('externos.acceso.revocado', compact('titmesage', 'messag', 'submessag'));

        // }
        $documento = DocumCotizacion::where('id', '=', $acceso->documento_id)->first();

        $path = public_path("cotizaciones/cotizacion{$documento->id_cotizacion}/{$file}");

        if (!file_exists($path)) {
            $titmesage = 'Archivo no encontrado';
            $messag = 'El archivo que intentas descargar no existe.';
            $submessag = '';
            return redirect()->route('externos.acceso.revocado', compact('titmesage', 'messag', 'submessag'));
        }
        // dd($path);
        return response()->download($path);


        return Storage::download("cotizaciones/$archivo");
    }


    public function downloadZip(Request $request, $token)
    {
        $files = $request->input('files', []);

        //dd($files);
        $acceso = $this->accesoValido($token);
        if (!$acceso) {
            $titmesage = 'Acceso revocado';
            $messag = 'El acceso a estos documentos ya no es válido.';
            $submessag = 'La contraseña fue revocada o el enlace ha expirado.';
            return redirect()->route('externos.acceso.revocado', compact('titmesage', 'messag', 'submessag'));
        }

        $columns =  $files;
        if (is_string($columns)) {
            $columns = json_decode($columns, true);
        }

        $documentoC = DocumCotizacion::where('id', '=', $acceso->documento_id)->first();


        $documentos = $this->getFilesByColumns(
            $acceso->documento_id,
            $columns
        );

        // dd($documentos);

        $zipFileName = 'documentos_' . now()->format('Ymd_His') . '.zip';
        $zipPath = public_path($zipFileName);

        $zip = new \ZipArchive();
        $result = $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        if ($result !== true) {
            dd('error 1', $result);
        }
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($documentos as $doc) {
                $filePath = public_path("cotizaciones/cotizacion{$documentoC->id_cotizacion}/{$doc['filePath']}");
                //$path2 = public_path("cotizaciones/cotizacion{$documentoC->id_cotizacion}/{$doc['filePath']}");
                //dd($filePath, $path2);
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, $doc['secondaryFileName'] . '.' . $doc['fileType']);
                    //dd('added', $filePath);
                }

            }
            $zip->close();
        } else {
            return redirect()->back()->withErrors(['error' => 'No se pudo crear el archivo ZIP.']);
        }

        if (!file_exists($zipPath)) {
            dd('error 2', $zip);
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function validarRevocacion($token)
    {
        return response()->json([
            'activo' => (bool) $this->accesoValido($token)
        ]);
    }
    public function getFilesByColumns(int $documentacionId, array $columns)
    {
        $documentList = [];


        $documentos = DocumCotizacion::where('id', $documentacionId)->first();
        $cotizacion  = Cotizaciones::find($documentos->id_cotizacion);

        //dd($documentos, $cotizacion);

        if (!$documentos || !$cotizacion) {
            return [];
        }

        $columnskey = config('CatAuxiliares.columnsbycode');

        foreach ($columns as $item) {
            $column = $columnskey[$item['fileCode']] ?? null;
            if (!$column) {
                $column = $item['fileCode'];
            }



            //dd($column);

            // buscar en DocumCotizacion
            if (isset($documentos->$column) && $documentos->$column) {
                $file = $documentos->$column;

                $doc = self::fileProperties(
                    $documentos->id_cotizacion,
                    $file,
                    $column
                );

                if (!empty($doc)) {
                    $documentList[] = $doc;
                }
            }

            // buscar en Cotizaciones
            if (isset($cotizacion->$column) && $cotizacion->$column) {
                $file = $cotizacion->$column;

                $doc = self::fileProperties(
                    $documentos->id_cotizacion,
                    $file,
                    $column
                );

                if (!empty($doc)) {
                    $documentList[] = $doc;
                }
            }
        }

        return $documentList;
    }

    public function fileProperties($id, $file, $title)
    {
        $columnsbycode = config('CatAuxiliares.columnsbycode');
        $clave = array_search($title, $columnsbycode, true);
        $path = public_path('cotizaciones/cotizacion'.$id.'/'.$file);

        if (File::exists($path)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE); // Abrir la base de datos de tipos MIME
            $mimeType = finfo_file($finfo, $path);
            finfo_close($finfo);

            return [
                "filePath" => $file,
                'fileName' => $title,
                "folder" => $id,
                'secondaryFileName' => str_replace('-', ' ', $clave),
                "fileDate" => CommonTrait::obtenerFechaEnLetra(date("Y-m-d", filemtime($path))),
                "fileSize" => CommonTrait::calculateFileSize(filesize($path)),
                "fileSizeBytes" => (filesize($path)),
                "fileType" => pathinfo($path, PATHINFO_EXTENSION),
                "mimeType" => $mimeType,
                "identifier" => $id,
                "fileCode" => iconv('UTF-8', 'ASCII//TRANSLIT', str_replace(' ', '-', $title))
                ];
            //iconv('UTF-8', 'ASCII//TRANSLIT', $cadena);
        } else {
            return [];
        }
    }

    public function accesoRevocado()
    {
        return view('mep.docs.revocado');
    }

}
