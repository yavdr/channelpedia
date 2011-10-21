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

class epg2vdrMapper{

    const
        epgMappingDir = "../epg_mappings/";

    private
        $db,
        $config,
        $externalEPGMappings;

    private static $instance = null;

    private function __clone(){}

    public static function getInstance(){
        if ( self::$instance == null){
            self::$instance = new epg2vdrMapper();
        }
        return self::$instance;
    }

    protected function __construct(){
        $this->db = dbConnection::getInstance();
        $this->config = config::getInstance();
        $this->externalEPGMappings = array();
    }

    private function getExternalEPGMappings($epgservice){
        if (!array_key_exists($epgservice, $this->externalEPGMappings)){
            $this->externalEPGMappings[$epgservice] = unserialize(file_get_contents(epg2vdrMapper::epgMappingDir .$epgservice."2vdr.txt"));
        }
        return $this->externalEPGMappings[$epgservice];
    }

    /*
     * epgservice can be either epgdata or tvm
     */

    public function writeEPGChannelmap( $type, $puresource, $epgservice){
        if ( $epgservice != "epgdata" && $epgservice != "tvm")
            throw new Exception("Illegal epgservice value!");
        $visibletype = ($type == "A") ? "ATSC" : "DVB-". $type;
        if ($type !== "S")
            $source = $type . "[" . $puresource . "]";
        else
            $source = $puresource;
        $map = "";
        $noepg_config = "";
        $notfoundlist = array();
        foreach ($this->getExternalEPGMappings($epgservice) as $channel => $epgid){
            $origchannel = $channel;
            $channelvariants = $this->fixChannelName($channel);
            $queryparts = array();
            foreach ($channelvariants as $channel){
                if (substr($channel, -1, 1) == "%")
                    $queryparts[] =
                        "UPPER(name) LIKE" . $this->db->quote(strtoupper($channel)) . " ";
                else
                    $queryparts[] =
                        "UPPER(name) =" . $this->db->quote(strtoupper($channel)) . " ".
                        "OR UPPER(name) LIKE" . $this->db->quote(strtoupper($channel.' HD%')) . " ".
                        "OR UPPER(name) LIKE " . $this->db->quote(strtoupper($channel.',%')). " ";
                        //"OR UPPER(name) LIKE " . $this->db->quote(strtoupper($channel.'.%')). " ";
                        //line above maybe only matches outdated sky channels?? check!
            }
            $sqlquery=
                "SELECT * FROM channels ".
                "WHERE source = ".$this->db->quote($source)." ".
                "AND vpid != '0' ". //only tv channels
                "AND (x_label LIKE 'de.%' OR x_label LIKE 'at.%' OR x_label LIKE 'ch.%') ".
                "AND ( ".
                implode( " OR ", $queryparts).
                ")";
            $result = $this->db->query($sqlquery);
            $idlist = array();
            $comments = array();
            foreach ($result as $row){
                $currentChannel = new channel($row);
                //FIXME use channel object properly here
                $idlist[] = $type . "-" . $row["nid"] . "-" . $row["tid"] . "-" . $row["sid"];
                $comments[] = $currentChannel->getChannelString();
            }
            if (count($idlist) > 0 ){
                $map .=
                    "//\n".
                    "//=======================================================\n".
                    "// '" . $origchannel . "' (" . $epgid . ")\n".
                    "//-------------------------------------------------------\n".
                    "//\n".
                    "//  ".
                    "found matches:\n//    " .implode( "\n//    ", $comments )."\n//\n".
                    $epgid . " = " . implode( ",", $idlist )."\n"
                    ;
                $noepg_config .= implode( " ", $idlist ) . " ";
                $notfoundlist[] = "[X] " . $origchannel . " (" . $epgid . ")";
            }
            else{
                $notfoundlist[] = "[ ] " . $origchannel . " (" . $epgid . ")";
            }
        }
        if ($map != ""){
            $map =
                "//\n".
                "// ChannelMap for ".strtoupper($epgservice)."2VDR-Plugin\n".
                "// --------------------------\n".
                "// Automatically generated by yaVDR Channelpedia\n".
                "// Created on: ". date("D M j G:i:s T Y")."\n".
                "// Only valid for provider/source ". $source . "\n".
                "//\n".
                "// Mapping format: ChannelID = VDR ChannelID (Src-NID-TID-SID) \n".
                "//\n".
//                "// Rename this file and and put it into the \n".
//                "// directory /etc/vdr/plugins/".$epgservice."2vdr/".$epgservice."2vdr_channelmap.conf\n".
//                "//\n".
                "//=======================================================\n".
                "//   1) Overview: Matched and unmatched channels\n".
                "//=======================================================\n".
                "// It is unlikely that we were able to match all of \n".
                "// the following channels automatically. Those channels \n".
                "// that were matched successfully are marked with an [X].\n".
                "// For the others, you have to care manually.\n".
                "// Either the channels marked with [ ] don't exist \n".
                "// in the portfolio of your provider\n".
                "// or the channel detection script used is not good enough.\n".
                "// Please help to improve it by checking channelpedia source code:\n".
                "// https://github.com/yavdr/channelpedia/tree/master/epg_mappings\n".
                "// See: https://bugs.yavdr.com/issues/647 \n".
                "//\n".
                "//  ".implode( "\n//  ", $notfoundlist)."\n".
                "//\n".
                "//=======================================================\n".
                "//   2) noEpg config\n".
                "//=======================================================\n".
                "// You can put the following line into\n".
                "// /etc/vdr/setup.conf to disable the ordinary EIT epg\n".
                "// for the channels listed here.\n".
                "//\n".
                "// noEpg = $noepg_config\n".
                "//\n".
                "//=======================================================\n".
                "//   3) Details: Matched channels\n".
                "//=======================================================\n".
                "// The following lines contain the successfully matched channels.\n".
                "//\n".
                $map.
                "//\n";

            $gpath = $this->config->getValue("exportfolder")."/";
            $filename = $visibletype ."/". strtr(strtr( trim($puresource," _"), "/", ""),"_","/"). "/" . $source . '.' . $epgservice . '2vdr_channelmap.conf';
            $this->config->addToDebugLog("Writing channelmap $filename\n");
            file_put_contents($gpath . $filename, $map);
        }
    }

    //this is the most stupid way to fix the problem
    private function fixChannelName($channel){
        $variants = array($channel);
        switch (strtoupper($channel)) {
        case "ARD":
            $variants[] = "Das Erste";
            break;
        case "ZDFNEO":
            $variants[] = "ZDF_neo";
            break;
        case "ZDFINFO":
            $variants[] = "ZDFinfokanal";
            break;
        case "SAT1":
        case "SAT.1":
            $variants[] = "SAT.1";
            $variants[] = "SAT. 1";
            $variants[] = "SAT 1";
            $variants[] = "SAT.1 Bayern";
            $variants[] = "SAT.1 HH/SH";
            $variants[] = "SAT.1 NRW";
            $variants[] = "SAT.1 NS/Bremen";
            $variants[] = "SAT.1 RhlPf/Hessen";
            break;
        case "RTL":
            $variants[] = "RTL Television";
            $variants[] = "RTL FS";
            $variants[] = "RTL HB NDS";
            $variants[] = "RTL HH %";
            $variants[] = "RTL Austria";
            break;
        case "S RTL":
            $variants[] = "Super RTL";
            break;
        case "RTL II":
            $variants[] = "RTL2";
            break;
        case "DSF":
            $variants[] = "Sport1";
            break;
        case "VIVA":
            $variants[] = "VIVA Germany";
            break;
        case "KI.KA":
            $variants[] = "KIKA";
            break;
        case "ORF 1":
            $variants[] = "ORF1";
            break;
        case "ORF 2":
            $variants[] = "ORF2";
            break;
        case "NDR":
        case "HR":
        case "MDR":
        case "WDR":
        case "SWR":
        case "RBB":
            $variants[] = $channel."%";
            break;
        case "BR":
            $variants[] = "Bayerisches FS %";
            break;
        case "BR ALPHA":
            $variants[] = "BR-alpha";
            break;
        //case "SKY Fußball Bundesliga"://do not use UTF-8 here!!!
        //    $variants[] = "SKY Bundesliga";
        //    break;
        case "SKY SPORT HD":
            $variants[] = "SKY Sport HD 1";
            break;
        case "RHEINMAINTV":
            $variants[] = "rhein main TV";
            break;
        case "NATIONAL GEOGRAPHIC HD":
            $variants[] = "NatGeo HD";
            break;
        }

        return $variants;
    }

}
?>