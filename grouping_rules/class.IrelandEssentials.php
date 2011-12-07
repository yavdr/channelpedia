<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Henning Pingel
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

class IrelandEssentials  extends ruleBase {

    function __construct(){

    }

    function getConfig(){
        return array (
            "country" => "ie",
            "lang" => "eng", //this is the language code used in the channels audio description
            "validForSatellites" => array( "S28.2E"),
            "validForCableProviders" => array(),
            "validForTerrProviders" => array(),
        );
    }

    function getGroups(){
        return array (

            array(
                "title" => "RTÉ",
                "outputSortPriority" => 30,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND UPPER(name) LIKE 'RTE%'"
            ),

            array(
                "title" => "Private",
                "outputSortPriority" => 30,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND (UPPER(name) LIKE 'TV3' OR UPPER(name) LIKE 'TG4')"
            ),

            array(
                "title" => "Setanta",
                "outputSortPriority" => 30,
                "languageOverrule"=>"",
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND UPPER(name) LIKE 'SETANTA%'"
            ),


            array(
                "title" => "freesat RTÉ",
                "outputSortPriority" => 41,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND upper(name) LIKE 'RTE %' "
            ),

        );
    }

}

?>