<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 - 2012 Henning Pingel
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

class ItalyEssentials  extends ruleBase {

    function __construct(){

    }

    function getConfig(){
        return array(
            "country" => "it",
            "lang" => "ita", //this is the language code used in the channels audio description
            "validForSatellites" => array( "S13E"),
            "validForCableProviders" => array(),//none
            "validForTerrProviders" => array(),//none
        );
    }

    function getGroups(){
        return array(
            array(
                "title" => "RAI",
                "outputSortPriority" => 1,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                "languageOverrule" => "",
                "customwhere" => " AND UPPER( provider ) = 'RAI'"
            ),

            array(
                "title" => "RAI",
                "outputSortPriority" => 2,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "languageOverrule" => "",
                "customwhere" => " AND UPPER( provider ) = 'RAI'"
            ),

            array(
                "title" => "RAI",
                "outputSortPriority" => 3,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "languageOverrule" => "",
                "customwhere" => " AND UPPER( provider ) = 'RAI'"
            ),

            array(
                "title" => "RAI",
                "outputSortPriority" => 4,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "languageOverrule" => "",
                "customwhere" => " AND UPPER( provider ) = 'RAI'"
            ),

            array(
                "title" => "Sky Italia",
                "outputSortPriority" => 10,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                "languageOverrule" => "",
                "customwhere" => " AND UPPER( provider ) = 'SKYITALIA'"
            ),

            array(
                "title" => "Sky Italia",
                "outputSortPriority" => 11,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "languageOverrule" => "",
                "customwhere" => " AND UPPER( provider ) = 'SKYITALIA'"
            ),

            array(
                "title" => "Sky Italia",
                "outputSortPriority" => 12,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "languageOverrule" => "",
                "customwhere" => " AND UPPER( provider ) = 'SKYITALIA'"
            ),

            array(
                "title" => "Sky Italia",
                "outputSortPriority" => 13,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "languageOverrule" => "",
                "customwhere" => " AND UPPER( provider ) = 'SKYITALIA'"
            ),

            array(
                "title" => "Various",
                "outputSortPriority" => 20,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => ""
            ),

            array(
                "title" => "Various",
                "outputSortPriority" => 21,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => ""
            ),

            array(
                "title" => "Various",
                "outputSortPriority" => 22,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => ""
            ),

            array(
                "title" => "Various",
                "outputSortPriority" => 23,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => ""
            ),

            array(
                "title" => "RAI",
                "outputSortPriority" => 40,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "languageOverrule" => "",
                "customwhere" => " AND UPPER( provider ) = 'RAI'"
            ),

            array(
                "title" => "RAI",
                "outputSortPriority" => 41,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeRadio,
                "languageOverrule" => "",
                "customwhere" => " AND UPPER( provider ) = 'RAI'"
            ),

            array(
                "title" => "Sky Italia",
                "outputSortPriority" => 42,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND UPPER( provider ) = 'SKYITALIA'",
                "languageOverrule" => "",
            ),

            array(
                "title" => "Sky Italia",
                "outputSortPriority" => 43,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND UPPER( provider ) = 'SKYITALIA'",
                "languageOverrule" => "",
            ),

            array(
                "title" => "Various",
                "outputSortPriority" => 44,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => ""
            ),

            array(
                "title" => "Various",
                "outputSortPriority" => 45,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => ""
            ),
        );
    }

}

?>