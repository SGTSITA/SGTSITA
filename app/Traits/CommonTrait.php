<?php

namespace App\Traits;
use Carbon\Carbon;

trait CommonTrait
{
    public static function completeZero($number){
        $decimalDot = strpos('.',$number);
        $Len = strlen($number);
        $format = substr($number,$decimalDot+1,$Len);
        $LenFormat = 8 - strlen($format);
    
        $addZeros = '';
        for($x=0;$x<=$LenFormat;$x++){
            $addZeros .= '0';
        }
    
        return $addZeros.$number;
    
    }
    
    public static function randomPassword($Tam, $caracteres = null){
            if ($caracteres == null) "JwbOCFRvB3Vh2MfdWeT9Pu71xHaQ4cYqjk5X0rnGyzSUDstlmIi6Z8oEAgNKLp";
            $cadena = ""; //variable para almacenar la cadena generada
            for($i=1;$i<$Tam;$i++)
            {
             $cadena .= substr($caracteres,rand(0,strlen($caracteres)-1),1); /*Extraemos 1 caracter de los caracteres
            entre el rango 0 a Numero de letras que tiene la cadena */
            }
            return $cadena;
    }

   
    public static function calculateFileSize($bytes) {
        if ($bytes == 0) {
            return "0 B";
        }
    
        $unidad = ["B", "KB", "MB", "GB", "TB"];
        $exp = floor(log($bytes, 1024)); // Calcula la potencia de 1024
        $tamanio = $bytes / pow(1024, $exp); // Convierte el tamaño según la potencia
    
        // Redondea a 2 decimales y añade la unidad correspondiente
        return round($tamanio, 2) . ' ' . $unidad[$exp];
    }
    public static function obtenerFechaEnLetra($fecha){
        $dia= self::conocerDiaSemanaFecha($fecha);
        $num = date("j", strtotime($fecha));
        $anno = date("Y", strtotime($fecha));
        $mes = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');
        $mes = $mes[(date('m', strtotime($fecha))*1)-1];
        return $dia.', '.$num.' de '.$mes.' del '.$anno;
    }
    
    /*=================================================================*/
    
    public static function conocerDiaSemanaFecha($fecha) {
        $dias = array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado');
        $dia = $dias[date('w', strtotime($fecha))];
        return $dia;
    }
}
