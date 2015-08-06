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
class satBandHelper extends singleSourceHTMLReportBase{

    public function popuplatePageBody(){
        $this->setPageTitle( "LNB Setup helper table for satellite position " .$this->parent->getSource() );
        $this->setDescription("Clarifies what band of satellite position " . $this->parent->getPureSource() . " provides which channels. Lists all TV and FTA radio channels grouped by the four different sat bands.");

        $this->addBodyHeader();
        $this->appendToBody(
            "<p>A channel list specifically grouped by sat bands might be helpful when testing a complex DiSEqC setup, ".
                "a new LNB/Multiswitch or when evaluating VDR with LNB sharing feature enabled. ".
                "Basically, if your setup is flawless you should be able to receive something on any of the four sat bands ".
                //"(as long as there are FTA channels available on each band). ".
                //"Encrypted channels are excluded from the tables below to reduce the amount of data.".
                "</p>
            <ul>
               <li>Horizontal High Band (11700 MHz to 12750 MHz)</li>
               <li>Vertical High Band (11700 MHz to 12750 MHz)</li>
               <li>Horizontal Low Band (10700 MHz to 11700 MHz)</li>
               <li>Vertical Low Band (10700 Mhz to 11700 MHz)</li>
                </ul>
                <pre>".
            $this->addChannelSectionPerBand( "H", "High", "TV",    false ).
            $this->addChannelSectionPerBand( "H", "High", "TV",    true ).
            $this->addChannelSectionPerBand( "H", "High", "Radio", false ).
            $this->addChannelSectionPerBand( "V", "High", "TV",    false ).
            $this->addChannelSectionPerBand( "V", "High", "TV",    true ).
            $this->addChannelSectionPerBand( "V", "High", "Radio", false ).
            $this->addChannelSectionPerBand( "H", "Low",  "TV",    false ).
            $this->addChannelSectionPerBand( "H", "Low",  "TV",    true ).
            $this->addChannelSectionPerBand( "H", "Low",  "Radio", false ).
            $this->addChannelSectionPerBand( "V", "Low",  "TV",    false ).
            $this->addChannelSectionPerBand( "V", "Low",  "TV",    true ).
            $this->addChannelSectionPerBand( "V", "Low",  "Radio", false ).
            "\n<b>:End of list. The following channels were added by VDR automatically</b>\n".
            "</pre>\n"
        );
        $this->addToOverviewAndSave( "LNB setup help", "LNBSetupHelperTable.html");
    }

    private function addChannelSectionPerBand( $direction, $band, $type, $encrypted = false ){
        if ($direction == "H")
            $direction_long = "Horizontal";
        else if ($direction == "V")
            $direction_long = "Vertical";
        else
            throw new Exception("direction should either be H or V");
        if ($band == "Low"){
            $lowfreq = 10700;
            $hifreq = 11700;
        }
        else if ($band == "High"){
            $lowfreq = 11700;
            $hifreq = 12750;
        }
        else
            throw new Exception("band should either be High or Low");

        $customwhere = "
                AND frequency >= ".$lowfreq."
                AND frequency <= ".$hifreq."
                AND substr(parameter,1,1) = '".$direction."'
        ";
        $description = "\n<b>:".($encrypted?"Scrambled":"FTA"). " " .$type." channels on " . $direction_long . " ".$band." Band ".htmlspecialchars($this->parent->getSource())."</b>\n\n";
        return $this->getChannelSection( $type, $encrypted, $customwhere, $description );
    }

    private function getChannelSection( $type, $encrypted = false, $customwhere, $description ){
        return
            $description.
            $this->addCustomChannelList( $this->getCustomChannelListSQL( $type, $encrypted, $customwhere, "*" ) );
    }

    private function addCustomChannelList( $statement ){
        $list = "";
        $x = new channelIterator( $shortenSource = true );
        $x->init2( $statement );
        $transp = "";
        $oldtransp = "";
        while ($x->moveToNextChannel() !== false){
            $ch = $x->getCurrentChannelObject();
            $transp = $ch->getFrequency();
            if( $oldtransp != $transp ){
                $list .= "<p><b>Transponder @ " . $transp . " MHz</b></p>";
            }
            $labelparts = explode(".", $ch->getXLabel());
            $list .=
                $this->pageFragments->getFlagIcon($labelparts[0], $this->parent->getRelPath()).
                $this->pageFragments->getScrambledIcon( $ch->getCAID(), $this->parent->getRelPath() ).
                htmlspecialchars( $ch->getChannelString() )."\n";
            $oldtransp = $transp;
        }
        return $list;
    }

}
?>