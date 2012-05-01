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
class uniqueID2 extends globalHTMLReportBase{

    public function popuplatePageBody(){
        $this->setPageTitle( "Draft for unique IDs" );
        $this->setKeywords("german, deutsch, kanäle, vergleich");
        $this->setDescription("Attempt to auto-create matchable unique IDs.");
        $this->addBodyHeader();
        $this->appendToBody( "<h2>bla</h2>" );

        $divider = ",/,/,";

        $result = $this->db->query("
            SELECT
                x_xmltv_id AS cpid,
                count(nid) AS x_sum,
                group_concat( name, '".$divider."') AS original_names,
                group_concat( lower(provider), '".$divider."') AS matching_providers,
                (sid  || '-' || nid  || '-' || tid) AS sidnidtid,
                group_concat( source, '".$divider."') AS matching_sources,
                x_label
            FROM channels
            WHERE
                x_xmltv_id != '' AND
                ( x_label LIKE 'de.%' OR x_label LIKE 'at.%' OR x_label LIKE 'ch.%' )
                AND x_label NOT LIKE '%uncategorized%'
                AND x_label NOT LIKE 'de.024.sky_de%'
            GROUP BY
                x_label, sidnidtid, x_xmltv_id
            ORDER BY
                x_xmltv_id ASC,
                x_sum DESC
        ");
        $lastid = "";
        foreach ($result as $row) {
            $original_name_array = array_unique( explode( $divider, $row["original_names"] ) );
            $matching_name_array = array_unique( explode( $divider, $row["matching_providers"]) );
            $sources_array = explode( $divider, $row["matching_sources"]);
            if ( $row["cpid"] !== $lastid ){
                $this->appendToBody(
                    '<hr/>
                    <p><b>'. $row["cpid"] . '</b></p>
                    <div class="channel_illustration '. uniqueIDTools::getInstance()->getMatchingCSSClasses( $row["cpid"], '_small'). '"></div>'
                );
            }
            $this->appendToBody(
                '<p>' . $row["sidnidtid"]. '</p><ul class="uidhelper">'."\n".
                '<li>found '.$row["x_sum"]. " time(s) in channelpedias database</li>\n".
                '<li>Original names:<ul><li>'. implode('</li><li>', $original_name_array ) ."</li></ul></li>\n".
                '<li>Matching sources:<ul><li>'. implode('</li><li>', $sources_array ) ."</li></ul></li></ul>\n"
            );
            $lastid = $row["cpid"];
        }
        $filename = "de_uniqueIDs2.html";
        $this->addToOverviewAndSave( "de_uniqueIDs2", $filename );
    }

}