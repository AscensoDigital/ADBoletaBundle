<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 03-04-16
 * Time: 23:50
 */

namespace AscensoDigital\BoletaBundle\DataFixtures\ORM;


use AscensoDigital\PerfilBundle\DataFixtures\ORM\LoadColorData;
use AscensoDigital\PerfilBundle\Entity\Menu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadMenuData extends Fixture
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $bh_list= new Menu();
        $bh_list->setOrden(9)
            ->setNombre('Listado Boletas')
            ->setDescripcion('Listado con las boletas de honorarios cargadas en el sistema')
            ->setColor($this->getReference('clr-azul'))
            ->setIcono('fa fa-list-alt')
            ->setRoute('ad_boleta_boleta_list')
            ->setPermiso($this->getReference('ad_boleta_per-bh-list'))
            ->setMenuSuperior($this->getReference('ad_boleta_mn-boleta-honorario'));
        $manager->persist($bh_list);
        $this->addReference('ad_boleta_mn-bh-list',$bh_list);

        $bh_load_resumen= new Menu();
        $bh_load_resumen->setOrden(10)
            ->setNombre('Cargar Resumen Boletas')
            ->setDescripcion('Se carga archivo excel con el resumen de boletas obtenidos desde el sii')
            ->setColor($this->getReference('clr-morado'))
            ->setIcono('fa fa-cloud-upload')
            ->setRoute('ad_boleta_load_resumen_boletas_sii')
            ->setPermiso($this->getReference('ad_boleta_per-bh-load-resumen-sii'))
            ->setMenuSuperior($this->getReference('ad_boleta_mn-boleta-honorario'));
        $manager->persist($bh_load_resumen);
        $this->addReference('ad_boleta_mn-load-resumen',$bh_load_resumen);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [LoadColorData::class,
            LoadPermisoData::class,
            $this->container->getParameter('menu_superior_load_class')];
    }
}