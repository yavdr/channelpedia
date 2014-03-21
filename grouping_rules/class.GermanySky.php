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

class GermanySky  extends ruleBase{

    function __construct(){

    }

    function getConfig(){
        return array (
            "country" => "sky_de",
            "lang" => "deu", //this is the language code used in the channels audio description
            "validForSatellites" => array( "S19.2E"),
            "validForCableProviders" => "all",
            "validForTerrProviders" => array(), //todo: set none!!!
        );
    }

    function getGroups(){
        return array (

/*
 *    10 FTA HDTV (we don't expect to find much here)
 *    15 FTA SDTV  (we don't expect to find much here)
 *
 *    30 Starter scrambled SDTV
 *    40 Welt scrambled HDTV
 *    41 Welt scrambled SDTV
 *
 *    50 Film scrambled HDTV
 *    51 Film scrambled SDTV
 *
 *    80 3D
 *
 *   100 Sport scrambled HDTV
 *   110 Sport Feeds scrambled HDTV
 *   120 Sport scrambled SDTV
 *   130 Sport Feeds scrambled SDTV
 *
 *   150 Bundesliga HDTV
 *   160 Bundesliga Feeds scrambled HDTV
 *   170 Bundesliga SDTV
 *   180 Bundesliga Feeds scrambled SDTV
 *   190 Eurosport 360 Feeds
 *
 *   200 Select Portal FTA SDTV
 *   201 Select scrambled HDTV
 *   202 Select Feeds scrambled SDTV
 *   203 Select Event Feeds scrambled SDTV
 *
 *   400 Blue Movie HDTV
 *   401 Blue Movie SDTV
 *
 *   450 Diverse scrambled SDTV (what still didn't match...)
 *
 *   500 Diverse scrambled Radio
 */

            //will not work for cable providers with TVS2!!!
            array(
                "title" => "3D HD",
                "outputSortPriority" => 80,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeTVS2,
                "customwhere" => " AND nid=133 AND sid=117"
            ),

            array(
                "title" => "3D HD",
                "outputSortPriority" => 80,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeTVS2,
                "customwhere" => " AND nid=133 AND sid=117"
            ),

            array(
                "title" => "",
                "outputSortPriority" => 10,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => " AND nid=133"
            ),

            //we expect only one channel (18)
            array(
                "title" => "Select Portal",
                "outputSortPriority" => 200,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND nid=133 AND sid=18"
            ),

            array(
                "title" => "Select",
                "outputSortPriority" => 201,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => " AND nid=133 AND sid=120"
            ),

            array(
                "title" => "",
                "outputSortPriority" => 15,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND nid=133 AND (UPPER(provider) = 'SKY' OR provider = '' OR provider = 'undefined')"
                ),

            array(
                "title" => "Eurosport 360 Feeds HD",
                "outputSortPriority" => 190,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeTVS2,
                "languageOverrule" => "",
                "customwhere" => " AND nid=133 AND sid BETWEEN 250 AND 380 AND SID % 10 = 0"
            ),
/*
            array(
                "title" => "Eurosport 360 Feeds",
                "outputSortPriority" => 191,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeData,
                "languageOverrule" => "",
                "customwhere" => " AND nid=133 AND sid BETWEEN 250 AND 380 AND SID % 10 = 0"
            ),
*/
            array(
                "title" => "Bundesliga",
                "outputSortPriority" => 150,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "languageOverrule" => "",
                "customwhere" => " AND nid=133 AND sid BETWEEN 250 AND 380 AND SID % 10 = 7"
            ),
/*
            array(
                "title" => "Bundesliga Feeds HD",
                "outputSortPriority" => 161,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeData,
                "languageOverrule" => "",
                "customwhere" => " AND nid=133 AND sid BETWEEN 250 AND 380 AND SID % 10 = 7"
            ),
*/
            array(
                "title" => "Sport", //Feeds HD
                "outputSortPriority" => 100,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "languageOverrule" => "",
                "customwhere" => " AND nid=133 AND sid BETWEEN 250 AND 380 AND SID % 10 = 8"
            ),
/*
            array(
                "title" => "Sport Feeds HD",
                "outputSortPriority" => 111,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeData,
                "languageOverrule" => "",
                "customwhere" => " AND nid=133 AND sid BETWEEN 250 AND 380 AND SID % 10 = 8"
            ),
*/
            array(
                "title" => "Sport", //HDTV
                "outputSortPriority" => 100,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "languageOverrule" => "", //ESPN America HD is in English!
                "customwhere" => " AND nid=133 AND name != '.' AND NOT name LIKE '%news%' AND NOT name LIKE '%eurosport hd%'  AND (name LIKE '%sport%' OR name LIKE 'espn%')"
            ),

            array(
                "title" => "Bundesliga",
                "outputSortPriority" => 150,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "languageOverrule" => "", //ESPN America HD is in English!
                "customwhere" => " AND nid=133 AND (name LIKE 'sky bundesliga%' OR name LIKE '%fanzone')"
            ),

            array(
                "title" => "Blue Movie",
                "outputSortPriority" => 400,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "languageOverrule" => "",
                "customwhere" => " AND nid=133 AND name LIKE '%blue movie%'"
            ),

            array(
                "title" => "Blue Movie",
                "outputSortPriority" => 401,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "languageOverrule" => "",
                "customwhere" => " AND nid=133 AND name LIKE '%blue movie%'"
            ),

            array(
                "title" => "Film",
                "outputSortPriority" => 50,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "customwhere" => " AND name NOT LIKE '% - %' ".
                                 "AND name != 'Spieldaten' ".
                                 "AND name NOT LIKE  '%pitlane%' ".
                                 "AND name NOT LIKE  '%racer%' ".
                                 "AND name NOT LIKE  '%konf%' ".
                                 "AND name NOT LIKE  '%liga%' ".
                                 "AND name NOT LIKE '%sky 3d%' ".
                                 "AND name NOT LIKE '%krimi%' ".
                                 "AND name NOT LIKE '%sport news%' ".
                                 "AND nid=133 ".
                                 "AND name != '.' ".
                                 "AND (name LIKE 'sky%' OR name LIKE '%mgm%' OR name LIKE 'disney cinemagic%')"
            ),

            //kabel eins classics, sat.1 emotions, rtl Living
            array(
                "title" => "Welt",
                "outputSortPriority" => 41,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND nid=1 AND NOT (" . AUSTRIA." OR ".SWITZERLAND.") AND ".DE_PRIVATE_PRO7_RTL
            ),

            array(
                "title" => "Welt",
                "outputSortPriority" => 41,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND (
                                     name LIKE 'axn action%' OR
                                     lower(name) = 'boomerang' OR
                                     name LIKE 'cartoon network%' OR
                                     lower(name) = 'history' OR
                                     name LIKE 'kinowelt tv%' OR
                                     name LIKE 'biography channel%' OR
                                     name LIKE 'tnt film%' OR
                                     name LIKE 'romance tv%' OR
                                     name LIKE 'animax%' OR
                                     name LIKE 'espn%' OR
                                     name LIKE 'sky sport news%' OR
                                     name LIKE 'eurosport 2%'
                                 ) "
            ),

            array(
                "title" => "Welt",
                "outputSortPriority" => 40,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "languageOverrule" => "", // for mtv live hd
                "customwhere" => " AND (UPPER(provider) = 'SKY') AND name != '.'"
                //OR provider = '' OR UPPER(provider) = 'UNDEFINED'
            ),

            array(
                "title" => "Sport",
                "outputSortPriority" => 120,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "languageOverrule" => "", //ESPN America HD is in English!
                "customwhere" => " AND (((UPPER(provider) = 'SKY') AND name != '.' AND NOT name LIKE '%news%' AND (name LIKE '%sport%' OR name LIKE '%espn%') OR sid=222))"
                //OR provider = '' OR UPPER(provider) = 'UNDEFINED'
            ),

            array(
                "title" => "Bundesliga",
                "outputSortPriority" => 170,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "languageOverrule" => "", //ESPN America HD is in English!
                "customwhere" => " AND (UPPER(provider) = 'SKY') AND name != '.' AND NOT name LIKE '%news%' AND (name LIKE 'sky bundesliga%')"
                //OR provider = '' OR UPPER(provider) = 'UNDEFINED'
            ),

            array(
                "title" => "Select Feeds",
                "outputSortPriority" => 202,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND nid=133 AND sid BETWEEN 250 AND 380 AND SID % 10 = 1"
            ),

            array(
                "title" => "Select Event Feeds",
                "outputSortPriority" => 203,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND nid=133 AND (sid=254 OR sid=264 OR sid=334)"
            ),

            array(
                "title" => "Bundesliga Feeds",
                "outputSortPriority" => 180,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND nid=133 AND sid BETWEEN 250 AND 380 AND SID % 10 = 2"
            ),

            array(
                "title" => "Sport Feeds",
                "outputSortPriority" => 130,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND nid=133 AND sid BETWEEN 250 AND 380 AND SID % 10 = 3"
            ),

            array(
                "title" => "Film",
                "outputSortPriority" => 51,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND nid=133 AND name NOT LIKE '%sky 3d%' AND name NOT LIKE '%krimi%'  AND (UPPER(provider) = 'SKY' OR provider = '' OR provider = 'undefined') AND name != '.' AND (name LIKE 'sky%' OR name LIKE '%mgm%' OR name LIKE 'disney cinemagic%')"
            ),

            array(
                "title" => "Starter",
                "outputSortPriority" => 30,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND nid=133 AND (UPPER(provider) = 'SKY' OR provider = '' OR provider = 'undefined') AND name != '.' AND sid != 222"
            ),

            //this is to catch any other channels that were not caught by other rules above, normally there is not much found by this rule
            array(
                "title" => "Diverse",
                "outputSortPriority" => 450,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "customwhere" => " AND nid=133 AND (UPPER(provider) = 'SKY' OR provider = '' OR provider = 'undefined')"
            ),

            //this is to catch any other channels that were not caught by other rules above, normally there is not much found by this rule
            array(
                "title" => "Diverse",
                "outputSortPriority" => 499,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeData,
                "languageOverrule" => "",
                "customwhere" => " AND nid=133 AND (UPPER(provider) = 'SKY' OR provider = '' OR provider = 'undefined')"
            ),

            //this is to catch any other channels that were not caught by other rules above, normally there is not much found by this rule
            array(
                "title" => "Diverse",
                "outputSortPriority" => 500,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeRadio,
                "customwhere" => " AND nid=133 AND (UPPER(provider) LIKE 'SKY' OR provider = '' OR provider = 'undefined')"
            ),
        );
    }
}

?>