<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 15-10-15
 * Time: 0:14
 */

namespace AscensoDigital\BoletaBundle\Util;


class BoletaMailEmision
{
    protected static $msg;
    protected static $rutEmisor;
    protected static $numeroBoleta;
    protected static $fechaEnvio;
    protected static $fechaBoleta;
    protected static $razonSocial;

    public static function loadMsg($msg){
        self::$msg=$msg;
        self::$fechaBoleta=null;
        self::$fechaEnvio=null;
        self::$numeroBoleta=null;
        self::$rutEmisor=null;
        self::$razonSocial=null;
    }

    public static function getFechaBoleta() {
        if(is_null(self::$fechaBoleta)){
            $init = strrpos(self::$msg, 'de fecha ') + strlen('de fecha ');
            $largo = 10;
            self::$fechaBoleta=substr(self::$msg,$init,$largo);
        }
        return self::$fechaBoleta;
    }

    public static function getFechaBoletaEstandar() {
        $tmpD=  explode('/', self::getFechaBoleta());
        return isset($tmpD[2]) ? $tmpD[2].'-'.$tmpD[1].'-'.$tmpD[0].' 00:00:00' : null;
    }

    public static function getFechaEnvio() {
        if(is_null(self::$fechaEnvio)){
            $init = strrpos(self::$msg, 'con fecha ') + strlen('con fecha ');
            $largo = 10;
            self::$fechaEnvio=substr(self::$msg,$init,$largo);
        }
        return self::$fechaEnvio;
    }

    public static function getFechaEnvioEstandar() {
        $tmpD=  explode('/', self::getFechaEnvio());
        return isset($tmpD[2]) ? $tmpD[2].'-'.$tmpD[1].'-'.$tmpD[0].' 00:00:00' : null;
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
            $i=strpos(self::$msg,'Estimado Contribuyente: ');
            if(false!==$i){
                $init = $i + strlen('Estimado Contribuyente: ') +2 ;
                $final=strpos(self::$msg,"\n",$init+1)-2;
                self::$razonSocial=substr(self::$msg,$init,$final-$init);
            }
        }
        return self::$razonSocial;
    }

    public static function getRutEmisor(){
        if(is_null(self::$rutEmisor)) {
            $i=strpos(self::$msg, 'RUT N° ');
            if($i===false){
                $i=strpos(self::$msg, 'RUT NÂ° ');
                if($i!=false){
                    $init = $i + strlen('RUT NÂ° ');
                }
            }
            else {
                $init = $i + strlen('RUT N° ');
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