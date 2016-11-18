<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 17-11-16
 * Time: 22:01
 */

namespace AscensoDigital\BoletaBundle\Repository;




use AscensoDigital\BoletaBundle\Entity\BoletaEstado;
use Doctrine\ORM\EntityRepository;

class BoletaEstadoRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function findAllByNombre(){
        $rs=$this->findAll();
        $ret=array();
        /** @var BoletaEstado $r */
        foreach ($rs as $r) {
            $ret[$r->getNombre()]=$r;
        }
        return $ret;
    }
}