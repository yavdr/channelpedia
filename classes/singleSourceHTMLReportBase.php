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

class singleSourceHTMLReportBase extends HTMLReportBase{

    function __construct( HTMLOutputRenderSource $obj ){
        $this->parent = & $obj;
        parent::__construct( $this->parent->getRelPath() );
    }

    protected function addBodyHeader( $language = ""){
        $this->appendToBody( $this->getSectionTabmenu( $language ) );
        parent::addBodyHeader();
    }

    private  function getSectionTabmenu($language){
        $class = "";
        $sourceMenuItem = $this->parent->getVisibleType() . ": " . $this->parent->getPureSource();
        if ("overview" == $language){
            $language = "";
            $tabmenu = $this->getMenuItem( $sourceMenuItem, "index.html", "active", false );
        }
        else if ("" == $language){
            $language = "";
            $tabmenu = $this->getMenuItem( $sourceMenuItem, "index.html", "", false );
        }
        else{
            $tabmenu = $this->getMenuItem( $sourceMenuItem, "../index.html", "", false );
        }
        foreach ($this->parent->getLanguages() as $language_temp){
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

    private function getMenuItem( $link, $filename, $class = "", $showflagicon = false){
        $class = ($class === "") ? "" : ' class="'.$class.'"';
        $path = $this->config->getValue("exportfolder") . substr( $filename, 0, strrpos ( $filename , "/" ) );
        $this->config->addToDebugLog( "HTMLOutputRenderer/getMenuItem: file '".$filename."', link: '$link'\n" );
        return '<li'.$class.'><a href="'.$this->pageFragments->getCrispFilename($filename).'">'.
            ($showflagicon ? $this->pageFragments->getFlagIcon($link, $this->parent->getRelPath()) : "") . $link .'</a></li>'."\n";
    }
}
?>