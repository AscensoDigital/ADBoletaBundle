<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 15-10-15
 * Time: 8:52
 */

namespace AscensoDigital\BoletaBundle\Util;


class BoletaMailAnulada
{
    private static $msg;
    private static $rutEmisor;
    private static $numeroBoleta;
    private static $fechaAnulacion;
    private static $razonSocial;

    public static function loadMsg($msg){
        self::$msg=$msg;
        self::$fechaAnulacion=null;
        self::$numeroBoleta=null;
        self::$rutEmisor=null;
    }

    public static function getFechaAnulacion() {
        if(is_null(self::$fechaAnulacion)){
            $init = strpos(self::$msg, 'con fecha ') + strlen('con fecha ');
            $largo = 10;
            self::$fechaAnulacion=substr(self::$msg,$init,$largo);
        }
        return self::$fechaAnulacion;
    }

    public static function getFechaAnulacionEstandar() {
        $tmpD=  explode('/', self::getFechaAnulacion());
        return isset($tmpD[2]) ? $tmpD[2].'-'.$tmpD[1].'-'.$tmpD[0] : null;
    }

    public static function getNumeroBoleta() {
        if(is_null(self::$numeroBoleta)){
            $i=strpos(self::$msg,'Electronica N° ');
            if($i===false){
                $i=strpos(self::$msg, 'Electronica NÂ° ');
                if($i!=false){
                    $init = $i + strlen('Electronica NÂ° ');
                }
            }
            else{
                $init =$i+strlen('Electronica N° ');
            }
            $largo=5;
            self::$numeroBoleta=isset($init) ? intval(substr(self::$msg,$init,$largo)) : null;
        }
        return self::$numeroBoleta;
    }

    public static function getRazonSocial(){
        if(is_null(self::$razonSocial)) {
            $i=strpos(self::$msg,'receptor, ');
            if(false!==$i){
                $init = $i + strlen('receptor, ');
                $final=strpos(self::$msg,',',$init+1);
                self::$razonSocial=substr(self::$msg,$init,$final-$init);
            }
        }
        return self::$razonSocial;
    }

    public static function getRutEmisor(){
        if(is_null(self::$rutEmisor)) {
            $i=strpos(self::$msg, 'Rut N° ');
            if($i===false){
                $i=strpos(self::$msg, 'Rut NÂ° ');
                if($i!=false){
                    $init = $i + strlen('Rut NÂ° ');
                }
            }
            else {
                $init = $i + strlen('Rut N° ');
            }
            $largo = 10;
            self::$rutEmisor=isset($init) ? trim(str_replace(',','',substr(self::$msg,$init,$largo))): null;
        }
        return self::$rutEmisor;
    }

    public static function getUsuarioId(){
        return intval(self::getRutEmisor());
    }
}