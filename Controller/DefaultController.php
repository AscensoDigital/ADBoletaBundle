<?php

namespace AscensoDigital\BoletaBundle\Controller;

use AscensoDigital\BoletaBundle\Doctrine\BoletaHonorarioManager;
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
}
