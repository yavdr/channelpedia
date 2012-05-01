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

class uniqueIDTools {

    private static $instance = null;

    protected function __construct(){
    }

    function __destruct(){
    }

    private function __clone(){}

    public static function getInstance(){
        if ( self::$instance == null){
            self::$instance = new uniqueIDTools();
        }
        return self::$instance;
    }

    public function sanitizeID4cssClass( $id ){
        return str_replace( array( ".",":","+","[","]"), array("_","_","plus","_",""), $id);
    }

    public function getMatchingCSSClasses( $id, $suffix){
        $alternative = "";
        if ($id !== ""){
            $idchunks = explode(":", $id);
            if (count($idchunks) !== 2)
                throw new Exception("getMatchingCSSClasses: Strange id: $id");
            $idparts = explode(".", $idchunks[1]);
            if ( count($idparts) === 3){
                $alternative = ' ' . $this->sanitizeID4cssClass( $idchunks[0] . ':' . $idparts[1]. '.' . $idparts[2] ) . $suffix;
            }
            elseif ( count($idparts) !== 2 ){
                throw new Exception("getMatchingCSSClasses: Strange id: $id");
            }
        }
        return $this->sanitizeID4cssClass( $id ) . $suffix . $alternative;
    }

//global function to be called from pdo

public function convertChannelNameToCPID( $name, $label){
    $labelparts = explode( '.', $label);
    $country = $labelparts[0];
    $name = explode(",", $name);
    $nameparts = explode("(", $name[0]); //cut off brackets that are used by wilhelm.tel and unitymedia
    $name = trim($nameparts[0]);

    //care for unimportant prefixes or suffixes that are seperated by a blank or any other special char that
    //will be deleted later on - now we still have it to work with it
    if ($country === "at" || $country === "de" || $country === "ch"){
        if (substr(strtolower($name), -4) === " neu")
            $name = substr($name, 0, strlen($name) -4 );
    }

    $name = str_replace(
        array(
            '.',
            '/',
            ' ',
            '&',
            '!',
            "'",
            '(',
            ')',
            '|',
            '`',
            '?',
            '-',
            '_',
            '*',
            ':'
        ), "", trim($name));

    if ($country === "at" || $country === "de" || $country === "ch"){

        $fullname_replacements_de = array(
            "br"              => "brfs",
            "skychristmas"    => "skycinemahits",
            "skysporthd1"     => "skysport1hd",
            "skysporthd2"     => "skysport2hd",
            'zdfinfokanal'    => 'zdfinfo',
            'zdftheaterkanal' => 'zdfkultur',
            'deutschlandradiokultur' => 'dkultur',
            'deutschlandfunk' => 'dlf',
            'wdrfunkhauseuropa' => 'funkhauseuropa',
        );

        $partial_name_replacements_de = array(
            "brnord" => "brfsnord",
            "brsüd" => "brfssüd",
            'pro7' => 'prosieben',
            'rtlii' => 'rtl2',
            'srtl' => 'superrtl',
            'rtltelevision' => 'rtl',
            "bayerischesfs" => "brfs",
            "brfernsehen" => "brfs",
            "wdrfernsehen"    => "wdr",
            "ndrfernsehen"    => "ndrfs",
            "swrfernsehen"    => "swrfs",
            "mdrfernsehen"    => "mdr",
            'mdr1radio' => 'mdr1',
            "rbbfernsehen"    => "rbb",
            "nationalgeographicchannel" => "natgeo",
            'badenwürttemberg' => 'bw',
            'rheinlandpfalz' => 'rp',
            'swrbw' => 'swrfsbw',
            'swrrp' => 'swrfsrp',
        );


        //full name replacements
        if ( array_key_exists($name, $fullname_replacements_de)){
            $name = $fullname_replacements_de[$name];
        }
        else{
            //partial name replacements (all apply at the same time)
            $name = str_ireplace( array_keys( $partial_name_replacements_de  ), array_values( $partial_name_replacements_de ), $name);
        }
    }

    if ($country === "at"){
        if (substr(strtolower($name), -7) === "austria")
            $name = substr($name, 0, strlen($name) -7 );
    }
    elseif ($country === "ch"){
        if (substr(strtolower($name), -7) === "schweiz")
            $name = substr($name, 0, strlen($name) -7 );
        elseif (substr(strtolower($name), -2) === "ch")
            $name = substr($name, 0, strlen($name) -2 );
        elseif (substr(strtolower($name), -5) === "chneu")
            $name = substr($name, 0, strlen($name) -5 );
    }

    //care for channel type and cut off variant labels like "hd" and "+1" at the end of the name
    $ext = "";
    $type = "data";
    if (stristr($labelparts[2], "sdtv") !== false){
        $type = "tv";
    }
    elseif (stristr($labelparts[2], "hdtv") !== false){
        $type = "tv";
        if ( substr($name,-2, 2) == "hd")
            $name = trim(substr($name,0, -2));
        $ext .= "[hd]";
    }
    elseif (stristr($labelparts[2], "radio") !== false){
        $type = "radio";
    }

    if ( substr($name,-2, 2) == "+1"){
        $name = trim(substr($name,0, -2));
        $ext .= "[+1]";
    }
    else if ( substr($name,-3, 3) == "+24"){
        $name = trim(substr($name,0, -3));
        $ext .= "[+24]";
    }

    //care for regional channel variants
    if ($country === "at" || $country === "de" || $country === "ch"){
        $regional_exceptions = array(
          //TODO
        );

        $regional_prefixes = array(
            "brfs",
            "wdr",
            "ndrfs",
            "mdr1", //needs to be in front of mdr in this list
            "mdr",
            "rbb",
            "swrfs",
            "sat1",
            "rtl",
            "swr1",
            "swr4",
            "ndr1",
            "ndrinfo",
        );
        foreach ( $regional_prefixes as $prefix){
            $prefix_length = strlen($prefix);
            if ( strlen($name) >  $prefix_length && substr( $name, 0, $prefix_length) == $prefix){
               $name = trim( substr( $name, $prefix_length)) . '.' . trim(substr( $name, 0, $prefix_length));
               break;
            }
        }
    }


    return "cpid_v1." . $type . ":" . $name . $ext . "."  . $labelparts[0];
}


private function global_convertChannelNameForCPID_alt( $name, $label){
    $labelparts = explode( '.', $label);
    $country = $labelparts[0];
    $name = explode(",", $name);
    $nameparts = explode("(", $name[0]); //cut off brackets that are used by wilhelm.tel and unitymedia
    $name = trim($nameparts[0]);
    $name = str_replace(
        array(
            '.',
            '/',
            ' ',
            '&',
            '!',
            "'",
            '(',
            ')',
            '|',
            '`',
            '?',
            '-',
            '_'
        ), "", trim($name));

        if ($country === "at" || $country === "de" || $country === "ch"){
            $name = str_replace(array( 'pro7', 'rtlii', 'srtl', 'rtltelevision'), array( 'prosieben', 'rtl2', 'superrtl', 'rtl' ), $name);
            if ($name == "skychristmas") $name = "skycinemahits";
        }
        if ($country === "at"){
            if (substr(strtolower($name), -7) === "austria")
                $name = substr($name, 0, strlen($name) -7 );
        }
        elseif ($country === "ch"){
            if (substr(strtolower($name), -7) === "schweiz")
                $name = substr($name, 0, strlen($name) -7 );
            elseif (substr(strtolower($name), -2) === "ch")
                $name = substr($name, 0, strlen($name) -2 );
            elseif (substr(strtolower($name), -5) === "chneu")
                $name = substr($name, 0, strlen($name) -5 );
        }
/*
        $ext = "";
        $type = "data";
        if (stristr($labelparts[2], "sdtv") !== false){
            $type = "tv";
        }
        elseif (stristr($labelparts[2], "hdtv") !== false){
            $type = "tv";
            if ( substr($name,-2, 2) == "hd")
                $name = trim(substr($name,0, -2));
            $ext .= "[hd]";
        }
        elseif (stristr($labelparts[2], "radio") !== false){
            $type = "radio";
        }

        if ( substr($name,-2, 2) == "+1"){
            $name = trim(substr($name,0, -2));
            $ext .= "[+1]";
        }
        else if ( substr($name,-3, 3) == "+24"){
            $name = trim(substr($name,0, -3));
            $ext .= "[+24]";
        }
        return "cp[v0.1]." . $type . "." . $labelparts[0] . "." . $name . $ext;
*/
    return $name;
}
//global function to be called from pdo

function convertChannelNameToCPID_v3alt( $name, $label){
    $labelparts = explode( '.', $label);
    $country = $labelparts[0];
    $name = explode(",", $name);
    $nameparts = explode("(", $name[0]); //cut off brackets that are used by wilhelm.tel and unitymedia
    $name = trim($nameparts[0]);
    if ($country === "at" || $country === "de" || $country === "ch"){
        if (substr(strtolower($name), -4) === " neu")
            $name = substr($name, 0, strlen($name) -4 );
        $name = str_replace(array( 'pro7', 'rtlii', 'srtl', 'rtltelevision'), array( 'prosieben', 'rtl2', 'superrtl', 'rtl' ), $name);
        if ($name == "skychristmas") $name = "skycinemahits";
    }
    if ($country === "at"){
        if (substr(strtolower($name), -7) === "austria")
            $name = substr($name, 0, strlen($name) -7 );
    }
    elseif ($country === "ch"){
        if (substr(strtolower($name), -7) === "schweiz")
            $name = substr($name, 0, strlen($name) -7 );
        elseif (substr(strtolower($name), -2) === "ch")
            $name = substr($name, 0, strlen($name) -2 );
        elseif (substr(strtolower($name), -5) === "chneu")
            $name = substr($name, 0, strlen($name) -5 );
    }

    $name = str_replace(
        array(
            '.',
            '/',
            ' ',
            '&',
            '!',
            "'",
            '(',
            ')',
            '|',
            '`',
            '?',
            '-',
            '_'
        ), "", trim($name));


        $ext = "";
        $type = "data";
        if (stristr($labelparts[2], "sdtv") !== false){
            $type = "tv";
        }
        elseif (stristr($labelparts[2], "hdtv") !== false){
            $type = "tv";
            if ( substr($name,-2, 2) == "hd")
                $name = trim(substr($name,0, -2));
            $ext .= "[hd]";
        }
        elseif (stristr($labelparts[2], "radio") !== false){
            $type = "radio";
        }

        if ( substr($name,-2, 2) == "+1"){
            $name = trim(substr($name,0, -2));
            $ext .= "[+1]";
        }
        else if ( substr($name,-3, 3) == "+24"){
            $name = trim(substr($name,0, -3));
            $ext .= "[+24]";
        }
        return "cpid_v1." . $type . ":" . $name . $ext . '.' . $labelparts[0];

    return $name;
}

}

function global_convertChannelNameToCPID( $name, $label ){
    return uniqueIDTools::getInstance()->convertChannelNameToCPID( $name , $label );
}
?>