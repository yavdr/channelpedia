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

class singleSourceHTMLReportBase extends HTMLReportBase{

    const NEWLY_ADDED_CHANNEL_TIMEFRAME = 2592000; //30 * 24 * 60 * 60  <-- 30 days

    function __construct( HTMLOutputRenderSource & $obj ){
        $this->parent = $obj;
        parent::__construct( $this->parent->getRelPath() );
    }

    protected function addBodyHeader( $language = ""){
        $descr =  ($this->parent->getType() === "S" ) ?
            $this->parent->getPureSource() . " - " . $this->config->getLongNameOfSatSource( $this->parent->getSource()) :
            $this->parent->getPureSource();
        $this->appendToBody( "<h1>" . $this->parent->getVisibleType() . ": " . $descr . "</h1>");
        $this->appendToBody( $this->getSectionTabmenu( $language ) );
        parent::addBodyHeader();
    }

    protected function addToOverviewAndSave( $menuLabel, $filename ){
        parent::addToOverviewAndSave( $menuLabel, $filename, $this->description );
    }

    private  function getSectionTabmenu($language){
        $menu = new HTMLControlTabMenu( $this->parent->getRelPath(), $this->config->getValue("exportfolder"));
        $sourceMenuItem = $this->parent->getVisibleType() . ": " . $this->parent->getPureSource();
        switch ($language){
        case "overview":
            $language = "";
            $menu->addMenuItem( "Overview", "index.html", "active", false );
            break;
        case "":
            $language = "";
            $menu->addMenuItem( "Overview", "index.html", "", false );
            break;
        default:
            $menu->addMenuItem( "Overview", "../index.html", "", false );
            break;
        }
        foreach ($this->parent->getLanguages() as $language_temp){
            if ("" == $language)
                $menu->addMenuItem($language_temp, $language_temp."/index.html", "", true);
            else{
                $class = ($language_temp == $language) ? "active" : "";
                $menu->addMenuItem($language_temp, "../". $language_temp."/index.html", $class, true );
            }
        }
        return $menu->getMarkup();
    }

    protected function getCustomChannelListSQL( $type, $encrypted = false, $customwhere, $columns ){
        if ($type == "TV")
            $type_where = "AND vpid != '0'";
        else if ($type == "Radio")
            $type_where = "AND vpid = '0' AND apid != '0'";
        else if ($type == "Data")
            $type_where = "AND vpid == '0' AND apid == '0'";
        else{
            $type = "";
            $type_where = "";
        }

        if ($encrypted)
            $caidflag = "!=";
        else
            $caidflag = "=";

        return "
                SELECT ".$columns." FROM channels WHERE source = ".$this->db->quote($this->parent->getSource())."
                AND caid $caidflag '0'
                ".$customwhere."
                ".$type_where."
                ORDER BY frequency, parameter, symbolrate, sid
        ";
    }

    protected function getWikipediaPageURL( $cpid ){
        $ids = uniqueIDTools::getInstance()->deregionalizeID( $cpid );
        $idlist = $this->db->quote( $cpid );
        if ($ids !== false){
          $ids2 = array();
          foreach ($ids as $id){
            $ids2[] = "cpid = " . $this->db->quote( $id );
          }
          $idlist = implode( ' OR ', $ids2);
        }
        $query = $this->db->query( "SELECT wikipedia_page_url FROM channel_meta_data WHERE ". $idlist );

        $result = $query->fetch(PDO::FETCH_ASSOC);
        if ($result !== false)
            $wurl = $result["wikipedia_page_url"];
        else
            $wurl = "";
        return $wurl;
    }

    protected function getChannelMetaInfo($ch, $chname){
        $cpid = $ch->getXCPID();
        $flag = $this->getFlagIcon( $ch );
        $newlyAdded = $ch->isNewlyAdded( self::NEWLY_ADDED_CHANNEL_TIMEFRAME ) ? ' <span class="newlyAdded">NEW</span>' : "";
        if ( $cpid !== ""){
            $classes = uniqueIDTools::getInstance()->getMatchingCSSClasses( $cpid, '_small');
            $wurl = $this->getWikipediaPageURL( $cpid );
            if ($wurl !== "")
                $wurl = '<a href="'. $wurl . '" target="_blank">'.$this->pageFragments->getWikipediaIcon( 'Look it up on Wikipedia', $this->parent->getRelPath() ).'</a>' . "\n";
        }
        else{
            $classes = "";
            $cpid = "No ID.";
            $wurl = "";
        }
        $chmetainfo =
            '<div class="channel_illustration '. $classes.
            '" title="'.$cpid.'">'.
            "</div>\n".
            '<div class="channel_details"><p><b>'. $chname . '</b><br/><br/>' .  $wurl.' '.$flag . $ch->getReadableServiceType() . ' ' .
            $this->pageFragments->getScrambledIcon( $ch->getCAID(), $this->parent->getRelPath() ) . ' ' .  $newlyAdded .
            "</p></div>\n";
        return $chmetainfo;
    }

    protected function collectStatisticsOfSource( $types, $encstatus ){
        $results = array();
        $total = 0;
        foreach ($types as $type){
            $results[$type] = array();
            $results[$type]["enc"] = array();
            $rowtotal = 0;
            foreach ($encstatus as $isencrypted){
                $results[$type]["enc"][$isencrypted] = $this->collectNumberOfChannelsOfSourceSection( $type, $isencrypted);
                $rowtotal += $results[$type]["enc"][$isencrypted];
            }
            $results[$type]["rowtotal"] = $rowtotal;
            $total += $rowtotal;
        }
        $results["total"] = $total;
        return $results;
    }

    private function collectNumberOfChannelsOfSourceSection( $type, $encrypted = false){
        $result = $this->db->query( $this->getCustomChannelListSQL( $type, $encrypted, "", "COUNT(*) AS number" ) );
        $info = $result->fetch(PDO::FETCH_ASSOC);
        return $info["number"];
    }

    protected function getPopupContent($curChan){
        return $curChan->getName(). " \n".
            ($curChan->isSatelliteSource() ?
                "Type: DVB-S"    . ( $curChan->onS2SatTransponder()   ? "2"        : ""           ) ." \n".
                "Polarisation: " . ( $curChan->belongsToSatVertical() ? "Vertical" : "Horizontal" ) ." \n".
                "Band: "         . ( $curChan->belongsToSatHighBand() ? "High"     : "Low"        ) ." \n".
                "FEC: "          . $curChan->getFECOfSatTransponder()                          ." \n"
            : "" ).
            "Modulation: "        . $curChan->getModulation() ." \n".
            "Frequency: "         . $curChan->getReadableFrequency() ." \n".
            "Symbolrate: "        . $curChan->getSymbolrate() ." \n".
            "Audio PID: "         . $curChan->getAudioPID() ." \n".
            "Video PID: "         . $curChan->getVideoPID() ." \n".
            "Teletext PID: "      . $curChan->getTeletextPID() ." \n".
            "Encryption state: "  . (($curChan->getCAID() == "0") ? "not encrypted" : "encrypted (" . $curChan->getCAID() . ")" )." \n".
            "SID: "               . $curChan->getSID() . " (Hex: " . str_pad(strtoupper(dechex($curChan->getSID())), 4, "0", STR_PAD_LEFT) . ") \n".
            "NID: "               . $curChan->getNID() ." \n".
            "TID: "               . $curChan->getTID() ." \n".
            "Date added: "        . date("D, d M Y H:i:s", $curChan->getXTimestampAdded() ) . " \n".
            "Date last changed: " . date("D, d M Y H:i:s", $curChan->getXLastChanged()    ) . " \n".
            "Date last seen: "    . date("D, d M Y H:i:s", $curChan->getXLastConfirmed()  ) . " \n".
            "CPID draft: "        . htmlspecialchars( $curChan->getXCPID() ) . " \n".
            "VDR internal ID: "   . htmlspecialchars( $curChan->getUniqueID() ) . "".
            "";
    }

    protected function getFlagIcon( $ch ){
        return $this->pageFragments->getFlagIcon( $ch->getXLabelRegion(), $this->parent->getRelPath());
    }
}
?>