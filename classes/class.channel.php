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

class channel{

    protected
        $db,
        $config,
        $params,

        $channelstring = "",
        $uniqueID = "",
        $longUniqueID = "",
        $isCheckedSatelliteSource = false,
        $processedParameters = array(),

        $name,
        $provider,
        $frequency,
        $parameter,
        $source,
        $symbolrate,
        $vpid,
        $apid,
        $tpid,
        $caid,
        $sid,
        $nid,
        $tid,
        $rid;

    public function __construct( $channelparams){
        $this->db = dbConnection::getInstance();
        $this->config = config::getInstance();
        $this->params = array();
        $this->isCheckedSatelliteSource = false;

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
        //split transponder parameters (as early as possible)
        $tempProcessedParameters = explode(";", preg_replace( "/(\D|\d)(\D)/", "$1;$2", $this->params["parameter"]));
        foreach ($tempProcessedParameters as $item){
            $this->processedParameters[ strtoupper(substr($item,0,1)) ] = substr($item,1);
        }

        //sanity check for satellite source
        $check1 = (substr( $this->params["source"], 0, 1) === "S");
        $check2 = (stristr( $this->params["parameter"], "S") !== false);
        //TODO: Also check for presence of H or V
        if ($check1 !== $check2){
            if ($check1 === true){
                throw new Exception("A satellite channel should have an S in parameters: '". $this->params["parameter"]."'. Is this obsolete VDR 1.6 syntax?");
                //$check1 = false;
            }
            //IPTV channels might have an S in the parameter field
//            else
//                $this->markChannelAsInvalid("Channel parameters misleadingly indicate a satellite channel: '". $this->params["parameter"]."'");
        }
        $this->isCheckedSatelliteSource = $check1;
    }

    public function getSingleTransponderParameter( $key ){
        $key = strtoupper($key);
        if ( array_key_exists( $key, $this->processedParameters ) ){
            if ($this->processedParameters[$key] !== "")
                return intval( $this->processedParameters[$key]);
            else
                return true; //for H and V that don't have a numeric value
        }
        else
            return false;
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

    public function getFrequency(){
        return $this->params["frequency"];
    }

    //TODO: http://www.mysnip.de/forum-archiv/thema/8773/414227/DVB_+Wie+wird+die+Symbolrate+berechnet.html
    public function getSymbolrate(){
        return $this->params["symbolrate"];
    }

    public function getReadableFrequency(){
        if ($this->isSatelliteSource())
            $value = $this->params["frequency"]." MHz";
        else{
            //    * MHz, kHz oder Hz angegeben.
            //Der angegebene Wert wird mit 1000 multipliziert, bis er größer als 1000000 ist.
             $value2 = intval( $this->params["frequency"] );
             $step = 0;    //113000
             while($value2 < 1000000){
                 $step++;
                 $value2 = $value2 * 1000;
             }
             $value = $value2 / (1000*1000);
             $value = $value . " Mhz";
        }
        return $value;
    }


    public function getParameter(){
        return $this->params["parameter"];
    }

    public function getSource(){
        return $this->source;
    }

    public function getUniqueID(){
        return $this->uniqueID;
    }

    public function getLongUniqueID(){
        return $this->longUniqueID;
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

    //FIXME temp
    public function getAsArray(){
        //hotfix modulation - we need modulation in json structure!
        $this->params["modulation"] = $this->params["parameter"];
        $this->params["x_unique_id"] = $this->getUniqueID();
        $this->params["source"] = $this->source;
        return $this->params;
    }

    public function isSatelliteSource(){
        return $this->isCheckedSatelliteSource;
    }

    public function onS2SatTransponder(){
        return  $this->getSingleTransponderParameter("S") === 1;
    }

    public function belongsToSatHighBand(){
        return ($this->params["frequency"] >= 11700 && $this->params["frequency"] <= 12750);
    }

    public function belongsToSatLowBand(){
        return ($this->params["frequency"] >= 10700 && $this->params["frequency"] < 11700);
    }

    public function belongsToSatVertical(){
        return $this->getSingleTransponderParameter( "V" );
    }

    public function belongsToSatHorizontal(){
        return $this->getSingleTransponderParameter( "H" );
    }

    public function getFECOfSatTransponder(){
        $rawCoderate = $this->getSingleTransponderParameter( "C" );
        if ($rawCoderate !== false && strlen($rawCoderate) >= 2 ){
            $n1 = intval(substr($rawCoderate,0,1));
            $n2 = intval(substr($rawCoderate,1));
            if ($n1 + 1 - $n2 !== 0)
                 throw new Exception("Satellite channel has wrong FEC parameters");
            $rawCoderate = $n1."/".$n2;
        }
        else
            $rawCoderate = "";
        return $rawCoderate;
    }

    public function getModulation(){
        $rawModulation = $this->getSingleTransponderParameter( "M" );
        $retVal = "";
        switch ($rawModulation){
            case 16:
            case 32:
            case 64:
            case 128:
            case 256:
               $retVal = "QAM".$rawModulation;
               break;
            case 998:
                $retVal = "QAM-Auto";
               break;
            case 2:
               $retVal = "QPSK";
               break;
            case 5:
               $retVal = "8PSK";
               break;
           case 6:
               $retVal = "16APSK";
               break;
            case 10:
                $retVal = "VSB8";
               break;
            case 11:
                $retVal = "VSB16";
                break;
        }
        return $retVal;
    }
/*
C (0, 12, 13, 14, 23, 25, 34, 35, 45, 56, 67, 78, 89, 910) Code rate high priority
D (0, 12, 13, 14, 23, 25, 34, 35, 45, 56, 67, 78, 89, 910) Code rate low priority
B (5, 6, 7, 8) Bandbreite in MHz (DVB-T)
Y (0, 1, 2, 4) Hierarchie (DVB-T/H), 0 = aus, 1, 2, 4 = Alpha (Hierarchy ein)
G (4, 8, 16, 32) Guard interval (DVB-T/H)
I (0, 1) Inversion, 0 = aus, 1 = ein (DVB-T/H, DVB-C)
T (2, 4, 8) Transmission mode (DVB-T/H)
H Polarisation horizontal (DVB-S/S2)
V Polarisation vertikal (DVB-S/S2)
R Polarisation zirkular rechts (DVB-S/S2)
L Polarisation zirkular links (DVB-S/S2)
S (0, 1) Modulationssystem, 0 = DVB-S, 1 = DVB-S2
O (20, 25, 35) RollOff für DVB-S/S2, DVB-S: 35, DVB-S2: alle Werte
*/

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