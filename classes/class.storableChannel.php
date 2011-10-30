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

class storableChannel extends channel{

    private
        $metaData,
        $sourceDB; //needed for db

    public function __construct( $channelparams, & $metaDataObj ){
        $this->metaData = $metaDataObj;
        $this->metaData->increaseCheckedChannelCount();
        parent::__construct( $channelparams);

        //convert name and provider strings to utf-8 if they are not in utf-8
        //this only needs to be done if the channels were read from file
        //usually is necessary for sky_de channels that are encoded in ISO-8859-15
        $this->params["x_utf8"] = 1;
        if (mb_check_encoding($this->params["name"], "UTF-8") === false){
            $this->params["name"] = mb_convert_encoding ( $this->params["name"] , "UTF-8", "ISO-8859-15");
            $this->params["x_utf8"] = 0;
        }
        if (mb_check_encoding($this->params["provider"], "UTF-8") === false){
            $this->params["provider"] = mb_convert_encoding ( $this->params["provider"] , "UTF-8", "ISO-8859-15");
            $this->params["x_utf8"] = 0;
        }

        //FIXME: This should go into a method in the metadata class
        //ignore changed channel names that should check if a channel is eclipsed
        $checkignore= "[checkignore]";
        if (substr($this->params["name"], 0, 13) == $checkignore){
            $this->params["name"] = substr($this->params["name"], 13);
            //$this->config->addToDebugLog("checkignore: " .$this->params["name"]."\n");
        }

        //if a channel was read from a file the source of non-sat channels
        //need to be modified before they are being put into the db
        //this does not apply for channels read from the db
        $this->setSourceForDB();
        $this->longUniqueID = $this->sourceDB."-". $this->sourceLessId;
    }

    protected function setSourceForDB(){
        if (!$this->isSatelliteSource()){
            switch ($this->source){
            case "C":
            case "T":
            case "A":
                if ( $this->metaData->isValidNonSatSource( $this->source ) ){
                    $nonSatProvider = $this->metaData->getProviderNameForLastCheckedNonSatSource();
                    $this->sourceDB = $this->source . '[' . $nonSatProvider . ']';
                }
                else{
                    $this->markChannelAsInvalid();
                }
                break;
            case "I":
            case "P":
                $this->markChannelAsInvalid();
                break;
            default:
                $this->markChannelAsInvalid();
                throw new Exception( "Unknown source type! " . $this->source );
            }
        }
        else{
            //for sat sources, remove a redundant ".0", Example: Turn S9.0E into S9E
            if (substr( $this->source, -3, 2 ) == ".0")
                $this->source = substr( $this->source, 0, strlen( $this->source ) - 3 ) . substr( $this->source, -1, 1 );
            if ( $this->metaData->isValidSatSource( $this->source ) ){
                $this->sourceDB = $this->source;
            }
            else
                $this->markChannelAsInvalid();
        }
    }

    protected function markChannelAsInvalid(){
        $this->params = false;
        $this->config->addToDebugLog( "Channel was marked as invalid. Source is not allowed: '". $this->source  ."'\n");
    }

    /*
     * inserts a channel into db
     * takes an associative array with keys and values
     * that are used for insert
     */

    public function insertIntoDB(){
        $success = true;
        if ($this->isValid()){
            //this only has to be added if native channel data is to be inserted to db
            $this->params = $this->params + array(
                "x_label"         => "",
                "x_last_changed"  => $this->metaData->getTimestamp(),
                "x_timestamp_added" => $this->metaData->getTimestamp(),
                "x_last_confirmed" => 0
            );
            $this->params["source"] = $this->sourceDB;
            $this->params["modulation"] = strtoupper( $this->params["modulation"] ); //w_scan has lower case, we don't want that

            $query = $this->db->insert( "channels", $this->params);
            //19 = channel already exists, couldn't be inserted
            if ($query != 19) {
                if ( $this->metaData !== null)
                    $this->metaData->increaseAddedChannelCount();
                $query = $this->db->insert( "channel_update_log", array(
                    "combined_id" => $this->longUniqueID,
                    "name" => $this->params["name"],
                    "update_description" => "New channel added: " . $this->getChannelString(),
                    "timestamp" => $this->metaData->getTimestamp(),
                    "importance" => "1"
                ));
            }
            else{
                $this->updateInDB();
                $success = false;
            }
        }
        else{
            $this->config->addToDebugLog( "Source is not allowed: '". $this->source  ."'. Channel not added to database.\n");
            $success = false;
        }
        return $success;
    }

    protected function updateInDB(){
        //$this->config->addToDebugLog( "checking channel ".$this->params["name"]." for changes: \n");
        $result = $this->getChannelsWithMatchingUniqueParams();
        foreach ($result as $row){
            $otherchannel = new channel($row);
            if ($row["x_timestamp_added"] == $this->metaData->getTimestamp() || $row["x_last_confirmed"] == $this->metaData->getTimestamp()){
                $this->config->addToDebugLog(
                    "ERROR: Trying to update channel ".$this->params["name"]." that was added or updated earlier! Double channel entry!\n".
                    "To update: " . $this->getChannelString() ."\n".
                    "Existing : " . $otherchannel->getChannelString() ."\n".
                    "---\n"
                );
                if ( $this->metaData !== null)
                    $this->metaData->increaseIgnoredChannelCount();
            }
            else{
                $changes = array();
                $update_data = array();
                $importance = 0;
                foreach ($this->params as $key => $value){
                    if ($value != $row[$key]  && substr($key,0,2) !== "x_" ){
                        if ($key != "apid" && $key != "vpid" && $key != "caid")
                            $importance = 1;
                        $changes[] = "$key: '".$row[$key]. "' to '". $value."'";
                        $update_data[] = "$key = ".$this->db->quote( $value);
                    }
                }
                $update_data[] = "x_last_changed = "   . $this->metaData->getTimestamp();
                $update_data[] = "x_last_confirmed = " . $this->metaData->getTimestamp();

                if (count ($changes) != 0){
                    $this->config->addToDebugLog( "Changed: ".$this->getUniqueID() . "-" . $this->params["name"] . ": " . implode(", ",$changes)."\n");
                    $query = $this->db->exec2(
                        "UPDATE channels SET ".implode(", " , $update_data),
                        $this->getWhereArray( "source, nid, tid, sid" )
                    );
                    $query = $this->db->insert( "channel_update_log",
                        array(
                            "combined_id"        => $this->longUniqueID,
                            "name"               => $this->params["name"],
                            "update_description" => implode("\n",$changes),
                            "timestamp"          => $this->metaData->getTimestamp(),
                            "importance"         => $importance
                        )
                    );
                    if ( $this->metaData !== null)
                        $this->metaData->increaseChangedChannelCount();
                }
                else{
                    //$this->config->addToDebugLog( "channel unchanged, but update x_last_confirmed\n");
                    //channel unchanged, but update x_last_confirmed
                    $query = $this->db->exec2(
                        "UPDATE channels SET x_last_confirmed = " . $this->metaData->getTimestamp(),
                        $this->getWhereArray( "source, nid, tid, sid" )
                    );
                }
            }
        }
    }
}

?>