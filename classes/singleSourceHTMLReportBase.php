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


class singleSourceHTMLReportBase extends HTMLPage{

    protected
        $config,
        $fullPage = "",
        $relPath = "",
        $source = "",
        $languages = array(),
        $visibletype = "",
        $puresource = "",
        $parentPageLink = null,
        $db;

    function __construct($relPath, $source, $languages){
        $this->relPath = $relPath;
        $this->source = $source;
        $this->db = dbConnection::getInstance();
        $this->config = config::getInstance();
        $this->languages = $languages;
        parent::__construct($relPath);
    }

    protected function renderHTMLPage(){
        $this->appendToBody(
            $this->getSectionTabmenu("").
            '<h1>'.htmlspecialchars( $this->pageTitle ).'</h1>
            <p>Last updated on: '. date("D M j G:i:s T Y").'</p>'
        );
    }

    protected function getHTMLPage(){
        return $this->getContents();
    }

    public function getParentPageLink(){
        return $this->parentPageLink;
    }

    protected function addToOverviewAndSave( $link, $filename, $filecontent ){
        $this->config->save($filename, $filecontent);
        $this->parentPageLink = array( $link, $this->relPath . $this->pageFragments->getCrispFilename($filename));
    }

    protected  function getSectionTabmenu($language){
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

    protected function getMenuItem( $link, $filename, $class = "", $showflagicon = false){
        $class = ($class === "") ? "" : ' class="'.$class.'"';
        $path = $this->config->getValue("exportfolder") . substr( $filename, 0, strrpos ( $filename , "/" ) );
        $this->config->addToDebugLog( "HTMLOutputRenderer/getMenuItem: file '".$filename."', link: '$link'\n" );
        return '<li'.$class.'><a href="'.$this->pageFragments->getCrispFilename($filename).'">'.
            ($showflagicon ? $this->pageFragments->getFlagIcon($link, $this->relPath) : "") . $link .'</a></li>'."\n";
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
}
?>