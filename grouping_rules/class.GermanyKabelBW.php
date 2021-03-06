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

class GermanyKabelBW  extends ruleBase{

    function __construct(){

    }

    function getConfig(){
        return array (
            "country" => "de",
            "lang" => "deu", //this is the language code used in the channels audio description
            "validForSatellites" => array(),
            "validForCableProviders" => array(
                "C[de_KabelBW_Heidelberg]"
            ),
            "validForTerrProviders" => array(),//none
        );
    }

    function getGroups(){
        return array (
            array(
                "title" => "Private",
                "outputSortPriority" => 5,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "languageOverrule" => "",
                "customwhere" => "AND provider = 'HD'"
            ),
            array(
                "title" => "Diverse Private",
                "outputSortPriority" => 15,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => "" //AND provider = 'Bonus'"
            ),
            array(
                "title" => "Diverse Private",
                "outputSortPriority" => 16,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                //"languageOverrule" => "",
                "customwhere" => "" //AND provider = 'Bonus'"
            ),
            array(
                "title" => "Diverse",
                "outputSortPriority" => 33,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeRadio,
                "languageOverrule" => "",
                "customwhere" => "" // "AND provider = 'Bonus'"
            ),
            array(
                "title" => "Diverse",
                "outputSortPriority" => 34,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                //"languageOverrule" => "",
                "customwhere" => "" // "AND provider = 'Bonus'"
            ),

/*
            array(
                "title" => "KDHome Private",
                "outputSortPriority" => 6,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => "AND provider = 'KD Home'"
            )*/
        );
    }

}

?>