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

class GermanyEssentials  extends ruleBase{

    function __construct(){

    }

    function getConfig(){
        return array (
            "country" => "de",
            "lang" => "deu", //this is the language code used in the channels audio description
            "validForSatellites" => array( "S19.2E"),
            "validForCableProviders" => "all",//TODO, exclude non-de
            "validForTerrProviders" => "all",
        );
    }

    function getGroups(){
        return array (
            array(
                "title" => "Public",
                "outputSortPriority" => 1,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => "AND ".DE_PUBLIC_PROVIDER
            ),

            array(
                "title" => "Public",
                "outputSortPriority" => 2,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" =>
                    "AND ( ".
                    "(".DE_PROVIDER_ARD." AND ( UPPER(name) LIKE '%ERSTE%' OR UPPER(name) LIKE '%EINS%' OR UPPER(name) LIKE '%ARTE%' OR UPPER(name) LIKE '%PHOENIX%' )) ".
                    " OR provider LIKE 'ZDF%'".
                    ") "
            ),

            array(
                "title" => "Public Regional",
                "outputSortPriority" => 3,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" =>
                    "AND ".DE_PROVIDER_ARD." AND NOT ( UPPER(name) LIKE '%ERSTE%' OR UPPER(name) LIKE '%EINS%' OR UPPER(name) LIKE '%ARTE%' OR UPPER(name) LIKE '%PHOENIX%' OR UPPER(name) LIKE '%TEST%') "
            ),

            array(
                "title" => "Public ARD-Test",
                "outputSortPriority" => 99, // 99 should just ensure it is at the end of the list
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" =>
                    "AND ".DE_PROVIDER_ARD." AND UPPER(name) LIKE '%TEST%' "
            ),

            array(
                "title" => "Private",
                "outputSortPriority" => 10,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => "AND NOT (" . DE_PUBLIC_PROVIDER . " OR ".AUSTRIA." " . " OR ".SWITZERLAND.")"
            ),

            array(
                "title" => "Private HD+",
                "outputSortPriority" => 11,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" =>
                    "AND NOT (" . AUSTRIA." OR ".SWITZERLAND.") ".
                    "AND (UPPER(provider) = 'BETADIGITAL' ".
                    "OR UPPER(provider) = 'CBC' ".
                    "OR UPPER(provider) = 'PROSIEBENSAT.1' ".
                    "OR UPPER(provider) = 'MTV NETWORKS'".
                    ")"
            ),

            array(
                "title" => "Private",
                "outputSortPriority" => 12,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => "AND ".DE_PRIVATE_PRO7_RTL . "AND NOT (" . AUSTRIA." OR ".SWITZERLAND.")"
            ),

            //provider undefined only wilhelm.tel --> sky
            //don't change details here (Private 2/13) - it is merged with GermanySatNonEssentials!!!
            array(
                "title" => "Private2",
                "outputSortPriority" => 13,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" =>
                    " AND ". FILTER_ASTRA1_FTA . " AND NOT (". DE_PUBLIC_PROVIDER. " OR ".DE_PRIVATE_PRO7_RTL." OR ".AUSTRIA." OR ".SWITZERLAND." OR UPPER(provider) = 'UNDEFINED') AND NOT name = '.' AND NOT UPPER(provider) = 'CSAT'"
            ),

            array(
                "title" => "sky_de",
                "outputSortPriority" => 20,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => " AND (UPPER(provider) = 'SKY' OR provider = '' OR provider = 'undefined')"
            ),

            array(
                "title" => "sky_de",
                "outputSortPriority" => 21,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND (UPPER(provider) = 'SKY' OR provider = '' OR provider = 'undefined')"
            ),

            array(
                "title" => "sky_de",
                "outputSortPriority" => 22,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "languageOverrule" => "", //ESPN America HD is in English!
                "customwhere" => " AND (UPPER(provider) = 'SKY') AND name != '.'"
                //OR provider = '' OR UPPER(provider) = 'UNDEFINED'
            ),

            //provider undefined only wilhelm.tel --> sky
            array(
                "title" => "sky_de",
                "outputSortPriority" => 23,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND name NOT LIKE '% - %' AND name != 'Spieldaten' AND (UPPER(provider) = 'SKY' OR provider = '' OR provider = 'undefined') AND name != '.'"
            ),

            //provider undefined only wilhelm.tel --> sky
            array(
                "title" => "sky_de",
                "outputSortPriority" => 24,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND (name LIKE '% - %' OR name = 'Spieldaten' OR name = '.') AND (UPPER(provider) = 'SKY' OR provider = '' OR provider = 'undefined')"
            ),

            array(
                "title" => "MTVNetworksEurope",
                "outputSortPriority" => 25,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                "languageOverrule" => "",
                "customwhere" => " AND UPPER(provider) = 'MTV NETWORKS EUROPE'"
            ),

            array(
                "title" => "MTVNetworksEurope",
                "outputSortPriority" => 26,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "languageOverrule" => "",
            	"customwhere" => " AND UPPER(provider) = 'MTV NETWORKS EUROPE'"
            ),

            array(
                "title" => "MTVNetworksEurope",
                "outputSortPriority" => 27,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "languageOverrule" => "",
                "customwhere" => " AND UPPER(provider) = 'MTV NETWORKS EUROPE'"
            ),

            array(
                "title" => "MTVNetworksEurope",
                "outputSortPriority" => 28,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "languageOverrule" => "",
                "customwhere" => " AND UPPER(provider) = 'MTV NETWORKS EUROPE'"
            ),

            array(
                "title" => "Public ARD ZDF",
                "outputSortPriority" => 30,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND ".DE_PUBLIC_PROVIDER
            ),

            //CAUTION: Don't change Private 31 because it matches a group in GermanySatNonEssentials that is merged with this group
            array(
                "title" => "Private",
                "outputSortPriority" => 31,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND NOT  ".DE_PUBLIC_PROVIDER . "AND NOT " . AUSTRIA
            ),

            //provider undefined only wilhelm.tel --> sky
            array(
                "title" => "sky_de",
                "outputSortPriority" => 40,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND (UPPER(provider) LIKE 'SKY' OR provider = '' OR provider = 'undefined')"
            ),
        );
    }
}

?>