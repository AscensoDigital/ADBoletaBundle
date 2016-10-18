<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 19-10-15
 * Time: 16:06
 */

namespace AscensoDigital\BoletaBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

class BoletaHonorarioRepository extends EntityRepository {

    public function findSinAsignacion($usuario_id) {
        return $this->createQueryBuilder('bh')
            ->join('bh.boletaEstado','be', Join::WITH, 'be.vigente=:vigente')
            ->where('bh.usuario=:usuario')
            ->andWhere('bh.proyectoKey IS NULL')
            ->setParameter(':usuario',$usuario_id)
            ->setParameter(':vigente','true')
            ->getQuery()->getResult();
    }

    public function hasSinAsignacion($usuario_id) {
        $rs=$this->createQueryBuilder('bh')
            ->select('COUNT(bh)')
            ->join('bh.boletaEstado','be', Join::WITH, 'be.vigente=:vigente')
            ->where('bh.usuario=:usuario')
            ->andWhere('bh.proyectoKey IS NULL')
            ->setParameter(':usuario',$usuario_id)
            ->setParameter(':vigente','true')
            ->getQuery()->getSingleScalarResult();
        return $rs>0;
    }
}