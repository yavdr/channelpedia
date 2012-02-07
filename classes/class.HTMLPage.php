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

class HTMLPage {

    private
        $relPath = "",
        $pageBody = "",
        $pagetitle = "undefined pagetitle",
        $pageFragments;


    function __construct( $relPath ){
        $this->relPath = $relPath;
        $this->pageFragments = HTMLFragments::getInstance();
    }

    public function setPageTitle( $title ){
        $this->pagetitle = $title;
    }

    public function appendToBody( $content ){
        $this->pageBody .= $content;
    }

    public function getContents(){
        return $this->getHTMLHeader(). $this->pageBody . $this->getHTMLFooter();
    }

    private function getHTMLHeader(){
        return $this->pageFragments->getHTMLHeader($this->pagetitle, $this->relPath);
    }

    private function getHTMLFooter(){
        return $this->pageFragments->getHTMLFooter();
    }
}

?>