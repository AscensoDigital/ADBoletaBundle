<?php

namespace AscensoDigital\BoletaBundle\Command;

use AppBundle\Entity\BoletaEstado;
use AppBundle\Entity\BoletaHonorario;
use AscensoWeb\Component\Util\BoletaMailVca;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of boletaMailAnuladaCommand
 *
 * @author claudio
 */
class BoletaMailConfirmarAnulacionCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
            ->setName('app:boleta:vca')
            ->setDescription('Procesa los correos de vca enviados por SII desde mail.')
            ->addOption('mail_id','m',InputOption::VALUE_OPTIONAL,'id email en particular',null);
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $bh_vca=$em->getRepository('AppBundle:BoletaEstado')->find(BoletaEstado::VCA);

        $user = 'pagos@cgslogistica.cl';
        $password = 'pagos.2015';
        $mailbox = "{imap.gmail.com:993/imap/ssl}INBOX";

        $conn=imap_open($mailbox , $user , $password) or die('Cannot connect to Gmail: ' . imap_last_error());

        if(is_null($input->getOption('mail_id'))){
            $anulados = imap_search($conn, 'SUBJECT "Solicitud de Anulacion" UNSEEN', SE_UID);
            //$anulados = imap_search($conn, 'SUBJECT "Solicitud de Anulacion"', SE_UID);
        }
        else {
            $anulados=array($input->getOption('mail_id'));
        }
        if(!$anulados) {
            imap_close($conn);
            return;
        }
        //dump($anulados);

        $tot=count($anulados);
        $particion = round($tot/20) ? round($tot/20) : 20;
        //$output->writeln('Se revisarán '.$tot.' correos');
        $cont=0;
        foreach ($anulados as $mail_id) {
            BoletaMailVca::loadMsg(utf8_encode(imap_qprint(imap_fetchbody($conn,$mail_id,'1'))));
            $rut_boleta=BoletaMailVca::getRutEmisor();
            $boleta_numero=BoletaMailVca::getNumeroBoleta();
            $fecha_anulacion = BoletaMailVca::getFechaAnulacionEstandar();

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
                $usuario = $em->getRepository('AppBundle:Usuario')->find(BoletaMailVca::getUsuarioId());
                if ($usuario) {
                    $bhe->setUsuario($usuario);
                }
            }
            $bhe->setBoletaEstado($bh_vca)
                ->setFechaAnulacion(is_null($fecha_anulacion) ? null : new \DateTime($fecha_anulacion));
            $em->persist($bhe);
            //if(($cont % $particion) == 0) {
                $em->flush();
                //$output->writeln($cont."/".$tot.' - '.round($cont/$tot*100).'% - '.date('H:i:s'));
            //}
            $cont++;
        }
        $em->flush();
        //close the stream
        imap_close($conn);
    }
}