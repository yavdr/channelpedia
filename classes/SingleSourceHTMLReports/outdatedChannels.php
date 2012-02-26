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
class outdatedChannels extends singleSourceHTMLReportBase{

    public function popuplatePageBody(){
        $this->setPageTitle( "Outdated channels of " .$this->parent->getSource() );
        $this->setDescription("List of recently eclipsed channels within the portfolio of DVB source " . $this->parent->getPureSource() . ".");
        $this->addBodyHeader();
        $html_table = "";
        $timestamp = intval($this->parent->getLastConfirmedTimestamp());
        if ($timestamp != 0){
            $this->appendToBody( "<p>Looking for channels that were last confirmed before ". date("D, d M Y H:i:s", $timestamp). " ($timestamp)</p>\n");

            $x = new channelIterator( $shortenSource = true );
            $x->tolerateInvalidChannels();
            $x->init2( "SELECT * FROM channels WHERE source = ".$this->db->quote($this->parent->getSource())." AND x_last_confirmed < ".$timestamp);
            $lastname = "";
            while ($x->moveToNextChannel() !== false){
                $carray = $x->getCurrentChannelObject()->getAsArray();
                if ($lastname == ""){
                    $html_table .= "<h3>Table view</h3>\n<div class=\"tablecontainer\"><table>\n<tr>";
                    foreach ($x->getCurrentChannelArrayKeys() as $header){
                        $html_table .= '<th class="'.htmlspecialchars($header).'">'.htmlspecialchars(ucfirst($header))."</th>\n";
                    }
                    $html_table .= "</tr>\n";
                }
                $html_table .= "<tr>\n";
                foreach ($carray as $param => $value){
                    if ($param == "apid" || $param == "caid" || $param == "tpid" ){
                        $value = str_replace ( array(",",";"), ",<br/>", htmlspecialchars($value ));
                    }
                    elseif ($param == "x_last_changed" || $param == "x_timestamp_added" || $param == "x_last_confirmed"){
                        $value = date("D, d M Y H:i:s", $value);
                    }
                    else
                        $value = htmlspecialchars($value);
                    $html_table .= '<td class="'.htmlspecialchars($param).'">'.$value."</td>\n";
                }
                $html_table .= "</tr>\n";
                $lastname = $carray["name"];
            }
        }
        $html_table .= "</table></div>\n";
        $this->appendToBody($html_table);
        $this->addToOverviewAndSave( "Outdated channels", "outdated_channels.html");
    }
}
?>