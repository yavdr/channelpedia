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

class HTMLReportBase extends HTMLPage{

    protected
        $parent = null,
        $config,
        $parentPageLink = null,
        $db;

    function __construct( $relPath ){
        $this->db = dbConnection::getInstance();
        $this->config = config::getInstance();
        parent::__construct( $relPath );
    }

    protected function addBodyHeader(){
        $this->appendToBody(
            '<h1>'.htmlspecialchars( $this->pageTitle )."</h1>\n".
            $this->getLastUpdated()
        );
    }

    private function getLastUpdated(){
        return '<p>Last updated on: '. date("D M j G:i:s T Y")."</p>\n";
    }

    public function getParentPageLink(){
        if ($this->parentPageLink === null)
            throw new Exception("getParentPageLink is empty. It must be called after addToOverviewAndSave.\n");
        return $this->parentPageLink;
    }

    protected function addToOverviewAndSave( $menuLabel, $filename ){
        $this->config->save( $this->parent->getCraftedPath() . $filename, $this->getContents());
        $this->parentPageLink = array( $menuLabel, $this->parent->getRelPath() . $this->pageFragments->getCrispFilename( $this->parent->getCraftedPath() . $filename));
    }
}
?>