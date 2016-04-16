<?php

namespace AscensoDigital\BoletaBundle\Command;

use AppBundle\Entity\BoletaEstado;
use AppBundle\Entity\BoletaHonorario;
use AppBundle\Entity\PagoEstado;
use AppBundle\Entity\UsuarioPago;
use AppBundle\Entity\UsuarioPagoHistorico;
use AscensoWeb\Component\Util\BoletaMailAnulada;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of boletaMailAnuladaCommand
 *
 * @author claudio
 */
class BoletaMailAnuladaCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
            ->setName('app:boleta:anulada')
            ->setDescription('Procesa los correos de boletas anuladas enviados por SII desde mail.')
            ->addOption('mail_id','m',InputOption::VALUE_OPTIONAL,'id email en particular',null);
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        $em = $this->getContainer()->get('doctrine')->getManager();
        //$estados=array(PagoEstado::ENVIADO_BANCO, PagoEstado::PAGADO, PagoEstado::PAGADO_CHEQUE);
        $pe_anulada=$em->getRepository('AppBundle:PagoEstado')->find(PagoEstado::BOLETA_ANULADA);
        $bh_anulada=$em->getRepository('AppBundle:BoletaEstado')->find(BoletaEstado::ANULADA);

        $user = 'pagos@cgslogistica.cl';
        $password = 'pagos.2015';
        $mailbox = "{imap.gmail.com:993/imap/ssl}INBOX";

        $conn=imap_open($mailbox , $user , $password) or die('Cannot connect to Gmail: ' . imap_last_error());

        if(is_null($input->getOption('mail_id'))){
            $anulados = imap_search($conn, 'SUBJECT "Anulada" UNSEEN', SE_UID);
            //$anulados = imap_search($conn, 'SUBJECT "Anulada"', SE_UID);
        }
        else {
            $anulados=array($input->getOption('mail_id'));
        }
        if(!$anulados) {
            imap_close($conn); 
            return;
        }
        $tot=count($anulados);
        $particion = round($tot/20) ? round($tot/20) : 20;
        //$output->writeln('Se revisarán '.$tot.' correos');
        $cont=0;
        foreach ($anulados as $mail_id) {
            BoletaMailAnulada::loadMsg(utf8_encode(imap_qprint(imap_fetchbody($conn,$mail_id,'1'))));
            $rut_boleta=BoletaMailAnulada::getRutEmisor();
            $boleta_numero=BoletaMailAnulada::getNumeroBoleta();
            $fecha_anulacion = BoletaMailAnulada::getFechaAnulacionEstandar();
            if(is_null($rut_boleta) or is_null($boleta_numero)){
                $output->writeln($mail_id.' Correo invalido boleta:'.$boleta_numero.' RUT: '.$rut_boleta);
                continue;
            }

            /** @var BoletaHonorario $bhe */
            $bhe=$em->getRepository('AppBundle:BoletaHonorario')->findOneWithPagoByRutNumero($rut_boleta, $boleta_numero);
            if(!$bhe){
                $bhe= new BoletaHonorario();
                $bhe->setRut($rut_boleta)
                    ->setNumero($boleta_numero)
                    ->setMailId($mail_id);
                $usuario = $em->getRepository('AppBundle:Usuario')->find(BoletaMailAnulada::getUsuarioId());
                if ($usuario) {
                    $bhe->setUsuario($usuario);
                }
            }
            else {
                if($bhe->getArchivo()) {
                    $pdf_vigente = $bhe->getArchivo()->getRuta();
                    if (file_exists($bhe->getRootDir().$pdf_vigente)) {
                        $path = explode('/', $pdf_vigente);
                        $pdf_anulada = $path[0] . '/anulada/' . $path[1];
                        if (rename($bhe->getRootDir().$pdf_vigente, $bhe->getRootDir().$pdf_anulada)) {
                            $arch = $bhe->getArchivo();
                            $arch->setRuta($pdf_anulada);
                            $em->persist($arch);
                        }
                    }
                }
            }
            $bhe->setBoletaEstado($bh_anulada)
                ->setFechaAnulacion(is_null($fecha_anulacion) ? null : new \DateTime($fecha_anulacion));
            $em->persist($bhe);

            /** @var UsuarioPago $up */
            foreach ($bhe->getUsuarioPagos() as $up) {
                /*$uph=new UsuarioPagoHistorico();
                $uph->setFecha(new \DateTime())
                    ->setPagoEstado($pe_anulada)
                    ->setUsuarioPago($up);
                /** @var UsuarioPagoHistorico $uphl */
                /*$uphl=$up->getLastHistorico();
                if($uphl){
                    $uph->setPagoMetodo($uphl->getPagoMetodo());
                }
                $em->persist($uph);*/
                $up->setBoletaHonorario(null);
                $em->persist($up);
            }
            if(($cont % $particion) == 0) {
                $em->flush();
                //$output->writeln($cont."/".$tot.' - '.round($cont/$tot*100).'% - '.date('H:i:s'));
            }
            $cont++;
        }
        $em->flush();
        //close the stream 
        imap_close($conn); 
    }
}