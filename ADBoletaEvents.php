<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 26-10-16
 * Time: 19:03
 */

namespace AscensoDigital\BoletaBundle;


final class ADBoletaEvents
{
    /**
     * El evento MAIL_ANULADA_SUCCESS ocurre cuando se termina de anular una boleta correctamente.
     *
     * Este evento permite retornar un conjunto de objetos a actualizar.
     * The event listener method receives a AscensoDigital\BoletaBundle\Event\BoletaHonorarioEvent instance.
     */
    const MAIL_ANULADA_SUCCESS = 'ad_boleta.mail_anulada.success';

    /**
     * El evento VCA_ANULADA_SUCCESS ocurre cuando una boleta con estado vca es marcada como anulada correctamente.
     */
    const VCA_ANULADA_SUCCESS = 'ad_boleta.vca_anulada.success';
}
