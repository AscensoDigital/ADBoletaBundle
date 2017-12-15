<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 15-12-17
 * Time: 11:36
 */

namespace AscensoDigital\BoletaBundle\Util;


class BoletaXml implements BoletaInterface
{
    private static $contenido;

    public static function load($pathXml) {
        self::$contenido = simplexml_load_file($pathXml);
    }

    public static function getBoletaNumero()
    {
        return self::$contenido->numeroBoleta;
    }

    public static function getRutEmisorCompleto()
    {
        return implode('-',[substr(self::$contenido->rutEmisor,0,strlen(self::$contenido->rutEmisor)-1), substr(self::$contenido->rutEmisor,-1)]);
    }

    public static function getRutDestinatarioCompleto()
    {
        return implode('-',[substr(self::$contenido->rutReceptor,0,strlen(self::$contenido->rutReceptor)-1), substr(self::$contenido->rutReceptor,-1)]);
    }

    public static function getGlosaCompleta()
    {
        $glosa=array();
        foreach (self::$contenido->prestacionServicios->item as $item) {
            $glosa[]=$item->descripcionLinea;
        }
        return $glosa;
    }

    public static function getMontoBruto()
    {
        return self::$contenido->totalHonorarios;
    }

    public static function getMontoImpuesto()
    {
        return self::$contenido->impuestoHonorarios;
    }

    public static function getMontoLiquido()
    {
        return self::$contenido->liquidoHonorarios;
    }

    public static function getFechaBoleta()
    {
        $fecha=self::$contenido->fechaBoleta;
        return implode('-',[substr($fecha,0,4),substr($fecha,4,2),substr($fecha,6,2)]);
    }

    public static function getFechaEmision()
    {
        return self::$contenido->FechaGen;
    }
}