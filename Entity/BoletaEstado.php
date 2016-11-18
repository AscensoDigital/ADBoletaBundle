<?php

namespace AscensoDigital\BoletaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BoletaEstado
 *
 * @ORM\Table(name="ad_boleta_boleta_estado")
 * @ORM\Entity(repositoryClass="AscensoDigital\BoletaBundle\Repository\BoletaEstadoRepository")
 */
class BoletaEstado
{
    const VIGENTE = 1;
    const ANULADA = 2;
    const VCA = 3;
    const PDF_INVALIDO = 4;
    const VCA_VIGENTE = 5;
    const VCA_ANULADA = 6;
    const ANULAR = 7;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="boleta_estado_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=200, nullable=false)
     */
    protected $nombre;

    /**
     * @var boolean
     *
     * @ORM\Column(name="vigente", type="boolean", nullable=false)
     */
    protected $vigente = false;



    public function __toString() {
        return $this->getNombre();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return BoletaEstado
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set vigente
     *
     * @param boolean $vigente
     * @return BoletaEstado
     */
    public function setVigente($vigente)
    {
        $this->vigente = $vigente;

        return $this;
    }

    /**
     * Get vigente
     *
     * @return boolean 
     */
    public function getVigente()
    {
        return $this->vigente;
    }
}
