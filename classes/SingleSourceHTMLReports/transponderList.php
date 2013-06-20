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
class transponderList extends singleSourceHTMLReportBase{

    public function popuplatePageBody(){
        $this->setPageTitle( "Transponder list of ".$this->parent->getSource() );
        $this->setDescription("A list of all transponder frequencies found for  DVB source ". $this->parent->getPureSource());
        $this->addBodyHeader();

        $frequency = ($this->parent->getVisibleType() === "DVB-S") ? " frequency || ' ' || substr(parameter,1,1) AS frequency2 " : "frequency AS frequency2";

        $result = $this->db->query(
            "SELECT parameter, ".$frequency.", symbolrate, nid, tid
            FROM channels
            WHERE source = ".$this->db->quote($this->parent->getSource())."
            GROUP BY parameter, frequency2, nid, tid
            ORDER BY frequency2, parameter, nid, tid"
            );
        $this->appendToBody( "<table><tr><th>Frequency</th><th>Parameter</th><th>Symbolrate</th><th>NID</th><th>TID</th></tr>\n" );
        foreach ($result as $row) {
            if ( $this->parent->getVisibleType() === "DVB-S")
                $row["frequency2"] .= " DVB-S". (substr($row["parameter"], -1, 1) === "1" ? "2":"");
            $this->appendToBody( "<tr>".
                "<td>".htmlspecialchars($row["frequency2"])."</td>".
                "<td>".htmlspecialchars($row["parameter"])."</td>".
                "<td>".htmlspecialchars($row["symbolrate"])."</td>".
                "<td>".htmlspecialchars($row["nid"])."</td>".
                "<td>".htmlspecialchars($row["tid"])."</td>".
            "</tr>\n");
        }
        $this->appendToBody("</table>\n");
        $this->addToOverviewAndSave( "Transponders", "transponder_list.html");
    }
}

?>