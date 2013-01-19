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

class semanticDataCollector_de_wikipedia{

    private
       $db,
        $config,
        $list;

    function __construct(){
        $this->db = dbConnection::getInstance();
        $this->config = config::getInstance();
        $this->list = array();

        $pagesAndChunks = array(
            array( 'de', 'Liste_deutschsprachiger_Fernsehsender',
                array(
                    array( "Deutschland", "de", "tv", 10 ),
                    array( "Österreich", "at", "tv", 3),
                    array( "Schweiz", "ch", "tv", 3),
                    array( "Privat-rechtlich (Free-TV)", "auto", "tv", 30),
                    array( "HD Plus/ HD Austria", "at", "hdtv", 6),
                    //array( "Privat-rechtlich/deutschsprachiger HD Sender (Pay-TV)", "de", "hdtv", 61), //temporary incomplete
                    array( "Deutschsprachige HD Sender (Öffentlich-rechtliche, privat-rechtliche und Pay-TV)", "de", "hdtv", 61),
                    array( "Privat-rechtliche deutschsprachige Sender in Standardauflösung (Pay-TV)", "de", "tv", 68),
                )
            ),
            array( 'de','Liste_deutscher_H%C3%B6rfunksender',
                array(
                    array( "page", "de", "radio", 76 )
                )
            )
        );
        foreach ($pagesAndChunks as $info){
            $currentPage =  $this->getRawWikipediaPageBuffered( $info[0], $info[1] );
            foreach ($info[2] as $chunk){
                $section = ( $chunk[0] !== "page" ) ? $this->grabSection( $chunk[0], $currentPage ) : $currentPage;
                $this->config->addToDebugLog( $this->getChannelsFromSection( $section , $chunk[1], $chunk[2], $chunk[3] ) );

            }
        }

        ksort($this->list);
        $this->createJSONFile();
    }

    private function createJSONFile(){
        $this->config->save( "semantic_data/de_wikipedia_channels.json", json_encode( $this->list ) );
    }

    private function grabSection( $name, $content ){
        $retval = "";
        $pattern = "<span class\=\"mw\-headline\".*?".">".preg_quote($name,"/")."<\/span>";
        preg_match( "/$pattern/", $content, $matches);
        if (count($matches) !== 1){
            $this->config->addToDebugLog( "Marker '$name' couldn't be found. Skipping it.\n" );
        }
        else {
            $this->config->addToDebugLog( "Trying to grab channels from section '$name'\n" );
            $pattern .= "(.*?\n)*?<table class\=\"wikitable((.*?\n)*?)<\/table>";
            /*
            <h2><span class="editsection">[<a href="/w/index.php?title=Liste_deutschsprachiger_Fernsehsender&amp;action=edit&amp;section=75" title="Abschnitt bearbeiten: deutschsprachige HD-Sender">Bearbeiten</a>]</span>
            <span class="mw-headline" id="deutschsprachige_HD-Sender">deutschsprachige HD-Sender</span></h2>
            <p>Während die öffentlich-rechtlichen Sender ihre HD-Programme generell ohne Zusatzgebühren ausstrahlen, handelt es sich bei den HD-Angeboten der privaten Sender mehrheitlich um Bezahlfernsehen oder um grundverschlüsselte Programme, die über die Plattform <a href="/wiki/HD%2B" title="HD+">HD+</a> kostenpflichtig ausgestrahlt werden.</p>
            <table class="wikitable sortable">
            */
            preg_match( "/$pattern/", $content, $matches);
            if (count($matches) < 3){
                throw new Exception("grabSection: In section $name, matches are not well-formed. Stopping.");
            }
            $retval = $matches[2];
        }
        return $retval;
    }

    private function getChannelsFromSection( $content, $regioncode_static, $type, $expectedNumber){
        $trace = "Parsing $regioncode_static $type\n";
        $pattern =
          "<tr.*?>\n".
          "<td.*?><a href=\"(.*?)\".*?>(.*?)<\/a>.*?<\/td>\n".
          "<td.*?><a href=\"\/\/de\.wikipedia\.org\/w\/index\.php\?title\=Datei\:(.*?)\&amp\;.*?>(<img.*? src=\"(.*?\/wikipedia\/(.*?)\/thumb\/(.\/..)\/.*?)\" width=\"(.*?)\" height=\"(.*?)\".*?>)<\/a><\/td>\n".
          "<td(.*?|.*?\n.*?)\/td>\n";
        if ($regioncode_static !== "ch" )
            $pattern .= "<td.*?>(.*?|.*?\n.*?)<\/td>\n";
        preg_match_all( "/".$pattern."/mi", $content, $matches, PREG_SET_ORDER);
        //<img alt=\"1-2-3-tv Logo.svg\" src=\"http:\/\/upload.wikimedia.org\/wikipedia\/de\/thumb\/9\/9b\/1-2-3-tv_Logo.svg\/60px-1-2-3-tv_Logo.svg.png\" width=\"60\" height=\"15\" \/>
        $type = ($type == "tv") ? "sdtv" : $type;
        $counter = 0;
        foreach ($matches as $match){
            $regioncode = ($regioncode_static == "auto") ? $this->getRegioncode($match[11]): $regioncode_static;
            $id = uniqueIDTools::getInstance()->convertChannelNameToCPID( strtolower( $match[2]) , $regioncode.".10.".$type);
            if (!array_key_exists( $id, $this->list)){
                //convert special characters in file names
                $filename = $match[3];
                $filename  = str_replace("%C3%9F", "ß", $filename);
                $filename  = urldecode($filename);
                /*if (!mb_check_encoding($filename, 'UTF-8')) {
                    $filename = utf8_encode($filename);
                    die( $filename ." converted\n");
                }*/
                $extension = substr( $filename, strrpos($filename, '.') +1);

                //get naming variants from channelpedia database
                $divider = ",/,/,";
                $result = $this->db->query("
                    SELECT
                        count(nid) AS x_sum,
                        group_concat( name, '".$divider."') AS original_names,
                        group_concat( lower(provider), '".$divider."') AS matching_providers,
                        (sid  || '-' || nid  || '-' || tid) AS sidnidtid,
                        group_concat( source, '".$divider."') AS matching_sources
                    FROM channels
                    WHERE
                        x_xmltv_id = '".$id."'
                    GROUP BY
                        x_label, sidnidtid, x_xmltv_id
                    ORDER BY
                        x_sum DESC
                ");
                $row = $result->fetch(PDO::FETCH_ASSOC);
                if (isset($row["original_names"])){
                    $row["original_names"] .= $divider . $match[2];
                }
                else
                  $row["original_names"] = $match[2];
                if (!isset($row["matching_providers"]))
                    $row["matching_providers"] = "";
                if (!isset($row["matching_sources"]))
                    $row["matching_sources"] = "";
                $original_name_array = array_values( array_unique( explode( $divider, $row["original_names"] ) ));
                //unused:
                //$matching_name_array = array_unique( explode( $divider, $row["matching_providers"]) );
                //$sources_array = explode( $divider, $row["matching_sources"]);

                $this->list[ $id ] = array(
                    "wikipedia-page-url" => $match[1],
                    "wikipedia-logo" => array(
                        "extension" => $extension,
                        "filename" => $filename,
                        "wikimedia-path-hint" => $match[7],
                        "wikimedia-path-hint-namespace" => $match[6],
                        "small-png" => array(
                            "url" => str_replace("//", "http://", $match[5]),
                            "width" => $match[8],
                            "height" => $match[9]
                        ),
                    ),
                    "naming-variants" => $original_name_array,
                    "regioncode" => $regioncode,
                    "wikipedia-remark" => isset($match[11]) ? $match[11] : ""
                 );

                 $counter++;
                 if ($counter > 100000) break;
                 //$trace.= " $id, ";
            }
           else{
              //$trace.= " Warning: ID $id (name $match[2]) already exists.\n";
           }
        }
        $trace.= "  Hits: $counter\n";
        if ( $counter < $expectedNumber )
            $this->config->addToDebugLog( "WARNING: Expected number of hits ($expectedNumber) not reached (only $counter).\n" );
        return $trace;
    }

    private function getRegioncode( $remark ){
        preg_match("/.*?\((.*?)\).*?/", trim($remark), $regions);
        if ( count($regions) < 2 ){
          $regions = "";
          if (stristr($remark,'salzburg' ) !== false)
              $regions ="a";
        }
        else{
          $regions = $regions[1];
        }
        //$regions = explode(",")
        if (stristr("d", $regions ) !== false)
            $regioncode = "de";
        elseif (stristr("a", $regions ) !== false)
            $regioncode = "at";
        elseif (stristr("ch", $regions ) !== false)
            $regioncode = "ch";
        else
            $regioncode = "de";
        return $regioncode;
    }

    /*
    //function is obsolete / get and parse content of logo page to get the URLs of all logo variants
    private function getLogoList( $file ){
        //http://upload.wikimedia.org/wikipedia/commons/thumb/f/f2/3sat-Logo.svg/200px-3sat-Logo.svg.png
        $logo_pattern = "\"(\/\/upload\.wikimedia\.org\/wikipedia\/commons\/.\/..\/*?\/".preg_quote($file,"/").")\"";
        $logopage_content = $this->getHTTPResponse( "http://de.wikipedia.org/w/index.php?title=Datei:" . $file."&action=render");
        //print $logopage_content;
        preg_match_all( $logo_pattern, $logopage_content, $logo_matches);
        $logo_urls = array_unique($logo_matches[1]);
        foreach( $logo_urls as $count => $url){
            $logo_urls[ $count ] = str_replace("//", "http://", $url);
        }
        return $logo_urls;
    }*/

    private function getRawWikipediaPage( $language, $pagetitle, $bufferFolder ){
        $this->config->addToDebugLog( "Loading ". htmlspecialchars( $pagetitle ) ." via HTTP.\n");
        $content = $this->getHTTPResponse( "http://".$language.".wikipedia.org/w/index.php?action=render&title=" . $pagetitle );
        file_put_contents( $bufferFolder .  $pagetitle, $content);
        return $content;
    }

    private function getRawWikipediaPageBuffered( $language, $pagetitle ){
        $bufferFolder = $this->config->getValue("userdata")."cache/semanticCollectors/wikipedia/".$language."/";
        if (!file_exists( $bufferFolder . $pagetitle)){
            if (!is_dir($bufferFolder))
                mkdir( $bufferFolder, 0777, true );
            $content = $this->getRawWikipediaPage( $language, $pagetitle, $bufferFolder );
        }
        else{
            $this->config->addToDebugLog( "Loading buffered ". htmlspecialchars( $pagetitle ) ." from local file system.\n" );
            $content =  file_get_contents(  $bufferFolder . $pagetitle);
        }
        return $content;
    }

    //TODO: put cURL related functions in an own class for general usage
    private function getHTTPResponse( $url ){
        $this->config->addToDebugLog( "Reading URL $url\n" );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; U; Linux x86_64; de; rv:1.9.1.9) Gecko/20100402 Ubuntu/9.10 (karmic) Firefox/3.5.9");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 ); //don't print response directly
        $response = curl_exec($ch);
        if ( curl_errno($ch) != 0) {
            $this->config->addToDebugLog( "cURL error: number:" . curl_errno($ch) . " , message: " . curl_error($ch)."\n");
        }
        curl_close($ch);
        return $response;
    }
}
?>