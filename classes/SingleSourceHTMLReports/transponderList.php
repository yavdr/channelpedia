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
        //    AND ( substr(x_label,1,6) = 'sky_de' OR substr(x_label,1,2) = 'de')
        $colspan = 6;
        $tchHeader = "";//<tr><th>Name</th><th>Provider</th><th>SID</th><th>Added on</th><th>Last changed</th></tr>";
        //<th>Last confirmed</th> <th>VideoPID</th><th>AudioPID</th>
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
        $countByBand = array();
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
                $flag = $this->getFlagIcon( $ch );
                if ( !in_array( $flag, $flaglist))
                    $flaglist[] = $flag;
                $scramblestate = $this->pageFragments->getScrambledIcon( $ch->getCAID(), $this->parent->getRelPath() );
                if ( $scramblestate == "") $scramblestate = "FTA";
                $channelNameSegments = explode(',', $ch->getName());
                $chname = htmlspecialchars( count($channelNameSegments) > 0 ? $channelNameSegments[0] : $ch->getName() );
                $channels[] =
                    '<div class="' . ( $scramblestate == "FTA" ? "fta":"scrambled").'"><div title="'.$this->getPopupContent( $ch ).'" class="single_channel">'.
                    $this->getChannelMetaInfo( $ch, $chname ).
                    "</div></div>";
            }
            $tp = new transponderParameters();
            $tp->importData( $row["frequency"], $row["parameter"], $row["symbolrate"]);
            if ( $tp->isSatelliteSource()){
                $band = $tp->belongsToSatHighBand() ? "High-Band" : "Low-Band";
                $polarisation = $tp->belongsToSatVertical() ? "Vertical" : "Horizontal";
                if ( $lastband !== $band ){
                    $this->appendToBody( '<h1>'.$band. ' ' . $polarisation . '</h2>');
                    $countByBand[ $band. ' ' . $polarisation ] = 0;
                }
                $countByBand[ $band. ' ' . $polarisation ] ++;
                $this->appendToBody(
                    '<a name="wrapper"><h2 class="transponder">' .
                        //htmlspecialchars(
                            $tp->getReadableFrequency() . ' ' . $polarisation . ' (' . $band. ')'.
                            " | DVB-S". ( $tp->onS2SatTransponder() ? "2":"").
                            ' | RollOff ' . $tp->getRollOff().
                            '</h2><div class="wikipedia_data"><p>Modulation ' . $tp->getModulation() .
                            ' | Symbolrate ' . $row["symbolrate"] .
                            ' | FEC ' . $tp->getFECOfSatTransponder() .
                            ' | NID ' . $row["nid"] .
                            ' | TID ' . $row["tid"].
                        //).
                    '</p>'.
                    ''
                    /*.
                    '<table>' . $theader .
                    "<td><b>".htmlspecialchars( $tp->getReadableFrequency()) . "</b> (" . $band ." ". $polarisation .")</td>".
                    "<td>".htmlspecialchars( $tp->getModulation() )."</td>".
                    "<td>".htmlspecialchars( $tp->getFECOfSatTransponder() )."</td>".
                    "<td>DVB-S". ( $tp->onS2SatTransponder() ? "2":"")."</td>".
                    "<td>".htmlspecialchars( $tp->getRollOff() )."</td>"
                    */
                );
                $lastband = $band;
            }
            else{
                $this->appendToBody(
                    '<a name="wrapper"><h2 class="transponder">' .
                        htmlspecialchars(
                            $tp->getReadableFrequency() .
                            ' | Modulation ' . $tp->getModulation() .
                            ' | Raw Params ' . $row["parameter"] .
                            ' | Symbolrate ' . $row["symbolrate"] .
                            ' | NID ' . $row["nid"] .
                            ' | TID ' . $row["tid"]
                        ).
                    '</h2>'.
                    '<div class="wikipedia_data">'
                /*$this->appendToBody(
                    '<div class="wikipedia_data"><table>' . $theader . "<tr>".
                    "<td><b>".htmlspecialchars( $tp->getReadableFrequency() )."</b></td>".
                    "<td>".htmlspecialchars($row["parameter"])."</td>"
                    */
                );
            }
            $providerlist = implode (", ", $providerlist);
            $flaglist = implode (" ", $flaglist);
            /*
            $this->appendToBody(
                "<td>".htmlspecialchars($row["symbolrate"])."</td>".
                "<td>".htmlspecialchars($providerlist) . ' ' . $flaglist."</td>".
                "<td>".htmlspecialchars($row["nid"])."</td>".
                "<td>".htmlspecialchars($row["tid"])."</td>".
            "</tr>\n");
*/
            $this->appendToBody(
                //'</table>'.
                implode("", $channels).
                '<br clear="all"></div></a><br/>'
            );
        }
        $this->appendToBody( "<p>Total number of transponders found: " . $count . "<p>");
        foreach ( $countByBand as $label => $number ){
            $this->appendToBody( "<p>Number of transponders found on $label: " . $number . "<p>");
        }

        $this->addToOverviewAndSave( "Transponders", "transponder_list.html");
    }
}

?>