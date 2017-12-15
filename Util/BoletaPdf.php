<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 15-12-17
 * Time: 11:36
 */

namespace AscensoDigital\BoletaBundle\Util;


use AscensoDigital\BoletaBundle\Service\XpdfService;

class BoletaPdf implements BoletaInterface
{
    private static $contenido;
    private static $arrCont;

    /**
     * @var XpdfService
     */
    private static $xpdf;

    public static function setXpdf($xpdf) {
        self::$xpdf=$xpdf;
    }

    public static function load($pathPdf) {
        self::$contenido = self::$xpdf->getText($pathPdf);
        self::$arrCont = explode("\n", self::$contenido);
    }

    private static function find($context) {
        foreach (self::$arrCont as $key => $fila) {
            if(strrpos($fila, $context)!==false) {
                return $key;
            }
        }
        return null;
    }

    public static function getBoletaNumero() {
        $i=is_null(self::find('N °')) ? self::find('N°') : self::find('N °');
        return intval(substr(self::$arrCont[$i], strrpos(self::$arrCont[$i]," ")+1));
    }

    public static function getRutEmisorCompleto() {
        $i=self::find('RUT:');
        return str_replace(' ', '',substr(self::$arrCont[$i], strrpos(self::$arrCont[$i],": ")+2));

    }

    public static function getRutDestinatarioCompleto() {
        $i=self::find('Señor(es):');
        return str_replace(' ', '',substr(self::$arrCont[$i], strrpos(self::$arrCont[$i],": ")+2));

    }

    public static function getGlosaCompleta() {
        $glosa=array();
        $i=self::find('Por atención profesional:')+1;
        do {
            $glosa[]=substr(self::$arrCont[$i], 0,strrpos(self::$arrCont[$i],' '));
            $i++;
        } while (isset(self::$arrCont[$i]) and strrpos(self::$arrCont[$i], 'Total Honorarios $: ')===false);
        return $glosa;
    }

    public static function getMontoBruto() {
        $i=self::find('Total Honorarios $:');
        return intval(str_replace('.', '',substr(self::$arrCont[$i], strrpos(self::$arrCont[$i],'Total Honorarios $: ')+strlen('Total Honorarios $: '))));
    }

    public static function getMontoImpuesto() {
        $i=self::find('Retenido:');
        return ($i===false or is_null($i)) ? 0 : intval(str_replace('.', '',substr(self::$arrCont[$i], strrpos(self::$arrCont[$i],'Retenido: ')+strlen('Retenido: '))));

    }

    public static function getMontoLiquido() {
        $i=self::find('Total:');
        return ($i===false or is_null($i)) ? 0 : intval(str_replace('.', '',substr(self::$arrCont[$i], strrpos(self::$arrCont[$i],'Total: ')+strlen('Total: '))));
    }

    public static function getFechaBoleta() {
        $i=self::find('Fecha:');
        return substr(self::$arrCont[$i], strrpos(self::$arrCont[$i],'Fecha: ')+strlen('Fecha: '));
    }

    public static function getFechaEmision() {
        $i=self::find('Fecha / Hora Emisi');
        return trim(substr(self::$arrCont[$i], strrpos(self::$arrCont[$i],'Fecha / Hora Emisi')+strlen('Fecha / Hora Emisi')+4));
    }
}