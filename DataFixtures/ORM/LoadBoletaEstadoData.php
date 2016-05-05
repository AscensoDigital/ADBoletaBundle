<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 05-05-16
 * Time: 1:24
 */

namespace AppBundle\DataFixtures\ORM;


use AscensoDigital\BoletaBundle\Entity\BoletaEstado;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadBoletaEstadoData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $vig= new BoletaEstado();
        $vig->setNombre('Vigente')
            ->setVigente(true);
        $manager->persist($vig);

        $anulada= new BoletaEstado();
        $anulada->setNombre('Anulada')
            ->setVigente(false);
        $manager->persist($anulada);

        $vca= new BoletaEstado();
        $vca->setNombre('V.C.A.')
            ->setVigente(false);
        $manager->persist($vca);

        $inv= new BoletaEstado();
        $inv->setNombre('PDF Inválido')
            ->setVigente(false);
        $manager->persist($inv);

        $vcav= new BoletaEstado();
        $vcav->setNombre('V.C.A. Vigente')
            ->setVigente(true);
        $manager->persist($vcav);

        $vcaa= new BoletaEstado();
        $vcaa->setNombre('V.C.A. Anulada')
            ->setVigente(false);
        $manager->persist($vcaa);
        $manager->flush();
    }
}