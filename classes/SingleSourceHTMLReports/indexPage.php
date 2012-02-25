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
        $this->setPageTitle( $this->parent->getSource() . " - Overview");
        $this->addBodyHeader( "overview" );
        $this->appendToBody("<ul>\n");
        foreach ($this->linklist as $linkarray){
            $this->appendToBody('<li><a href="'.$linkarray[1].'">'.$linkarray[0].'</a></li>'."\n");
        }
        $this->appendToBody("</ul>");
        $this->addToOverviewAndSave( $this->parent->getPureSource(), "index.html");
    }
}
?>