<?php

class HTMLOutputRenderSource {

    private
        $db,
        $config,
        $source_linklist = array(),
        $craftedPath = "",
        $visibletype = "",
        $puresource = "",
        $languages = array(),
        $HTMLFragments,
        $source = "",
        $relPath = "";

    public function __construct( $type, $puresource, $languages ){
        $this->HTMLFragments = HTMLFragments::getInstance();
        $this->db = dbConnection::getInstance();
        $this->config = config::getInstance();
        $this->relPath = "";
        $this->visibletype = ($type == "A") ? "ATSC" : "DVB-". $type;
        $this->puresource = $puresource;
        $this->languages = $languages;
        $this->source = ($type !== "S") ? $type . "[" . $puresource . "]" : $puresource;
        $this->source_linklist = array();
        foreach ($this->languages as $language)
            $this->writeNiceHTMLPage( $language );
        $this->setCraftedPath();
        //$this->renderGroupingHints();
        $this->renderUnconfirmedChannels();
        $this->renderLatestChannels();
        $this->writeChangelog();
        $this->renderTransponderList();
        $this->renderTransponderNIDCheck();
        if ($type === "S")
            $this->renderLNBSetupHelperTable();
        $this->addCompleteListLink();
        if (in_array("de", $this->languages)){
            $this->addEPGChannelmapLink();
        }
        $this->setCraftedPath();
        $this->writeSourceLinklistPage();
        //$this->source_linklist = array();
    }

    public function getVisibletype(){
        return $this->visibletype;
    }

    private function getMenuItem( $link, $filename, $class = "", $showflagicon = false){
        $class = ($class === "") ? "" : ' class="'.$class.'"';
        $path = $this->config->getValue("exportfolder")."/" . substr( $filename, 0, strrpos ( $filename , "/" ) );
        $this->config->addToDebugLog( "HTMLOutputRenderer/getMenuItem: file '".$filename."', link: '$link'\n" );
        return '<li'.$class.'><a href="'.$this->HTMLFragments->getCrispFilename($filename).'">'.
            ($showflagicon ? $this->HTMLFragments->getFlagIcon($link, $this->relPath) : "") . $link .'</a></li>'."\n";
    }

    private function setCraftedPath( $suffix = ""){
        //print "Old craftedpath: $this->craftedPath\n";
        $this->craftedPath = $this->visibletype ."/". strtr(strtr( trim($this->puresource," _"), "/", ""),"_","/"). $suffix . "/";
        $this->relPath = "";
        $dirjumps = substr_count( $this->craftedPath, "/");
        for ($z = 0; $z < $dirjumps; $z++){
            $this->relPath .= '../';
        }
        //print "New craftedpath: $this->craftedPath\n";
    }

    private function writeSourceLinklistPage(){
        $pagetitle = "$this->source - Overview";
        $page = new HTMLPage($this->relPath);
        $page->setPageTitle($pagetitle);
        $page->appendToBody(
            $this->getSectionTabmenu("overview").
            '<h1>'.htmlspecialchars( $pagetitle ).'</h1>'.
            '<p>Last updated on: '. date("D M j G:i:s T Y").'</p><ul>'
        );
        foreach ($this->source_linklist as $linkarray){
            $page->appendToBody('<li><a href="'.$linkarray[1].'">'.$linkarray[0].'</a></li>'."\n");
        }
        $page->appendToBody("</ul>");
        $this->config->save($this->craftedPath . "index.html", $page->getContents());
    }

    private function addCompleteListLink(){
        $filename = $this->source."_complete.channels.conf";
        $this->source_linklist[] = array("List sorted by transponder", $filename);
        $filename = $this->source."_complete_sorted_by_groups.channels.conf";
        $this->source_linklist[] = array("List sorted by group", $filename);
    }

    private function addEPGChannelmapLink(){
        $filename = $this->source.".epgdata2vdr_channelmap.conf";
        $this->source_linklist[] = array("epgdata2vdr Channelmap", $filename);
        $filename = $this->source.".tvm2vdr_channelmap.conf";
        $this->source_linklist[] = array("tvm2vdr Channelmap", $filename);
    }

    private function getSectionTabmenu($language){
        $class = "";
        if ("overview" == $language){
            $language = "";
            $tabmenu = $this->getMenuItem( $this->source, "index.html", "active", false );
            $this->setCraftedPath();
        }
        else if ("" == $language){
            $language = "";
            $tabmenu = $this->getMenuItem( $this->source, "index.html", "", false );
            $this->setCraftedPath();
        }
        else{
            $tabmenu = $this->getMenuItem( $this->source, "../index.html", "", false );
            $this->setCraftedPath("/" . $language);
        }
        foreach ($this->languages as $language_temp){
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

    private function addToOverviewAndSave( $link, $filename, $filecontent ){
        $this->config->save($filename, $filecontent);
        $this->source_linklist[] = array( $link, $this->relPath . $this->HTMLFragments->getCrispFilename($filename));
    }

    private function writeChangelog(){
        $where = array(
            "timestamp >= " . $this->db->quote( $this->getLastConfirmedTimestamp($this->source) - 60*60*24*2 ), //last confirmed + the 2 previous days
            "combined_id LIKE ".$this->db->quote( $this->source."%" ) . " "
        );
        $changelog = new HTMLChangelog($where, 'Changelog for '.$this->source, "", 0, $this->relPath);
        $this->addToOverviewAndSave('Changelog', $this->craftedPath . "changelog.html", $changelog->getContents());
    }

    //assembles all pre-written channel lists from hdd into one html page
    private function writeNiceHTMLPage($language){
        $pagetitle = ''.$this->source.' - Section '.$language.'';
        $sectionTabmenu = $this->getSectionTabmenu($language); // this updates craftedPath and relpath! use before HTMLPage
        $page = new HTMLPage($this->relPath);
        $page->setPageTitle( $pagetitle );
        $page->appendToBody(
            $sectionTabmenu .
            '<h1>'.$this->source.': Section '. $this->HTMLFragments->getFlagIcon($language, $this->relPath) .$language ."</h1>\n".
            "<p>Last updated on: ". date("D M j G:i:s T Y")."</p>\n"
        );
        $nice_html_body = "";
        $nice_html_linklist = "";
        //FIXME timestamp only needs to be determined once per source, not for every language again and again
        $timestamp = $this->getLastConfirmedTimestamp();
        $groupIterator = new channelGroupIterator();
        $groupIterator->init($this->source, $language);
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
            //print $this->source. "/" . $cols["x_label"]."\n";
            $x->init1($cols["x_label"], $this->source, $orderby = "UPPER(name) ASC");
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
                            $sourcetype = substr($this->source,0,1);
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

        $page->appendToBody(
            "<h2>Overview</h2><ul class=\"overview\">" .
            $nice_html_linklist . "</ul>\n".
            $nice_html_body
        );
        $this->config->save( $this->craftedPath . "index.html", $page->getContents() );
    }

    //FIXME: this method is a duplicate of the one in channelImport.php
    private function getLastConfirmedTimestamp(){
        $timestamp = 0;
        $sqlquery = "SELECT x_last_confirmed FROM channels WHERE source = ".$this->db->quote($this->source)." ORDER BY x_last_confirmed DESC LIMIT 1";
        $result = $this->db->query($sqlquery);
        $timestamp_raw = $result->fetchAll();
        if (isset($timestamp_raw[0][0]))
            $timestamp = intval($timestamp_raw[0][0]);
        return $timestamp;
    }

    private function getEarliestChannelAddedTimestamp(){
        $timestamp = 0;
        $sqlquery = "SELECT x_timestamp_added FROM channels WHERE source = ".$this->db->quote($this->source)." AND x_timestamp_added > 0 ORDER BY x_timestamp_added ASC LIMIT 1";
        $result = $this->db->query($sqlquery);
        $timestamp_raw = $result->fetchAll();
        if (isset($timestamp_raw[0][0]))
            $timestamp = intval($timestamp_raw[0][0]);
        return $timestamp;
    }


    private function renderUnconfirmedChannels(){
        $pagetitle = "Unconfirmed channels on $this->source / likely to be outdated";
        $page = new HTMLPage($this->relPath);
        $page->setPageTitle($pagetitle);
        $page->appendToBody(
            $this->getSectionTabmenu("").
            '<h1>'.htmlspecialchars( $pagetitle ).'</h1>
            <p>Last updated on: '. date("D M j G:i:s T Y").'</p>'
        );
        $html_table = "";
        $timestamp = intval($this->getLastConfirmedTimestamp());
        if ($timestamp != 0){
            $page->appendToBody( "<p>Looking for channels that were last confirmed before ". date("D, d M Y H:i:s", $timestamp). " ($timestamp)</p>\n");

            $x = new channelIterator( $shortenSource = true );
            $x->tolerateInvalidChannels();
            $x->init2( "SELECT * FROM channels WHERE source = ".$this->db->quote($this->source)." AND x_last_confirmed < ".$timestamp);
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
        $page->appendToBody($html_table);
        $this->addToOverviewAndSave( "Unconfirmed/outdated", $this->craftedPath . "unconfirmed_channels.html", $page->getContents());
    }

    private function renderLatestChannels(){
        $pagetitle = "Latest channel additions on $this->source";
        $page = new HTMLPage($this->relPath);
        $page->setPageTitle($pagetitle);
        $page->appendToBody(
            $this->getSectionTabmenu("").
            '<h1>'.htmlspecialchars( $pagetitle ).'</h1>
            <p>Last updated on: '. date("D M j G:i:s T Y").'</p>'
        );
        $html_table = "";
        $timestamp = intval($this->getEarliestChannelAddedTimestamp());
        if ($timestamp != 0){
            $page->appendToBody("<p>Channels that were recently found (only the latest 25 channels that were added after the initial upload of this source).</p>\n");

            $x = new channelIterator( $shortenSource = true );
            $x->init2( "SELECT name, provider, source, frequency, parameter, symbolrate, vpid, apid, tpid, caid, sid, nid, tid, x_timestamp_added FROM channels WHERE source = ".$this->db->quote($this->source)." AND x_timestamp_added > " . $this->db->quote($timestamp) . " ORDER BY x_timestamp_added DESC, name DESC LIMIT 25");
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
        $page->appendToBody($html_table);
        $this->addToOverviewAndSave( "New channels", $this->craftedPath . "latest_channel_additions.html", $page->getContents() );
    }

    private function renderGroupingHints(){
        $pagetitle = "Grouping hints for ".$this->source;
        $page = new HTMLPage($this->relPath);
        $page->setPageTitle($pagetitle);
        $page->appendToBody(
            $this->getSectionTabmenu("").
            '<h1>'.htmlspecialchars( $pagetitle ).'</h1>
            <p>Last updated on: '. date("D M j G:i:s T Y").'</p>'
        );
        $html_table = "<table><tr><th>Provider</th><th>Number of related channels</th></tr>\n";
        $nice_html_body = "";
        $result = $this->db->query(
            "SELECT provider, COUNT(*) AS providercount FROM channels ".
            "WHERE source = ".$this->db->quote($this->source).
            " GROUP BY provider ORDER by providercount DESC"
        );
        foreach ($result as $row) {
            $html_table .= "<tr><td>".htmlspecialchars($row["provider"])."</td><td>".htmlspecialchars($row["providercount"])."</td></tr>\n";
/*            $nice_html_body .= "<h2>".htmlspecialchars($row["provider"]). " (" . htmlspecialchars($row["providercount"]) ." channels)</h2>\n<pre>\n";
            $x = new channelIterator( $shortenSource = true );
            $x->init2( "SELECT * FROM channels ".
                "WHERE source = ".$this->db->quote($this->source)." AND ".
                "provider = ".$this->db->quote($row["provider"]).
                " ORDER by x_label ASC, lower(name) ASC, source ASC");
            while ($x->moveToNextChannel() !== false){
                $nice_html_body .= htmlspecialchars( $x->getCurrentChannelObject()->getChannelString())."\n";
            }
            $nice_html_body .= "</pre>";*/
        }

        $html_table .= "</table>\n";
        $page->appendToBody( $html_table . $nice_html_body );
        $this->addToOverviewAndSave( "Grouping hints", $this->craftedPath . "grouping_hints.html", $page->getContents());
    }

    private function renderTransponderList(){
        $pagetitle = htmlspecialchars($this->source) . " - Transponder list";
        $page = new HTMLPage($this->relPath);
        $page->setPageTitle($pagetitle);
        $page->appendToBody(
            $this->getSectionTabmenu("").
            '<h1>'.htmlspecialchars( $pagetitle ).'</h1>
            <p>Last updated on: '. date("D M j G:i:s T Y").'</p>'
        );
        $result = $this->db->query(
            "SELECT parameter, frequency, symbolrate, nid
            FROM channels
            WHERE source = ".$this->db->quote($this->source)."
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
        $page->appendToBody($html_table);
        $this->addToOverviewAndSave( "Transponders", $this->craftedPath . "transponder_list.html", $page->getContents() );
    }

    private function renderTransponderNIDCheck(){
        $pagetitle = htmlspecialchars($this->source) . " - Transponder plausibility check";
        $page = new HTMLPage($this->relPath);
        $page->setPageTitle($pagetitle);
        $page->appendToBody(
            $this->getSectionTabmenu("").
            '<h1>'.htmlspecialchars( $pagetitle ).'</h1>
            <p>Last updated on: '. date("D M j G:i:s T Y").'</p>
            <p>This page only has content if the channel list of this source is faulty. This means that some channel data is wrong or outdated. There should only be one NID per transponder.</p>'
        );
        $nice_html_output = "";

        $result = $this->db->query(
            "SELECT channels1.frequency as fre, channels1.parameter as mod, channels1.symbolrate as sym, channels1.nid, channels2.nid
            FROM channels AS channels1
            LEFT JOIN channels AS channels2 WHERE
            channels1.source = ".$this->db->quote($this->source)." AND
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
                "source     = " . $this->db->quote( $this->source ) . " AND ".
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
            $page->appendToBody( $nice_html_output );
        }
        $this->addToOverviewAndSave( "NID check", $this->craftedPath . "transponder_nid_check.html", $page->getContents() );
    }

    private function renderLNBSetupHelperTable(){
        $pagetitle = "LNB Setup helper table for satellite position $this->source";
        $page = new HTMLPage($this->relPath);
        $page->setPageTitle($pagetitle);
        $page->appendToBody(
            $this->getSectionTabmenu("").
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
            $this->addChannelSection( "H", "High", "TV", false ).
            $this->addChannelSection( "H", "High", "TV", true ).
            $this->addChannelSection( "H", "High", "Radio", false ).
            $this->addChannelSection( "V", "High", "TV", false ).
            $this->addChannelSection( "V", "High", "TV", true ).
            $this->addChannelSection( "V", "High", "Radio", false ).
            $this->addChannelSection( "H", "Low", "TV", false ).
            $this->addChannelSection( "H", "Low", "TV", true ).
            $this->addChannelSection( "H", "Low", "Radio", false ).
            $this->addChannelSection( "V", "Low", "TV", false ).
            $this->addChannelSection( "V", "Low", "TV", true ).
            $this->addChannelSection( "V", "Low", "Radio", false ).
            "\n<b>:End of list. The following channels were added by VDR automatically</b>\n".
            "</pre>\n"
        );
        $this->addToOverviewAndSave( "LNB setup help", $this->craftedPath . "LNBSetupHelperTable.html", $page->getContents() );
    }

    private function addChannelSection( $direction, $band, $type, $encrypted = false ){
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
            "\n<b>:".($encrypted?"Scrambled":"FTA"). " " .$type." channels on " . $direction_long . " ".$band." Band ".htmlspecialchars($this->source)."</b>\n\n".
            $this->addCustomChannelList( "
                SELECT * FROM channels WHERE source = ".$this->db->quote($this->source)."
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
            $list .= $this->HTMLFragments->getFlagIcon($labelparts[0], $this->relPath).
                //lock icon taken from http://www.openwebgraphics.com/resources/data/1629/lock.png
                (($ch->getCAID() !== "0")? '<img src="'.$this->relPath.'../res/icons/lock.png" class="lock_icon" title="'.htmlspecialchars($ch->getCAID()).'" />':'');
            $list .= htmlspecialchars( $ch->getChannelString() )."\n";
        }
        return $list;
    }
}
?>