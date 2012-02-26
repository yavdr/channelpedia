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

define( 'PATH_TO_REPORT_CLASSES', dirname(__FILE__) );
require_once PATH_TO_REPORT_CLASSES . '/HTMLReportBase.php';
require_once PATH_TO_REPORT_CLASSES . '/singleSourceHTMLReportBase.php';
require_once PATH_TO_REPORT_CLASSES . '/SingleSourceHTMLReports/indexPage.php';
require_once PATH_TO_REPORT_CLASSES . '/SingleSourceHTMLReports/latestChannels.php';
require_once PATH_TO_REPORT_CLASSES . '/SingleSourceHTMLReports/satBandHelper.php';
require_once PATH_TO_REPORT_CLASSES . '/SingleSourceHTMLReports/transponderList.php';
require_once PATH_TO_REPORT_CLASSES . '/SingleSourceHTMLReports/changelog.php';
require_once PATH_TO_REPORT_CLASSES . '/SingleSourceHTMLReports/languageSection.php';
require_once PATH_TO_REPORT_CLASSES . '/SingleSourceHTMLReports/NIDCheck.php';
require_once PATH_TO_REPORT_CLASSES . '/SingleSourceHTMLReports/outdatedChannels.php';

class HTMLOutputRenderSource {

    private
        $db,
        $getLastConfirmedTimestamp = 0,
        $config,
        $source_linklist = array(),
        $craftedPath = "",
        $visibletype = "",
        $puresource = "",
        $languages = array(),
        $HTMLFragments,
        $source = "",
        $relPath = "",
        $type = "";

    public function __construct( $type, $puresource, $languages ){
        $this->HTMLFragments = HTMLFragments::getInstance();
        $this->db = dbConnection::getInstance();
        $this->config = config::getInstance();
        $this->relPath = "";
        $this->visibletype = ($type == "A") ? "ATSC" : "DVB-". $type;
        $this->puresource = $puresource;
        $this->languages = $languages;
        $this->source = ($type !== "S") ? $type . "[" . $puresource . "]" : $puresource;
        $this->getLastConfirmedTimestamp = dbVariousTools::getInstance()->getLastConfirmedTimestamp( $this->source );
        $this->source_linklist = array();
        $this->type = $type;
    }

    public function render(){
        foreach ($this->languages as $language){
            $this->setCraftedPath( '/'.$language );
            $x = new languageSection($this, $language);
            $x->popuplatePageBody();
        }
        $this->setCraftedPath();

        $x = new latestChannels($this);
        $x->popuplatePageBody();
        $this->source_linklist[] = $x->getParentPageLink();

        $x = new changelog($this);
        $x->popuplatePageBody();
        $this->source_linklist[] = $x->getParentPageLink();

        $x = new outdatedChannels($this);
        $x->popuplatePageBody();
        $this->source_linklist[] = $x->getParentPageLink();

        $x = new NIDCheck($this);
        $x->popuplatePageBody();
        $this->source_linklist[] = $x->getParentPageLink();

        if ($this->type === "S"){
            $x = new satBandHelper( $this );
            $x->popuplatePageBody();
            $this->source_linklist[] = $x->getParentPageLink();
        }

        $x = new transponderList($this);
        $x->popuplatePageBody();
        $this->source_linklist[] = $x->getParentPageLink();

        //now add download links
        $this->addCompleteListLink();
        if (in_array("de", $this->languages)){
            $this->addEPGChannelmapLink();
        }
        $this->setCraftedPath();

        $x = new indexPage($this, $this->source_linklist);
        $x->popuplatePageBody();
        return array( $this->puresource, "./" . HTMLFragments::getCrispFilename( $this->getCraftedPath() . "index.html"));

    }

    public function getVisibleType(){
        return $this->visibletype;
    }

    public function getCraftedPath(){
        return $this->craftedPath;
    }

    public function getRelPath(){
        return $this->relPath;
    }

    public function getLanguages(){
        return $this->languages;
    }

    public function getSource(){
        return $this->source;
    }

    public function getPureSource(){
        return $this->puresource;
    }

    public function getLastConfirmedTimestamp(){
        return $this->getLastConfirmedTimestamp;
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

    private function addCompleteListLink(){
        $this->source_linklist[] = array(
            "Download channels.conf",
            $this->source."_complete_sorted_by_groups.channels.conf",
            "A complete and ready-to-use channel file with group delimiters, grouped by the regional sections and channel groups configured for this DVB source. The regional sections are sorted in alphabetical order."
        );
        $this->source_linklist[] = array(
            "Download channels.conf",
            $this->source."_complete.channels.conf",
            "A complete and ready-to-use channel file with group delimiters, grouped by transponders and ordered by the frequency and band of the transponder"
        );
    }

    private function addEPGChannelmapLink(){
        $this->source_linklist[] = array("Download epgdata2vdr Channelmap", $this->source.".epgdata2vdr_channelmap.conf", "To be used with VDR plugin epgdata2vdr. Also contains NoEPG configuration string.");
        $this->source_linklist[] = array("Download tvm2vdr Channelmap", $this->source.".tvm2vdr_channelmap.conf", "");
    }

    /*unused
     *
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
    }*/
}
?>