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
            while( $chunk = $x->getNextInfoChunk()){
                $amount = count( $chunk["content"] );
                if ( $amount > 1 )
                    $header = $amount . " new DVB services";
                else
                    $header = "New DVB service";
                $header .= " found on " .  date("D, d M Y H:i:s", $chunk["timestamp"] );
                $names = array();
                $strings = array();
                foreach ( $chunk["content"] as $currChan ){
                    array_push ( $names,
                        $currChan->getName() . " " .
                        $this->getRegionFlagIcon( $currChan->getXLabelRegion() ) .
                        $this->pageFragments->getScrambledIcon( $currChan->getCAID(), $this->parent->getRelPath() )
                    );
                    array_push ( $strings, $currChan->getChannelString() );
                }
                $this->appendToBody(
                    '<a name="'.$chunk["timestamp"].'"><h2>' . $header . "</h2></a>\n" .
                    "<p>" . implode ( "<br/>" , $names ) . "</p>" .
                    "<pre>" . implode ( "\n" , $strings ) . "</pre>"
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