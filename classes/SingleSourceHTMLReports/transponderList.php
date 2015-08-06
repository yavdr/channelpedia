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
class transponderList extends singleSourceHTMLReportBase{

    public function popuplatePageBody(){
        $this->setPageTitle( "Transponder list of ".$this->parent->getSource() );
        $this->setDescription("A list of all transponder frequencies found for  DVB source ". $this->parent->getPureSource());
        $this->addBodyHeader();

        $frequency = ($this->parent->getVisibleType() === "DVB-S") ? " substr(parameter,1,1) || ' ' || frequency AS frequency2 " : "frequency AS frequency2";

        $result = $this->db->query(
            "SELECT parameter, ".$frequency.", symbolrate, nid, tid, frequency
            FROM channels
            WHERE source = ".$this->db->quote($this->parent->getSource())."
            GROUP BY parameter, frequency2, nid, tid
            ORDER BY frequency2, parameter, nid, tid"
        );
        //    AND caid = '0'
        //    AND substr(x_label,1,2) = 'de'
        $this->appendToBody( "<table>");
        $colspan = 6;
        $tchHeader = "<tr><th>Region</th><th>FTA/Enc</th><th>Type</th><th>Name</th><th>SID</th><th>Added on</th><th>Last changed</th><th>Last confirmed</th></tr>";
        $theader = "<tr>";
        if ( $this->parent->getVisibleType() === "DVB-S"){
            $colspan += 3;
            $theader .=  "<th>Frequency</th><th>Modulation</th><th>FEC</th><th>Type</th><th>RollOff</th>";
        }
        else
            $theader .=  "<th>Frequency</th><th>Raw Parameters</th>";
        $theader .= "<th>Symbolrate</th><th>Provider</th><th>NID</th><th>TID</th></tr>\n";
        $lastband = "";
        $band = "";
        $count = 0;
        foreach ($result as $row) {
            $count ++;
            //Collect data of individual channels
            $statement = "SELECT * FROM channels WHERE source = ".$this->db->quote($this->parent->getSource()).
                         " AND frequency = ".$this->db->quote($row["frequency"]).
                         " AND parameter = ".$this->db->quote($row["parameter"]).
                         " AND nid = ".$this->db->quote($row["nid"]).
                         " AND tid = ".$this->db->quote($row["tid"]).
                         " ORDER BY sid";
            $x = new channelIterator( $shortenSource = true );
            $x->init2( $statement );
            $channels = array();
            $providerlist = array();
            $flaglist = array();
            while ($x->moveToNextChannel() !== false){
                $ch = $x->getCurrentChannelObject();
                if ( !in_array( $ch->getProvider(), $providerlist))
                    $providerlist[] = $ch->getProvider();
                $labelparts = explode(".", $ch->getXLabel());
                $flag = $this->pageFragments->getFlagIcon($labelparts[0], $this->parent->getRelPath());
                if ( !in_array( $flag, $flaglist))
                    $flaglist[] = $flag;
                $scramblestate = $this->pageFragments->getScrambledIcon( $ch->getCAID(), $this->parent->getRelPath() );
                if ( $scramblestate == "") $scramblestate = "FTA";
                $channels[] =
                    "<td>".$flag."</td>".
                    "<td>".$scramblestate ."</td>".
                    "<td>".$ch->getReadableServiceType()."</td>".
                    "<td>".$ch->getName(). "</td>".
                    "<td>". $ch->getSID(). "</td>".
                    "<td>" . date("D, d M Y H:i:s", $ch->getXTimestampAdded() ) . "</td>".
                    "<td>" . date("D, d M Y H:i:s", $ch->getXLastChanged() ) . "</td>".
                    "<td>" . date("D, d M Y H:i:s", $ch->getXLastConfirmed() ) . "</td>";
            }
            $tp = new transponderParameters();
            $tp->importData( $row["frequency"], $row["parameter"], $row["symbolrate"]);
            if ( $tp->isSatelliteSource()){
                $band = $tp->belongsToSatHighBand() ? "High-Band" : "Low-Band";
                $polarisation = $tp->belongsToSatVertical() ? "Vertical" : "Horizontal";
                if ( $lastband !== $band )
                    $this->appendToBody( '<tr><td colspan="'.$colspan.'"><h2>'.$band. ' ' . $polarisation . '</h2></td></tr>');
                $this->appendToBody(
                    $theader .
                    "<td><b>".htmlspecialchars( $tp->getReadableFrequency()) . "</b> (" . $band ." ". $polarisation .")</td>".
                    "<td>".htmlspecialchars( $tp->getModulation() )."</td>".
                    "<td>".htmlspecialchars( $tp->getFECOfSatTransponder() )."</td>".
                    "<td>DVB-S". ( $tp->onS2SatTransponder() ? "2":"")."</td>".
                    "<td>".htmlspecialchars( $tp->getRollOff() )."</td>"
                );
                $lastband = $band;
            }
            else{
                $this->appendToBody(
                    $theader . "<tr>".
                    "<td><b>".htmlspecialchars( $tp->getReadableFrequency() )."</b></td>".
                    "<td>".htmlspecialchars($row["parameter"])."</td>"
                );
            }
            $providerlist = implode (", ", $providerlist);
            $flaglist = implode (" ", $flaglist);
            $this->appendToBody(
                "<td>".htmlspecialchars($row["symbolrate"])."</td>".
                "<td>".htmlspecialchars($providerlist) . ' ' . $flaglist."</td>".
                "<td>".htmlspecialchars($row["nid"])."</td>".
                "<td>".htmlspecialchars($row["tid"])."</td>".
            "</tr>\n");

            $this->appendToBody( '<tr><td style="background-color: #bbbbbb;" colspan="'.$colspan.'"><br/>'.
                                  '<table style="font-size: 1em;">'.$tchHeader.'<tr>'. implode("</tr><tr>", $channels) . "</tr></table><br/>");
            $this->appendToBody( '</td></tr>');
        }
        $this->appendToBody("</table>\n");
        $this->appendToBody( "<p>Number of transponders found: " . $count . "<p>");
        $this->addToOverviewAndSave( "Transponders", "transponder_list.html");
    }
}

?>