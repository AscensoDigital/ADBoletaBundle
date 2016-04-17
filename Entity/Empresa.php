<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 14-04-16
 * Time: 0:06
 */

namespace AscensoDigital\BoletaBundle\Entity;


class Empresa
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="empresa_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="rut", type="integer", nullable=false)
     */
    protected $rut;

    /**
     * @var string
     *
     * @ORM\Column(name="dv", type="string", length=1, nullable=false)
     */
    protected $dv;

    /**
     * @var string
     *
     * @ORM\Column(name="rut_str", type="text")
     */
    protected $rutStr;

    /**
     * @var string
     *
     * @ORM\Column(name="razon_social", type="string", length=200)
     */
    protected $razonSocial;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=200, nullable=false)
     */
    protected $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="direccion", type="string", length=200)
     */
    protected $direccion;

    /**
     * @var string
     *
     * @ORM\Column(name="comuna", type="string", length=100)
     */
    protected $comuna;

    /**
     * @var string
     *
     * @ORM\Column(name="region", type="string", length=200)
     */
    protected $region;

    /**
     * @var string
     *
     * @ORM\Column(name="giro", type="text")
     */
    protected $giro;

    /**
     * @var string
     *
     * @ORM\Column(name="representante_nombre", type="string", length=255)
     */
    protected $representanteNombre;

    /**
     * @var string
     *
     * @ORM\Column(name="representante_rut_str", type="text")
     */
    protected $representanteRutStr;

    /**
     * @var string
     *
     * @ORM\Column(name="representante_nacionalidad", type="string", length=100)
     */
    protected $representanteNacionalidad;

    /**
     * @var string
     *
     * @ORM\Column(name="representante_carrera", type="string", length=200)
     */
    protected $representanteCarrera;

    /**
     * @var string
     *
     * @ORM\Column(name="representante_estado_civil", type="string", length=250)
     */
    protected $representanteEstadoCivil;

    
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $rut
     * @return Empresa
     */
    public function setRut($rut)
    {
        $this->rut = $rut;
        return $this;
    }

    /**
     * @return int
     */
    public function getRut()
    {
        return $this->rut;
    }

    /**
     * @param string $dv
     * @return Empresa
     */
    public function setDv($dv)
    {
        $this->dv = $dv;
        return $this;
    }

    /**
     * @return string
     */
    public function getDv()
    {
        return $this->dv;
    }

    /**
     * @param string $rutStr
     * @return Empresa
     */
    public function setRutStr($rutStr)
    {
        $this->rutStr = $rutStr;
        return $this;
    }

    /**
     * @return string
     */
    public function getRutStr()
    {
        return $this->rutStr;
    }

    /**
     * @param string $razonSocial
     * @return Empresa
     */
    public function setRazonSocial($razonSocial)
    {
        $this->razonSocial = $razonSocial;
        return $this;
    }

    /**
     * @return string
     */
    public function getRazonSocial()
    {
        return $this->razonSocial;
    }

    /**
     * @param string $nombre
     * @return Empresa
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
        return $this;
    }

    /**
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * @param string $direccion
     * @return Empresa
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;
        return $this;
    }

    /**
     * @return string
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * @param string $comuna
     * @return Empresa
     */
    public function setComuna($comuna)
    {
        $this->comuna = $comuna;
        return $this;
    }

    /**
     * @return string
     */
    public function getComuna()
    {
        return $this->comuna;
    }

    /**
     * @param string $region
     * @return Empresa
     */
    public function setRegion($region)
    {
        $this->region = $region;
        return $this;
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param string $giro
     * @return Empresa
     */
    public function setGiro($giro)
    {
        $this->giro = $giro;
        return $this;
    }

    /**
     * @return string
     */
    public function getGiro()
    {
        return $this->giro;
    }

    /**
     * @param string $representanteNombre
     * @return Empresa
     */
    public function setRepresentanteNombre($representanteNombre)
    {
        $this->representanteNombre = $representanteNombre;
        return $this;
    }

    /**
     * @return string
     */
    public function getRepresentanteNombre()
    {
        return $this->representanteNombre;
    }

    /**
     * @param string $representanteRutStr
     * @return Empresa
     */
    public function setRepresentanteRutStr($representanteRutStr)
    {
        $this->representanteRutStr = $representanteRutStr;
        return $this;
    }

    /**
     * @return string
     */
    public function getRepresentanteRutStr()
    {
        return $this->representanteRutStr;
    }

    /**
     * @param string $representanteNacionalidad
     * @return Empresa
     */
    public function setRepresentanteNacionalidad($representanteNacionalidad)
    {
        $this->representanteNacionalidad = $representanteNacionalidad;
        return $this;
    }

    /**
     * @return string
     */
    public function getRepresentanteNacionalidad()
    {
        return $this->representanteNacionalidad;
    }

    /**
     * @param string $representanteCarrera
     * @return Empresa
     */
    public function setRepresentanteCarrera($representanteCarrera)
    {
        $this->representanteCarrera = $representanteCarrera;
        return $this;
    }

    /**
     * @return string
     */
    public function getRepresentanteCarrera()
    {
        return $this->representanteCarrera;
    }

    /**
     * @param string $representanteEstadoCivil
     * @return Empresa
     */
    public function setRepresentanteEstadoCivil($representanteEstadoCivil)
    {
        $this->representanteEstadoCivil = $representanteEstadoCivil;
        return $this;
    }

    /**
     * @return string
     */
    public function getRepresentanteEstadoCivil()
    {
        return $this->representanteEstadoCivil;
    }
}
