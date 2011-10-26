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

class AustriaSatEssentials extends ruleBase{

    function __construct(){

    }

    function getConfig(){
        return array(
            "country" => "at",
            "lang" => "deu", //this is the language code used in the channels audio description
            "validForSatellites" => array( "S19.2E"),
            "validForCableProviders" => array("at_salzburg-ag"),
            "validForTerrProviders" => array(),//none
        );
    }

    function getGroups(){
        return array(

            array(
                "title" => "ORF",
                "outputSortPriority" => 1,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => "AND UPPER(provider) = 'ORF'"
            ),

            array(
                "title" => "ORF",
                "outputSortPriority" => 2,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => "AND UPPER(provider) = 'ORF'"
            ),

            array(
                "title" => "ORF",
                "outputSortPriority" => 3,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => "AND UPPER(provider) = 'ORF'"
            ),

            array(
                "title" => "ORF",
                "outputSortPriority" => 4,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => "AND UPPER(provider) = 'ORF'"
            ),

            array(
                "title" => "Private",
                "outputSortPriority" => 5,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => "AND ". AUSTRIA
            ),

            array(
                "title" => "Private",
                "outputSortPriority" => 6,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => "AND ". AUSTRIA. "AND ". DE_PRIVATE_PRO7_RTL
            ),

            array(
                "title" => "Private",
                "outputSortPriority" => 7,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "languageOverrule" => "", // needed for RTL2
                "customwhere" => "AND ". AUSTRIA . " AND ". DE_PRIVATE_PRO7_RTL
            ),

            array(
                "title" => "Private2",
                "outputSortPriority" => 8,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => "AND ". AUSTRIA
            ),

            array(
                "title" => "Private",
                "outputSortPriority" => 9,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "languageOverrule" => "", // needed for RTL2
                "customwhere" => "AND ". AUSTRIA
            ),


            //radio

            array(
                "title" => "ORF/ORS",
                "outputSortPriority" => 10,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => "AND UPPER(provider) = 'ORF' OR  UPPER(provider) = 'ORS'"
            ),

            array(
                "title" => "Diverse",
                "outputSortPriority" => 11,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND " . AUSTRIA
            ),

            array(
                "title" => "Diverse",
                "outputSortPriority" => 12,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND " . AUSTRIA
            ),

        );
    }

}
?>