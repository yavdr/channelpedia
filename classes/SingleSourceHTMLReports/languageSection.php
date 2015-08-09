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
class languageSection extends singleSourceHTMLReportBase{

    private
        $language;

    function __construct( HTMLOutputRenderSource & $obj, $language ){
        $this->language = $language;
        parent::__construct( $obj );
    }

    public function popuplatePageBody(){
        $previewChannelLimit = 100;
        $this->setPageTitle( $this->parent->getSource() . " - Section " . $this->language );
        $this->addBodyHeader( $this->language );
        $this->appendToBody("");
        $nice_html_body = "";
        $nice_html_linklist = "";
        $groupIterator = new channelGroupIterator();
        $groupIterator->init($this->parent->getSource(), $this->language);
        while ($groupIterator->moveToNextChannelGroup() !== false){
            $cols = $groupIterator->getCurrentChannelGroupArray();
            $x = new channelIterator( $shortenSource = true);
            $x->init1($cols["x_label"], $this->parent->getSource(), "UPPER(name) ASC");
            $channelNameList = array();
            $channelMetaInfoList = array();
            $channelStringList = "";
            while ($x->moveToNextChannel() !== false){
                $curChan = $x->getCurrentChannelObject();
                $curChanString = htmlspecialchars($curChan->getChannelString());
                $channellogo = "";
                if (strlen($curChan->getName()) > 2 && substr($curChan->getName(),0,1) !== "."){
                    if (count($channelNameList) < $previewChannelLimit){
                        $channelNameSegments = explode(',', $curChan->getName());
                        $chname = htmlspecialchars( count($channelNameSegments) > 0 ? $channelNameSegments[0] : $curChan->getName() );
                        if ( $this->language === "de" || $this->language === "sky_de" || $this->language === "at" || $this->language === "ch"){
                            $channelMetaInfoList[]  = $this->getChannelMetaInfo( $curChan, $chname );
                        }
                        $channelNameList[] = $chname;
                    }
                    else if (count($channelNameList) === $previewChannelLimit)
                        $channelNameList[] = '... ';
                }
                $curChanString = $channellogo . $curChanString;
                //check if channel might be outdated, if so, apply additional css class
                $class = ( $curChan->getXLastConfirmed() < $this->parent->getLastConfirmedTimestamp() ) ? ' class="outdated"' : '';
                //$class = $curChan->isOutdated() ? ' class="outdated"' : '';
                $channelStringList .=
                    '<span title="'.$this->getPopupContent($curChan).'"'.$class.'>'. $curChanString ."</span>\n";
            }

            preg_match ( "/.*?\.\d*?\.(.*)/" , $cols["x_label"], $shortlabelparts );
            $shortlabel = (count($shortlabelparts) == 2) ? $shortlabelparts[1] : $cols["x_label"];
            $prestyle = (strstr($shortlabel, "FTA") === false  || strstr($shortlabel, "scrambled") !== false) ? 'scrambled' : 'fta';
            $icons = "";
            $icons .= (strstr($shortlabel, "FTA") === false  || strstr($shortlabel, "scrambled") !== false) ? ' <img src="'.$this->relPath.'../res/icons/lock.png" class="lock_icon" />' : '';
            $escaped_anchor = htmlspecialchars($shortlabel);
            $nice_html_body .=
                '<a name ="'.$escaped_anchor.'">'.
                '<h2 class="'.$prestyle.'">'.
                $escaped_anchor. " " . $icons . " (" . $cols["channelcount"] . ' channel'.($cols["channelcount"] !== 1 ? 's':'').')'.
                "</h2>\n";

            if ( $this->language === "de" || $this->language === "sky_de" ||  $this->language === "at" || $this->language === "ch"){
                $nice_html_body .=
                    '<div class="wikipedia_data '.$prestyle.'">'."\n" .
                    (count($channelMetaInfoList) > 0 ? '<div class="single_channel">'."\n".implode("</div>\n".'<div class="single_channel">',$channelMetaInfoList)."</div>\n":'').
                    '<br clear="all">'."\n".
                    "</div>\n";
            }

            $nice_html_body.=
                '<pre class="'.$prestyle.'">'.
                $channelStringList.
                "</pre>\n</a>\n";

                $separator = "/ ";
                $nice_html_linklist .=
                    '<li><a href="#'.$escaped_anchor.'"><span class="anchorlist_groupname '.$prestyle.'">'.$escaped_anchor. " " . $icons . "</span><br/>".
                    (count($channelNameList) > 0 ? '<span class="anchorlist_channelnames"> <span class="single">'.implode('</span> '.$separator.'<span class="single">',$channelNameList).'</span></span></span>':'').
                    '</a></li><br clear="all">'."\n";
        }
        $this->appendToBody(
            "<h2>Groups Overview</h2>\n<div class=\"group_anchors\"><ul class=\"group_anchors\">\n" .
            $nice_html_linklist . "</ul></div>\n"
        );
        $this->appendToBody(
            $nice_html_body
        );
        $this->addToOverviewAndSave( "", "index.html");
    }


/*
 *
                if ($html_table == ""){
                    $html_table = "<h3>Table view</h3>\n<div class=\"tablecontainer\"><table class=\"nice_table\">\n<tr>";
                    foreach ($x->getCurrentChannelArrayKeys() as $header){
                        $html_table .= '<th class="'.htmlspecialchars($header).'">'.htmlspecialchars(ucfirst($header))."</th>\n";
                    }
                    $html_table .= "</tr>\n";
                }

                $html_table .= '<tr class="'.$prestyle.'">'."\n";
                //FIXME use channel object here
                foreach ($curChan->getAsArray() as $param => $value){
                    switch ($param){
                        case "apid":
                        case "caid":
                            $value = str_replace ( array(",",";"), ",<br/>", htmlspecialchars($value ));
                            break;
                        case "frequency":
                            $sourcetype = substr($this->parent->getSource(),0,1);
                            if ($sourcetype == "S")
                                $value = $value." MHz";
                            else{
//    * MHz, kHz oder Hz angegeben.
//Der angegebene Wert wird mit 1000 multipliziert, bis er größer als 1000000 ist.
                                 $value2 = intval($value);
                                 $step = 0;    //113000
                                 while($value2 < 1000000){
                                     $step++;
                                     $value2 = $value2 * 1000;
                                 }
                                 $value = $value2 / (1000*1000);
                                 $value = $value . " Mhz";
                            }
                            break;
                        case "x_last_changed":
                            $value = date("D, d M Y H:i:s", $value);
                            break;
                        default:
                            $value = htmlspecialchars($value);
                    }
                    $html_table .= '<td class="'.htmlspecialchars($param).'">'.$value."</td>\n";
                }
                $html_table .= "</tr>\n";*/

}
?>