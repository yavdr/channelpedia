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

class transponderParameters{

    protected
        $processedParameters = array(),
        $isCheckedSatelliteSource = false,
        $params;

    public function __construct(){
        $this->params = array();
        $this->isCheckedSatelliteSource = false;
    }

    public function importData( $frequency, $parameter, $symbolrate ){
        $this->params["frequency"]  = $frequency;
        $this->params["parameter"]  = $parameter;
        $this->params["symbolrate"] = $symbolrate;
        $this->prepareAndValidateParameters();
    }

    public function prepareAndValidateParameters(){
        //split transponder parameters (as early as possible)
        $tempProcessedParameters = explode(";", preg_replace( "/(\D|\d)(\D)/", "$1;$2", $this->params["parameter"]));
        foreach ($tempProcessedParameters as $item){
            $value = substr($item,1);
            if ($value === false) $value = ""; // in case of H and V
            $this->processedParameters[ strtoupper(substr($item,0,1)) ] = $value;
        }
        $this->isCheckedSatelliteSource = (stristr( $this->params["parameter"], "S") !== false);
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

    public function getFrequency(){
        return $this->params["frequency"];
    }

    //TODO: http://www.mysnip.de/forum-archiv/thema/8773/414227/DVB_+Wie+wird+die+Symbolrate+berechnet.html
    public function getSymbolrate(){
        return $this->params["symbolrate"];
    }

    public function getParameter(){
        return $this->params["parameter"];
    }


    public function isSatelliteSource(){
        return $this->isCheckedSatelliteSource;
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
    public function getRollOff(){
        $ro = false;
        if ( !$this->isSatelliteSource() )
            $ro = false;
        else if ( $this->onS2SatTransponder() )
            $ro = "0." . $this->getSingleTransponderParameter( "O" );
        else
            $ro = "0.35"; // DVB-S standard value
        return $ro;
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

}