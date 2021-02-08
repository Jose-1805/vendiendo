<?php

/**
 * Created by PhpStorm.
 * User: Desarrollador 1
 * Date: 16/08/2016
 * Time: 1:45 PM
 */
namespace App;
class TildeHtml
{
    public static function TildesToHtml($cadena)
    {
        return str_replace(
            array("á","é","í","ó","ú","ñ","Á","É","Í","Ó","Ú","Ñ"),
            array("&aacute;","&eacute;","&iacute;","&oacute;","&uacute;","&ntilde;",
                "&Aacute;","&Eacute;","&Iacute;","&Oacute;","&Uacute;","&Ntilde;"), $cadena);
    }

}