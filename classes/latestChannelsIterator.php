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

class latestChannelsIterator extends channelIterator{

    private
        $currentChunk,
        $lastTimestamp;

    function __construct(){
        parent::__construct();
        $this->currentChunk = 0;
        $this->lastTimestamp = 0;
    }

    public function notEmptyForSource( $source ){
        $timestamp = $this->getEarliestChannelAddedTimestamp( $source );
        if ($timestamp != 0){
            $this->init2( "
                SELECT * FROM channels
                WHERE source = ".$this->db->quote( $source ) ." AND x_timestamp_added > " . $this->db->quote($timestamp) . "
                ORDER BY x_timestamp_added DESC, name DESC
                LIMIT 100
            ");
            return true;
        }
        else
            return false;
    }

    public function getNextInfoChunk(){
        $retval = false;
        $chunk = array();
        $timestamp = $this->lastTimestamp;
        if ($this->currentChunk != 0){
            array_push( $chunk, $this->currentChunk);
            $this->currentChunk = 0;
        }
        while ($this->moveToNextChannel() !== false ){
            $currChan = $this->getCurrentChannelObject();
            if ( $this->lastTimestamp == 0 ){
                array_push( $chunk, $currChan );
                $this->lastTimestamp = $currChan->getXTimestampAdded();
                $timestamp = $this->lastTimestamp;
            }
            else if ( $currChan->getXTimestampAdded() == $this->lastTimestamp ){
                array_push( $chunk, $currChan );
            }
            else{
                $this->currentChunk = $currChan;
                $this->lastTimestamp = $currChan->getXTimestampAdded();
                break;
            }
        }
        if (count($chunk) > 0 ){
            $retval = array(
                "timestamp" => $timestamp,
                "content"   => $chunk
            );
        }
        return $retval;
    }

    private function getEarliestChannelAddedTimestamp( $source ){
        $timestamp = 0;
        $sqlquery = "
            SELECT x_timestamp_added
            FROM channels
            WHERE source = ".$this->db->quote( $source )." AND x_timestamp_added > 0
            ORDER BY x_timestamp_added ASC
            LIMIT 1
        ";
        $result = $this->db->query($sqlquery);
        $timestamp_raw = $result->fetchAll();
        if (isset($timestamp_raw[0][0]))
            $timestamp = intval($timestamp_raw[0][0]);
        return $timestamp;
    }
}