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
class globalChangelog extends globalHTMLReportBase{

    public function popuplatePageBody(){
        $title = 'Changelog for all sources';
        $this->setPageTitle( $title );
        $this->setKeywords("changelog, changes");
        $this->setDescription("List of the most recent channel attribute changes within all regularly uploaded DVB sources. Some lesser important changes are filtered here. Those changes are still visible in the individual changelog of the corresponding DVB source.");
        $this->addBodyHeader();
        $changelog = new HTMLChangelog( array(), " LIMIT 100", 1);
        $this->appendToBody( $changelog->getContents());
        $this->addToOverviewAndSave( $title, "changelog.html" );
    }
}
?>