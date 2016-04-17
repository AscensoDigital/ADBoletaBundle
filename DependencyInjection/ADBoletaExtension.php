<?php

namespace AscensoDigital\BoletaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 20-01-16
 * Time: 19:18
 */
class ADBoletaExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter('ad_boleta.config',$config);
        $container->setParameter('ad_boleta_ruta_boletas', $config['ruta_boletas']);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    public function getAlias()
    {
        return 'ad_boleta';
    }

    private function loadBundleFiltros($config){
        $filtro_permiso=[
            'type' => 'Symfony\Bridge\Doctrine\Form\Type\EntityType',
            'table_alias' => 'adp_prm',
            'field' => 'id',
            'operator' => 'in',
            'query_builder_perfil' => false,
            'query_builder_user' => false,
            'query_builder_method' => 'getQueryBuilderOrderNombre',
            'options' => [
                'label' => 'Permiso',
                'class' => 'AscensoDigital\PerfilBundle\Entity\Permiso',
                'multiple' => true
            ]];
        $config['filtros']['adperfil_permiso']=$filtro_permiso;

        $filtro_perfil=[
            'type' => 'Symfony\Bridge\Doctrine\Form\Type\EntityType',
            'table_alias' => $config['perfil_table_alias'],
            'field' => 'id',
            'operator' => 'in',
            'query_builder_perfil' => true,
            'query_builder_user' => false,
            'query_builder_method' => 'getQueryBuilderOrderRole',
            'options' => [
                'label' => 'Perfil',
                'class' => $config['perfil_class'],
                'multiple' => true
            ]];
        $config['filtros']['adperfil_perfil']=$filtro_perfil;
        return $config;
    }
}
