<?php

namespace AscensoDigital\BoletaBundle\Command;

use AscensoDigital\BoletaBundle\Entity\BoletaEstado;
use AscensoDigital\BoletaBundle\Entity\BoletaHonorario;
use AscensoDigital\BoletaBundle\Entity\Empresa;
use AscensoDigital\BoletaBundle\Doctrine\BoletaHonorarioManager;
use AscensoDigital\BoletaBundle\Service\BoletaService;
use AscensoDigital\BoletaBundle\Service\EmailReaderService;
use AscensoDigital\BoletaBundle\Util\BoletaMailEmision;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use XPDF\Exception\RuntimeException;

class BoletaMailVigenteCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->setName('adboleta:mail:vigente')
            ->setDescription('Procesa los correos de boletas vigentes enviados por SII al mail pagos.')
            ->addArgument('user',InputArgument::REQUIRED,'Cuenta de email a leer')
            ->addArgument('password',InputArgument::REQUIRED,'Password cuenta de email a leer')
            ->addOption('mail_id','m',InputOption::VALUE_OPTIONAL,'id email en particular',null)
            ->addOption('boleta_formato','f',InputOption::VALUE_OPTIONAL,'formato del archivo de boleta a leer',BoletaService::XML)
            ->addOption('status','t',InputOption::VALUE_NONE,'mostrar barra de avance')
            ->addOption('mailbox','b',InputOption::VALUE_OPTIONAL,'mailbox distinto de gmail',null)
            ->addOption('all','a',InputOption::VALUE_NONE,'releer todos los correos')
            ->addOption('reprocesar','r', InputOption::VALUE_NONE,'reprocesa las boletas');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) {

        //$user = 'consultas@ennea.cl';
        //$password = 'ennea.2014';

        $user = $input->getArgument('user');#'pagos@cgslogistica.cl';
        $password = $input->getArgument('password');#'pagos.2015';
        $mailbox = ($input->getOption('mailbox') ? $input->getOption('mailbox') : '{imap.gmail.com:993/imap/ssl}')."INBOX";

        $conn=imap_open($mailbox , $user , $password);
        if($conn===false) {
            $output->writeln('No se pudo conectar con gmail: '. $user. ' -> '.$password);
            return;
        }
        if(is_null($input->getOption('mail_id'))){
            if($input->getOption('all')){
                $vigentes = imap_search($conn, 'SUBJECT "Emision"');
            }
            else {
                $vigentes = imap_search($conn, 'SUBJECT "Emision" UNSEEN');
            }
        }
        else {
            $vigentes=array($input->getOption('mail_id'));
        }
        if(!$vigentes) {
            imap_close($conn);
            return;
        }
        // var_dump($vigentes);

        /** @var ObjectManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        /** @var BoletaService $boleta_srv */
        $boleta_srv= $this->getContainer()->get('ad_boleta.boleta_service');
        /** @var EmailReaderService $email_reader */
        $email_reader=$this->getContainer()->get('ad_boleta.email_reader');
        /** @var BoletaHonorarioManager $bh_manager */
        $bh_manager=$this->getContainer()->get('ad_boleta.boleta_honorario_manager');

        $bhe_vigente=$em->getRepository('ADBoletaBundle:BoletaEstado')->find(BoletaEstado::VIGENTE);
        $bhe_invalid=$em->getRepository('ADBoletaBundle:BoletaEstado')->find(BoletaEstado::PDF_INVALIDO);
        $bhe_anular=$em->getRepository('ADBoletaBundle:BoletaEstado')->find(BoletaEstado::ANULAR);

        $boleta_formato=$input->getOption('boleta_formato');

        $tot=count($vigentes);
        if($input->getOption('status')) {
            $output->writeln('Se revisarán ' . $tot . ' correos');
            $progressBar= new ProgressBar($output,$tot);
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        }
        else {
            $progressBar=null;
        }

        $cont=0;
        foreach ($vigentes as $email) {
            $contenidos=$email_reader->getContenido($conn,$email);
            // var_dump($contenidos);
            // exit();
            if(!isset($contenidos['plain'][0])) {
                $output->writeln($email.' Correo sin cuerpo leible');
                $email_body='';
            }
            else {
                $email_body=$contenidos['plain'][0]['plain'];
            }
            BoletaMailEmision::loadMsg($email_body);
            $rut_boleta=BoletaMailEmision::getRutEmisor();
            $boleta_numero=BoletaMailEmision::getNumeroBoleta();
            $razon_social=BoletaMailEmision::getRazonSocial();
            if(is_null($rut_boleta) or is_null($boleta_numero) or is_null($razon_social)){
                $output->writeln($email.' Correo invalido boleta:'.$boleta_numero.' RUT: '.$rut_boleta.' Razon Social: '.$razon_social);
                continue;
            }
            /** @var Empresa $empresa */
            $empresa=$em->getRepository('ADBoletaBundle:Empresa')->findOneBy(['razonSocial' => $razon_social]);
            if(!$empresa){
                $output->writeln($email.' Empresa con razon social '.$razon_social.' no registrada');
                continue;
            }
            $this->existPath($this->getContainer()->getParameter('ad_boleta_ruta_boletas'). DIRECTORY_SEPARATOR . $empresa->getSlug());
            if($input->getOption('verbose')) {
                $output->writeln(' Buscando Boleta ' . $boleta_numero . ' RUT: ' . $rut_boleta);
            }
            /** @var BoletaHonorario $bhe */
            $bhe=$bh_manager->findBoletaHonorarioBy(array('rutEmisor' => $rut_boleta, 'numero' => $boleta_numero));
            // dump($bhe);
            if(!$bhe or $bhe->isInvalidPdf() or $input->getOption('reprocesar')) {
                if(!$bhe){
                    $bhe = $bh_manager->createBoletaHonorario();
                }
                try {
                    $fecha_envio=BoletaMailEmision::getFechaEnvioEstandar();
                    $fecha_envio= is_null($fecha_envio) ? new \DateTime($contenidos['overview']->date) : new \DateTime($fecha_envio);
                    $fecha_boleta = BoletaMailEmision::getFechaBoletaEstandar();
                    $bhe->setBoletaEstado($bhe_vigente)
                        ->setFechaBoleta(is_null($fecha_boleta) ? null : new \DateTime($fecha_boleta))
                        ->setFechaEnvio($fecha_envio)
                        ->setFechaLectura(new \DateTime())
                        ->setNumero($boleta_numero)
                        ->setRutEmisor($rut_boleta)
                        ->setEmpresa($empresa)
                        ->setMailId($email);
                } catch(\Exception $e){
                    $output->writeln('Error de lectura util boleta mail: '.$email.' RUT:'.$rut_boleta.' boleta: '.$boleta_numero);
                }
                foreach ($contenidos['attachment'] as $attachment) {
                    if ($attachment ['is_attachment']) {
                        $nombreFichero = $attachment ['filename'];
                        $nombre = explode(".", $nombreFichero);
                        $extension = $nombre[count($nombre) - 1];

                        if (in_array($extension,['pdf', $boleta_formato])) {
                            // dump($attachment ['attachment']);
                            $adjunto = $attachment ['attachment'];
                            if ($adjunto) {
                                $path_boleta=$this->getContainer()->getParameter('ad_boleta_ruta_boletas'). DIRECTORY_SEPARATOR . $empresa->getSlug() . DIRECTORY_SEPARATOR .$nombreFichero;
                                // dump($path_boleta);
                                if($extension==BoletaService::PDF) {
                                    $bhe->setRutaArchivo($empresa->getSlug() . DIRECTORY_SEPARATOR . $nombreFichero);
                                }
                                //se guarda boleta en el servidor
                                $gestor = fopen($path_boleta, 'w');
                                if($gestor==false) {
                                    $output->writeln('Hubo un error al tratar de abrir: '.$path_boleta);
                                }
                                else {
                                    fwrite($gestor, $adjunto);
                                    fclose($gestor);
                                }

                                try {
                                    $boleta_srv->load($path_boleta,$extension);
                                    if($input->getOption('verbose')) {
                                        $output->writeln('Lectura '.$extension);
                                        $output->writeln('Glosa: '.$boleta_srv->getGlosa());
                                        $output->writeln('Bruto: '.$boleta_srv->getMontoBruto());
                                        $output->writeln('Impuesto: '.$boleta_srv->getMontoImpuesto());
                                        $output->writeln('Liquido: '.$boleta_srv->getMontoLiquido());
                                        $output->writeln('Fecha Boleta: '.$boleta_srv->getFechaBoleta());
                                    }
                                    try {
                                        $fecha_emision = $boleta_srv->getFechaEmisionEstandar();
                                        if (!is_null($fecha_emision)) {
                                            $bhe->setFechaEmision(new \DateTime($fecha_emision));
                                        }
                                        $bhe->setGlosa($boleta_srv->getGlosa())
                                            ->setMonto($boleta_srv->getMontoBruto())
                                            ->setMontoImpuesto($boleta_srv->getMontoImpuesto())
                                            ->setMontoLiquido($boleta_srv->getMontoLiquido())
                                            ->setFechaBoletaStr($boleta_srv->getFechaBoleta());
                                        if (is_null($bhe->getFechaBoleta())) {
                                            $bhe->setFechaBoleta(new \DateTime($boleta_srv->getFechaBoletaEstandar()));
                                        }
                                        if(is_null($bhe->getEmpresa())){
                                            $empresa=$em->getRepository('ADBoletaBundle:Empresa')->findOneBy(['rut' => $boleta_srv->getRutDestinatario()]);
                                            $bhe->setEmpresa($empresa);
                                        }
                                    } catch(\Exception $e){
                                        $output->writeln('Error fecha service boleta mail ('.$extension.'): '.$email.' RUT:'.$rut_boleta.' boleta: '.$boleta_numero);
                                    }
                                } catch (RuntimeException $e){
                                    if(is_null($bhe->getMonto())){
                                        $bhe->setBoletaEstado($bhe_invalid);
                                    }
                                    $output->writeln('Error de Boleta Service ('.$extension.'): '.$email.' RUT:'.$rut_boleta.' boleta: '.$boleta_numero);
                                }
                            }
                        }
                    }
                }
                if($bhe->isInvalidFecha()){
                    $bhe->setBoletaEstado($bhe_anular);
                }
                $em->persist($bhe);
                $em->flush();
            }
            if($input->getOption('status')) {
                $progressBar->advance();
            }
            $cont++;
        }
        imap_close($conn);
        if($input->getOption('status')) {
            $progressBar->finish();
        }
    }

    private function existPath($path){
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
