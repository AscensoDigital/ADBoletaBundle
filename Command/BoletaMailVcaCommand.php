<?php

namespace AscensoDigital\BoletaBundle\Command;

use AscensoDigital\BoletaBundle\Doctrine\BoletaHonorarioManager;
use AscensoDigital\BoletaBundle\Entity\BoletaEstado;
use AscensoDigital\BoletaBundle\Entity\BoletaHonorario;
use AscensoDigital\BoletaBundle\Entity\Empresa;
use AscensoDigital\BoletaBundle\Service\EmailReaderService;
use AscensoDigital\BoletaBundle\Util\BoletaMailVca;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of boletaMailVcaCommand
 *
 * @author claudio
 */
class BoletaMailVcaCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
            ->setName('adboleta:mail:vca')
            ->setDescription('Procesa los correos de vca enviados por SII desde mail.')
            ->addArgument('user',InputArgument::REQUIRED,'Cuenta de email a leer')
            ->addArgument('password',InputArgument::REQUIRED,'Password cuenta de email a leer')
            ->addOption('mail_id','m',InputOption::VALUE_OPTIONAL,'id email en particular',null)
            ->addOption('status','t',InputOption::VALUE_NONE,'mostrar barra de avance');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        $user = $input->getArgument('user');#'pagos@cgslogistica.cl';
        $password = $input->getArgument('password');#'pagos.2015';
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
        $tot=count($anulados);

        /** @var ObjectManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        $bh_vca=$em->getRepository('ADBoletaBundle:BoletaEstado')->find(BoletaEstado::VCA);

        /** @var EmailReaderService $email_reader */
        $email_reader=$this->getContainer()->get('ad_boleta.email_reader');
        /** @var BoletaHonorarioManager $bh_manager */
        $bh_manager=$this->getContainer()->get('ad_boleta.boleta_honorario_manager');

        if($input->getOption('status')) {
            $output->writeln('Se revisarán ' . $tot . ' correos');
            $progressBar= new ProgressBar($output,$tot);
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        }
        else {
            $progressBar=null;
        }

        foreach ($anulados as $mail_id) {
            $contenidos=$email_reader->getContenido($conn,$mail_id);
            if(!isset($contenidos['plain'][0])) {
                $output->writeln($mail_id.' Correo sin cuerpo leible');
                $email_body='';
            }
            else {
                $email_body=$contenidos['plain'][0]['plain'];
            }
            BoletaMailVca::loadMsg($email_body);
            $rut_boleta=BoletaMailVca::getRutEmisor();
            $boleta_numero=BoletaMailVca::getNumeroBoleta();
            $fecha_anulacion = BoletaMailVca::getFechaAnulacionEstandar();
            $razon_social=BoletaMailVca::getRazonSocial();
            if(is_null($rut_boleta) or is_null($boleta_numero) or is_null($razon_social)){
                $output->writeln($mail_id.' Correo invalido boleta:'.$boleta_numero.' RUT: '.$rut_boleta.' Razon Social: '.$razon_social);
                continue;
            }
            /** @var Empresa $empresa */
            $empresa=$em->getRepository('ADBoletaBundle:Empresa')->findOneBy(['razonSocial' => $razon_social]);
            if(!$empresa){
                $output->writeln($mail_id.' Empresa con razon social '.$razon_social.' no registrada');
                continue;
            }

            /** @var BoletaHonorario $bhe */
            $bhe=$bh_manager->findBoletaHonorarioBy(['rutEmisor' => $rut_boleta, 'numero' => $boleta_numero]);
            if(!$bhe){
                $bhe = $bh_manager->createBoletaHonorario();
                $bhe->setRutEmisor($rut_boleta)
                    ->setNumero($boleta_numero);
            }
            $bhe->setBoletaEstado($bh_vca)
                ->setMailAnulacionId($mail_id)
                ->setFechaAnulacion(is_null($fecha_anulacion) ? null : new \DateTime($fecha_anulacion));
            $em->persist($bhe);
            $em->flush();
            if($input->getOption('status')) {
                $progressBar->advance();
            }
        }
        //close the stream
        imap_close($conn);
        if($input->getOption('status')) {
            $progressBar->finish();
        }
    }
}