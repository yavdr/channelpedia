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

class HTMLChangelog {

    private
        $db,
        $page;

    function __construct( $where, $limit, $importance) {
        $this->db = dbConnection::getInstance();
        $this->page = "";
        if ($importance === 1 ){
            $where[] = " importance = $importance ";
        }
        $wherestring = "";
        if (count($where) > 0){
            $wherestring = "WHERE ". implode(" AND ", $where);
        }
        $result = $this->db->query(
            "SELECT DATETIME( timestamp, 'unixepoch', 'localtime' ) AS datestamp, name, combined_id, importance, update_description ".
            "FROM channel_update_log $wherestring ORDER BY timestamp DESC".$limit
        );
        foreach ($result as $row) {
            $desclist = explode("\n", $row["update_description"]);
            $desc = "";
            foreach ($desclist as $descitem){
                $delimiter = strpos( $descitem, ":");
                $desc .= "<b>" .
                    htmlspecialchars( substr( $descitem,0, $delimiter)) . "</b>" .
                    htmlspecialchars( substr( $descitem, $delimiter)) . "<br/>";
            }
            $class = "changelog_row_style_".$row["importance"];
            $this->page .=
                '<div class="'.$class.'">'.
                "<p>At ". htmlspecialchars( $row["datestamp"] ). ", attributes of the channel called '<b>".
                htmlspecialchars( $row["name"] ). "</b>' (with the unique ID ".htmlspecialchars( $row["combined_id"] ).
                ') have changed: </p><pre class="changelog">'.
                $desc.
                "</pre></div>\n";
        }
    }

/* this is a new and better version but too slow on the sqlite side... maybe some sql tuning helps here

    function __construct( $where, $limit, $importance) {
        $this->db = dbConnection::getInstance();
        $this->page = "";
        $where[] = "combined_id = uniqueid";
        if ($importance === 1 ){
            $where[] = " log.importance = $importance ";
        }
        $wherestring = "";
        if (count($where) > 0){
            $wherestring = "WHERE ". implode(" AND ", $where);
        }
        $result = $this->db->query("
            SELECT
                 DATETIME( log.timestamp, 'unixepoch', 'localtime' ) AS datestamp,
                 log.name AS name,
                 update_description,
                 channels.x_label,
                 channels.source,
                 (channels.source || '-' || channels.sid  || '-' || channels.nid  || '-' || channels.tid) AS uniqueid
            FROM channel_update_log AS log
            JOIN channels
            $wherestring
            ORDER BY log.timestamp
            DESC".$limit
        );

        foreach ($result as $row) {
            $desclist = explode("\n", $row["update_description"]);
            $desc = "";
            foreach ($desclist as $descitem){
                $delimiter = strpos( $descitem, ":");
                $desc .= "<b>" .
                    htmlspecialchars( substr( $descitem,0, $delimiter)) . "</b>" .
                    htmlspecialchars( substr( $descitem, $delimiter)) . "<br/>";
            }
            //$class = "changelog_row_style_".$row["importance"];
            $this->page .=
                '<div>'. // class="'.$class.'">'.
                "<p>At ". htmlspecialchars( $row["datestamp"] ). ", attributes of the channel called '<b>".
                htmlspecialchars( $row["name"] ). "</b>' (with the source ".
                htmlspecialchars( $row["source"] ). " and group " .
                htmlspecialchars( $row["x_label"] ).
                ') have changed: </p><pre class="changelog">'.
                $desc.
                "</pre></div>\n";
        }
    }

*/
    public function getContents(){
        return $this->page;
    }
}
?>