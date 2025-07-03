<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WhatsAppController extends Controller
{

    public static function sendText($phone, $message){
      $endpoint = 'https://graph.facebook.com/v22.0/708560132336379/messages';
      $token = ENV('WHATSAPP_TOKEN');
		
		  $Header = array('Content-Type:application/json',
						  'Authorization:Bearer '.$token,
						 );

     // $phone = (ENV('APP_DEBUG') == false) ? '52'.$phone : "529931362770";
      $dataObject = '{"to":"52'.$phone.'","messaging_product":"whatsapp", "recipient_type": "individual", "type":"text", "text":{"body": "'.$message.'"}}'; 
      $API = curl_init();
      curl_setopt($API, CURLOPT_URL,$endpoint);
      curl_setopt($API, CURLOPT_HTTPHEADER,$Header);
      curl_setopt($API, CURLOPT_CUSTOMREQUEST,'POST');
      curl_setopt($API, CURLOPT_POSTFIELDS,$dataObject);
      curl_setopt($API, CURLOPT_RETURNTRANSFER,true);

      $Result = curl_exec($API);
      $httpCode = curl_getinfo($API,CURLINFO_HTTP_CODE);

          //Evaluamos la respuesta recibida
		  $respuestaAPI = json_decode($Result);
          switch ($httpCode) {
            case 200:              
              $response =  ["code" => $httpCode,"respuesta" => '',"status" => "Mensaje enviado"];
              break;
              default:
              $response =  ["code" => $httpCode,"respuesta" => $respuestaAPI->error->message, "status" => "Validacion incorrecta - Registros no encontrados"];
              break;
          }

          curl_close($API);
		  return json_encode($response);
    }

    //Este metodo recibe la data lista para ser enviada, lo que permite que se envie todo tipo de mensaje de WhatsApp
    public static function sendWhatsAppMessage($data){
        
        try {
            $endpoint = 'https://graph.facebook.com/v22.0/708560132336379/messages';
            $token = env('WHATSAPP_TOKEN');

            $headers = [
                'Content-Type:application/json',
                'Authorization:Bearer ' . $token,
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($result === false) {
                throw new \Exception('Error al ejecutar cURL: ' . $curlError);
            }

            $respuestaAPI = json_decode($result);

            if ($httpCode === 200) {
                return response()->json([
                    "code" => $httpCode,
                    "respuesta" => '',
                    "status" => "Mensaje enviado"
                ]);
            } else {
                $mensajeError = $respuestaAPI->error->message ?? 'Error desconocido';
                return response()->json([
                    "code" => $httpCode,
                    "respuesta" => $mensajeError,
                    "status" => "Validación incorrecta - Registros no encontrados"
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                "code" => 500,
                "respuesta" => $e->getMessage(),
                "status" => "Error en el servidor"
            ]);
        }
    }

    public static function sendFile($phone, $typeFile, $urlFile, $caption, $titleFile)
    {
        try {
            $endpoint = 'https://graph.facebook.com/v22.0/708560132336379/messages';
            $token = env('WHATSAPP_TOKEN');

            $headers = [
                'Content-Type:application/json',
                'Authorization:Bearer ' . $token,
            ];

            // Si no está en debug, usar el teléfono real
        //  $phone = (env('APP_DEBUG') === false) ? '52' . $phone : '529931362770';

            $data = [
                "to" => "52$phone",
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "type" => $typeFile,
                $typeFile => [
                    "link" => $urlFile,
                    "caption" => $caption,
                    "filename" => $titleFile
                ]
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($result === false) {
                throw new \Exception('Error al ejecutar cURL: ' . $curlError);
            }

            $respuestaAPI = json_decode($result);

            if ($httpCode === 200) {
                return response()->json([
                    "code" => $httpCode,
                    "respuesta" => '',
                    "status" => "Mensaje enviado"
                ]);
            } else {
                $mensajeError = $respuestaAPI->error->message ?? 'Error desconocido';
                return response()->json([
                    "code" => $httpCode,
                    "respuesta" => $mensajeError,
                    "status" => "Validación incorrecta - Registros no encontrados"
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                "code" => 500,
                "respuesta" => $e->getMessage(),
                "status" => "Error en el servidor"
            ]);
        }
    }

    // Verificación inicial del webhook (GET)
    public function verifyWebHook(Request $request)
    {
        $verify_token = env('WHATSAPP_WEBHOOK_TOKEN');

        if (
            $request->get('hub_mode') === 'subscribe' &&
            $request->get('hub_verify_token') === $verify_token
        ) {
            return response($request->get('hub_challenge'), 200);
        }

        return response('Token inválido', 403);
    }

    public function webHook(Request $request){
        // Guarda el payload en el log
        Log::info('Webhook recibido:', $request->all());

        // Puedes acceder a datos específicos, por ejemplo:
        $event = $request->input('event');
        $data = $request->input('data');

        // Procesa según el tipo de evento
        //if ($event === 'envio.completado') {
            // Tu lógica aquí...
        //}

        return response()->json(['status' => 'ok'], 200);
    }

}
