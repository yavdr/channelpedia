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
class globalIndexPage extends globalHTMLReportBase{


    private function getLastUpdated(){
        return '';
    }

    public function popuplatePageBody(){
        $title = 'Overview';
        $this->setPageTitle( $title );
        $this->addBodyHeader();

        foreach ($this->parent->getHomepageLinkList() as $line){
            $title = $line[0];
            $sourceParts = explode("_", $title);
            if (count($sourceParts) > 1){
                $flag = $this->pageFragments->getFlagIcon($sourceParts[0], "");
                $title = $flag;
                foreach ($sourceParts as $part){
                    $title .= " " . $part;
                }
            }
           $url = $line[1];
           if($url == "")
               $this->appendToBody( '<li><b>'.htmlspecialchars( $title )."</b></li>\n<ul>");
           elseif($url == "close")
               $this->appendToBody( "<br clear=\"all\" /></ul>\n" );
           else
              $this->appendToBody( '<li><a href="'. urldecode( $url ) .'">'.$title ."</a></li>\n" );
        }

        $this->appendToBody( "<br clear=\"all\" /></ul>\n");
        $this->addToOverviewAndSave("","index.html");
    }
}
?>