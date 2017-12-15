<?php

namespace AscensoDigital\BoletaBundle\Service;

use AscensoDigital\BoletaBundle\Entity\BoletaEstado;
use Doctrine\ORM\EntityManager;

/**
 * Description of Boleta
 *
 * @author claudio
 */
class BoletaService {

    const PDF = "pdf";
    const XML = "xml";

    /**
     * @var EntityManager
     */
    private $em;
    
    /**
     *
     * @var XpdfService 
     */
    private $xpdf;
    private $manejador;
    
    protected $boletaNumero;
    protected $rutEmisor;
    protected $rutDestinatario;
    protected $glosa;
    protected $montoBruto;
    protected $montoImpuesto;
    protected $montoLiquido;
    protected $fechaEmision;
    protected $fechaBoleta;
    protected $nombreEmisor;

    private $MMM2mm = array('Enero' => '01','Febrero' => '02', 'Marzo' => '03', 'Abril' => '04', 'Mayo' => '05', 'Junio' => '06', 'Julio' => '07', 'Agosto' => '08',
        'Septiembre' => '09', 'Octubre' => '10', 'Noviembre' => '11', 'Diciembre' => '12');

    public function __construct(XpdfService $xpdf, EntityManager $em) {
        $this->xpdf=$xpdf;
        $this->em=$em;
    }

    public function load($path, $modalidad) {
        $this->boletaNumero=null;
        $this->fechaEmision=null;
        $this->fechaBoleta=null;
        $this->glosa=null;
        $this->montoBruto=null;
        $this->montoImpuesto=null;
        $this->montoLiquido=null;
        $this->rutDestinatario=null;
        $this->rutEmisor=null;
        $this->nombreEmisor=null;

        $this->manejador = ($modalidad==self::XML ? 'AscensoDigital\BoletaBundle\Util\BoletaXml' : 'AscensoDigital\BoletaBundle\Util\BoletaPdf');
        if($modalidad==self::PDF){
            call_user_func(array($this->manejador, 'setXpdf'), $this->xpdf);
        }
        call_user_func(array($this->manejador, 'load'), $path);

    }


    public function getBoletaNumero() {
        if(is_null($this->boletaNumero)) {
            $this->boletaNumero=call_user_func(array($this->manejador, 'getBoletaNumero'));
        }
        return $this->boletaNumero;
    }
    
    public function getRutEmisor() {
        $rutEmisor=$this->getRutEmisorCompleto();
        return intval(str_replace('.', '', substr($rutEmisor, 0, strlen($rutEmisor)-2)));
    }
    
    public function getRutEmisorCompleto() {
        if(is_null($this->rutEmisor)) {
            $this->rutEmisor=call_user_func(array($this->manejador, 'getRutEmisorCompleto'));
        }
        return $this->rutEmisor;
    }
    
    public function getRutDestinatario() {
        $rutDestinatario=$this->getRutDestinatarioCompleto();
        return intval(str_replace('.', '', substr($rutDestinatario, 0, strlen($rutDestinatario)-2)));
    }
    
    public function getRutDestinatarioCompleto() {
        if(is_null($this->rutDestinatario)) {
            $this->rutDestinatario=call_user_func(array($this->manejador, 'getRutDestinatarioCompleto'));
        }
        return $this->rutDestinatario;
    }
    
    public function getGlosa() {
        return implode(", ", $this->getGlosaCompleta());
    }
    
    public function getGlosaCompleta() {
        if(is_null($this->glosa)) {
            $this->glosa=call_user_func(array($this->manejador, 'getGlosaCompleta'));
        }
        return $this->glosa;
    }
    
    public function getMontoBruto() {
        if(is_null($this->montoBruto)) {
            $this->montoBruto=call_user_func(array($this->manejador, 'getRutDestinatarioCompleto'));
        }
        return $this->montoBruto;
    }
    
    public function getMontoImpuesto() {
        if(is_null($this->montoImpuesto)) {
            $this->montoImpuesto=call_user_func(array($this->manejador, 'getRutDestinatarioCompleto'));
        }
        return $this->montoImpuesto;
    }
    
    public function getMontoLiquido() {
        if(is_null($this->montoLiquido)) {
            $this->montoLiquido= call_user_func(array($this->manejador, 'getMontoLiquido'));
            $this->montoLiquido= ($this->montoLiquido==0) ? $this->getMontoBruto() : $this->montoLiquido;
        }
        return $this->montoLiquido;
    }

    public function getFechaBoleta() {
        if(is_null($this->fechaBoleta)) {
            $this->fechaBoleta=call_user_func(array($this->manejador, 'getFechaBoleta'));
        }
        return $this->fechaBoleta;
    }

    public function getFechaBoletaEstandar() {
        $fecha=$this->getFechaBoleta();
        $tmpD=explode(' ', $fecha);
        return (count($tmpD) ? $tmpD[4].'-'.$this->MMM2mm[$tmpD[2]].'-'.$tmpD[0] : $fecha).' 00:00:00';
    }

    public function getFechaEmision() {
        if(is_null($this->fechaEmision)) {
            $this->fechaEmision=call_user_func(array($this->manejador, 'getFechaEmision'));
        }
        return $this->fechaEmision;
    }
    
    public function getFechaEmisionEstandar() {
        $tmpDT=explode(' ', $this->getFechaEmision());
        $tmpD=  explode('/', $tmpDT[0]);
        return isset($tmpD[2]) ? $tmpD[2].'-'.$tmpD[1].'-'.$tmpD[0].' '.$tmpDT[1].':00' : null;
    }
    
    public function isUniqueLoaded() {
        return !$this->em->getRepository('ADBoletaBundle:BoletaHonorario')->findOneBy(array('numero' => $this->getBoletaNumero(), 'rut' => $this->getRutEmisorCompleto()));
    }
    
    public function isAnulada() {
        return is_object($this->em->getRepository('ADBoletaBundle:BoletaHonorario')->findOneBy(array('rut' => $this->getRutEmisorCompleto(),
                                                                                            'numero' => $this->getBoletaNumero(),
                                                                                            'boletaEstado' => array(BoletaEstado::ANULADA,BoletaEstado::VCA_ANULADA))));
    }
}