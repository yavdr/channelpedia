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

class GermanySatNonEssentials  extends ruleBase{

    function __construct(){

    }

    function getConfig(){
        return array (
            "country" => "de",
            "lang" => "deu", //this is the language code used in the channels audio description
            "validForSatellites" => array( "S19.2E"),
            "validForCableProviders" => array(),//none
            "validForTerrProviders" => array(),//none
        );
    }

    function getGroups(){
        return array (

            //don't change details here (Private 2/13) - it is merged with GermanyEssentials!!!
            array(
                "title" => "Private2",
                "outputSortPriority" => 13,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                //"languageOverrule" => "", //MEDIA BROADCAST doesn't have German audio language
                "customwhere" =>
                    " AND NOT ". FILTER_ASTRA1_FTA . " AND NOT (". DE_PUBLIC_PROVIDER. " OR ".DE_PRIVATE_PRO7_RTL." OR ".AUSTRIA." OR ".SWITZERLAND.") "
            ),

            array(
                "title" => "Private",
                "outputSortPriority" => 11,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => "AND ".DE_PRIVATE_PRO7_RTL . "AND NOT (" . AUSTRIA." OR ".SWITZERLAND.")"
            ),

            //CAUTION: details here (Private 31) are deliberately the same as in GermanyEssentials. Content of this selection should
            //be merged with group from GermanyEssentials.
            array(
                "title" => "Private",
                "outputSortPriority" => 31,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "languageOverrule" => "", //MEDIA BROADCAST doesn't have German audio language, we could use "und" instead
                "customwhere" => " AND (UPPER(provider) = 'MEDIA BROADCAST' OR UPPER(provider) = 'BETADIGITAL')"
            ),
        );
    }

}

?>