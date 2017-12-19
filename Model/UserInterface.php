<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 18-10-16
 * Time: 11:52
 */

namespace AscensoDigital\BoletaBundle\Model;


interface UserInterface
{
    public function __toString();
    public function getId();
}