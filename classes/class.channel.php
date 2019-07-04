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

class channel extends transponderParameters{

    protected
        $db,
        $config,

        $channelstring = "",
        $vdr_compatibility_version,

        $cpid = "",
        $uniqueID = "",
        $longUniqueID = "",

        $name,
        $provider,
        $frequency,
        $source,
        $symbolrate,
        $parameter,
        $vpid,
        $apid,
        $tpid,
        $caid,
        $sid,
        $nid,
        $tid,
        $rid;

    public function __construct( $channelparams ){
        $this->db = dbConnection::getInstance();
        $this->config = config::getInstance();
        parent::__construct();
        $this->vdr_compatibility_version = 1722;

        if (is_array( $channelparams )){
            //turn some integer params back into integer values
            //if they came from db they are all of type string
            $int_params = array(
                "frequency",
                "symbolrate",
                "sid",
                "nid",
                "tid",
                "rid",
                "x_last_changed",
                "x_timestamp_added",
                "x_last_confirmed",
                "x_utf8"
            );
            foreach ( $channelparams as $param => $value){
                if (in_array($param, $int_params)){
                    if (!array_key_exists($param, $channelparams)){
                        throw new Exception( "channel: param $param ist not in channel array!");
                    }
                    $channelparams[$param] = intval($value);
                }
            }
            $this->params = $channelparams;
            $this->source = $this->params["source"];
            $this->prepareAndValidateParameters();
            $this->channelstring = $this->convertArray2String();
        }
        elseif (is_string( $channelparams )){
            $this->params = $this->convertString2Array( $channelparams );
            $this->source = $this->params["source"];
            $this->prepareAndValidateParameters();
            //For debugging (slower): compare channel strings to check if our conversion works fine
            //$this->channelstring = $this->convertArray2String();
            //if ( $this->channelstring != $channelparams)
            //    throw new Exception("Channelstrings don't match:\n".$this->channelstring ."\n". $channelparams );
            $this->channelstring = $channelparams;
        }
        else
            throw new Exception("Channelparams are neither of type array nor of type string!");

        $this->sourceLessId = $this->params["nid"]."-". $this->params["tid"]."-". $this->params["sid"];
        $this->uniqueID = $this->getShortenedSource()."-". $this->sourceLessId;
        $this->longUniqueID = $this->params["source"]."-". $this->sourceLessId;
    }

    public function prepareAndValidateParameters(){
        parent::prepareAndValidateParameters();
        //sanity check for satellite source
        $check1 = (substr( $this->params["source"], 0, 1) === "S");
        $check2 = (stristr( $this->params["parameter"], "S") !== false);
        //TODO: Also check for presence of H or V
        //Caution: DVB-T2 can have source T + parameter S1, this is also possible for IPTV
        if ($check1 && !$check2){
            throw new Exception("A satellite channel should have an S in parameters: '". $this->params["parameter"]."'. Is this obsolete VDR 1.6 syntax? " . $this->params["name"] . " " . $this->channelstring);
        }
        $this->isCheckedSatelliteSource = $check1;
    }

    public function enforceCompatibilityToVDRVersion( $version ){
        $this->vdr_compatibility_version = $version;
        if ($version < 1721){
            $this->params["apid"] = preg_replace( '/\@\d+/', '', $this->params["apid"]);
            $this->params["tpid"] = preg_replace( '/\;.*$/', '', $this->params["tpid"]);
            $this->params["parameter"] = preg_replace( '/P0/', '', $this->params["parameter"]);
            if( $this->isSatelliteSource()){
                $this->params["parameter"] = preg_replace( array('/H/i','/V/i'), array('h','v'), $this->getParameter() );
            }
            $this->channelstring = $this->convertArray2String();
        }
    }

    protected function markChannelAsInvalid( $msg ){
        $this->params = false;
        $this->config->addToDebugLog( "Channel was marked as invalid: $msg\n");
    }

    public function isValid(){
        return ($this->params !== false);
    }

    public function getShortenedSource(){
        $retval = "";
        if (!$this->isSatelliteSource()){
            $retval = substr($this->source,0,1);
        }
        else
            $retval = $this->source;
        return $retval;
    }

    public function setSourceToShortForm(){
        $this->source = $this->getShortenedSource();
    }

    public function getName(){
        return $this->params["name"];
    }

    public function getProvider(){
        return $this->params["provider"];
    }

    public function getSource(){
        return $this->source;
    }

    public function getCAID(){
        return $this->params["caid"];
    }

    public function getSID(){
        return $this->params["sid"];
    }

    public function getNID(){
        return $this->params["nid"];
    }

    public function getTID(){
        return $this->params["tid"];
    }

    public function getUniqueID(){
        return $this->uniqueID;
    }

    public function getLongUniqueID(){
        return $this->longUniqueID;
    }

    public function getCPID(){
        return $this->cpid;
    }

    public function getXLastConfirmed(){
        return $this->params["x_last_confirmed"];
    }

    public function getXLastChanged(){
        return $this->params["x_last_changed"];
    }

    public function getXTimestampAdded(){
        return $this->params["x_timestamp_added"];
    }

    public function getXLabel(){
        return $this->params["x_label"];
    }

    public function getXCPID(){
        return $this->params["x_xmltv_id"];
    }

    public function getVideoPID(){
        return $this->params["vpid"];
    }

    public function getAudioPID(){
        return $this->params["apid"];
    }

    public function getTeletextPID(){
        return $this->params["tpid"];
    }

    public function hasVideoPID(){
        return ($this->params["vpid"] !== '0');
    }

    public function hasAudioPID(){
        return ($this->params["apid"] !== '0');
    }

    public function getReadableServiceType(){
        if ($this->hasVideoPID()){
            if ( stristr( $this->getXLabel(), 'hdtv' ) !== false)
                return "HDTV";
            else
                return "TV";
        }
        else if ($this->hasAudioPID())
            return "Radio";
        else
            return "Data";
    }

    public function getXLabelRegion(){
        $parts = explode(".",$this->getXLabel());
        if (count($parts) > 0 )
            return $parts[0];
        else
            return "";
    }

    /*
    public function isOutdated(){
        return $this->getXLastConfirmed() < $this->parent->getLastConfirmedTimestamp();
    }
    */

    public function isNewlyAdded( $timeframe ){
        return ( time() - $this->getXTimestampAdded()) < $timeframe;
    }

    //FIXME temp
    public function getAsArray(){
        //hotfix modulation - we need modulation in json structure!
        $this->params["modulation"] = $this->params["parameter"];
        $this->params["x_unique_id"] = $this->getUniqueID();
        $this->params["source"] = $this->source;
        return $this->params;
    }


    protected function getChannelsWithMatchingUniqueParams(){
        return $this->db->query2( "SELECT * FROM channels", $this->getWhereArray( "source, nid, tid, sid") );
    }

    protected function getWhereArray( $wherelist){
        $where_array = array();
        foreach ( explode(",", $wherelist) as $key ){
            $key = trim($key);
            $where_array[$key] = $this->params[$key];
        }
        return $where_array;
    }

    public function getChannelString(){
        return $this->channelstring;
    }

    public function convertArray2String(){
        $provider = "";
        if ($this->params["provider"] != "")
            $provider = ";". $this->params["provider"];

        return
            $this->params["name"] .
            $provider . ":".
            $this->params["frequency"] . ":".
            $this->params["parameter"] . ":".
            $this->getShortenedSource() . ":".
            $this->params["symbolrate"] . ":".
            $this->params["vpid"] . ":".
            $this->params["apid"] . ":".
            $this->params["tpid"] . ":".
            $this->params["caid"] . ":".
            $this->params["sid"] . ":".
            $this->params["nid"] . ":".
            $this->params["tid"] . ":".
            $this->params["rid"];
    }

    public function convertString2Array( $string ){
        $result = false;
        $details = explode( ":", $string);
        if (count($details) == 13){
            $cname = $details[0];
            $cprovider = "";
            $cnamedetails = explode( ";", $cname);
            if (count($cnamedetails) == 2){
                $cname = $cnamedetails[0];
                $cprovider = $cnamedetails[1];
            }

            $result = array(
                "name"            => $cname,
                "provider"        => $cprovider,
                "frequency"       => intval($details[1]),
                "parameter"       => $details[2],
                "source"          => $details[3],
                "symbolrate"      => intval($details[4]),
                "vpid"            => $details[5],
                "apid"            => $details[6],
                "tpid"            => $details[7],
                "caid"            => $details[8],
                "sid"             => intval($details[9]),
                "nid"             => intval($details[10]),
                "tid"             => intval($details[11]),
                "rid"             => intval($details[12])
            );
        }
        else{
            throw new Exception( "Couldn't convert channel string to channel array");
        }
        return $result;
    }
}
?>
