<?php

namespace AscensoDigital\BoletaBundle\Command;

use AscensoDigital\BoletaBundle\Entity\BoletaEstado;
use AscensoDigital\BoletaBundle\Entity\BoletaHonorario;
use AscensoDigital\BoletaBundle\Entity\Empresa;
use AscensoDigital\BoletaBundle\Service\BoletaManager;
use AscensoDigital\BoletaBundle\Service\EmailReaderService;
use AscensoDigital\BoletaBundle\Util\BoletaMailEmision;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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
            ->addOption('mail_id','m',InputOption::VALUE_OPTIONAL,'id email en particular',null);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        /** @var ObjectManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('ad_boleta');
        
        /** @var BoletaManager $boleta_srv */
        $boleta_srv= $this->getContainer()->get('ad_boleta.boleta_manager');
        /** @var EmailReaderService $email_reader */
        $email_reader=$this->getContainer()->get('ad_boleta.email_reader');
        
        $bhe_vigente=$em->getRepository('ADBoletaBundle:BoletaEstado')->find(BoletaEstado::VIGENTE);
        $bhe_invalid=$em->getRepository('ADBoletaBundle:BoletaEstado')->find(BoletaEstado::PDF_INVALIDO);
        //$user = 'consultas@ennea.cl';
        //$password = 'ennea.2014';

        $user = $input->getArgument('user');#'pagos@cgslogistica.cl';
        $password = $input->getArgument('password');#'pagos.2015';
        $mailbox = "{imap.gmail.com:993/imap/ssl}INBOX";

        $conn=imap_open($mailbox , $user , $password) or die('Cannot connect to Gmail: ' . imap_last_error());
        $log=false;
        if(is_null($input->getOption('mail_id'))){
            $vigentes = imap_search($conn, 'SUBJECT "Emision" UNSEEN', SE_UID);
            //$vigentes = imap_search($conn, 'SUBJECT "Emision"', SE_UID);
        }
        else {
            $log=true;
            $vigentes=array($input->getOption('mail_id'));
        }
        if(!$vigentes) {
            imap_close($conn);
            return;
        }
        $tot=count($vigentes);
        $particion = round($tot/20) ? round($tot/20) : 20;
        if($log) {
            $output->writeln('Se revisarán ' . $tot . ' correos');
        }
        $cont=0;
        foreach ($vigentes as $email) {
            if($log){
                $output->writeln($email);
                $structure = imap_fetchstructure($conn, $email);
                print_r($structure);
            }
            $contenidos=$email_reader->getContenido($conn,$email);
            if(!isset($contenidos['plain'][0])) {
                $output->writeln($email.' Correo sin cuerpo leible');
                $email_body='';
            }
            else {
                $email_body=$contenidos['plain'][0];
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
            }
            //$output->writeln(' Buscando Boleta '.$boleta_numero.' RUT: '.$rut_boleta);
            /** @var BoletaHonorario $bhe */
            $bhe=$em->getRepository('ADBoletaBundle:BoletaHonorario')->findOneBy(array('rutEmisor' => $rut_boleta, 'numero' => $boleta_numero));
            if(!$bhe or $bhe->isInvalidPdf()) {
                if(!$bhe){
                    $bhe= new BoletaHonorario();
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
                        $nombre = explode(".", $attachment ['filename']);
                        $extension = $nombre[count($nombre) - 1];
                        if ($extension == 'pdf') {
                            //dump($attachment ['attachment']);
                            $nombreFichero = $attachment ['filename'];
                            $adjunto = $attachment ['attachment'];
                            if ($adjunto) {
                                $path_boleta=$this->getContainer()->getParameter('ad_boleta_ruta_boletas'). DIRECTORY_SEPARATOR . $empresa->getSlug() . DIRECTORY_SEPARATOR .$nombreFichero;
                                $bhe->setRutaArchivo($empresa->getSlug() . DIRECTORY_SEPARATOR . $nombreFichero);
                                
                                //se guarda boleta en el servidor
                                $gestor = fopen($path_boleta, 'w');
                                fwrite($gestor, $adjunto);
                                fclose($gestor);
                                try {
                                    $boleta_srv->loadPdf($path_boleta);
                                    /*$output->writeln('Lectura pdf');
                                    $output->writeln('Glosa: '.$boleta_srv->getGlosa());
                                    $output->writeln('Bruto: '.$boleta_srv->getMontoBruto());
                                    $output->writeln('Impuesto: '.$boleta_srv->getMontoImpuesto());
                                    $output->writeln('Liquido: '.$boleta_srv->getMontoLiquido());
                                    $output->writeln('Fecha Boleta: '.$boleta_srv->getFechaBoleta());*/
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
                                        $output->writeln('Error fecha service boleta mail: '.$email.' RUT:'.$rut_boleta.' boleta: '.$boleta_numero);
                                    }
                                } catch (RuntimeException $e){
                                    if(is_null($bhe->getMonto())){
                                        $bhe->setBoletaEstado($bhe_invalid);
                                    }
                                    $output->writeln('Error de pdf mail: '.$email.' RUT:'.$rut_boleta.' boleta: '.$boleta_numero);
                                }
                            }
                        }
                    }
                }
                $em->persist($bhe);
                $em->flush();
            }
            /*else {
                $output->writeln(' [OK]');
            }*/
            if(($cont % $particion) == 0) {
                //$em->flush();
                //$output->writeln($cont."/".$tot.' - '.round($cont/$tot*100).'% - '.date('H:i:s'));
            }
            $cont++;
        }
        //$em->flush();
        imap_close($conn);
    }
}
