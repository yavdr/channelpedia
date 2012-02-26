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
class uploadLog extends globalHTMLReportBase{

    public function popuplatePageBody(){
        $title = "Upload log";
        $this->setPageTitle( $title );
        $this->setDescription( "Chronological list of the most recent channel list uploads contributed by the Channelpedia community supporters" );
        $this->addBodyHeader();
        $this->appendToBody(
            "<table><tr><th>Timestamp</th><th>Channels.conf of user</th><th>Source</th><th>Description</th></tr>\n"
        );
        $result = $this->db->query(
            "SELECT DATETIME( timestamp, 'unixepoch', 'localtime' ) AS datestamp, user, description, source ".
            "FROM upload_log ORDER BY timestamp DESC LIMIT 100"
        );
        foreach ($result as $row) {
            $this->appendToBody(
                '<tr><td>'.
                htmlspecialchars( $row["datestamp"] ). "</td><td>".
                htmlspecialchars( substr($row["user"],0,2)."..." ). "</td><td>".
                htmlspecialchars( $row["source"] ). "</td><td>".
                htmlspecialchars( $row["description"] ). "</td>".
                "</tr>\n"
            );
        }
        $this->appendToBody( "<table>\n" );
        $this->addToOverviewAndSave($title, "upload_log.html");
    }
}
?>