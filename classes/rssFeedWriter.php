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

spl_autoload_register(function($c) { @include_once strtr($c, '\\_', '//').'.php'; });
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__.'/lib/');

use \Suin\RSSWriter\Feed;
use \Suin\RSSWriter\Channel;
use \Suin\RSSWriter\Item;


class rssFeedWriter {

    private
        $folder = "",
        $html_filename = "",
        $source = "",
        $puresource = "",
        $config,
        $filehandle = null,
        $filename = "",
        $pageFragments,
        $iconPath = "http://channelpedia.yavdr.com/";

    function __construct( $type, $puresource){
        $this->pageFragments = HTMLFragments::getInstance();
        $this->puresource = $puresource;
        $this->config = config::getInstance();
        $visibletype = ($type == "A") ? "ATSC" : "DVB-". $type;
        if ($type !== "S")
            $source = $type . "[" . $puresource . "]";
        else
            $source = $puresource;
        $this->source = $source;
        $this->folder = $visibletype ."/". strtr(strtr( trim($puresource," _"), "/", ""),"_","/"). "/";
        $this->filename = "new_dvb_services.xml";
        $this->html_filename = "latest_channel_additions.html";
    }

    public function generateRSS(){
        $url_prefix = "http://channelpedia.yavdr.com/gen/";
        $feed = new Feed();
        $channel = new Channel();
        $channel
            ->title( "yaVDR Channelpedia: DVB service news for "  . $this->puresource )
            ->description( "" )
            ->url( $url_prefix . $this->folder )
            ->language('en-GB')
            ->copyright('Copyright 2015, yaVDR Channelpedia')
            ->pubDate( time() )
            ->lastBuildDate( time() )
            ->ttl(60 * 60 ) //hourly
            ->appendTo($feed);

        $x = new latestChannelsIterator();
        if ( $x->notEmptyForSource( $this->source )){

            while( $chunk = $x->getNextInfoChunk()){
                $amount = count( $chunk["content"] );
                if ( $amount > 1 )
                    $header = $amount . " new DVB services";
                else{
                    $name = trim( $chunk["content"][0]->getName());
                    $name = (strlen($name) > 1) ? " '" . $name . "'" : "";
                    $header = "New DVB service" . $name ;
                }
                $header .= " found on " . $this->puresource;
                $names = array();
                $strings = array();
                foreach ( $chunk["content"] as $currChan ){
                    $prov = trim( $currChan->getProvider() );
                    if ($prov != "" )
                        $prov = " (by " . $prov .  ")";
                    array_push ( $names,
                        $currChan->getName() . $prov . " " .
                        $this->getRegionFlagIcon( $currChan->getXLabelRegion() ) .
                        $this->pageFragments->getScrambledIcon( $currChan->getCAID(), $this->iconPath )
                    );
                    array_push ( $strings, $currChan->getChannelString() );
                }
                $desc = "<p>" . implode ( "<br/>" , $names ) . "</p>\n<pre>" . implode ( "\n" , $strings ) . "</pre><p>". date("D, d M Y H:i:s", $chunk["timestamp"] )."</p>";
                $url = $url_prefix . $this->folder. $this->html_filename . "#".  $chunk["timestamp"];

                $item = new Item();
                $item
                    ->title( $header )
                    ->description( $desc )
                    ->url( $url )
                    ->pubDate( $chunk["timestamp"] )
                    ->guid( $url , true)
                    ->appendTo($channel);
            }
        }
        file_put_contents( $this->config->getValue("exportfolder") . $this->folder . $this->filename, $feed );
    }

    private function getRegionFlagIcon( $region ) {
        if ($region !== "")
            $region = $this->pageFragments->getFlagIcon( $region, $this->iconPath );
        return $region;
    }
}