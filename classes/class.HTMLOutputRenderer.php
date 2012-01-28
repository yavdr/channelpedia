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

class HTMLOutputRenderer{

    const
        stylesheet = "../templates/styles.css",
        htmlHeaderTemplate = "../templates/html_header.html",
        htmlFooterTemplate = "../templates/html_footer.html",
        htmlCustomFooterTemplate = "../templates/html_custom_footer.html";

    private
        $db,
        $exportpath,
        $config,
        $craftedPath = "",
        $linklist = array(),
        $source_linklist = array(),
        $html_header_template = "",
        $html_footer_template = "",
        $cutOffIndexHtml = false,
        $relPath;

    function __construct(){
        $this->db = dbConnection::getInstance();
        $this->config = config::getInstance();
        $this->exportpath = $this->config->getValue("exportfolder")."/";
        $this->cutOffIndexHtml = CUT_OFF_INDEX_HTML;
        $this->relPath = "";
    }

    public function renderAllHTMLPages(){
        $this->addDividerTitle("DVB sources");

        $this->addDividerTitle("Satellite positions");
        foreach ($this->config->getValue("sat_positions") as $sat => $languages){
            $this->renderPagesOfSingleSource( "S", $sat, $languages );
        }
        $this->closeHierarchy();

        $this->addDividerTitle("Cable providers");
        foreach ($this->config->getValue("cable_providers") as $cablep => $languages){
            $this->renderPagesOfSingleSource( "C", $cablep, $languages );
        }
        $this->closeHierarchy();

        $this->addDividerTitle("Terrestrial providers");
        foreach ($this->config->getValue("terr_providers") as $terrp => $languages){
            $this->renderPagesOfSingleSource( "T", $terrp, $languages );
        }
        $this->craftedPath = "";
        $this->relPath = "";
        $this->closeHierarchy();

        $this->closeHierarchy();

        $this->addDividerTitle("Reports");
        $this->writeGeneralChangelog();
        $this->writeUploadLog();
        $this->renderDEComparison();
        $this->closeHierarchy();

        $this->renderIndexPage();
    }

    private function setCraftedPath($visibletype, $puresource ){
        //print "Old craftedpath: $this->craftedPath\n";
        $this->craftedPath = $visibletype ."/". strtr(strtr( trim($puresource," _"), "/", ""),"_","/"). "/";
        $this->relPath = "";
        $dirjumps = substr_count( $this->craftedPath, "/");
        for ($z = 0; $z < $dirjumps; $z++){
            $this->relPath .= '../';
        }
        //print "New craftedpath: $this->craftedPath\n";
    }

    public function renderPagesOfSingleSource( $type, $puresource, $languages ){
        if ($type !== "S")
            $source = $type . "[" . $puresource . "]";
        else
            $source = $puresource;
        $visibletype = ($type == "A") ? "ATSC" : "DVB-". $type;
        $this->setCraftedPath($visibletype, $puresource);
        $this->addToOverview( $puresource, $this->getCrispFilename( $this->craftedPath."index.html" ));
        $this->source_linklist = array();
        foreach ($languages as $language)
            $this->writeNiceHTMLPage( $visibletype, $source, $language, $languages, $puresource );
        //$this->renderGroupingHints( $source );
        $this->setCraftedPath($visibletype, $puresource);
        //$this->craftedPath = "";
        $this->renderUnconfirmedChannels( $source );
        $this->renderLatestChannels( $source );
        $this->writeChangelog( $source );
        $this->renderTransponderList( $source );
        $this->renderTransponderNIDCheck( $source );
        if ($type === "S")
            $this->renderLNBSetupHelperTable( $source );
        $this->addCompleteListLink( $source );
        if (in_array("de", $languages)){
            $this->addEPGChannelmapLink( $source );
        }
        $this->setCraftedPath($visibletype, $puresource);
        $this->writeSourceLinklistPage($source, $visibletype, $languages, $puresource);
        $this->source_linklist = array();
    }

    private function getHTMLHeader($pagetitle){
        if ( $this->html_header_template == ""){
            //prepare html header template + stylesheet include
            $stylefile = "styles_". md5( file_get_contents( HTMLOutputRenderer::stylesheet ) ). ".css";
            $this->html_header_template =
                preg_replace( "/\[STYLESHEET\]/", "[CHANNELPEDIA_REL_PATH]" . $stylefile, file_get_contents( HTMLOutputRenderer::htmlHeaderTemplate));
            //TODO: delete old stylesheet files before copying new one
            if (!file_exists( $this->exportpath . $stylefile))
                copy( HTMLOutputRenderer::stylesheet, $this->exportpath . $stylefile );
            $this->html_header_template =
                preg_replace( "/\[INDEX\]/", "[CHANNELPEDIA_REL_PATH]" . "index.html", $this->html_header_template );
                //preg_replace( "/\[INDEX\]/", $this->getCrispFilename( "[CHANNELPEDIA_REL_PATH]" . "index.html" ), $this->html_header_template );
        }
        return preg_replace(
            array( "/\[PAGE_TITLE\]/", "/\[CHANNELPEDIA_REL_PATH\]/" ),
            array( htmlspecialchars( $pagetitle ), $this->relPath ),
            $this->html_header_template
        );
    }

    private function getHTMLFooter(){
        if ( $this->html_footer_template == ""){
            $customfooter = "";
            if ( file_exists( HTMLOutputRenderer::htmlCustomFooterTemplate ) ){
                $customfooter = file_get_contents( HTMLOutputRenderer::htmlCustomFooterTemplate);
            }
            $this->html_footer_template =
                preg_replace( "/\[CUSTOM_FOOTER\]/", $customfooter, file_get_contents( HTMLOutputRenderer::htmlFooterTemplate) );
        }
        return $this->html_footer_template;
    }

    private function addCompleteListLink( $source ){
        $filename = $source."_complete.channels.conf";
        $this->source_linklist[] = array("List sorted by transponder", $filename);
        $filename = $source."_complete_sorted_by_groups.channels.conf";
        $this->source_linklist[] = array("List sorted by group", $filename);
    }

    private function addEPGChannelmapLink( $source ){
        $filename = $source.".epgdata2vdr_channelmap.conf";
        $this->source_linklist[] = array("epgdata2vdr Channelmap", $filename);
        $filename = $source.".tvm2vdr_channelmap.conf";
        $this->source_linklist[] = array("tvm2vdr Channelmap", $filename);
    }

    private function addDividerTitle( $title ){
        $this->addToOverview( $title, "");
    }

    private function addToOverview( $param, $value ){
        $this->linklist[] = array( $param, $value);
    }

    private function getCrispFilename( $filename){
        $retVal = $filename;
        if ($this->cutOffIndexHtml){
            $retVal = str_replace("index.html","", $filename);
            if ($retVal == "")
                $retVal = "/";
        }
        return $retVal;
    }

    private function getMenuItem( $link, $filename, $class = "", $showflagicon = false){
        $class = ($class === "") ? "" : ' class="'.$class.'"';
        $path = $this->exportpath . substr( $filename, 0, strrpos ( $filename , "/" ) );
        $this->config->addToDebugLog( "HTMLOutputRenderer/getMenuItem: file '".$filename."', link: '$link'\n" );
        return '<li'.$class.'><a href="'.$this->getCrispFilename($filename).'">'.
            ($showflagicon ? $this->getFlagIcon($link, $this->relPath) : "") . $link .'</a></li>'."\n";
    }

    private function save( $filename, $filecontent ){
        $path = $this->exportpath . substr( $filename, 0, strrpos ( $filename , "/" ) );
        $this->config->addToDebugLog( "HTMLOutputRenderer/save: file '".$filename."'\n" );
        if (!is_dir($path))
            mkdir($path, 0777, true);
        file_put_contents($this->exportpath . $filename, $filecontent );
    }

    private function addToOverviewAndSave( $link, $filename, $filecontent ){
        $this->save($filename, $filecontent);
        $this->source_linklist[] = array( $link, $this->relPath . $this->getCrispFilename($filename));
    }

    private function addToOverviewAndSave2( $link, $filename, $filecontent ){
        $this->save($filename, $filecontent);
        $this->linklist[] = array( $link, $this->relPath . $this->getCrispFilename($filename));
    }

    private function closeHierarchy(){
        $this->linklist[] = array( "", "close");
    }

    //general changelog for all sources
    public function writeGeneralChangelog(){
        $this->relPath = "";
        $this->writeChangelog("", 1 );
    }

    private function writeChangelog($source, $importance = 0){

        $where = array();
        $wherestring = "";
        if ($source != ""){
            //last confirmed + the 2 previous days
            $where[] = "timestamp >= " . $this->db->quote( $this->getLastConfirmedTimestamp($source) - 60*60*24*2 );
            $where[] = "combined_id LIKE ".$this->db->quote( $source."%" ) . " ";
            $pagetitle = 'Changelog for '.$source;
            $linktitle = 'Changelog';
            $filename = $this->craftedPath . "changelog.html";
            $limit = "";
        }
        else{
            $pagetitle = 'Changelog for all sources';
            $linktitle = $pagetitle;
            $filename = "changelog.html";
            $limit = " LIMIT 100";
        }
        if ($importance === 1 ){
        	$where[] = " importance = $importance ";
        }
        if (count($where) > 0){
            $wherestring = "WHERE ". implode(" AND ", $where);
        }

        $sqlquery=
            "SELECT DATETIME( timestamp, 'unixepoch', 'localtime' ) AS datestamp, name, combined_id, importance, update_description ".
            "FROM channel_update_log $wherestring ORDER BY timestamp DESC".$limit;
        $result = $this->db->query($sqlquery);
        $buffer =
            $this->getHTMLHeader($pagetitle)."\n".
            '<h1>'.htmlspecialchars($pagetitle).'</h1><p>Last updated on: '. date("D M j G:i:s T Y")."</p>\n<table>\n";

        foreach ($result as $row) {
            $desclist = explode("\n", $row["update_description"]);
            $desc = "";
            foreach ($desclist as $descitem){
                $delimiter = strpos( $descitem, ":");
                $desc .= "<b>" .
                    htmlspecialchars( substr( $descitem,0, $delimiter)) . "</b>" .
                    htmlspecialchars( substr( $descitem, $delimiter)) . "<br/>";
            }
            $class = "changelog_row_style_".$row["importance"];
            $buffer.='<tr class="'.$class.'"><td>'.
            htmlspecialchars( $row["datestamp"] ). "</td><td>".
            htmlspecialchars( $row["combined_id"] ). "</td><td>".
            htmlspecialchars( $row["name"] ). "</td><td>".
            $desc.
            "</td></tr>\n";
        }
        $buffer .= "<table>\n".$this->getHTMLFooter();
        if ($source != "")
            $this->addToOverviewAndSave($linktitle, $filename, $buffer);
        else
            $this->addToOverviewAndSave2($linktitle, $filename, $buffer);
    }

    public function writeUploadLog(){
        $pagetitle = "Upload log";
        $sqlquery=
            "SELECT DATETIME( timestamp, 'unixepoch', 'localtime' ) AS datestamp, user, description, source ".
            "FROM upload_log ORDER BY timestamp DESC LIMIT 100";
        $result = $this->db->query($sqlquery);
        $buffer =
            $this->getHTMLHeader($pagetitle)."\n".
            '<h1>'.htmlspecialchars($pagetitle).'</h1><p>Last updated on: '. date("D M j G:i:s T Y")."</p>\n".
            "<table><tr><th>Timestamp</th><th>Channels.conf of user</th><th>Source</th><th>Description</th></tr>\n";

        foreach ($result as $row) {
            $buffer.='<tr><td>'.
            htmlspecialchars( $row["datestamp"] ). "</td><td>".
            htmlspecialchars( substr($row["user"],0,2)."..." ). "</td><td>".
            htmlspecialchars( $row["source"] ). "</td><td>".
            htmlspecialchars( $row["description"] ). "</td>".
            "</tr>\n";
        }
        $buffer .= "<table>\n".$this->getHTMLFooter();
        $filename = "upload_log.html";
        $this->addToOverviewAndSave2($pagetitle, $filename, $buffer );
    }

    private function getSectionTabmenu($visibletype, $source, $language, $languages, $puresource){
        $class = "";
        if ("overview" == $language){
            $language = "";
            $tabmenu = $this->getMenuItem( $source, "index.html", "active", false );
            $this->setCraftedPath($visibletype, $puresource );
        }
        else{
            $tabmenu = $this->getMenuItem( $source, "../index.html", "", false );
            $this->setCraftedPath($visibletype, $puresource . "/" . $language);
        }
        foreach ($languages as $language_temp){
            if ("" == $language)
                $tabmenu .= $this->getMenuItem($language_temp, $language_temp."/index.html", "", true);
            else{
                $class = ($language_temp == $language) ? "active" : "";
                $tabmenu .= $this->getMenuItem($language_temp, "../". $language_temp."/index.html", $class, true );
            }
        }
        $tabmenu = "<ul class=\"section_menu\">" . $tabmenu . "<br clear=\"all\" /></ul>";
        return $tabmenu;
    }

    //assembles all pre-written channel lists from hdd into one html page
    public function writeNiceHTMLPage($visibletype, $source, $language, $languages, $puresource){
        $tabmenu = $this->getSectionTabmenu($visibletype, $source, $language, $languages, $puresource);
        $pagetitle = ''.$source.' - Section '.$language.'';
        $nice_html_output =
            $this->getHTMLHeader($pagetitle).
            $tabmenu.
            '<h1>'.$source.': Section '. $this->getFlagIcon($language, $this->relPath) .$language ."</h1>\n".
            "<p>Last updated on: ". date("D M j G:i:s T Y")."</p>\n";
        $nice_html_body = "";
        $nice_html_linklist = "";
        //FIXME timestamp only needs to be determined once per source, not for every language again and again
        $timestamp = $this->getLastConfirmedTimestamp($source);
        $groupIterator = new channelGroupIterator();
        $groupIterator->init($source, $language);
        while ($groupIterator->moveToNextChannelGroup() !== false){
            $cols = $groupIterator->getCurrentChannelGroupArray();
            //print "Processing channelgroup ".$cols["x_label"]."\n";
            $html_table = "";
            $shortlabel =
            preg_match ( "/.*?\.\d*?\.(.*)/" , $cols["x_label"], $shortlabelparts );
            if (count($shortlabelparts) == 2)
                $shortlabel =$shortlabelparts[1];
            else
                $shortlabel = $cols["x_label"];
            $prestyle = (strstr($shortlabel, "FTA") === false  || strstr($shortlabel, "scrambled") !== false) ? ' class = "scrambled" ' : '';
            $escaped_shortlabel = htmlspecialchars($shortlabel);
            $icons = "";
            $icons .= (strstr($shortlabel, "FTA") === false  || strstr($shortlabel, "scrambled") !== false) ? ' <img src="'.$this->relPath.'../res/icons/lock.png" class="lock_icon" />' : '';
            $nice_html_body .=
                '<h2'.$prestyle.'>'.
                '<a name ="'.$escaped_shortlabel.'">'.$escaped_shortlabel . $icons. " (" . $cols["channelcount"] . ' channels)</a>'.
                "</h2>\n".
                //"<h3>VDR channel format</h3>\n".
                "<pre".$prestyle.">";
            $x = new channelIterator( $shortenSource = true);
            //print $source. "/" . $cols["x_label"]."\n";
            $x->init1($cols["x_label"], $source, $orderby = "UPPER(name) ASC");
            while ($x->moveToNextChannel() !== false){
                if ($html_table == ""){
                    $html_table = "<h3>Table view</h3>\n<div class=\"tablecontainer\"><table class=\"nice_table\">\n<tr>";
                    foreach ($x->getCurrentChannelArrayKeys() as $header){
                        $html_table .= '<th class="'.htmlspecialchars($header).'">'.htmlspecialchars(ucfirst($header))."</th>\n";
                    }
                    $html_table .= "</tr>\n";
                }
                $curChan = $x->getCurrentChannelObject();
                $curChanString = htmlspecialchars($curChan->getChannelString());
                $popuptitle = "". $curChan->getName(). " | ".
                    ($curChan->isSatelliteSource() ?
                        "Type: DVB-S"    . ( $curChan->onS2SatTransponder()   ? "2"        : ""           ) ." | ".
                        "Polarisation: " . ( $curChan->belongsToSatVertical() ? "Vertical" : "Horizontal" ) ." | ".
                        "Band: "         . ( $curChan->belongsToSatHighBand() ? "High"     : "Low"        ) ." | ".
                        "FEC: "          . $curChan->getFECOfSatTransponder()                          ." | "
                    : "" ).
                    "Modulation: "        . $curChan->getModulation() ." | ".
                    "Frequency: "         . $curChan->getReadableFrequency() ." | ".
                    "Symbolrate: "        . $curChan->getSymbolrate() ." | ".
                    "Date added: "        . date("D, d M Y H:i:s", $curChan->getXTimestampAdded() ) . " | ".
                    "Date last changed: " . date("D, d M Y H:i:s", $curChan->getXLastChanged()    ) . " | ".
                    "Date last seen: "    . date("D, d M Y H:i:s", $curChan->getXLastConfirmed()  ) . " ".
                    "";
                //check if channel might be outdated, if so, apply additional css class
                if ( $x->getCurrentChannelObject()->getXLastConfirmed() < $timestamp)
                    $nice_html_body .= "<span title=\"".$popuptitle."\" class=\"outdated\">". $curChanString ."</span>\n";
                else
                    $nice_html_body .= "<span title=\"".$popuptitle."\">".$curChanString."</span>\n";

                $html_table .= "<tr".$prestyle.">\n";
                //FIXME use channel object here
                foreach ($x->getCurrentChannelObject()->getAsArray() as $param => $value){
                    switch ($param){
                        case "apid":
                        case "caid":
                            $value = str_replace ( array(",",";"), ",<br/>", htmlspecialchars($value ));
                            break;
                        case "frequency":
                            $sourcetype = substr($source,0,1);
                            if ($sourcetype == "S")
                                $value = $value." MHz";
                            else{
//    * MHz, kHz oder Hz angegeben.
//Der angegebene Wert wird mit 1000 multipliziert, bis er größer als 1000000 ist.
                                 $value2 = intval($value);
                                 $step = 0;    //113000
                                 while($value2 < 1000000){
                                     $step++;
                                     $value2 = $value2 * 1000;
                                 }
                                 $value = $value2 / (1000*1000);
                                 $value = $value . " Mhz";
                            }
                            break;
                        case "x_last_changed":
                            $value = date("D, d M Y H:i:s", $value);
                            break;
                        default:
                            $value = htmlspecialchars($value);
                    }
                    $html_table .= '<td class="'.htmlspecialchars($param).'">'.$value."</td>\n";
                }
                $html_table .= "</tr>\n";
            }
            $html_table .= "</table></div>";
            $nice_html_body .= "</pre>\n";
            //$nice_html_body .= "</pre>\n".$html_table;
            $nice_html_linklist .= '<li><a href="#'.$escaped_shortlabel.'">'.$escaped_shortlabel. " (" . $cols["channelcount"] . " channels)</a></li>\n";
        }

        $nice_html_output .=
            "<h2>Overview</h2><ul class=\"overview\">" .
            $nice_html_linklist . "</ul>\n".
            $nice_html_body.
            $this->getHTMLFooter();

        $this->save( $this->craftedPath . "index.html", $nice_html_output );
    }

    private function renderDEComparison(){
        $pagetitle = "Comparison: Parameters of German public TV channels at different providers";
        $nice_html_output =
            $this->getHTMLHeader($pagetitle).
            '<h1>'.htmlspecialchars( $pagetitle ).'</h1>
            <p>Last updated on: '. date("D M j G:i:s T Y").'</p>';
        $html_table = "";
        $x = new channelIterator( $shortenSource = false );
        $x->init2( "SELECT * FROM channels WHERE x_label LIKE 'de.%' AND lower(x_label) LIKE '%public%' ORDER by x_label ASC, lower(name) ASC, source ASC");
        $lastname = "";
        while ($x->moveToNextChannel() !== false){
            $carray = $x->getCurrentChannelObject()->getAsArray();
            if (strtolower($carray["name"]) != strtolower($lastname)){
                if ($lastname != ""){
                    $html_table .= "</table>\n</div>\n";
                }
                $html_table .= "<h2>".htmlspecialchars($carray["name"])."</h2>\n<h3>Table view</h3>\n<div class=\"tablecontainer\"><table>\n<tr>";
                foreach ($x->getCurrentChannelArrayKeys() as $header){
                    $html_table .= '<th class="'.htmlspecialchars($header).'">'.htmlspecialchars(ucfirst($header))."</th>\n";
                }
                $html_table .= "</tr>\n";
            }
            $html_table .= "<tr>\n";
            foreach ($carray as $param => $value){
                if ($param == "apid" || $param == "caid"){
                    $value = str_replace ( array(",",";"), ",<br/>", htmlspecialchars($value ));
                }
                elseif ($param == "x_last_changed"){
                    $value = date("D, d M Y H:i:s", $value);
                }
                else
                    $value = htmlspecialchars($value);
                $html_table .= '<td class="'.htmlspecialchars($param).'">'.$value."</td>\n";
            }
            $html_table .= "</tr>\n";
            $lastname = $carray["name"];
        }
        $html_table .= "</table></div>\n";
        $nice_html_output .=
            $html_table .
            $this->getHTMLFooter();
        $filename = "parameter_comparison_de.html";
        $this->addToOverviewAndSave2( "Comparison: Parameters of German public TV channels at different providers", $filename, $nice_html_output );
    }

    //FIXME: this method is a duplicate of the one in channelImport.php
    private function getLastConfirmedTimestamp($source){
        $timestamp = 0;
        $sqlquery = "SELECT x_last_confirmed FROM channels WHERE source = ".$this->db->quote($source)." ORDER BY x_last_confirmed DESC LIMIT 1";
        $result = $this->db->query($sqlquery);
        $timestamp_raw = $result->fetchAll();
        if (isset($timestamp_raw[0][0]))
            $timestamp = intval($timestamp_raw[0][0]);
        return $timestamp;
    }

    private function getEarliestChannelAddedTimestamp($source){
        $timestamp = 0;
        $sqlquery = "SELECT x_timestamp_added FROM channels WHERE source = ".$this->db->quote($source)." AND x_timestamp_added > 0 ORDER BY x_timestamp_added ASC LIMIT 1";
        $result = $this->db->query($sqlquery);
        $timestamp_raw = $result->fetchAll();
        if (isset($timestamp_raw[0][0]))
            $timestamp = intval($timestamp_raw[0][0]);
        return $timestamp;
    }


    private function renderUnconfirmedChannels($source){
        $pagetitle = "Unconfirmed channels on $source / likely to be outdated";
        $nice_html_output =
            $this->getHTMLHeader($pagetitle).
            '<h1>'.htmlspecialchars( $pagetitle ).'</h1>
            <p>Last updated on: '. date("D M j G:i:s T Y").'</p>';
        $html_table = "";
        $timestamp = intval($this->getLastConfirmedTimestamp($source));
        if ($timestamp != 0){
            $nice_html_output .= "<p>Looking for channels that were last confirmed before ". date("D, d M Y H:i:s", $timestamp). " ($timestamp)</p>\n";

            $x = new channelIterator( $shortenSource = true );
            $x->tolerateInvalidChannels();
            $x->init2( "SELECT * FROM channels WHERE source = ".$this->db->quote($source)." AND x_last_confirmed < ".$timestamp);
            $lastname = "";
            while ($x->moveToNextChannel() !== false){
                $carray = $x->getCurrentChannelObject()->getAsArray();
                if ($lastname == ""){
                    $html_table .= "<h3>Table view</h3>\n<div class=\"tablecontainer\"><table>\n<tr>";
                    foreach ($x->getCurrentChannelArrayKeys() as $header){
                        $html_table .= '<th class="'.htmlspecialchars($header).'">'.htmlspecialchars(ucfirst($header))."</th>\n";
                    }
                    $html_table .= "</tr>\n";
                }
                $html_table .= "<tr>\n";
                foreach ($carray as $param => $value){
                    if ($param == "apid" || $param == "caid" || $param == "tpid" ){
                        $value = str_replace ( array(",",";"), ",<br/>", htmlspecialchars($value ));
                    }
                    elseif ($param == "x_last_changed" || $param == "x_timestamp_added" || $param == "x_last_confirmed"){
                        $value = date("D, d M Y H:i:s", $value);
                    }
                    else
                        $value = htmlspecialchars($value);
                    $html_table .= '<td class="'.htmlspecialchars($param).'">'.$value."</td>\n";
                }
                $html_table .= "</tr>\n";
                $lastname = $carray["name"];
            }
        }
        $html_table .= "</table></div>\n";
        $nice_html_output .=
            $html_table .
            $this->getHTMLFooter();
        $filename = $this->craftedPath . "unconfirmed_channels.html";
        $this->addToOverviewAndSave( "Unconfirmed/outdated", $filename, $nice_html_output );
    }

    private function renderLatestChannels($source){
        $pagetitle = "Latest channel additions on $source";
        $nice_html_output =
            $this->getHTMLHeader($pagetitle).
            '<h1>'.htmlspecialchars( $pagetitle ).'</h1>
            <p>Last updated on: '. date("D M j G:i:s T Y").'</p>';
        $html_table = "";
        $timestamp = intval($this->getEarliestChannelAddedTimestamp($source));
        if ($timestamp != 0){
            $nice_html_output .= "<p>Channels that were recently found (only the latest 25 channels that were added after the initial upload of this source).</p>\n";

            $x = new channelIterator( $shortenSource = true );
            $x->init2( "SELECT name, provider, source, frequency, parameter, symbolrate, vpid, apid, tpid, caid, sid, nid, tid, x_timestamp_added FROM channels WHERE source = ".$this->db->quote($source)." AND x_timestamp_added > " . $this->db->quote($timestamp) . " ORDER BY x_timestamp_added DESC, name DESC LIMIT 25");
            $lastname = "";
            while ($x->moveToNextChannel() !== false){
                $carray = $x->getCurrentChannelObject()->getAsArray();
                if ($lastname == ""){
                    $html_table .= "<h3>Table view</h3>\n<div class=\"tablecontainer\"><table>\n<tr>";
                    foreach ($x->getCurrentChannelArrayKeys() as $header){
                        $html_table .= '<th class="'.htmlspecialchars($header).'">'.htmlspecialchars(ucfirst($header))."</th>\n";
                    }
                    $html_table .= "</tr>\n";
                }
                $html_table .= "<tr>\n";
                foreach ($carray as $param => $value){
                    if ($param == "apid" || $param == "caid"){
                        $value = str_replace ( array(",",";"), ",<br/>", htmlspecialchars($value ));
                    }
                    elseif ($param == "x_last_changed" || $param == "x_timestamp_added" || $param == "x_last_confirmed"){
                        $value = date("D, d M Y H:i:s", $value);
                    }
                    else
                        $value = htmlspecialchars($value);
                    $html_table .= '<td class="'.htmlspecialchars($param).'">'.$value."</td>\n";
                }
                $html_table .= "</tr>\n";
                $lastname = $carray["name"];
            }
        }
        $html_table .= "</table></div>\n";
        $nice_html_output .=
            $html_table .
            $this->getHTMLFooter();
        $filename = $this->craftedPath . "latest_channel_additions.html";
        $this->addToOverviewAndSave( "New channels", $filename, $nice_html_output );
    }

    private function renderGroupingHints($source){
        $pagetitle = "Grouping hints for ".$source;
        $nice_html_output =
            $this->getHTMLHeader($pagetitle).
            '<h1>'.htmlspecialchars( $pagetitle ).'</h1>
            <p>Last updated on: '. date("D M j G:i:s T Y").'</p>';
        $html_table = "<table><tr><th>Provider</th><th>Number of related channels</th></tr>\n";
        $nice_html_body = "";
        $result = $this->db->query(
            "SELECT provider, COUNT(*) AS providercount FROM channels ".
            "WHERE source = ".$this->db->quote($source).
            " GROUP BY provider ORDER by providercount DESC"
        );
        foreach ($result as $row) {
            $html_table .= "<tr><td>".htmlspecialchars($row["provider"])."</td><td>".htmlspecialchars($row["providercount"])."</td></tr>\n";
/*            $nice_html_body .= "<h2>".htmlspecialchars($row["provider"]). " (" . htmlspecialchars($row["providercount"]) ." channels)</h2>\n<pre>\n";
            $x = new channelIterator( $shortenSource = true );
            $x->init2( "SELECT * FROM channels ".
                "WHERE source = ".$this->db->quote($source)." AND ".
                "provider = ".$this->db->quote($row["provider"]).
                " ORDER by x_label ASC, lower(name) ASC, source ASC");
            while ($x->moveToNextChannel() !== false){
                $nice_html_body .= htmlspecialchars( $x->getCurrentChannelObject()->getChannelString())."\n";
            }
            $nice_html_body .= "</pre>";*/
        }

        $html_table .= "</table>\n";
        $nice_html_output .=
            $html_table .
            $nice_html_body.
            $this->getHTMLFooter();
        $filename = $this->craftedPath . "grouping_hints.html";
//        $this->addDividerTitle("Reports");
        $this->addToOverviewAndSave( "Grouping hints", $filename, $nice_html_output );
    }

    private function renderTransponderList( $source ){
        $pagetitle = htmlspecialchars($source) . " - Transponder list";
        $nice_html_output =
            $this->getHTMLHeader($pagetitle).
            '<h1>'.htmlspecialchars( $pagetitle ).'</h1>
            <p>Last updated on: '. date("D M j G:i:s T Y").'</p>';
        $result = $this->db->query(
            "SELECT parameter, frequency, symbolrate, nid
            FROM channels
            WHERE source = ".$this->db->quote($source)."
            GROUP BY parameter, frequency, nid
            ORDER BY frequency, parameter, nid"
            );
        $html_table = "<table><tr><th>Frequency</th><th>Parameter</th><th>Symbolrate</th><th>NID</th></tr>\n";
        foreach ($result as $row) {
            $html_table .= "<tr>".
                "<td>".htmlspecialchars($row["frequency"])."</td>".
                "<td>".htmlspecialchars($row["parameter"])."</td>".
                "<td>".htmlspecialchars($row["symbolrate"])."</td>".
                "<td>".htmlspecialchars($row["nid"])."</td>".
                "</tr>\n";
        }
        $html_table .= "</table>\n";
        $nice_html_output .= $html_table;
        $nice_html_output .= $this->getHTMLFooter();
        $filename = $this->craftedPath . "transponder_list.html";
        $this->addToOverviewAndSave( "Transponders", $filename, $nice_html_output );
    }

    private function renderTransponderNIDCheck( $source ){
        $pagetitle = htmlspecialchars($source) . " - Transponder plausibility check";
        $nice_html_output =
            $this->getHTMLHeader($pagetitle).
            '<h1>'.htmlspecialchars( $pagetitle ).'</h1>
            <p>Last updated on: '. date("D M j G:i:s T Y").'</p>
            <p>This page only has content if the channel list of this source is faulty. This means that some channel data is wrong or outdated. There should only be one NID per transponder.</p>';

        $result = $this->db->query(
            "SELECT channels1.frequency as fre, channels1.parameter as mod, channels1.symbolrate as sym, channels1.nid, channels2.nid
            FROM channels AS channels1
            LEFT JOIN channels AS channels2 WHERE
            channels1.source = ".$this->db->quote($source)." AND
            channels1.source = channels2.source AND
            channels1.frequency = channels2.frequency AND
            channels1.symbolrate = channels2.symbolrate AND
            channels1.parameter = channels2.parameter AND
            channels1.nid != channels2.nid
            GROUP BY channels1.source, channels1.frequency, channels1.parameter, channels1.symbolrate"
        );
        foreach ($result as $row) {
            $nice_html_output .= "<h2>" .htmlspecialchars(
                    $row["fre"]. " " .
                    $row["mod"]. " " .
                    $row["sym"]).
                    "</h2>";
            $x = new channelIterator( $shortenSource = true );
            $x->init2( "SELECT * FROM channels WHERE ".
                "source     = " . $this->db->quote( $source ) . " AND ".
                "frequency  = " . $this->db->quote( $row["fre" ] ) . " AND " .
                "parameter = " . $this->db->quote( $row["mod"] ) . " AND " .
                "symbolrate = " . $this->db->quote( $row["sym"] )
            );
            $html_table = "";
            $lastname = "";
            while ($x->moveToNextChannel() !== false){
                $carray = $x->getCurrentChannelObject()->getAsArray();
                if ($lastname == ""){
                    $html_table .= "<h3>Table view</h3>\n<div class=\"tablecontainer\"><table>\n<tr>";
                    foreach ($x->getCurrentChannelArrayKeys() as $header){
                        $html_table .= '<th class="'.htmlspecialchars($header).'">'.htmlspecialchars(ucfirst($header))."</th>\n";
                    }
                    $html_table .= "</tr>\n";
                }
                $html_table .= "<tr>\n";
                foreach ($carray as $param => $value){
                    if ($param == "apid" || $param == "caid"){
                        $value = str_replace ( array(",",";"), ",<br/>", htmlspecialchars($value ));
                    }
                    elseif ($param == "x_last_changed"){
                        $value = date("D, d M Y H:i:s", $value);
                    }
                    else
                        $value = htmlspecialchars($value);
                    $html_table .= '<td class="'.htmlspecialchars($param).'">'.$value."</td>\n";
                }
                $html_table .= "</tr>\n";
                $lastname = $carray["name"];
            }
            $html_table .= "</table></div>\n";
            $nice_html_output .= $html_table;
        }
        $nice_html_output .= $this->getHTMLFooter();
        $filename = $this->craftedPath . "transponder_nid_check.html";
        $this->addToOverviewAndSave( "NID check", $filename, $nice_html_output );
    }

    private function renderLNBSetupHelperTable($source){
        $pagetitle = "LNB Setup helper table for satellite position $source";
        $nice_html_output =
            $this->getHTMLHeader($pagetitle).
            '<h1>'.htmlspecialchars( $pagetitle ).'</h1>
            <p>Last updated on: '. date("D M j G:i:s T Y").'</p>'.
            "<p>This page contains all TV and FTA radio channels sorted by transponders and grouped by the four different sat bands:</br><ul>
               <li>Horizontal High Band (11700 MHz to 12750 MHz)</li>
               <li>Vertical High Band (11700 MHz to 12750 MHz)</li>
               <li>Horizontal Low Band (10700 MHz to 11700 MHz)</li>
               <li>Vertical Low Band (10700 Mhz to 11700 MHz)</li>
                </ul><p>A channel list specifically grouped by sat bands might be helpful when testing a new sat cable setup, ".
                "a new LNB/Multiswitch or when evaluating VDR with LNB sharing feature enabled. ".
                "Basically, if your setup is flawless you should be able to receive something on any of the four sat bands ".
                //"(as long as there are FTA channels available on each band). ".
                //"Encrypted channels are excluded from the tables below to reduce the amount of data.".
                "</p>\n<pre>".
            $this->addChannelSection( $source, "H", "High", "TV", false ).
            $this->addChannelSection( $source, "H", "High", "TV", true ).
            $this->addChannelSection( $source, "H", "High", "Radio", false ).
            $this->addChannelSection( $source, "V", "High", "TV", false ).
            $this->addChannelSection( $source, "V", "High", "TV", true ).
            $this->addChannelSection( $source, "V", "High", "Radio", false ).
            $this->addChannelSection( $source, "H", "Low", "TV", false ).
            $this->addChannelSection( $source, "H", "Low", "TV", true ).
            $this->addChannelSection( $source, "H", "Low", "Radio", false ).
            $this->addChannelSection( $source, "V", "Low", "TV", false ).
            $this->addChannelSection( $source, "V", "Low", "TV", true ).
            $this->addChannelSection( $source, "V", "Low", "Radio", false ).
            "\n<b>:End of list. The following channels were added by VDR automatically</b>\n".
            "</pre>\n".
            $this->getHTMLFooter();
        $filename = $this->craftedPath . "LNBSetupHelperTable.html";
        $this->addToOverviewAndSave( "LNB setup help", $filename, $nice_html_output );
    }

    private function addChannelSection( $source, $direction, $band, $type, $encrypted = false ){
        if ($direction == "H")
            $direction_long = "Horizontal";
        else if ($direction == "V")
            $direction_long = "Vertical";
        else
            throw new Exception("direction should either be H or V");
        if ($band == "High"){
            $lowfreq = 10700;
            $hifreq = 11700;
        }
        else if ($band == "Low"){
            $lowfreq = 11700;
            $hifreq = 12750;
        }
        else
            throw new Exception("band should either be High or Low");
        if ($type == "TV")
            $type_where = "AND vpid != '0'";
        else if ($type == "Radio")
            $type_where = "AND vpid = '0' AND apid != '0'";
        else
            $type = "";

        if ($encrypted)
            $caidflag = "!=";
        else
            $caidflag = "=";

        return
            "\n<b>:".($encrypted?"Scrambled":"FTA"). " " .$type." channels on " . $direction_long . " ".$band." Band ".htmlspecialchars($source)."</b>\n\n".
            $this->addCustomChannelList( "
                SELECT * FROM channels WHERE source = ".$this->db->quote($source)."
                AND caid $caidflag '0'
                AND frequency >= ".$lowfreq."
                AND frequency <= ".$hifreq."
                AND substr(parameter,1,1) = '".$direction."'
                ".$type_where."
                ORDER BY frequency, parameter, symbolrate, sid
            " );
    }

    private function addChannelTable( $statement ){
        $html_table = "";
        $x = new channelIterator( $shortenSource = true );
        $x->init2( $statement );
        $lastname = "";
        while ($x->moveToNextChannel() !== false){
            $carray = $x->getCurrentChannelObject()->getAsArray();
            if ($lastname == ""){
                $html_table .= "<div class=\"tablecontainer\"><table>\n<tr>";
                foreach ($x->getCurrentChannelArrayKeys() as $header){
                    $html_table .= '<th class="'.htmlspecialchars($header).'">'.htmlspecialchars(ucfirst($header))."</th>\n";
                }
                $html_table .= "</tr>\n";
            }
            $html_table .= "<tr>\n";
            foreach ($carray as $param => $value){
                if ($param == "apid" || $param == "caid"){
                    $value = str_replace ( array(",",";"), ",<br/>", htmlspecialchars($value ));
                }
                elseif ($param == "x_last_changed" || $param == "x_timestamp_added" || $param == "x_last_confirmed"){
                    $value = date("D, d M Y H:i:s", $value);
                }
                else
                    $value = htmlspecialchars($value);
                $html_table .= '<td class="'.htmlspecialchars($param).'">'.$value."</td>\n";
            }
            $html_table .= "</tr>\n";
            $lastname = $carray["name"];
        }
        $html_table .= "</table></div>\n";
        return $html_table;
    }

    private function addCustomChannelList( $statement ){
        $list = "";
        $x = new channelIterator( $shortenSource = true );
        $x->init2( $statement );
        while ($x->moveToNextChannel() !== false){
            $ch = $x->getCurrentChannelObject();
            $labelparts = explode(".", $ch->getXLabel());
            $list .= $this->getFlagIcon($labelparts[0], $this->relPath).
                //lock icon taken from http://www.openwebgraphics.com/resources/data/1629/lock.png
                (($ch->getCAID() !== "0")? '<img src="'.$this->relPath.'../res/icons/lock.png" class="lock_icon" title="'.htmlspecialchars($ch->getCAID()).'" />':'');
            $list .= htmlspecialchars( $ch->getChannelString() )."\n";
        }
        return $list;
    }

    private function getFlagIcon($label, $relPath){
        $image = "";
        if ($label != "uncategorized"){
            if ($label == "uk"){
                $label = "gb";
            }
            $checkpath = "../res/icons/flags/".$label.".png";
            if (file_exists( $checkpath ))
                $image = "<img src=\"".$relPath."../res/icons/flags/".$label.".png\" class=\"flag_icon\" />";
            //else
                //die("image $checkpath does not exist! Stopping\n");
        }
        return $image;
    }

    private function writeSourceLinklistPage($source, $visibletype, $languages, $puresource){
        $pagetitle = "$source - Overview";
        $nice_html_output =
            $this->getHTMLHeader($pagetitle).
            $this->getSectionTabmenu($visibletype, $source, "overview", $languages, $puresource).
            '<h1>'.htmlspecialchars( $pagetitle ).'</h1>'.
            '<p>Last updated on: '. date("D M j G:i:s T Y").'</p><ul>';
        foreach ($this->source_linklist as $linkarray){
            $nice_html_output .= '<li><a href="'.$linkarray[1].'">'.$linkarray[0].'</a></li>'."\n";
        }
        $nice_html_output .= "</ul>".
            $this->getHTMLFooter();
        $filename = $this->craftedPath . "index.html";
        $path = $this->exportpath . substr( $filename, 0, strrpos ( $filename , "/" ) );
        $this->config->addToDebugLog( "HTMLOutputRenderer/writeSourceLinklistPage: file '".$filename."'\n" );
        if (!is_dir($path))
            mkdir($path, 0777, true);
        file_put_contents($this->exportpath . $filename, $nice_html_output );
    }

    private function renderIndexPage(){
        $pagetitle = "Overview";
        $nice_html_output =
            $this->getHTMLHeader($pagetitle).
            '<h1>'.htmlspecialchars( $pagetitle ).'</h1>
            <ul>
        ';
        foreach ($this->linklist as $line){
           $title = $line[0];
           $url = $line[1];
           if($url == "")
               $nice_html_output .= '<li><b>'.htmlspecialchars( $title )."</b></li>\n<ul>";
           elseif($url == "close")
               $nice_html_output .= "<br clear=\"all\" /></ul>\n";
           else
              $nice_html_output .= '<li><a href="'. urldecode( $url ) .'">'.$title ."</a></li>\n";
        }

        $nice_html_output .= "<br clear=\"all\" /></ul>\n".$this->getHTMLFooter();
        file_put_contents($this->exportpath . "index.html", $nice_html_output );
    }
}
?>