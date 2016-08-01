<?php

namespace AscensoDigital\BoletaBundle\Controller;

use AscensoDigital\BoletaBundle\Entity\BoletaHonorario;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    /**
     * @param BoletaHonorario $bhe
     * @return Response
     * @Route("/bhe/download/{id}", name="ad_boleta_download")
     * @Security("is_granted('permiso','ad_boleta-download')")
     */
    public function downloadAction(BoletaHonorario $bhe)
    {
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
}
