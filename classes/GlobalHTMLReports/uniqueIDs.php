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
class uniqueIDs extends globalHTMLReportBase{

    public function popuplatePageBody(){
        $this->setPageTitle( "Draft for unique IDs" );
        $this->setKeywords("german, deutsch, kanäle, vergleich");
        $this->setDescription("Attempt to auto-create matchable unique IDs.");
        $this->addBodyHeader();
        $this->appendToBody( '<p>Use Firefox Addon JSONView to view JSON data in a human readable way.</p>');
        $this->appendToBody( '<p>Channelpedia-IDs and their corresponding channels: <a href="de_unique_id_draft.json" target="_blank">de_unique_id_draft.json</a></p> ' );
        $this->appendToBody( '<p>Lookup Channelpedia-ID by SID-NID-TID: <a href="de_snt2cp.json" target="_blank">de_snt2cp.json</a></p> ' );
        $this->appendToBody( '<p>View complete list of all generated IDs: <a href="de_pureidlist.json" target="_blank">de_pureidlist.json</a></p> ' );

        $divider = ",/,/,";
/*
        $replacer = $this->sqlMultiReplace( "lower(name)",
            array(
              '.' => '',
              ' ' => '',
              '!' => '',
              '`' => '',
              "'" => '',
              '/' => '',
              '?' => '',
              '|' => '',
              '&' => '',
              '-' => '',
              '_' => '',
              'pro7' => 'prosieben',
              'rtlii' => 'rtl2',
            )
        );
*/

        $this->db->getDBHandle()->sqliteCreateFunction('convertchannelname', 'global_convertChannelNameForCPID', 2);

        $replacer = " convertchannelname( name, x_label) ";

        $result = $this->db->query("
            SELECT
                count(nid) AS x_sum,
                group_concat( name, '".$divider."') AS original_names,
                ".$replacer." AS trimmed_name,
                group_concat( ".$replacer.", '".$divider."' ) AS matching_names,
                group_concat( lower(provider), '".$divider."') AS matching_providers,
                nid,
                tid,
                sid,
                group_concat( source, '".$divider."') AS matching_sources,
                x_label
            FROM channels
            WHERE
                ( x_label LIKE 'de.%' OR x_label LIKE 'at.%' OR x_label LIKE 'ch.%' )
                AND x_label NOT LIKE '%uncategorized%'
                AND NOT x_label LIKE 'de.024.sky_de%'
                AND name NOT LIKE '.%'
                AND name NOT LIKE '%*'
                AND name NOT LIKE '%.'
                AND name NOT LIKE '%test%'
                AND name NOT LIKE '%_alt'
                GROUP BY
                x_label, nid, tid, sid, trimmed_name
            ORDER BY
                trimmed_name ASC,
                x_sum DESC
        ");
//                AND x_label LIKE '%public%'

        /*$this->appendToBody( "<table>\n");
            $this->appendToBody(
                '<tr><th>'.
                "x_sum" . "</th><th>".
                "trimmed_name" . "</th><th>".
                "names" . "</th><th>".
                "providers" . "</th><th>".
                "nid" . "</th><th>".
                "tid" . "</th><th>".
                "sid" . "</th><th>".
                "matching_sources" . "</th>".
                "</tr>\n"
            );*/
        $strictlist = "";
        $lastid = "";
        $uidlist = array();
        $snt2cp_list = array();
        $pure_id_list = array();
        $idstring = "";

        foreach ($result as $row) {

            $matching_name_array = array_unique( explode( $divider, $row["matching_names"]) );
            $row["matching_names"] = implode($divider , $matching_name_array);
            if (count($matching_name_array) > 1 ){
                $strictlist .= "Warning: Channel name variants: " . $row["matching_names"] . "\n";
            }
            $labelparts = explode(".", $row["x_label"]);
            $name = $this->repairChannelName($matching_name_array[0], $labelparts[0]);
            if ($name !== $row["trimmed_name"]){
                $strictlist .= "Warning: sqlite convertchannelname brings up different result than repairchannelname: " .$name . " - ". $row["trimmed_name"] . "\n";
            }
            if ( !$this->isBlacklisted($name)){
                $row["matching_providers"] = implode($divider , array_unique( explode( $divider, $row["matching_providers"]) ));
                $tempidstring = $this->getIDString( $name, $labelparts);
                if ($lastid != $tempidstring){
                    $idstring = $tempidstring;
                    if (!array_key_exists( $idstring, $uidlist)){
                        $uidlist[ $idstring ] = array();
                        $uidlist[ $idstring ][ "naming_variants" ] = array();
                        $uidlist[ $idstring ][ "nidtidsids" ] = array();
                    }
                    //else
                    //    $strictlist .= "Warning: Channel is already in array '" . $idstring . "'\n";
                }
                $related_sources_info = explode($divider, $row["matching_sources"]);
                if (array_key_exists($idstring, $pure_id_list))
                    $pure_id_list[ $idstring ] += count($related_sources_info);
                else
                    $pure_id_list[ $idstring ] = count($related_sources_info);
                $ntsid = $row["nid"] . "-" . $row["tid"] . "-". $row["sid"];
                if (!array_key_exists( $ntsid, $snt2cp_list ))
                    $snt2cp_list[ $ntsid ] = $idstring;
                else if ( $snt2cp_list[ $ntsid ] !== $idstring )
                    $strictlist .= "Warning: Doublette NID_TID_SID : '" . $ntsid. "' <b>". $snt2cp_list[ $ntsid ]. "</b> new: <b>" . $idstring. "</b>\n";
                $lastid = $name;
                sort( $related_sources_info );
                $uidlist[ $idstring ][ "nidtidsids" ][] = array(
                    'nid' => intval($row["nid"]),
                    'tid' => intval($row["tid"]),
                    'sid' => intval($row["sid"]),
                    'related_sources_info' => $related_sources_info
                );
                $uidlist[ $idstring ][ "naming_variants" ] =
                    array_unique( array_merge( $uidlist[ $idstring ][ "naming_variants" ], explode( $divider, $row["original_names"])));
            }
            else{
                $strictlist .= "Warning: Row with channel name: " . $row["matching_names"] ." ignored!\n";
            }
            /*$this->appendToBody(
                '<tr><td>'.
                htmlspecialchars( $row["x_sum"] ). "</td><td>".
                htmlspecialchars( $row["trimmed_name"] ). "</td><td>".
                htmlspecialchars( $row["matching_names"] ). "</td><td>".
                htmlspecialchars( $row["matching_providers"] ). "</td><td>".
                htmlspecialchars( $row["nid"] ). "</td><td>".
                htmlspecialchars( $row["tid"] ). "</td><td>".
                htmlspecialchars( $row["sid"] ). "</td><td>".
                htmlspecialchars( $row["matching_sources"] ). "</td>".
                "</tr>\n"
            );*/
        }
        //$this->appendToBody( "</table>\n" );
        ksort($uidlist);
        ksort($pure_id_list);
        asort($snt2cp_list);
        $this->appendToBody( "<pre>". $strictlist."</pre>" );
        $this->config->save( "de_unique_id_draft.json", json_encode( array("result" => $uidlist)) );
        $this->config->save( "de_snt2cp.json",          json_encode( array("result" => $snt2cp_list)) );
        $this->config->save( "de_pureidlist.json",      json_encode( array("result" => $pure_id_list)) );
        $filename = "de_uniqueIDs.html";
        $this->addToOverviewAndSave( "de_uniqueIDs", $filename );
    }

    private function getIDString( $name, $labelparts){
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
     }

    private function isBlacklisted ($name){
        return
            !(
                strlen($name) > 1 &&
                strstr($name, "test") === false &&
                substr($name,-1) !== "*" &&
                substr($name,-1) !== "." &&
                substr($name,-4) !== "_alt"
            );
    }

    private function repairChannelName( $namearray, $country ){
            $name = explode(",", $namearray);
            $nameparts = explode("(", $name[0]); //cut off brackets that are used by wilhelm.tel and unitymedia
            $name = trim($nameparts[0]);
            //this rtl hack can only be done here and not earlier
            if ($name == "srtl") $name = "superrtl";
            if ($name == "rtltelevision") $name = "rtl";
            if ($name == "skychristmas") $name = "skycinemahits";

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

        return $name;
    }

    private function sqlMultiReplace($string, $from_to_array){
        $fragment = "";
        foreach ($from_to_array as $from => $to){
            $string = $this->sqlReplace($string, $from, $to);
        }
        return $string;
    }

    private function sqlReplace($string, $from, $to){
        return "REPLACE( ". $string .", ". $this->db->quote($from) .", " . $this->db->quote($to) . ")";
    }
}

//global function to be called from pdo

function global_convertChannelNameForCPID( $name, $label){
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

    return $name;
}

?>