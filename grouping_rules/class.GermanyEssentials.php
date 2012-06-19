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

class GermanyEssentials  extends ruleBase{

    function __construct(){

    }

    function getConfig(){
        return array (
            "country" => "de",
            "lang" => "deu", //this is the language code used in the channels audio description
            "validForSatellites" => array( "S19.2E"),
            "validForCableProviders" => "all",
            "validForTerrProviders" => "all",
        );
    }

    function getGroups(){
        return array (

            //group regional channels before the national channel are grouped
            array(
                "title" => "Public Regional",
                "outputSortPriority" => 2,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => "AND ".DE_PUBLIC_REGIONAL
            ),

            array(
                "title" => "Public",
                "outputSortPriority" => 1,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => "AND ".DE_PUBLIC_PROVIDER
            ),

            array(
                "title" => "Public OLD FREQUENCIES",
                "outputSortPriority" => 99,
                "languageOverrule" => "",
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => "AND ".DE_PUBLIC_PROVIDER . " AND (name LIKE '% alt' OR name LIKE '%_alt')"
            ),

            array(
                "title" => "Public OLD FREQUENCIES",
                "outputSortPriority" => 99,
                "languageOverrule" => "",
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeData,
                "customwhere" => "AND ".DE_PUBLIC_PROVIDER . " AND (name LIKE '% alt' OR name LIKE '%_alt')"
            ),

            //group test channels before the other channel are grouped
            array(
                "title" => "Public ARD-Test",
                "outputSortPriority" => 99, // 99 should just ensure it is at the end of the list
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => "AND ".DE_PROVIDER_ARD." AND UPPER(name) LIKE '%TEST%' "
            ),

            //group regional channels before the national channel are grouped
            array(
                "title" => "Public Regional",
                "outputSortPriority" => 5,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => "AND ".DE_PUBLIC_REGIONAL
            ),

            array(
                "title" => "Public",
                "outputSortPriority" => 4,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" =>
                    "AND ( ". DE_PROVIDER_ARD." OR provider LIKE 'ZDF%') "
            ),

            array(
                "title" => "Private",
                "outputSortPriority" => 10,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => "AND NOT (" . DE_PUBLIC_PROVIDER . " OR ".AUSTRIA." " . " OR ".SWITZERLAND." OR UPPER(provider) = 'SKY')"
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
                "languageOverrule" => "", //needed for channel 21
                "customwhere" => "AND ".DE_PRIVATE_PRO7_RTL . "AND NOT (" . AUSTRIA." OR ".SWITZERLAND.") AND NOT ( name LIKE '% alt' OR name LIKE '%_alt')"
            ),

            //provider undefined only wilhelm.tel --> sky
            //don't change details here (Private 2/13) - it is merged with GermanySatNonEssentials!!!
            array(
                "title" => "Private2",
                "outputSortPriority" => 13,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" =>
                    " AND ". FILTER_ASTRA1_FTA . " AND NOT (". DE_PUBLIC_PROVIDER. " OR ".DE_PRIVATE_PRO7_RTL." OR ".AUSTRIA." OR ".SWITZERLAND." OR UPPER(provider) = 'UNDEFINED') AND NOT name = '.' AND NOT UPPER(provider) = 'CSAT' AND NOT name LIKE '% alt' AND NOT name LIKE '%_alt'"
            ),

            //provider undefined only wilhelm.tel --> sky
            //don't change details here (Private 2/13) - it is merged with GermanySatNonEssentials!!!
            //This is only for Viva Germany
            array(
                "title" => "Private2",
                "outputSortPriority" => 13,
                "languageOverrule" => "",
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND UPPER(provider) = 'MTV NETWORKS EUROPE'"
            ),

            array(
                "title" => "MTVNetworksEurope",
                "outputSortPriority" => 26,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                //"languageOverrule" => "",
                "customwhere" => " AND UPPER(provider) = 'MTV NETWORKS EUROPE'"
            ),

            array(
                "title" => "MTVNetworksEurope",
                "outputSortPriority" => 27,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                //"languageOverrule" => "",
            	"customwhere" => " AND UPPER(provider) = 'MTV NETWORKS EUROPE'"
            ),

            array(
                "title" => "MTVNetworksEurope",
                "outputSortPriority" => 28,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                //"languageOverrule" => "",
                "customwhere" => " AND UPPER(provider) = 'MTV NETWORKS EUROPE'"
            ),

            array(
                "title" => "MTVNetworksEurope",
                "outputSortPriority" => 29,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                //"languageOverrule" => "",
                "customwhere" => " AND UPPER(provider) = 'MTV NETWORKS EUROPE' AND NOT UPPER(name) LIKE '%FRANCE%' AND NOT apid LIKE '%fra%'"
            ),

            array(
                "title" => "Public Test",
                "outputSortPriority" => 99,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND ".DE_PUBLIC_PROVIDER . " AND name LIKE '%test%'"
            ),

            array(
                "title" => "Public NDR",
                "outputSortPriority" => 30,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND ".DE_PUBLIC_PROVIDER . " AND provider = 'ARD NDR'"
            ),

            array(
                "title" => "Public MDR",
                "outputSortPriority" => 30,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND ".DE_PUBLIC_PROVIDER  . " AND provider = 'ARD MDR'"
            ),

            array(
                "title" => "Public WDR",
                "outputSortPriority" => 30,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND ".DE_PUBLIC_PROVIDER . " AND provider = 'ARD WDR'"
            ),

            array(
                "title" => "Public HR",
                "outputSortPriority" => 30,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND ".DE_PUBLIC_PROVIDER  . " AND provider = 'ARD HR'"
            ),

            array(
                "title" => "Public RB",
                "outputSortPriority" => 30,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND ".DE_PUBLIC_PROVIDER  . " AND provider = 'ARD RB'"
            ),

            array(
                "title" => "Public SWR",
                "outputSortPriority" => 30,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND ".DE_PUBLIC_PROVIDER . " AND provider = 'ARD SWR'"
            ),

            array(
                "title" => "Public BR",
                "outputSortPriority" => 30,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND ".DE_PUBLIC_PROVIDER  . " AND provider = 'ARD BR'"
            ),

            array(
                "title" => "Public rbb",
                "outputSortPriority" => 30,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND ".DE_PUBLIC_PROVIDER  . " AND provider = 'ARD rbb'"
            ),

            array(
                "title" => "Public SR",
                "outputSortPriority" => 30,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND ".DE_PUBLIC_PROVIDER  . " AND provider = 'ARD SR'"
            ),

            array(
                "title" => "Public ZDFvision",
                "outputSortPriority" => 30,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND ".DE_PUBLIC_PROVIDER  . " AND provider = 'ZDFvision'"
            ),

            //catch remaining channels, should be empty
            array(
                "title" => "Public",
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
        );
    }
}

?>