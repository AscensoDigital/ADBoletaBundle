<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 03-04-16
 * Time: 19:36
 */

namespace AscensoDigital\BoletaBundle\DataFixtures\ORM;


use AscensoDigital\PerfilBundle\Entity\Permiso;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadPermisoData extends Fixture
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $bh_download=$manager->getRepository('ADPerfilBundle:Permiso')->findOneBy(['nombre' => 'ad_boleta-download']);
        if(!$bh_download) {
            $bh_download = new Permiso();
            $bh_download->setNombre('ad_boleta-download')->setDescripcion('Descargar Boleta de Honorarios BoletaBundle');
            $manager->persist($bh_download);
        }
        $this->addReference('ad_boleta_per-bh-download',$bh_download);

        $bh_list=$manager->getRepository('ADPerfilBundle:Permiso')->findOneBy(['nombre' => 'ad_boleta-list']);
        if(!$bh_list) {
            $bh_list = new Permiso();
            $bh_list->setNombre('ad_boleta-list')->setDescripcion('Listado de Boletas de Honorarios BoletaBundle');
            $manager->persist($bh_list);
        }
        $this->addReference('ad_boleta_per-bh-list',$bh_list);

        $bh_load_resumen=$manager->getRepository('ADPerfilBundle:Permiso')->findOneBy(['nombre' => 'ad_boleta-load-resumen-bhe-sii']);
        if(!$bh_load_resumen) {
            $bh_load_resumen = new Permiso();
            $bh_load_resumen->setNombre('ad_boleta-load-resumen-bhe-sii')->setDescripcion('Descargar Boleta de Honorarios BoletaBundle');
            $manager->persist($bh_load_resumen);
        }
        $this->addReference('ad_boleta_per-bh-load-resumen-sii',$bh_load_resumen);

        $bh_vca = $manager->getRepository('ADPerfilBundle:Permiso')->findOneBy(['nombre' => 'ad_boleta-vca']);
        if (!$bh_vca) {
            $bh_vca = new Permiso();
            $bh_vca->setNombre('ad_boleta-vca')->setDescripcion('Verificar Boletas VCA');
            $manager->persist($bh_vca);
        }
        $this->addReference('ad_boleta_per-bh-vca', $bh_vca);

        $manager->flush();
    }
}
