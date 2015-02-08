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
        $puresource = "",
        $config,
        $filehandle = null,
        $filename = "";

    function __construct( $type, $puresource){
        $this->puresource = $puresource;
        $this->config = config::getInstance();
        $visibletype = ($type == "A") ? "ATSC" : "DVB-". $type;
        if ($type !== "S")
            $source = $type . "[" . $puresource . "]";
        else
            $source = $puresource;
        $this->folder = $visibletype ."/". strtr(strtr( trim($puresource," _"), "/", ""),"_","/"). "/";
        $this->filename = "new_dvb_services.xml";
        $this->html_filename = "latest_channel_additions.html";
    }

    public function generateRSS(){
        $url_prefix = "http://channelpedia.yavdr.com/gen/";
        $feed = new Feed();
        $channel = new Channel();
        $channel
            ->title( "Channelpedia: New DVB services on "  . $this->puresource )
            ->description( "" )
            ->url( $url_prefix . $this->folder )
            ->language('en-GB')
            ->copyright('Copyright 2015, hepi')
            ->pubDate( time() )
            ->lastBuildDate( time() )
            ->ttl(60)
            ->appendTo($feed);

        $x = new latestChannelsIterator();
        if ( $x->notEmptyForSource( $this->puresource )){
            while ($x->moveToNextChannel() !== false){
                $currChan = $x->getCurrentChannelObject();
                $desc =
                    "<p><b>". $this->getRegionFlagIcon( $currChan->getXLabelRegion() ) . $currChan->getName() . "</b> ".
                    "(added on " . date("D, d M Y H:i:s", $currChan->getXTimestampAdded()) . ")</p>".
                    "<pre>". $currChan->getChannelString() ."</pre>\n"
                ;
                $url = $url_prefix . $this->folder. $this->html_filename . "#".  $currChan->getLongUniqueID();
                $item = new Item();
                $item
                    ->title( "Channelpedia: New DVB service '" . $currChan->getName() . "' on " . $this->puresource )
                    ->description( $desc )
                    ->url( $url )
                    ->pubDate( $currChan->getXTimestampAdded() )
                    ->guid( $url , true)
                    ->appendTo($channel);
            }
        }
        file_put_contents( $this->config->getValue("exportfolder") . $this->folder . $this->filename, $feed );
    }

    private function getRegionFlagIcon( $region ) {
        if ($region !== "")
            //$region = $this->pageFragments->getFlagIcon( $region, $this->parent->getRelPath() );
        return $region;
    }
}