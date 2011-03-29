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

class AustriaSatEssentials {

    function __construct(){

    }

    function getRules(){
        return array(
            "country" => "at",
            "lang" => "deu", //this is the language code used in the channels audio description
            "validForSatellites" => array( "S19.2E"),
            "validForCableProviders" => array("at_salzburg-ag"),
            "validForTerrProviders" => array(),//none
            "groups" => array(

                "01.scrambled.HDTV.ORF" => array(

                    "caidMode" => 2,
                    "mediaType" => 1,
                    "customwhere" => " AND " . HD_CHANNEL. "AND UPPER(provider) = 'ORF'"
                ),

                "02.scrambled.SDTV.ORF" => array(

                    "caidMode" => 2,
                    "mediaType" => 1,
                    "customwhere" => " AND NOT " . HD_CHANNEL . "AND UPPER(provider) = 'ORF'"
                ),

                "03.FTA.SDTV.Private" => array(

                    "caidMode" => 1,
                    "mediaType" => 1,
                    "languageOverrule" => "", // needed for RTL2
                    "customwhere" => "AND NOT ". HD_CHANNEL. "AND ". AUSTRIA. "AND (". DE_PRIVATE_PRO7_RTL . " OR UPPER(provider) LIKE '%AUSTRIA%')"
                ),

                "04.scrambled.SDTV.Private" => array(

                    "caidMode" => 2,
                    "mediaType" => 1,
                    "languageOverrule" => "", // needed for RTL2
                    "customwhere" => "AND NOT ". HD_CHANNEL. "AND ". AUSTRIA. "AND (". DE_PRIVATE_PRO7_RTL . " OR UPPER(provider) LIKE '%AUSTRIA%')"
                ),

                "05.FTA.SDTV.diverse" => array(

                    "caidMode" => 1,
                    "mediaType" => 1,
                    "customwhere" => " AND (UPPER(provider) = 'SERVUSTV' OR ".AUSTRIA.") AND NOT ". HD_CHANNEL
                ),

                "06.FTA.HDTV.diverse" => array(

                    "caidMode" => 1,
                    "mediaType" => 1,
                    "customwhere" => " AND (UPPER(provider) = 'SERVUSTV' OR ".AUSTRIA.") AND ". HD_CHANNEL
                ),

                "10.FTA.Radio.ORF" => array(
                    "caidMode" => 1,
                    "mediaType" => 2,
                    "customwhere" => " AND UPPER(provider) = 'ORF'"
                ),

                "11.FTA.Radio.diverse" => array(
                    "caidMode" => 1,
                    "mediaType" => 2,
                    "customwhere" => " AND " . AUSTRIA
                ),


                "12.scrambled.Radio.diverse" => array(
                    "caidMode" => 2,
                    "mediaType" => 2,
                    "customwhere" => " AND " . AUSTRIA
                ),

/* FIXME: radio channels with language deu are already grabbed by the de list...
                "05.FTA.radio" => array(

                    "caidMode" => 1,
                    "mediaType" => 2,
                ),

                "06.scrambled.radio" => array(

                    "caidMode" => 2,
                    "mediaType" => 2,
                ),
*/
            )
        );
    }

}
?>
