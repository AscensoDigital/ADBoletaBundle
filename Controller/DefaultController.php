<?php

namespace AscensoDigital\BoletaBundle\Controller;

use AscensoDigital\BoletaBundle\ADBoletaEvents;
use AscensoDigital\BoletaBundle\Doctrine\BoletaHonorarioManager;
use AscensoDigital\BoletaBundle\Entity\BoletaEstado;
use AscensoDigital\BoletaBundle\Event\BoletaHonorarioEvent;
use AscensoDigital\BoletaBundle\Form\CargaResumenBoletasSiiFormType;
use AscensoDigital\BoletaBundle\Util\CargaResumenBoletasSii;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    /**
     * @param $id
     * @return BinaryFileResponse
     * @Route("/bhe/download/{id}", name="ad_boleta_download")
     * @Security("is_granted('permiso','ad_boleta-download')")
     */
    public function downloadAction($id) {
        /** @var BoletaHonorarioManager $bh_manager */
        $bh_manager=$this->get('ad_boleta.boleta_honorario_manager');
        $bhe=$bh_manager->findBoletaHonorarioBy(['id' => $id]);

        if(is_null($bhe)){
            throw new NotFoundHttpException('Boleta de Honorario No encontrada');
        }
        $path = $this->getParameter('ad_boleta_ruta_boletas'). DIRECTORY_SEPARATOR;
        $ruta = $bhe->getRutaArchivo();
        $get_nombre = explode("/", $ruta);
        $nombre = array_pop($get_nombre);
        $archivo = $path.$ruta; // Path to the file on the server
        $response = new BinaryFileResponse($archivo);

        // Give the file a name:
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,$nombre);

        return $response;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/bhe/list", name="ad_boleta_boleta_list")
     * @Security("is_granted('permiso','ad_boleta-list')")
     */
    public function listAction()
    {
        return $this->render('ADBoletaBundle:default:list.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/bhe/list-table", name="ad_boleta_boleta_list_table")
     * @Security("is_granted('permiso','ad_boleta-list')")
     */
    public function listTableAction() {
        $filtros = $this->get('ad_perfil.filtro_manager');
        $boletas = $this->get('ad_boleta.boleta_honorario_manager')->findArrayByFiltros($filtros);
        return $this->render('ADBoletaBundle:default:list-table.html.twig', array(
            'boletas' => $boletas
        ));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/bhe/load-resumen-sii", name="ad_boleta_load_resumen_boletas_sii")
     * @Security("is_granted('permiso','ad_boleta-load-resumen-bhe-sii')")
     */
    public function loadResumenBoletasSiiAction(Request $request) {
        $crbh = new CargaResumenBoletasSii();
        $form = $this->createForm(CargaResumenBoletasSiiFormType::class, $crbh);
        $form->handleRequest($request);
        $data=array();
        if($form->isSubmitted() && $form->isValid()){
            $data=$crbh->procesa($this->get('ad_boleta.boleta_honorario_manager'),  $this->getUser());
        }
        return $this->render('ADBoletaBundle:default:load-resumen-boletas-sii.html.twig',
            [ 'form' => $form->createView(), 'menu_superior' => $this->getParameter('ad_boleta.config')['menu_superior_slug'], 'data' => $data ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/bhe/vca/vigente/{boleta_id}", name="ad_boleta_vca_vigente")
     * @Security("is_granted('permiso','ad_boleta-vca')")
     */
    public function boletaVcaVigenteAction($boleta_id) {
        $boletaHonorario=$this->get('ad_boleta.boleta_honorario_manager')->find($boleta_id);
        if($boletaHonorario){
            if($boletaHonorario->getBoletaEstado()->getId()==BoletaEstado::VCA) {
                $em = $this->getDoctrine()->getManager();
                $vcav = $em->getRepository('AppBundle:BoletaEstado')->find(BoletaEstado::VCA_VIGENTE);
                $boletaHonorario->setBoletaEstado($vcav);
                $em->persist($boletaHonorario);
                $em->flush();
                $this->addFlash('success','Boleta Número '.$boletaHonorario->getNumero().' del RUT '.$boletaHonorario->getRutEmisor().' registrado como VCA Vigente');
            }
            else{
                $this->addFlash('warning','La boleta de '.$boletaHonorario->getRutEmisor().' número '.$boletaHonorario->getNumero().' no tiene estado V.C.A.');
            }
        }
        else {
            $this->addFlash('danger','No existe la boleta con el id ingresado');
        }
        return $this->redirect($this->generateUrl('ad_boleta_boleta_list'));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/bhe/vca/anulada/{boleta_id}", name="ad_boleta_vca_anulada")
     * @Security("is_granted('permiso','ad_boleta-vca')")
     */
    public function boletaVcaAnuladaAction($boleta_id){
        $boletaHonorario=$this->get('ad_boleta.boleta_honorario_manager')->find($boleta_id);
        if($boletaHonorario){
            if($boletaHonorario->getBoletaEstado()->getId()==BoletaEstado::VCA) {
                $em = $this->getDoctrine()->getManager();
                $vcaa = $em->getRepository('AppBundle:BoletaEstado')->find(BoletaEstado::VCA_ANULADA);
                $boletaHonorario->setBoletaEstado($vcaa);
                $em->persist($boletaHonorario);

                /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
                $dispatcher = $this->get('event_dispatcher');
                $event = new BoletaHonorarioEvent($boletaHonorario);
                $dispatcher->dispatch(ADBoletaEvents::VCA_ANULADA_SUCCESS, $event);

                if (0 < count($event->getModificados())) {
                    foreach ($event->getModificados() as $obj) {
                        $em->persist($obj);
                    }
                }

                /* /** @var UsuarioPago $usuarioPago */
                /* foreach ($boletaHonorario->getUsuarioPagos() as $usuarioPago) {
                    $usuarioPago->setBoletaHonorario(null);
                    $em->persist($usuarioPago);
                }*/

                $em->flush();
                $this->addFlash('success','Boleta Número '.$boletaHonorario->getNumero().' del RUT '.$boletaHonorario->getRutEmisor().' registrado como VCA Anulada');
            }
            else{
                $this->addFlash('warning','La boleta de '.$boletaHonorario->getRutEmisor().' número '.$boletaHonorario->getNumero().' no tiene estado V.C.A.');
            }
        }
        else {
            $this->addFlash('danger','No existe la boleta con el id ingresado');
        }
        return $this->redirect($this->generateUrl('ad_boleta_boleta_list'));
    }
}
