<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 03-04-16
 * Time: 23:50
 */

namespace AscensoDigital\BoletaBundle\DataFixtures\ORM;


use AscensoDigital\PerfilBundle\Entity\Menu;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadMenuData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $mnConfig= new Menu();
        $mnConfig->setOrden(99)
            ->setNombre('Configuración')
            ->setDescripcion('Inicializar el sistema de menu y permisos')
            ->setColor($this->getReference('clr-negro'))
            ->setIcono('fa fa-cogs')
            ->setPermiso($this->getReference('per-config-index'));
        $manager->persist($mnConfig);

        $perNew= new Menu();
        $perNew->setMenuSuperior($mnConfig)
            ->setOrden(1)
            ->setNombre('Crear Permiso')
            ->setDescripcion('Crear Permisos y asociar a los perfiles')
            ->setRoute('ad_perfil_permiso_new')
            ->setColor($this->getReference('clr-verde'))
            ->setIcono('fa fa-unlock-alt')
            ->setPermiso($this->getReference('per-per-new'));
        $manager->persist($perNew);

        $perList= new Menu();
        $perList->setMenuSuperior($mnConfig)
            ->setOrden(2)
            ->setNombre('Listar Permisos')
            ->setDescripcion('Listar los permisos asignados')
            ->setRoute('ad_perfil_permiso_list')
            ->setColor($this->getReference('clr-rosado'))
            ->setIcono('fa fa-list-ul')
            ->setPermiso($this->getReference('per-per-list'));
        $manager->persist($perList);

        $mnNew= new Menu();
        $mnNew->setMenuSuperior($mnConfig)
            ->setOrden(3)
            ->setNombre('Crear Menu')
            ->setDescripcion('Permite agregar un menu invisible a los principales')
            ->setRoute('ad_perfil_menu_new')
            ->setColor($this->getReference('clr-celeste'))
            ->setIcono('fa fa-navicon')
            ->setPermiso($this->getReference('per-menu-new'));
        $manager->persist($mnNew);

        $mnRepo= new Menu();
        $mnRepo->setOrden(98)
            ->setNombre('Reportes')
            ->setDescripcion('Listado de los descargables del sistema')
            ->setColor($this->getReference('clr-gris'))
            ->setIcono('fa fa-file-excel-o')
            ->setPermiso($this->getReference('per-rep-list'));
        $manager->persist($mnRepo);

        $repList= new Menu();
        $repList->setMenuSuperior($mnRepo)
            ->setOrden(1)
            ->setNombre('Listar Reportes')
            ->setDescripcion('Listar los reportes disponibles')
            ->setRoute('ad_perfil_reportes')
            ->setColor($this->getReference('clr-amarillo'))
            ->setIcono('fa fa-list-ul')
            ->setPermiso($this->getReference('per-rep-list'));
        $manager->persist($repList);

        $repNew= new Menu();
        $repNew->setMenuSuperior($mnRepo)
            ->setOrden(2)
            ->setNombre('Crear Reporte')
            ->setDescripcion('Permite configurar un nuevo reporte de PerfilBundle')
            ->setRoute('ad_perfil_reporte_new')
            ->setColor($this->getReference('clr-celeste'))
            ->setIcono('fa fa-plus')
            ->setPermiso($this->getReference('per-rep-new'));
        $manager->persist($repNew);

        $repEdit= new Menu();
        $repEdit->setMenuSuperior($repList)
            ->setVisible(false)
            ->setOrden(1)
            ->setNombre('Editar Reporte')
            ->setDescripcion('Permite editar la configuración de un reporte de PerfilBundle')
            ->setRoute('ad_perfil_reporte_edit')
            ->setColor($this->getReference('clr-cafe'))
            ->setIcono('fa fa-pencil-square-o')
            ->setPermiso($this->getReference('per-rep-edit'));
        $manager->persist($repEdit);

        $repLoad= new Menu();
        $repLoad->setMenuSuperior($repList)
            ->setVisible(false)
            ->setOrden(2)
            ->setNombre('Cargar Reporte Estático')
            ->setDescripcion('Permite dejar estatico un reporte de PerfilBundle')
            ->setRoute('ad_perfil_reporte_load_estatico')
            ->setColor($this->getReference('clr-rojo'))
            ->setIcono('fa fa-link')
            ->setPermiso($this->getReference('per-rep-load'));
        $manager->persist($repLoad);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 3;
    }
}