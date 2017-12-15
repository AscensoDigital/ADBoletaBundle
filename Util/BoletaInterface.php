<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 15-12-17
 * Time: 11:31
 */

namespace AscensoDigital\BoletaBundle\Util;


interface BoletaInterface
{
    public static function load($path);
    public static function getBoletaNumero();
    public static function getRutEmisorCompleto();
    public static function getRutDestinatarioCompleto();
    public static function getGlosaCompleta();
    public static function getMontoBruto();
    public static function getMontoImpuesto();
    public static function getMontoLiquido();
    public static function getFechaBoleta();
    public static function getFechaEmision();
}