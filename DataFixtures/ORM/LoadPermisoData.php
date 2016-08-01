<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 03-04-16
 * Time: 19:36
 */

namespace AscensoDigital\BoletaBundle\DataFixtures\ORM;


use AscensoDigital\PerfilBundle\Entity\Permiso;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadPermisoData extends AbstractFixture implements OrderedFixtureInterface
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
        $this->addReference('per-bh-download',$bh_download);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 2;
    }
}
