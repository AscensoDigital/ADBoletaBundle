<?php

namespace AscensoDigital\BoletaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BoletaHonorario
 *
 * @ORM\Table(name="boleta_honorario", indexes={@ORM\Index(name="IDX_FD85147C76426C95", columns={"boleta_estado_id"}), @ORM\Index(name="IDX_FD85147CDB38439E", columns={"usuario_id"}), @ORM\Index(name="IDX_FD85147C46EBF93B", columns={"archivo_id"})})
 * @ORM\Entity(repositoryClass="AscensoDigital\BoletaBundle\Repository\BoletaHonorarioRepository")
 */
class BoletaHonorario
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
     * @ORM\Column(name="usuario_id", type="integer", nullable=true)
     */
    protected $usuarioId;

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
     * @var integer
     *
     * @ORM\Column(name="cargador_id", type="integer", nullable=true)
     */
    protected $cargadorId;

    /**
     * @var BoletaEstado
     *
     * @ORM\ManyToOne(targetEntity="BoletaEstado")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="boleta_estado_id", referencedColumnName="id")
     * })
     */
    protected $boletaEstado;

    /**
     * @var Empresa
     *
     * @ORM\ManyToOne(targetEntity="Empresa")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="empresa_id", referencedColumnName="id")
     * })
     */
    protected $empresa;


    public function isInvalidPdf(){
        return $this->getBoletaEstado()->getId()==BoletaEstado::PDF_INVALIDO;
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
        $rutArr=explode('-',$this->rutEmisor);
        $this->setUsuarioId(isset($rutArr[0]) ? $rutArr[0] : null);
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
     * Set boletaEstado
     *
     * @param BoletaEstado $boletaEstado
     * @return BoletaHonorario
     */
    public function setBoletaEstado(BoletaEstado $boletaEstado = null)
    {
        $this->boletaEstado = $boletaEstado;

        return $this;
    }

    /**
     * Get boletaEstado
     *
     * @return BoletaEstado 
     */
    public function getBoletaEstado()
    {
        return $this->boletaEstado;
    }

    /**
     * Set usuarioId
     *
     * @param integer $usuarioId
     * @return BoletaHonorario
     */
    public function setUsuarioId($usuarioId = null)
    {
        $this->usuarioId = $usuarioId;

        return $this;
    }

    /**
     * Get usuarioId
     *
     * @return integer
     */
    public function getUsuarioId()
    {
        return $this->usuarioId;
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
     * Set cargadorId
     *
     * @param integer $cargadorId
     * @return BoletaHonorario
     */
    public function setCargadorId($cargadorId = null)
    {
        $this->cargadorId = $cargadorId;

        return $this;
    }

    /**
     * Get cargadorId
     *
     * @return integer
     */
    public function getCargadorId()
    {
        return $this->cargadorId;
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
     * @param Empresa $empresa
     * @return BoletaHonorario
     */
    public function setEmpresa($empresa)
    {
        $this->empresa = $empresa;
        return $this;
    }

    /**
     * @return Empresa
     */
    public function getEmpresa()
    {
        return $this->empresa;
    }
}
