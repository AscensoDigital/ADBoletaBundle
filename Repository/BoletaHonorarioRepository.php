<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 19-10-15
 * Time: 16:06
 */

namespace AscensoDigital\BoletaBundle\Repository;


use AscensoDigital\BoletaBundle\Entity\BoletaEstado;
use AscensoDigital\PerfilBundle\Doctrine\FiltroManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

class BoletaHonorarioRepository extends EntityRepository {

    public function findArrayByFiltros(FiltroManager $filtros){
        $qb=$this->getEntityManager()->createQueryBuilder()
            ->select('bh.id bh_id')
            ->addSelect('bh.glosa as bh_glosa')
            ->addSelect('bh.numero as numero')
            ->addSelect('bh.monto as monto')
            ->addSelect('bh.montoImpuesto as montoImpuesto')
            ->addSelect('bh.montoLiquido as montoLiquido')
            ->addSelect('bh.fechaEmision as fechaEmision')
            ->addSelect('bh.fechaBoleta as fechaBoleta')
            ->addSelect('bhe.nombre as estado')
            ->addSelect('bhe.id as estado_id')
            ->addSelect('u.nombres as nombre')
            ->addSelect('u.apellidoPaterno as apP')
            ->addSelect('u.apellidoMaterno as apM')
            ->addSelect('u.username as rut')
            ->addSelect('u.celular as celular')
            ->from('ADBoletaBundle:BoletaHonorario', 'bh')
            ->leftJoin('bh.usuario', 'u')
            ->join('bh.boletaEstado','bhe')
            ->orderBy('bh.id');
        $rs=$filtros->getQueryBuilder($qb)->getQuery()->getScalarResult();
        $ret=array();
        foreach($rs as $u) {
            $ret[$u['bh_id']]['glosa']=$u['bh_glosa'];
            $ret[$u['bh_id']]['numero']=$u['numero'];
            $ret[$u['bh_id']]['monto']=$u['monto'];
            $ret[$u['bh_id']]['montoImpuesto']=$u['montoImpuesto'];
            $ret[$u['bh_id']]['montoLiquido']=$u['montoLiquido'];
            $ret[$u['bh_id']]['usuario']=$u['nombre'].' '.$u['apP'].' '.$u['apM'];
            $ret[$u['bh_id']]['fechaEmision']=$u['fechaEmision'];
            $ret[$u['bh_id']]['fechaBoleta']=$u['fechaBoleta'];
            $ret[$u['bh_id']]['estado']=$u['estado'];
            $ret[$u['bh_id']]['vca']=($u['estado_id']==BoletaEstado::VCA);
            $ret[$u['bh_id']]['rut']=$u['rut'];
            $ret[$u['bh_id']]['celular']=$u['celular'];
        }
        return $ret;
    }

    public function findSinAsignacion($usuario_id) {
        return $this->createQueryBuilder('bh')
            ->join('bh.boletaEstado','be', Join::WITH, 'be.vigente=:vigente')
            ->where('bh.rutEmisor=:usuario')
            ->andWhere('bh.proyectoKey IS NULL')
            ->setParameter(':usuario',$usuario_id)
            ->setParameter(':vigente','true')
            ->getQuery()->getResult();
    }

    public function hasSinAsignacion($usuario_id) {
        $rs=$this->createQueryBuilder('bh')
            ->select('COUNT(bh)')
            ->join('bh.boletaEstado','be', Join::WITH, 'be.vigente=:vigente')
            ->where('bh.rutEmisor=:usuario')
            ->andWhere('bh.proyectoKey IS NULL')
            ->setParameter(':usuario',$usuario_id)
            ->setParameter(':vigente','true')
            ->getQuery()->getSingleScalarResult();
        return $rs>0;
    }
}