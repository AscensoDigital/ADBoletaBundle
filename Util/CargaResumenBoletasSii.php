<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 16-11-16
 * Time: 11:19
 */

namespace AppBundle\Util;


use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CargaResumenBoletasSii
{
    /**
     * @var UploadedFile $file
     */
    protected $file;

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
    protected function cargarExcel() {
        $inputFileName = $this->getFile()->getRealPath();
        try {
            $cacheMethod = \PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
            $cacheSettings = array( 'memoryCacheSize' => '150MB');
            \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
            $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (\Exception $e) {
            die('Error al cargar el archivo "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }
        return $objPHPExcel->getAllSheets();
    }

    public function procesa(ObjectManager $em) {

    }
}