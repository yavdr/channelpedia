<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Henning Pingel
*  All rights reserved
*
*  This script is part of the yaVDR project. yaVDR is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*/

class ScotlandEssentials  extends ruleBase {

    function __construct(){

    }

    function getConfig(){
        return array (
            "country" => "scotland",
            "lang" => "eng", //this is the language code used in the channels audio description
            "validForSatellites" => array( "S28.2E"),
            "validForCableProviders" => array(),
            "validForTerrProviders" => array(),
        );
    }

    function getGroups(){
        return array (

            //3855;BSkyB:10935:VC56M2O0S0:S28.2E:22000:512=27:640=NAR@4;660=eng@106:576:0:3855:2:2056:0
            //channel 3855 aka STV HD doesn't indicate to be HDTV (no S2 transponder, no HD in name), therefore we need OR in customwhere
            array(
                "title" => "STV",
                "outputSortPriority" => 1,
                "languageOverrule" => "eng,gla",
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => " AND sid='3855' OR (sid='3855' AND nid='2' AND tid='2056')"
            ),

            array(
                "title" => "freesat",
                "outputSortPriority" => 2,
                "languageOverrule" => "eng,gla",
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND (upper(name) LIKE '%ALBA%' OR upper(name) LIKE '%SCOT%' OR upper(name) = 'STV')"
            ),

            array(
                "title" => "freesat",
                "outputSortPriority" => 40,
                "languageOverrule" => "eng,gla",
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND (upper(name) LIKE '%GAEL%' OR upper(name) LIKE '%SCOT%')"
            ),
        );
    }

}

?>