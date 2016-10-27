<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 26-10-16
 * Time: 19:00
 */

namespace AscensoDigital\BoletaBundle\Event;


use Symfony\Component\EventDispatcher\Event;

class BoletaHonorarioEvent extends Event
{
    private $boletaHonorario;
    private $modificados;

    public function __construct($boletaHonorario)
    {
        $this->boletaHonorario=$boletaHonorario;
        $this->modificados=array();
    }

    /**
     * @return mixed
     */
    public function getBoletaHonorario()
    {
        return $this->boletaHonorario;
    }

    /**
     * @param array $modificados
     * @return BoletaHonorarioEvent
     */
    public function setModificados($modificados)
    {
        $this->modificados = $modificados;
        return $this;
    }

    /**
     * @return array
     */
    public function getModificados()
    {
        return $this->modificados;
    }
}
