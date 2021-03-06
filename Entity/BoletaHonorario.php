<?php

namespace AscensoDigital\BoletaBundle\Entity;

use AscensoDigital\BoletaBundle\Model\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BoletaHonorario
 *
 */
abstract class BoletaHonorario
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="boleta_honorario_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="rut_emisor", type="string", length=20, nullable=true)
     * @Assert\NotBlank(
     *      message="Rut Obligatorio."
     * )
     * @Assert\Regex("/^[0-9]{7,8}-[0-9,K]$/")
     *
     */
    protected $rutEmisor;

    /**
     * @var integer
     *
     * @ORM\Column(name="numero", type="integer", nullable=true)
     * @Assert\NotBlank(
     *      message="Número Boleta Obligatorio."
     * )
     */
    protected $numero;

    /**
     * @var string
     *
     * @ORM\Column(name="proyecto_key", type="string", length=255, nullable=true)
     */
    protected $proyectoKey;

    /**
     * @var integer
     *
     * @ORM\Column(name="monto", type="integer", nullable=true)
     * @Assert\NotBlank(
     *      message="Monto Obligatorio."
     * )
     */
    protected $monto;

    /**
     * @var integer
     *
     * @ORM\Column(name="monto_impuesto", type="integer", nullable=true)
     */
    protected $montoImpuesto;

    /**
     * @var integer
     *
     * @ORM\Column(name="monto_liquido", type="integer", nullable=true)
     */
    protected $montoLiquido;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_emision", type="datetime", nullable=true)
     * @Assert\NotBlank(
     *      message="Fecha Emisión Obligatorio."
     * )
     */
    protected $fechaEmision;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_boleta", type="date", nullable=true)
     * @Assert\NotBlank(
     *      message="Fecha Boleta Obligatorio."
     * )
     */
    protected $fechaBoleta;

    /**
     * @var string
     *
     * @ORM\Column(name="fecha_boleta_str", type="string", length=60, nullable=true)
     */
    protected $fechaBoletaStr;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_envio", type="date", nullable=true)
     */
    protected $fechaEnvio;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_lectura", type="datetime", nullable=true)
     */
    protected $fechaLectura;

    /**
     * @var string
     *
     * @ORM\Column(name="glosa", type="text", nullable=true)
     * @Assert\NotBlank(
     *      message="Glosa Obligatorio."
     * )
     */
    protected $glosa;


    /**
     * @var string
     *
     * @ORM\Column(name="ruta_archivo", type="text", nullable=true)
     */
    protected $rutaArchivo;

    /**
     * @var integer
     *
     * @ORM\Column(name="mail_id", type="integer", nullable=true)
     */
    protected $mailId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_anulacion", type="date", nullable=true)
     */
    protected $fechaAnulacion;

    /**
     * @var integer
     *
     * @ORM\Column(name="mail_anulacion_id", type="integer", nullable=true)
     */
    protected $mailAnulacionId;

    /**
     * @var \AscensoDigital\BoletaBundle\Entity\BoletaEstado
     *
     * @ORM\ManyToOne(targetEntity="\AscensoDigital\BoletaBundle\Entity\BoletaEstado")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="boleta_estado_id", referencedColumnName="id")
     * })
     */
    protected $boletaEstado;

    /**
     * @var \AscensoDigital\BoletaBundle\Entity\Empresa
     *
     * @ORM\ManyToOne(targetEntity="\AscensoDigital\BoletaBundle\Entity\Empresa")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="empresa_id", referencedColumnName="id")
     * })
     */
    protected $empresa;

    /**
     * @var UserInterface
     *
     * @ORM\ManyToOne(targetEntity="\AscensoDigital\BoletaBundle\Model\UserInterface")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     * })
     */
    protected $usuario;

    /**
     * @var UserInterface
     *
     * @ORM\ManyToOne(targetEntity="\AscensoDigital\BoletaBundle\Model\UserInterface")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cargador_id", referencedColumnName="id")
     * })
     */
    protected $cargador;

    public function isInvalidFecha(){
        if(is_null($this->getFechaBoleta()) || is_null($this->getFechaEmision())){
            return false;
        }
        $fecha= clone $this->getFechaEmision();
        return ($this->getFechaBoleta()->format('m')!=$this->getFechaEmision()->format('m') && $fecha->diff($this->getFechaBoleta())->format('%a')>15);
    }

    public function isInvalidPdf(){
        return $this->getBoletaEstado()->getId()==BoletaEstado::PDF_INVALIDO;
    }


    public function isShow($can){
        return true===$can && strlen($this->getRutaArchivo())>0;
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
     * Set numero
     *
     * @param integer $numero
     * @return BoletaHonorario
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Get numero
     *
     * @return integer 
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set rut emisor
     *
     * @param string $rut
     * @return BoletaHonorario
     */
    public function setRutEmisor($rut)
    {
        $this->rutEmisor = str_replace('.','',$rut);
        return $this;
    }

    /**
     * Get rut emisor
     *
     * @return string 
     */
    public function getRutEmisor()
    {
        return $this->rutEmisor;
    }

    /**
     * Set fechaEmision
     *
     * @param \DateTime $fechaEmision
     * @return BoletaHonorario
     */
    public function setFechaEmision($fechaEmision)
    {
        $this->fechaEmision = $fechaEmision;

        return $this;
    }

    /**
     * Get fechaEmision
     *
     * @return \DateTime 
     */
    public function getFechaEmision()
    {
        return $this->fechaEmision;
    }

    /**
     * Set fechaLectura
     *
     * @param \DateTime $fechaLectura
     * @return BoletaHonorario
     */
    public function setFechaLectura($fechaLectura)
    {
        $this->fechaLectura = $fechaLectura;

        return $this;
    }

    /**
     * Get fechaLectura
     *
     * @return \DateTime 
     */
    public function getFechaLectura()
    {
        return $this->fechaLectura;
    }

    /**
     * Set montoImpuesto
     *
     * @param integer $montoImpuesto
     * @return BoletaHonorario
     */
    public function setMontoImpuesto($montoImpuesto)
    {
        $this->montoImpuesto = $montoImpuesto;

        return $this;
    }

    /**
     * Get montoImpuesto
     *
     * @return integer 
     */
    public function getMontoImpuesto()
    {
        return $this->montoImpuesto;
    }

    /**
     * Set montoLiquido
     *
     * @param integer $montoLiquido
     * @return BoletaHonorario
     */
    public function setMontoLiquido($montoLiquido)
    {
        $this->montoLiquido = $montoLiquido;

        return $this;
    }

    /**
     * Get montoLiquido
     *
     * @return integer 
     */
    public function getMontoLiquido()
    {
        return $this->montoLiquido;
    }

    /**
     * Set monto
     *
     * @param integer $monto
     * @return BoletaHonorario
     */
    public function setMonto($monto)
    {
        $this->monto = $monto;

        return $this;
    }

    /**
     * Get monto
     *
     * @return integer 
     */
    public function getMonto()
    {
        return $this->monto;
    }

    /**
     * Set glosa
     *
     * @param string $glosa
     * @return BoletaHonorario
     */
    public function setGlosa($glosa)
    {
        $this->glosa = $glosa;

        return $this;
    }

    /**
     * Get glosa
     *
     * @return string 
     */
    public function getGlosa()
    {
        return $this->glosa;
    }

    /**
     * Set \AscensoDigital\BoletaBundle\Entity\boletaEstado
     *
     * @param \AscensoDigital\BoletaBundle\Entity\BoletaEstado $boletaEstado
     * @return BoletaHonorario
     */
    public function setBoletaEstado(\AscensoDigital\BoletaBundle\Entity\BoletaEstado $boletaEstado = null)
    {
        $this->boletaEstado = $boletaEstado;

        return $this;
    }

    /**
     * Get boletaEstado
     *
     * @return \AscensoDigital\BoletaBundle\Entity\BoletaEstado
     */
    public function getBoletaEstado()
    {
        return $this->boletaEstado;
    }

    /**
     * Set fechaEnvio
     *
     * @param \DateTime $fechaEnvio
     * @return BoletaHonorario
     */
    public function setFechaEnvio($fechaEnvio)
    {
        $this->fechaEnvio = $fechaEnvio;

        return $this;
    }

    /**
     * Get fechaEnvio
     *
     * @return \DateTime 
     */
    public function getFechaEnvio()
    {
        return $this->fechaEnvio;
    }

    /**
     * Set fechaBoletaStr
     *
     * @param string $fechaBoletaStr
     * @return BoletaHonorario
     */
    public function setFechaBoletaStr($fechaBoletaStr)
    {
        $this->fechaBoletaStr = $fechaBoletaStr;

        return $this;
    }

    /**
     * Get fechaBoletaStr
     *
     * @return string 
     */
    public function getFechaBoletaStr()
    {
        return $this->fechaBoletaStr;
    }

    /**
     * Set fechaBoleta
     *
     * @param \DateTime $fechaBoleta
     * @return BoletaHonorario
     */
    public function setFechaBoleta($fechaBoleta)
    {
        $this->fechaBoleta = $fechaBoleta;

        return $this;
    }

    /**
     * Get fechaBoleta
     *
     * @return \DateTime 
     */
    public function getFechaBoleta()
    {
        return $this->fechaBoleta;
    }

    /**
     * Set mailId
     *
     * @param integer $mailId
     * @return BoletaHonorario
     */
    public function setMailId($mailId)
    {
        $this->mailId = $mailId;

        return $this;
    }

    /**
     * Get mailId
     *
     * @return integer 
     */
    public function getMailId()
    {
        return $this->mailId;
    }

    /**
     * Set fechaAnulacion
     *
     * @param \DateTime $fechaAnulacion
     * @return BoletaHonorario
     */
    public function setFechaAnulacion($fechaAnulacion)
    {
        $this->fechaAnulacion = $fechaAnulacion;

        return $this;
    }

    /**
     * Get fechaAnulacion
     *
     * @return \DateTime 
     */
    public function getFechaAnulacion()
    {
        return $this->fechaAnulacion;
    }

    /**
     * @param string $proyectoKey
     * @return BoletaHonorario
     */
    public function setProyectoKey($proyectoKey)
    {
        $this->proyectoKey = $proyectoKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getProyectoKey()
    {
        return $this->proyectoKey;
    }

    /**
     * @param string $rutaArchivo
     * @return BoletaHonorario
     */
    public function setRutaArchivo($rutaArchivo)
    {
        $this->rutaArchivo = $rutaArchivo;
        return $this;
    }

    /**
     * @return string
     */
    public function getRutaArchivo()
    {
        return $this->rutaArchivo;
    }

    /**
     * @param int $mailAnulacionId
     * @return BoletaHonorario
     */
    public function setMailAnulacionId($mailAnulacionId)
    {
        $this->mailAnulacionId = $mailAnulacionId;
        return $this;
    }

    /**
     * @return int
     */
    public function getMailAnulacionId()
    {
        return $this->mailAnulacionId;
    }

    /**
     * @param \AscensoDigital\BoletaBundle\Entity\Empresa $empresa
     * @return BoletaHonorario
     */
    public function setEmpresa(\AscensoDigital\BoletaBundle\Entity\Empresa $empresa)
    {
        $this->empresa = $empresa;
        return $this;
    }

    /**
     * @return \AscensoDigital\BoletaBundle\Entity\Empresa
     */
    public function getEmpresa()
    {
        return $this->empresa;
    }

    /**
     * @param UserInterface $usuario
     * @return BoletaHonorario
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;
        return $this;
    }

    /**
     * @return UserInterface
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * @param UserInterface $cargador
     * @return BoletaHonorario
     */
    public function setCargador($cargador)
    {
        $this->cargador = $cargador;
        return $this;
    }

    /**
     * @return UserInterface
     */
    public function getCargador()
    {
        return $this->cargador;
    }
}
