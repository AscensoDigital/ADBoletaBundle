<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 21-11-15
 * Time: 15:47
 */

namespace AscensoDigital\BoletaBundle\Util;


class BoletaMailVca extends BoletaMailAnulada {

    public static function loadMsg($msg){
        parent::loadMsg($msg);
    }

    public static function getRazonSocial(){
        if(is_null(self::$razonSocial)) {
            $i=strpos(self::$msg,'receptor  ');
            if(false!==$i){
                $init = $i + strlen('receptor  ');
                $final=strpos(self::$msg,',',$init+1);
                self::$razonSocial=substr(self::$msg,$init,$final-$init);
            }
        }
        return self::$razonSocial;
    }
}