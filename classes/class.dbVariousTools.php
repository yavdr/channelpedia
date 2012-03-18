<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010-2012 Henning Pingel
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

class dbVariousTools{

    private
        $db = null;

    private static $instance = null;

    protected function __construct(){
        $this->db = dbConnection::getInstance();
    }

    private function __clone(){}

    public static function getInstance(){
        if ( self::$instance == null){
            self::$instance = new dbVariousTools();
        }
        return self::$instance;
    }

    public function getLastConfirmedTimestamp( $source ){
        $timestamp = 0;
        $sqlquery = "SELECT x_last_confirmed FROM channels WHERE source = ".$this->db->quote($source)." ORDER BY x_last_confirmed DESC LIMIT 1";
        $result = $this->db->query($sqlquery);
        $timestamp_raw = $result->fetchAll();
        if (isset($timestamp_raw[0][0]))
            $timestamp = intval($timestamp_raw[0][0]);
//        else
        return $timestamp;
    }
}
?>