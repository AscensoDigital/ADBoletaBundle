<?php

namespace AscensoDigital\BoletaBundle\Service;

use XPDF\PdfToText;

/**
 * Description of XpdfService
 *
 * @author claudio
 */
class XpdfService {
    private $pdf2text;
    
    public function __construct($logger) {
        $this->pdf2text= PdfToText::create(array(), $logger);
    }
    
    public function getText($pathfile, $page_start = null, $page_end = null) {
        return $this->pdf2text->getText($pathfile, $page_start, $page_end);
    }
}