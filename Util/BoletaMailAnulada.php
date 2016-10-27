<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 15-10-15
 * Time: 8:52
 */

namespace AscensoDigital\BoletaBundle\Util;


class BoletaMailAnulada extends BoletaMailEmision
{
    protected static $fechaAnulacion;

    public static function loadMsg($msg){
        parent::loadMsg($msg);
        self::$fechaAnulacion=null;

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
}