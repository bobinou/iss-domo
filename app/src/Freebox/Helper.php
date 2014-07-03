<?php namespace Freebox;


class Helper
{
    /**
     * Converti (récursivement) un array en XML (via DOM)
     */
    public static function DomArrayToXml($array, $dom_doc, $node)
    {
        $array_special_char = array(true, false, "<", ">", "&", "'", "\"");
        $array_replace_char = array(1,0,"&lt;", "&gt;", "&amp;","&apos;", "&quot;");

        if (is_array($array))
        {
            foreach($array as $key => $item)
            {
                if (is_numeric($key))
                    $key = "id-".$key;

                if(is_array($item))
                {
                    $element = $dom_doc->createElement($key);
                    DomArrayToXml($item, $dom_doc, $element);
                    $node->appendChild($element);
                }
                else
                {
                    //if ($item === true) $item = 1;
                    //if ($item === false) $item = 0;
                    $encoded_item = str_replace ($array_special_char, $array_replace_char,$item);
                    $element = $dom_doc->createElement($key,utf8_encode($encoded_item));
                    $node->appendChild($element);
                }
            }
        }
        else
        {
            $element = $dom_doc->createElement("Datas",utf8_encode("Nothing"));
            $node->appendChild($element);
        }
    }
}