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
            //->addSelect('bh.fechaEnvio as fechaEnvio')
            ->addSelect('bh.fechaEmision as fechaEmision')
            //->addSelect('bh.fechaLectura as fechaLectura')
            ->addSelect('bh.fechaBoleta as fechaBoleta')
            ->addSelect('bhe.nombre as estado')
            ->addSelect('bhe.id as estado_id')
            ->addSelect('u.nombres as nombre')
            ->addSelect('u.apellidoPaterno as apP')
            ->addSelect('u.apellidoMaterno as apM')
            ->addSelect('u.username as rut')
            ->addSelect('u.celular as celular')
            ->addSelect('archivo.id as archivo_id')
            ->from('ADBoletaBundle:BoletaHonorario', 'bh')
            ->join('bh.usuario', 'u')
            ->join('bh.boletaEstado','bhe')
            ->join('bh.archivo', 'archivo')
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
            //$ret[$u['bh_id']]['fechaEnvio']=$u['fechaEnvio'];
            $ret[$u['bh_id']]['fechaEmision']=$u['fechaEmision'];
            //$ret[$u['bh_id']]['fechaLectura']=$u['fechaLectura'];
            $ret[$u['bh_id']]['fechaBoleta']=$u['fechaBoleta'];
            $ret[$u['bh_id']]['estado']=$u['estado'];
            $ret[$u['bh_id']]['vca']=($u['estado_id']==BoletaEstado::VCA);
            $ret[$u['bh_id']]['rut']=$u['rut'];
            $ret[$u['bh_id']]['celular']=$u['celular'];
            $ret[$u['bh_id']]['archivo']=$u['archivo_id'];
        }
        return $ret;
    }

    public function findSinAsignacion($usuario_id) {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('bh')
            ->from('ADBoletaBundle:BoletaHonorario','bh')
            ->join('bh.boletaEstado','be', Join::WITH, 'be.vigente=:vigente')
            ->leftJoin('bh.usuarioPagos','up')
            ->where('bh.usuario=:usuario')
            ->andWhere('up.id IS NULL')
            ->setParameter(':usuario',$usuario_id)
            ->setParameter(':vigente','true')
            ->getQuery()->getResult();
    }

    public function findOneWithPagoByRutNumero($rut,$numero){
        return $this->createQueryBuilder('bh')
            ->addSelect('up')
            ->addSelect('uph')
            ->leftJoin('bh.usuarioPagos','up')
            ->leftJoin('up.usuarioPagoHistoricos','uph')
            ->where('bh.rut=:rut AND bh.numero=:numero')
            ->orderBy('uph.fecha','DESC')
            ->setParameter(':rut',$rut)
            ->setParameter(':numero',$numero)
            ->getQuery()->getOneOrNullResult();
    }

    public function hasSinAsignacion($usuario_id) {
        $rs=$this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(bh)')
            ->from('ADBoletaBundle:BoletaHonorario','bh')
            ->join('bh.boletaEstado','be', Join::WITH, 'be.vigente=:vigente')
            ->where('bh.usuario=:usuario')
            ->andWhere('bh.proyectoKey IS NULL')
            ->setParameter(':usuario',$usuario_id)
            ->setParameter(':vigente','true')
            ->getQuery()->getSingleScalarResult();
        return $rs>0;
    }
}