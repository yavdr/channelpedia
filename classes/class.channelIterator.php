<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Henning Pingel
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

class channelIterator{

    private
        $db,
        $config,
        $result = false,
        $channel = false,
        $count = 0,
        $lastFrequency = "",
        $transponderChanged = true,
        $groupChanged = true,
        $lastGroup = "",
        $shortenSource,
        $tolerateInvalidChannels = false;

    function __construct($shortenSource = true){
        $this->db = dbConnection::getInstance();
        $this->config = config::getInstance();
        $this->shortenSource = $shortenSource;
        $this->tolerateInvalidChannels = false;
    }

    public function init1( $label, $source, $orderby = "frequency, parameter, provider, name ASC"){
        $where = array();
        $where["source"] = $source;
        if (substr($label,0,9) !== "_complete")
            $where["x_label"] = $label;
        $this->result = $this->db->query2("SELECT * FROM channels", $where, true, $orderby);
    }

    public function init2( $statement ){
        $db = dbConnection::getInstance();
        $this->result = $this->db->query($statement);
    }

    public function tolerateInvalidChannels(){
        $this->tolerateInvalidChannels = true;
    }

    public function moveToNextChannel(){
        $this->channel = false;
        $exists = false;
        if (!$this->result === false){
            //FIXME: encapsulate access to result fetch
            $temp = $this->result->fetch(PDO::FETCH_ASSOC);
            if (!$temp === false){
                $channelobj = new channel( $temp );
                //print "channelobject instanciated.\n";
                if ($this->tolerateInvalidChannels || $channelobj->isValid()) {
                    //print "channelobject is valid.\n";
                    $exists = true;
                    $this->channel = $channelobj;
                    if ($this->shortenSource){
                        $this->channel->setSourceToShortForm();
                    }
                    $this->count++;

                    $this->groupChanged = ( $this->lastGroup !== $this->channel->getXLabel() );
                    $this->lastGroup = $this->channel->getXLabel();

                    $this->transponderChanged = ( $this->lastFrequency !== $this->channel->getSource() ."-" . $this->channel->getFrequency() );
                    $this->lastFrequency = $this->channel->getSource() ."-" . $this->channel->getFrequency();
                }
                else{
                    print "channelIterator: channel is invalid.\n";
                }
            }
        }
        return $exists;
    }

    public function getCurrentChannelObject(){
        return $this->channel;
    }

    public function getCurrentChannelArrayKeys(){
        return array_keys($this->channel->getAsArray());
    }

    public function transponderChanged(){
        return $this->transponderChanged;
    }

    public function getCurrentTransponderInfo(){
        return "Transponder " .
                $this->channel->getSource() . ", " .
                ($this->channel->isSatelliteSource() ?
                    "DVB-S" . ( $this->channel->onS2SatTransponder() ? "2" : "" ) .", ".
                    $this->channel->getReadableFrequency() . ", " .
                    "" . ( $this->channel->belongsToSatVertical() ? "Vertical" : "Horizontal" ) ." ".
                    "" . ( $this->channel->belongsToSatHighBand() ? "High"     : "Low"        ) ." Band, ".
                    "FEC " . $this->channel->getFECOfSatTransponder() .", "
                :
                    $this->channel->getReadableFrequency() . ", "
                ).
                $this->channel->getModulation() .", " .
                $this->channel->getSymbolrate() .", " .
                $this->channel->getParameter();
    }

    public function groupChanged(){
        return $this->groupChanged;
    }

    public function getCurrentGroupInfo(){
        list( $langregion, $prio, $title) =  explode("." , $this->channel->getXLabel());
        return "[" . $this->channel->getSource() . "/" . $langregion . "] " . $title;
    }


    public function getCurrentChannelCount(){
        return $this->count;
    }
}