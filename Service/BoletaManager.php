<?php

namespace AscensoDigital\BoletaBundle\Service;

use AscensoDigital\BoletaBundle\Entity\BoletaEstado;
use AscensoDigital\BoletaBundle\Service\XpdfService;
use Doctrine\ORM\EntityManager;

/**
 * Description of Boleta
 *
 * @author claudio
 */
class BoletaManager {
    /**
     * @var EntityManager
     */
    private $em;
    
    /**
     *
     * @var XpdfService 
     */
    private $xpdf;
    protected $contenido;
    protected $arrCont;
    
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
    
    public function loadPdf($pathPdf) {
        $this->contenido=$this->xpdf->getText($pathPdf);
        $this->arrCont=  explode("\n", $this->contenido);
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
        //dump($this->arrCont);
    }
    
    public function getContenido() {
        return $this->contenido;
    }
    
    public function getArrayContenido() {
        return $this->arrCont;
    }
    
    public function find($context) {
        foreach ($this->getArrayContenido() as $key => $fila) {
            if(strrpos($fila, $context)!==false) {
                return $key;
            }
        }
        return null;
    }

    public function getBoletaNumero() {
        if(is_null($this->boletaNumero)) {
            $i=is_null($this->find('N °')) ? $this->find('N°') : $this->find('N °');
            $this->boletaNumero=intval(substr($this->arrCont[$i], strrpos($this->arrCont[$i]," ")+1));
        }
        return $this->boletaNumero;
    }
    
    public function getRutEmisor() {
        return intval(str_replace('.', '', substr($this->getRutEmisorCompleto(), 0, strlen($this->getRutEmisorCompleto())-2)));
    }
    
    public function getRutEmisorCompleto() {
        if(is_null($this->rutEmisor)) {
            $i=$this->find('RUT:');
            $this->rutEmisor=str_replace(' ', '',substr($this->arrCont[$i], strrpos($this->arrCont[$i],": ")+2));
        }
        return $this->rutEmisor;
    }
    
    public function getRutDestinatario() {
        return intval(str_replace('.', '', substr($this->getRutDestinatarioCompleto(), 0, strlen($this->getRutDestinatarioCompleto())-2)));
    }
    
    public function getRutDestinatarioCompleto() {
        if(is_null($this->rutDestinatario)) {
            $i=$this->find('Señor(es):');
            $this->rutDestinatario=str_replace(' ', '',substr($this->arrCont[$i], strrpos($this->arrCont[$i],": ")+2));
        }
        return $this->rutDestinatario;
    }
    
    public function getGlosa() {
        return implode(", ", $this->getGloseCompleta());
    }
    
    public function getGloseCompleta() {
        if(is_null($this->glosa)) {
            $this->glosa=array();
            $i=$this->find('Por atención profesional:')+1;
            do {
                $this->glosa[]=substr($this->arrCont[$i], 0,strrpos($this->arrCont[$i],' '));
                $i++;
            } while (isset($this->arrCont[$i]) and strrpos($this->arrCont[$i], 'Total Honorarios $: ')===false);
        }
        return $this->glosa;
    }
    
    public function getMontoBruto() {
        if(is_null($this->montoBruto)) {
            $i=$this->find('Total Honorarios $:');
            $this->montoBruto=intval(str_replace('.', '',substr($this->arrCont[$i], strrpos($this->arrCont[$i],'Total Honorarios $: ')+strlen('Total Honorarios $: '))));
        }
        return $this->montoBruto;
    }
    
    public function getMontoImpuesto() {
        if(is_null($this->montoImpuesto)) {
            $i=$this->find('Retenido:');
            $this->montoImpuesto= ($i===false or is_null($i)) ? 0 : intval(str_replace('.', '',substr($this->arrCont[$i], strrpos($this->arrCont[$i],'Retenido: ')+strlen('Retenido: '))));
        }
        return $this->montoImpuesto;
    }
    
    public function getMontoLiquido() {
        if(is_null($this->montoLiquido)) {
            $i=$this->find('Total:');
            $this->montoLiquido= ($i===false or is_null($i)) ? 0 : intval(str_replace('.', '',substr($this->arrCont[$i], strrpos($this->arrCont[$i],'Total: ')+strlen('Total: '))));
            $this->montoLiquido= ($this->montoLiquido==0) ? $this->getMontoBruto() : $this->montoLiquido;
        }
        return $this->montoLiquido;
    }

    public function getFechaBoleta() {
        if(is_null($this->fechaBoleta)) {
            $i=$this->find('Fecha:');
            $this->fechaBoleta=substr($this->arrCont[$i], strrpos($this->arrCont[$i],'Fecha: ')+strlen('Fecha: '));
        }
        return $this->fechaBoleta;
    }

    public function getFechaBoletaEstandar() {
        $tmpD=explode(' ', $this->getFechaBoleta());
        return $tmpD[4].'-'.$this->MMM2mm[$tmpD[2]].'-'.$tmpD[0].' 00:00:00';
    }

    public function getFechaEmision() {
        if(is_null($this->fechaEmision)) {
            $i=$this->find('Fecha / Hora Emisi');
            $this->fechaEmision=trim(substr($this->arrCont[$i], strrpos($this->arrCont[$i],'Fecha / Hora Emisi')+strlen('Fecha / Hora Emisi')+4));
        }
        return $this->fechaEmision;
    }
    
    public function getFechaEmisionEstandar() {
        $tmpDT=explode(' ', $this->getFechaEmision());
        $tmpD=  explode('/', $tmpDT[0]);
        return isset($tmpD[2]) ? $tmpD[2].'-'.$tmpD[1].'-'.$tmpD[0].' '.$tmpDT[1].':00' : null;
    }
    
    public function isUniqueLoaded() {
        return !$this->em->getRepository('ADBoletaBundle:BoletaHonorario','ad_boleta')->findOneBy(array('numero' => $this->getBoletaNumero(), 'rut' => $this->getRutEmisorCompleto()));
    }
    
    public function isAnulada() {
        return is_object($this->em->getRepository('ADBoletaBundle:BoletaHonorario','ad_boleta')->findOneBy(array('rut' => $this->getRutEmisorCompleto(),
                                                                                            'numero' => $this->getBoletaNumero(),
                                                                                            'boletaEstado' => array(BoletaEstado::ANULADA,BoletaEstado::VCA_ANULADA))));
    }
}