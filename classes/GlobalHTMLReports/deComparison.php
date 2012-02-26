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
class deComparison extends globalHTMLReportBase{

    public function popuplatePageBody(){
        $this->setPageTitle( "Comparison: Parameters of German public TV channels at different providers" );
        $this->setKeywords("german, deutsch, kanäle, vergleich");
        $this->setDescription("Allows to compare channel attributes of selected German TV channels. It should help to find similarities and differences.");
        $this->addBodyHeader();
        $x = new channelIterator( $shortenSource = false );
        $x->init2( "SELECT * FROM channels WHERE x_label LIKE 'de.%' AND lower(x_label) LIKE '%public%' ORDER by x_label ASC, lower(name) ASC, source ASC");
        $lastname = "";
        while ($x->moveToNextChannel() !== false){
            $carray = $x->getCurrentChannelObject()->getAsArray();
            if (strtolower($carray["name"]) != strtolower($lastname)){
                if ($lastname != ""){
                    $this->appendToBody( "</table>\n</div>\n");
                }
                $this->appendToBody( "<h2>".htmlspecialchars($carray["name"])."</h2>\n<h3>Table view</h3>\n<div class=\"tablecontainer\"><table>\n<tr>");
                foreach ($x->getCurrentChannelArrayKeys() as $header){
                    $this->appendToBody( '<th class="'.htmlspecialchars($header).'">'.htmlspecialchars(ucfirst($header))."</th>\n" );
                }
                $this->appendToBody( "</tr>\n" );
            }
            $this->appendToBody( "<tr>\n");
            foreach ($carray as $param => $value){
                if ($param == "apid" || $param == "caid"){
                    $value = str_replace ( array(",",";"), ",<br/>", htmlspecialchars($value ));
                }
                elseif ($param == "x_last_changed"){
                    $value = date("D, d M Y H:i:s", $value);
                }
                else
                    $value = htmlspecialchars($value);
                $this->appendToBody( '<td class="'.htmlspecialchars($param).'">'.$value."</td>\n");
            }
            $this->appendToBody( "</tr>\n" );
            $lastname = $carray["name"];
        }
        $this->appendToBody( "</table></div>\n" );
        $filename = "parameter_comparison_de.html";
        $this->addToOverviewAndSave( "de_Comparison: Parameters of German public TV channels at different providers", $filename );
    }
}
?>
