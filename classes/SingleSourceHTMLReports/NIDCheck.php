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
class NIDCheck extends singleSourceHTMLReportBase{

    public function popuplatePageBody(){
        $this->setPageTitle( "Transponder sanity check for " .$this->parent->getSource() );
        $this->setDescription('Report that helps to discover possibly faulty channel lists. It shows channels that share the same transponder but have different NID values. If no such channels were found, this report remains empty. There should only be one NID per transponder.');
        $this->addBodyHeader();
        $result = $this->db->query(
            "SELECT channels1.frequency as fre, channels1.parameter as mod, channels1.symbolrate as sym, channels1.nid, channels2.nid
            FROM channels AS channels1
            LEFT JOIN channels AS channels2 WHERE
            channels1.source = ".$this->db->quote($this->parent->getSource())." AND
            channels1.source = channels2.source AND
            channels1.frequency = channels2.frequency AND
            channels1.symbolrate = channels2.symbolrate AND
            channels1.parameter = channels2.parameter AND
            channels1.nid != channels2.nid
            GROUP BY channels1.source, channels1.frequency, channels1.parameter, channels1.symbolrate"
        );
        foreach ($result as $row) {
            $this->appendToBody("<h2>" .htmlspecialchars(
                    $row["fre"]. " " .
                    $row["mod"]. " " .
                    $row["sym"]).
                    "</h2>");
            $x = new channelIterator( $shortenSource = true );
            $x->init2( "SELECT * FROM channels WHERE ".
                "source     = " . $this->db->quote( $this->parent->getSource() ) . " AND ".
                "frequency  = " . $this->db->quote( $row["fre" ] ) . " AND " .
                "parameter = " . $this->db->quote( $row["mod"] ) . " AND " .
                "symbolrate = " . $this->db->quote( $row["sym"] )
            );
            $lastname = "";
            while ($x->moveToNextChannel() !== false){
                $carray = $x->getCurrentChannelObject()->getAsArray();
                if ($lastname == ""){
                    $this->appendToBody("<h3>Table view</h3>\n<div class=\"tablecontainer\"><table>\n<tr>");
                    foreach ($x->getCurrentChannelArrayKeys() as $header){
                        $this->appendToBody('<th class="'.htmlspecialchars($header).'">'.htmlspecialchars(ucfirst($header))."</th>\n");
                    }
                    $this->appendToBody("</tr>\n");
                }
                $this->appendToBody("<tr>\n");
                foreach ($carray as $param => $value){
                    if ($param == "apid" || $param == "caid"){
                        $value = str_replace ( array(",",";"), ",<br/>", htmlspecialchars( $value ));
                    }
                    elseif ($param == "x_last_changed"){
                        $value = date("D, d M Y H:i:s", $value);
                    }
                    else
                        $value = htmlspecialchars($value);
                    $this->appendToBody('<td class="'.htmlspecialchars($param).'">'.$value."</td>\n");
                }
                $this->appendToBody("</tr>\n");
                $lastname = $carray["name"];
            }
            $this->appendToBody("</table></div>\n");
        }
        $this->addToOverviewAndSave( "NID check", "nid_validity_check.html");
    }
}
?>