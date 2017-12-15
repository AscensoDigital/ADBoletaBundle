<?php

namespace AscensoDigital\BoletaBundle\Service;

use XPDF\PdfToText;

/**
 * Description of XpdfService
 *
 * @author claudio
 */
class XpdfService {
    /**
     * @var PdfToText
     */
    private $pdf2text;
    
    public function __construct($logger) {
        try {
            $this->pdf2text = PdfToText::create([], $logger);
        }
        catch (\Exception $e) {
            $this->pdf2text = null;
        }
    }
    
    public function getText($pathfile, $page_start = null, $page_end = null) {
        return is_null($this->pdf2text) ? '' : $this->pdf2text->getText($pathfile, $page_start, $page_end);
    }
}