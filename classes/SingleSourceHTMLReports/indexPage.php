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
class indexPage extends singleSourceHTMLReportBase{

    private $linklist;

    function __construct( HTMLOutputRenderSource & $obj, $linklist ){
        $this->linklist = $linklist;
        parent::__construct( $obj );
    }

    public function popuplatePageBody(){
        $descr =  ($this->parent->getType() === "S" ) ?
            $this->parent->getPureSource() . " - " . $this->config->getLongNameOfSatSource( $this->parent->getSource()) :
            $this->parent->getPureSource() . " (" . $this->parent->getVisibleType()  . ")";
        $this->setPageTitle( "Overview: " . $descr );
        $this->setDescription("All regional sections, reports and downloads for ". $this->parent->getPureSource() . " in a nutshell.");
        $this->addBodyHeader( "overview" );
        //$userconfig = $this->config->getUploadUserConfigBySource( $this->parent->getType(), $this->parent->getPureSource());
        //$this->appendToBody('<p> This source is being provided by: ' . $userconfig["visibleName"] . '</p>'."\n");
        $this->appendToBody('<ul class="singleSourceMainMenu">'."\n");
        foreach ($this->linklist as $linkarray){
            $description = ( $linkarray[2] !== "" ? ': <span>'.$linkarray[2] .'</span>' : '');
            $this->appendToBody('<li><a href="'.$linkarray[1].'">'.$linkarray[0].'</a>' . $description . '</li>'."\n");
        }
        $this->appendToBody("</ul>");
        $this->addToOverviewAndSave( $this->parent->getPureSource(), "index.html");
    }
}
?>