<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 16-11-16
 * Time: 11:19
 */

namespace AppBundle\Util;


use AscensoDigital\BoletaBundle\Doctrine\BoletaHonorarioManager;
use AscensoDigital\BoletaBundle\Entity\BoletaHonorario;
use AscensoDigital\BoletaBundle\Entity\Empresa;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CargaResumenBoletasSii
{
    const BH_NUMERO = 'B';
    const BH_ESTADO = 'C';
    const BH_FECHA = 'D';
    const EM_RUT = 'E';
    const EM_NOMBRE = 'F';
    const MONTO_BRUTO = 'H';
    const MONTO_RETENIDO = 'I';
    const MONTO_PAGADO = 'J';


    /**
     * @var UploadedFile $file
     */
    protected $file;

    /**
     * @var Empresa $empresa
     */
    protected $empresa;

    /**
     * @param UploadedFile $file
     * @return CargaResumenBoletasSii
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return \PHPExcel_Worksheet[]
     */
    protected function cargarExcel()
    {
        $inputFileName = $this->getFile()->getRealPath();
        try {
            $cacheMethod = \PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
            $cacheSettings = array('memoryCacheSize' => '150MB');
            \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
            $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (\Exception $e) {
            die('Error al cargar el archivo "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }
        return $objPHPExcel->getAllSheets();
    }

    public function procesa(BoletaHonorarioManager $bhm, $usuario)
    {
        $sheet = $this->cargarExcel()[0];
        $hRow = $sheet->getHighestRow();
        $boletaEstados = $bhm->getRepository('ADBoletaBundle:BoletaEstado')->findAllByNombre();
        $boletaEstadosTransforma=array('VIG' => 'Vigente', 'ANUL' => 'Anulada', 'VCA' => 'V.C.A.');
        $ret=array();
        $dat_id=0;
        // iterar sobre filas
        for ($row = 2; $row <= $hRow; $row++) {
            //dump($row);
            // Obtener datos basicos desde excel
            $numero = "" != self::BH_NUMERO ? (int)$sheet->getCell(self::BH_NUMERO . $row)->getValue() : null;
            $estado = "" != self::BH_ESTADO ? $sheet->getCell(self::BH_ESTADO . $row)->getValue() : null;
            $fecha = "" != self::BH_FECHA ? $sheet->getCell(self::BH_FECHA . $row)->getValue() : null;
            $rut = "" != self::EM_RUT ? $sheet->getCell(self::EM_RUT . $row)->getValue() : null;
            $m_bruto = "" != self::MONTO_BRUTO ? $sheet->getCell(self::MONTO_BRUTO . $row)->getValue() : null;
            $m_retenido = "" != self::MONTO_RETENIDO ? $sheet->getCell(self::MONTO_RETENIDO . $row)->getValue() : null;
            $m_pagado = "" != self::MONTO_PAGADO ? $sheet->getCell(self::MONTO_PAGADO . $row)->getValue() : null;

            if ($numero > 0) {
                $ret[$dat_id]['rut'] = $rut;

                $bh = $bhm->findBoletaHonorarioBy(['numero' => $numero, 'rutEmisor' => $rut]);
                if (!$bh) {
                    $be=isset($boletaEstados[$estado]) ? $boletaEstados[$estado] : $boletaEstados[$boletaEstadosTransforma[$estado]];

                    /** @var BoletaHonorario $bh */
                    $bh = $bhm->createBoletaHonorario();
                    $bh->setNumero($numero)
                        ->setCargador($usuario)
                        ->setEmpresa($this->getEmpresa())
                        ->setRutEmisor($rut)
                        ->setFechaBoleta($fecha)
                        ->setMonto($m_bruto)
                        ->setMontoImpuesto($m_retenido)
                        ->setMontoLiquido($m_pagado)
                        ->setBoletaEstado($be);
                    $bhm->getObjectManager()->persist($bh);

                    $ret[$dat_id]['estado'] = 'Boleta agregada';
                    $ret[$dat_id]['class'] = 'bg-success';
                }
                else {
                    $ret[$dat_id]['estado'] = 'En sistema simce';
                    $ret[$dat_id]['class'] = 'bg-info';
                }
            }
            else {
                $ret[$dat_id]['rut'] = '-';
                $ret[$dat_id]['estado'] = 'Número Boleta No válido';
                $ret[$dat_id]['class'] = 'bg-warning';
            }
            $ret[$dat_id]['fila']=$row;
            $ret[$dat_id]['numero'] = $numero;
            $dat_id++;
        }
        $bhm->getObjectManager()->flush();
        return $ret;
    }

    /**
     * @param Empresa $empresa
     * @return CargaResumenBoletasSii
     */
    public function setEmpresa($empresa)
    {
        $this->empresa = $empresa;
        return $this;
    }

    /**
     * @return Empresa
     */
    public function getEmpresa()
    {
        return $this->empresa;
    }
}
