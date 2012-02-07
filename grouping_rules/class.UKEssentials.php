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

define("UK_ITV","(".
 "upper(name) LIKE '%ITV%' OR ".
 "upper(name) LIKE 'UTV%' )");

define("UK_C4","(".
  "upper(name) LIKE 'CHANNEL 4%' OR ".
  "upper(name) LIKE 'MORE4%' OR ".
  "upper(name) LIKE 'FILM4%' OR ".
  "upper(name) LIKE 'E4%' OR ".
  "upper(name) LIKE 'S4C%'".
  ") AND provider = 'BSkyB' ");

define("UK_C5","(".
  "upper(name) LIKE 'CHANNEL 5%' OR  ".
  "upper(name) LIKE '5 USA%' OR ".
  "upper(name) LIKE '5*%'".
  ") AND provider = 'BSkyB' ");

define("IRISH","(".
  "UPPER(name) LIKE 'RTE%' OR ".
  "UPPER(name) = 'TV3' OR ".
  "UPPER(name) = 'TG4' OR ".
  "UPPER(name) LIKE 'SETANTA%'".
  ") ");

class UKEssentials  extends ruleBase {

    function __construct(){

    }

    function getConfig(){
        return array (
            "country" => "uk",
            "lang" => "eng", //this is the language code used in the channels audio description
            "validForSatellites" => array( "S28.2E"),
            "validForCableProviders" => array(),
            "validForTerrProviders" => array(),
        );
    }

    function getGroups(){
        return array (
            array(
                "title" => "freesat",
                "outputSortPriority" => 1,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => " AND (upper(name) LIKE '%BBC%' OR ".UK_ITV." OR ".UK_C4.")"
            ),

/*            array(
                "title" => "freesat BBC",
                "outputSortPriority" => 1,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => " AND upper(name) LIKE '%BBC%'"
            ),
*/
            array(
                "title" => "freesat BBC",
                "outputSortPriority" => 2,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND (upper(name) LIKE '%BBC%' OR upper(name) = 'CBEEBIES')"
            ),

            array(
                "title" => "freesat BBC Red Button / Interactive / Sports",
                "outputSortPriority" => 3,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND nid=2 AND tid=2013"
            ),

/*            array(
                "title" => "freesat ITV",
                "outputSortPriority" => 3,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => " AND ".UK_ITV
            ),
*/
            array(
                "title" => "freesat ITV",
                "outputSortPriority" => 4,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND ".UK_ITV
            ),

/*            array(
                "title" => "freesat Channel4 Family",
                "outputSortPriority" => 5,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => " AND ".UK_C4
            ),
*/
            array(
                "title" => "freesat Channel4Family",
                "outputSortPriority" => 6,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND ".UK_C4
            ),

            array(
                "title" => "freesat Channel5 Family",
                "outputSortPriority" => 6,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => " AND ".UK_C5
            ),

            array(
                "title" => "freesat Channel5Family",
                "outputSortPriority" => 6,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND ".UK_C5
            ),

            array(
                "title" => "freesat Diverse",
                "outputSortPriority" => 7,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND (
                    UPPER(name) LIKE 'CBS %' OR
                    UPPER(name) LIKE 'ZONE %' OR
                    UPPER(name) LIKE 'TRUE %' OR
                    UPPER(name) LIKE 'MOVIES4MEN%' OR
                    UPPER(name) LIKE 'MOV4MEN%' OR
                    UPPER(name) LIKE 'MEN&MOVIES%' OR
                    UPPER(name) LIKE 'FOOD NETWORK%' OR
                    UPPER(name) LIKE 'HORROR%' OR
                    UPPER(name) LIKE 'WEDDING TV%'
                )"
            ),

            array(
                "title" => "Diverse",
                "outputSortPriority" => 8,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => ""
            ),

            array(
                "title" => "sky_uk ITV",
                "outputSortPriority" => 10,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => " AND ".UK_ITV
            ),

            array(
                "title" => "sky_uk ITV",
                "outputSortPriority" => 11,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND ".UK_ITV
            ),

            array(
                "title" => "sky_uk Channel4Family",
                "outputSortPriority" => 12,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => " AND ".UK_C4
            ),

            array(
                "title" => "sky_uk Channel4Family",
                "outputSortPriority" => 13,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND  ".UK_C4
            ),

            array(
                "title" => "sky_uk Channel5Family",
                "outputSortPriority" => 14,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => " AND ".UK_C5
            ),

            array(
                "title" => "sky_uk Channel5Family",
                "outputSortPriority" => 15,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND  ".UK_C5
            ),

            array(
                "title" => "sky_uk Sports",
                "outputSortPriority" => 40,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => " AND (UPPER(name) LIKE '%SP NEWS%' OR UPPER(name) LIKE '%SPORT%' OR UPPER(name) LIKE 'ESPN%')"
            ),

            array(
                "title" => "sky_uk NVOD",
                "outputSortPriority" => 50,
                "languageOverrule" => "",
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => " AND UPPER(name) = 'NVOD'"
            ),

            array(
                "title" => "sky_uk",
                "outputSortPriority" => 30,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => " AND NOT ".IRISH
            ),

            array(
                "title" => "sky_uk Sports",
                "outputSortPriority" => 41,
                "languageOverrule" => "",
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND (UPPER(name) LIKE '%SP NEWS%' OR UPPER(name) LIKE '%SPORT%' OR UPPER(name) LIKE 'ESPN%' OR UPPER(name) LIKE '%SPTS%')"
            ),

            array(
                "title" => "sky_uk NVOD",
                "outputSortPriority" => 51,
                "languageOverrule" => "",
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND UPPER(name) = 'NVOD'"
            ),

            array(
                "title" => "sky_uk",
                "outputSortPriority" => 31,
                "languageOverrule" => "",
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND NOT ".IRISH
            ),

            array(
                "title" => "sky_uk",
                "outputSortPriority" => 30,
                "languageOverrule" => "",
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND UPPER(name) LIKE 'SKY%'"
            ),

            array(
                "title" => "Diverse",
                "outputSortPriority" => 9,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => ""
            ),

            array(
                "title" => "freesat BBC",
                "outputSortPriority" => 90,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND upper(name) LIKE '%BBC%' "
            ),

            array(
                "title" => "Rest",
                "outputSortPriority" => 91,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND NOT ".IRISH
            ),

            array(
                "title" => "Rest",
                "outputSortPriority" => 92,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND NOT ".IRISH
            ),
        );
    }

}

?>