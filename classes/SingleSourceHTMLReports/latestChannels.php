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

    private function getEarliestChannelAddedTimestamp(){
        $timestamp = 0;
        $sqlquery = "SELECT x_timestamp_added FROM channels WHERE source = ".$this->db->quote($this->parent->getSource())." AND x_timestamp_added > 0 ORDER BY x_timestamp_added ASC LIMIT 1";
        $result = $this->db->query($sqlquery);
        $timestamp_raw = $result->fetchAll();
        if (isset($timestamp_raw[0][0]))
            $timestamp = intval($timestamp_raw[0][0]);
        return $timestamp;
    }

    public function popuplatePageBody(){
        $this->setPageTitle( "Latest channel additions on ".$this->parent->getSource() );
        $this->setDescription("A listing of new channels on " . $this->parent->getPureSource() . " that were recently found. If an existing channel changes its name but its unique channel parameters (SID,NID,TID) stay the same, it won't show up in this list.");
        $this->addBodyHeader();
        $html_table = "";
        $timestamp = intval($this->getEarliestChannelAddedTimestamp());
        if ($timestamp != 0){
            $x = new channelIterator( $shortenSource = true );
            $x->init2( "SELECT name, provider, source, frequency, parameter, symbolrate, vpid, apid, tpid, caid, sid, nid, tid, x_timestamp_added FROM channels WHERE source = ".$this->db->quote($this->parent->getSource())." AND x_timestamp_added > " . $this->db->quote($timestamp) . " ORDER BY x_timestamp_added DESC, name DESC LIMIT 25");
            $lastname = "";
            while ($x->moveToNextChannel() !== false){
                $carray = $x->getCurrentChannelObject()->getAsArray();
                if ($lastname == ""){
                    $this->appendToBody("<h3>Table view</h3>\n<div class=\"tablecontainer\"><table>\n<tr>");
                    foreach ($x->getCurrentChannelArrayKeys() as $header){
                        $this->appendToBody( '<th class="'.htmlspecialchars($header).'">'.htmlspecialchars(ucfirst($header))."</th>\n" );
                    }
                    $this->appendToBody( "</tr>\n" );
                }
                $this->appendToBody( "<tr>\n");
                foreach ($carray as $param => $value){
                    if ($param == "apid" || $param == "caid"){
                        $value = str_replace ( array(",",";"), ",<br/>", htmlspecialchars($value ));
                    }
                    elseif ($param == "x_last_changed" || $param == "x_timestamp_added" || $param == "x_last_confirmed"){
                        $value = date("D, d M Y H:i:s", $value);
                    }
                    else
                        $value = htmlspecialchars($value);
                    $this->appendToBody( '<td class="'.htmlspecialchars($param).'">'.$value."</td>\n" );
                }
                $this->appendToBody( "</tr>\n" );
                $lastname = $carray["name"];
            }
            $this->appendToBody( "</table></div>\n" );
        }
        $this->addToOverviewAndSave( "New channels", "latest_channel_additions.html" );
    }
}

?>