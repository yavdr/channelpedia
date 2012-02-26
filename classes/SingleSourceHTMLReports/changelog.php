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
class changelog extends singleSourceHTMLReportBase{

    public function popuplatePageBody(){
        $this->setPageTitle( 'Changelog for '.$this->parent->getSource() );
        $this->setDescription("List of the most recent channel attribute changes on DVB source " . $this->parent->getPureSource() . ".");
        $this->addBodyHeader();
        $where = array(
            "timestamp >= " . $this->db->quote( $this->parent->getLastConfirmedTimestamp() - 60*60*24*2 ), //last confirmed + the 2 previous days
            "combined_id LIKE ".$this->db->quote( $this->parent->getSource()."%" ) . " "
        );
        $changelog = new HTMLChangelog( $where, " LIMIT 100", 1);
        $this->appendToBody( $changelog->getContents() );
        $this->addToOverviewAndSave('Changelog', "changelog.html");
    }
}
?>