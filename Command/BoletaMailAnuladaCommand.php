<?php

namespace AscensoDigital\BoletaBundle\Command;

use AscensoDigital\BoletaBundle\ADBoletaEvents;
use AscensoDigital\BoletaBundle\Doctrine\BoletaHonorarioManager;
use AscensoDigital\BoletaBundle\Entity\BoletaEstado;
use AscensoDigital\BoletaBundle\Entity\BoletaHonorario;
use AscensoDigital\BoletaBundle\Entity\Empresa;
use AscensoDigital\BoletaBundle\Event\BoletaHonorarioEvent;
use AscensoDigital\BoletaBundle\Service\EmailReaderService;
use AscensoDigital\BoletaBundle\Util\BoletaMailAnulada;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * Description of boletaMailAnuladaCommand
 *
 * @author claudio
 */
class BoletaMailAnuladaCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
            ->setName('adboleta:mail:anulada')
            ->setDescription('Procesa los correos de boletas anuladas enviados por SII desde mail.')
            ->addArgument('user',InputArgument::REQUIRED,'Cuenta de email a leer')
            ->addArgument('password',InputArgument::REQUIRED,'Password cuenta de email a leer')
            ->addOption('mail_id','m',InputOption::VALUE_OPTIONAL,'id email en particular',null)
            ->addOption('status','t',InputOption::VALUE_NONE,'mostrar barra de avance')
            ->addOption('mailbox','b',InputOption::VALUE_OPTIONAL,'mailbox distinto de gmail',null)
            ->addOption('all','a',InputOption::VALUE_NONE,'releer todos los correos');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $user = $input->getArgument('user');#'pagos@cgslogistica.cl';
        $password = $input->getArgument('password');#'pagos.2015';
        $mailbox = ($input->getOption('mailbox') ? $input->getOption('mailbox') : '{imap.gmail.com:993/imap/ssl}')."INBOX";

        $conn=imap_open($mailbox , $user , $password) or die('Cannot connect to Gmail: ' . imap_last_error());

        if(is_null($input->getOption('mail_id'))){
            if($input->getOption('all')){
                $anulados = imap_search($conn, 'SUBJECT "Anulada"');
            }
            else {
                $anulados = imap_search($conn, 'SUBJECT "Anulada" UNSEEN');
            }
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

        $bh_anulada=$em->getRepository('ADBoletaBundle:BoletaEstado')->find(BoletaEstado::ANULADA);

        /** @var EmailReaderService $email_reader */
        $email_reader=$this->getContainer()->get('ad_boleta.email_reader');
        /** @var BoletaHonorarioManager $bh_manager */
        $bh_manager=$this->getContainer()->get('ad_boleta.boleta_honorario_manager');

        $path_base=$this->getContainer()->getParameter('ad_boleta_ruta_boletas'). DIRECTORY_SEPARATOR;

        if($input->getOption('status')) {
            $output->writeln('Se revisarán ' . $tot . ' correos');
            $progressBar= new ProgressBar($output,$tot);
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        }
        else {
            $progressBar=null;
        }
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->getContainer()->get('event_dispatcher');

        $particion = round($tot/20) ? round($tot/20) : 20;
        $cont=0;
        foreach ($anulados as $mail_id) {
            $contenidos=$email_reader->getContenido($conn,$mail_id);
            if(!isset($contenidos['plain'][0])) {
                $output->writeln($mail_id.' Correo sin cuerpo leible');
                $email_body='';
            }
            else {
                $email_body=$contenidos['plain'][0]['plain'];
            }
            BoletaMailAnulada::loadMsg($email_body);
            $rut_boleta=BoletaMailAnulada::getRutEmisor();
            $boleta_numero=BoletaMailAnulada::getNumeroBoleta();
            $fecha_anulacion = BoletaMailAnulada::getFechaAnulacionEstandar();
            $razon_social=BoletaMailAnulada::getRazonSocial();
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
                $bhe= $bh_manager->createBoletaHonorario();
                $bhe->setRutEmisor($rut_boleta)
                    ->setEmpresa($empresa)
                    ->setNumero($boleta_numero);
            }
            else {
                if(0 < strlen($bhe->getRutaArchivo())) {
                    $pdf_vigente = $bhe->getRutaArchivo();

                    if (file_exists($path_base.$pdf_vigente)) {
                        $path = explode('/', $pdf_vigente);
                        $this->existPath($path_base.$path[0] . '/anulada');
                        $pdf_anulada = $path[0] . '/anulada/' . $path[1];
                        if (rename($path_base.$pdf_vigente, $path_base.$pdf_anulada)) {
                            $bhe->setRutaArchivo($pdf_anulada);
                        }
                    }
                }
            }
            $bhe->setBoletaEstado($bh_anulada)
                ->setMailAnulacionId($mail_id)
                ->setFechaAnulacion(is_null($fecha_anulacion) ? null : new \DateTime($fecha_anulacion));
            $em->persist($bhe);

            $event = new BoletaHonorarioEvent($bhe);
            $dispatcher->dispatch(ADBoletaEvents::MAIL_ANULADA_SUCCESS, $event);

            if (0 < count($event->getModificados())) {
                foreach ($event->getModificados() as $obj) {
                    $em->persist($obj);
                }
            }

            if(($cont % $particion) == 0) {
                $em->flush();
            }
            if($input->getOption('status')) {
                $progressBar->advance();
            }
            $cont++;
        }
        $em->flush();
        imap_close($conn);
        if($input->getOption('status')) {
            $progressBar->finish();
        }
    }

    private function existPath($path){
        echo $path;
        $fs = new Filesystem();
        if(!$fs->exists($path)) {
            try {
                $fs->mkdir($path);
            } catch (IOExceptionInterface $e) {
                echo "An error occurred while creating your directory at " . $e->getPath();
            }
        }
    }
}