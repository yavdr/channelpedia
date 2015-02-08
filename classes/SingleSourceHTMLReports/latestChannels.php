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
class latestChannels extends singleSourceHTMLReportBase{

    public function popuplatePageBody(){
        $this->setPageTitle( "Latest DVB services added to ".$this->parent->getSource() );
        $this->setDescription(
            "A listing of new DVB services on " . $this->parent->getPureSource() . " that were recently found. ".
            "If an existing service changes its name but its unique channel parameters (SID,NID,TID) stay the same, it won't show up in this list."
        );
        $this->addBodyHeader();
        $html_table = "";
        $x = new latestChannelsIterator();
        if ( $x->notEmptyForSource( $this->parent->getSource() )){
            while ($x->moveToNextChannel() !== false){
                $currChan = $x->getCurrentChannelObject();
                $this->appendToBody(
                    "<p><b>". $this->getRegionFlagIcon( $currChan->getXLabelRegion() ) . $currChan->getName() . "</b> ".
                    "(added on " . date("D, d M Y H:i:s", $currChan->getXTimestampAdded()) . ")</p>".
                    "<pre>". $currChan->getChannelString() ."</pre>"
                );
            }
        }
        $this->addToOverviewAndSave( "New DVB services", "latest_channel_additions.html" );
        //$this->addToOverviewAndSave( "New DVB services", "latest_dvb_service_additions.html" );
    }

    private function getRegionFlagIcon( $region ) {
        if ($region !== "")
            $region = $this->pageFragments->getFlagIcon( $region, $this->parent->getRelPath() );
        return $region;
    }

}
?>