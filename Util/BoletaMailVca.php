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
}