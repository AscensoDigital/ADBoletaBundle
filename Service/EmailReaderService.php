<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 14-04-16
 * Time: 2:50
 */

namespace AscensoDigital\BoletaBundle\Service;


class EmailReaderService
{
    public function getContenido($inbox, $email_number){
        $ret=array();
        /* get information specific to this email */
        $ret['overview'] = imap_fetch_overview($inbox,$email_number,0);

        /* get mail structure */
        $structure = imap_fetchstructure($inbox, $email_number);

        $plains=array();
        $attachments = array();

        /* if any attachments found... */
        if(isset($structure->parts) && count($structure->parts))
        {
            for($i = 0; $i < count($structure->parts); $i++)
            {
                $plains[$i]=array(
                    'is_plain' => false,
                    'plain' => ''
                );
                $attachments[$i] = array(
                    'is_attachment' => false,
                    'filename' => '',
                    'name' => '',
                    'attachment' => ''
                );

                if($structure->parts[$i]->ifdparameters)
                {
                    foreach($structure->parts[$i]->dparameters as $object)
                    {
                        if(strtolower($object->attribute) == 'filename')
                        {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['filename'] = $object->value;
                        }
                    }
                }

                if($structure->parts[$i]->ifparameters)
                {
                    foreach($structure->parts[$i]->parameters as $object)
                    {
                        if(strtolower($object->attribute) == 'name')
                        {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['name'] = $object->value;
                        }
                    }
                }

                if($attachments[$i]['is_attachment']) {
                    $attachments[$i]['attachment'] = $this->utf8_encode($structure->parts[$i]->encoding, imap_fetchbody($inbox, $email_number, $i+1));
                }

                if(strtolower($structure->parts[$i]->subtype) == 'plain') {
                    $plains[$i]['is_plain']=true;
                }

                if($plains[$i]['is_plain']) {
                    $plains[$i]['plain'] = $this->utf8_encode($structure->parts[$i]->encoding, imap_fetchbody($inbox,$email_number ,$i+1));
                }
            }
        }

        foreach($attachments as $attachment)
        {
            if($attachment['is_attachment'] == 1)
            {
                $ret['attachment'][]=$attachment;
            }
        }

        foreach ($plains as $plain) {
            if($plain['is_plain']){
                $ret['plain'][]=$plain;
            }
        }
        return $ret;
    }

    private function utf8_encode($encoding,$valor){
        /* 3 = BASE64 encoding */
        if($encoding == ENCBASE64)
        {
            $valor = base64_decode($valor);
        }
        /* 4 = QUOTED-PRINTABLE encoding */
        elseif($encoding == ENCQUOTEDPRINTABLE)
        {
            $valor = quoted_printable_decode($valor);
        }
        return mb_detect_encoding($valor) != 'UTF-8' ? utf8_encode($valor) : $valor;
    }
}