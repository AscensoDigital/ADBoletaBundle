<?php

namespace AscensoDigital\BoletaBundle\Controller;

use AscensoDigital\PerfilBundle\Controller\FiltroController as BaseFiltroController;
use Symfony\Component\HttpFoundation\Request;

class FiltroController extends BaseFiltroController
{
    public function boletasAction(Request $request) {
        $options=array(
            'route' => 'ad_boleta_boleta_list_table',
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
