<?php

namespace AscensoDigital\BoletaBundle\Controller;

use AscensoDigital\PerfilBundle\Controller\FiltroController as BaseFiltroController;
use Symfony\Component\HttpFoundation\Request;

class FiltroController extends BaseFiltroController
{
    /*public function boletasAction(Request $request) {
    $route='contabilidad_boletas_list';
    $url = $this->generateUrl($route);
    $update = 'table-boletas';
    $activos = array( 'usuario' => array('required'=> false),
        'boletaEstado' => array('required'=>false, 'multiple'=>false),
        'fechaRango' => array('table' => 'bh', 'campo' => 'fechaEmision', 'label' => 'Fecha Emisión', 'required' => false));
    return $this->filtrosAction($request, $route, $url, $update, $activos);
}*/
    public function boletasAction(Request $request) {
        $options=array(
            'route' => 'contabilidad_boletas_list',
            'update' => 'table-boletas',
            'filtros' => array(
                'usuario' => [],
                'boletaEstado' => [],
                'fechaEmision' => []
            ),
            'auto_filter' => false
        );
        return parent::filtroAction($request, $options);
    }
}
