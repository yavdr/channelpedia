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

class HTMLOutputRenderer{

    private
        $db,
        $config,
        $craftedPath = "",
        $homepageLinkList = array(),
        $relPath;

    function __construct(){
        $this->db = dbConnection::getInstance();
        $this->config = config::getInstance();
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
        $x = new HTMLOutputRenderSource( $type, $puresource, $languages );
        $this->setCraftedPath($x->getVisibletype(), $puresource);
        $this->addToOverview( $puresource, HTMLFragments::getInstance()->getCrispFilename( $this->craftedPath."index.html" ));
    }

    private function addDividerTitle( $title ){
        $this->addToOverview( $title, "");
    }

    private function addToOverview( $param, $value ){
        $this->homepageLinkList[] = array( $param, $value);
    }

    private function addLinkToHomepageAndSavePage( $link, $filename, $filecontent ){
        $this->config->save($filename, $filecontent);
        $this->homepageLinkList[] = array( $link, $this->relPath . HTMLFragments::getInstance()->getCrispFilename($filename));
    }

    private function closeHierarchy(){
        $this->homepageLinkList[] = array( "", "close");
    }

    //general changelog for all sources
    public function writeGeneralChangelog(){
        //$this->relPath = "";
        $pagetitle = 'Changelog for all sources';
        $changelog = new HTMLChangelog( array(), $pagetitle, " LIMIT 100", 1, $this->relPath);
        $this->addLinkToHomepageAndSavePage($pagetitle, "changelog.html", $changelog->getContents());
    }

    public function writeUploadLog(){
        $pagetitle = "Upload log";
        $page = new HTMLPage($this->relPath);
        $page->setPageTitle($pagetitle);
        $page->appendToBody(
            "<h1>".htmlspecialchars($pagetitle)."</h1>\n".
            '<p>Last updated on: '. date("D M j G:i:s T Y")."</p>\n".
            "<table><tr><th>Timestamp</th><th>Channels.conf of user</th><th>Source</th><th>Description</th></tr>\n"
        );
        $result = $this->db->query(
            "SELECT DATETIME( timestamp, 'unixepoch', 'localtime' ) AS datestamp, user, description, source ".
            "FROM upload_log ORDER BY timestamp DESC LIMIT 100"
        );
        foreach ($result as $row) {
            $page->appendToBody(
                '<tr><td>'.
                htmlspecialchars( $row["datestamp"] ). "</td><td>".
                htmlspecialchars( substr($row["user"],0,2)."..." ). "</td><td>".
                htmlspecialchars( $row["source"] ). "</td><td>".
                htmlspecialchars( $row["description"] ). "</td>".
                "</tr>\n"
            );
        }
        $page->appendToBody( "<table>\n" );
        $this->addLinkToHomepageAndSavePage($pagetitle, "upload_log.html", $page->getContents() );
    }

    private function renderDEComparison(){
        $pagetitle = "Comparison: Parameters of German public TV channels at different providers";
        $page = new HTMLPage($this->relPath);
        $page->setPageTitle($pagetitle);
        $page->appendToBody(
            '<h1>'.htmlspecialchars( $pagetitle ).'</h1>
            <p>Last updated on: '. date("D M j G:i:s T Y").'</p>'
        );
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
        $page->appendToBody( $html_table );
        $filename = "parameter_comparison_de.html";
        $this->addLinkToHomepageAndSavePage( "Comparison: Parameters of German public TV channels at different providers", $filename, $page->getContents() );
    }

    private function renderIndexPage(){
        $pagetitle = "Overview";
        $page = new HTMLPage($this->relPath);
        $page->setPageTitle($pagetitle);
        $page->appendToBody(
            '<h1>'.htmlspecialchars( $pagetitle ).'</h1>
            <ul>
        ');
        foreach ($this->homepageLinkList as $line){
           $title = $line[0];
           $url = $line[1];
           if($url == "")
               $page->appendToBody( '<li><b>'.htmlspecialchars( $title )."</b></li>\n<ul>");
           elseif($url == "close")
               $page->appendToBody( "<br clear=\"all\" /></ul>\n" );
           else
              $page->appendToBody( '<li><a href="'. urldecode( $url ) .'">'.$title ."</a></li>\n" );
        }

        $page->appendToBody( "<br clear=\"all\" /></ul>\n");
        $this->config->save("index.html", $page->getContents() );
    }
}
?>