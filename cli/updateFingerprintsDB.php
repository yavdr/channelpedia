<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Gerald Dachs and Henning Pingel
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

die("disabled");

ini_set("max_execution_time", 120);

require_once '../classes/class.config.php';

$x = new updateFingerprintDB();

class updateFingerprintDB {

    private
        $config,
        $dbh = null;

    public function __construct(){
        $startime = time();
        $this->config = config::getInstance();
        $channeldbfile = $this->config->getValue("userdata") . "channeldb.sqlite";
        $fpdb = $this->config->getValue("exportfolder") . "raw/fingerprintdb.sqlite";
        if (file_exists($fpdb)) @unlink( $fpdb );
        $this->dbh = new PDO( 'sqlite:'. $fpdb );

        $this->exec("ATTACH DATABASE '".$channeldbfile."' AS chandb;");
        $this->dbh->beginTransaction();
        $this->exec("DROP TABLE IF EXISTS fingerprints;");
        $this->exec("CREATE TABLE fingerprints (
          source TEXT,
          nid INTEGER,
          frequency INTEGER,
          symbolrate INTEGER
        );");
        $this->exec("DROP TABLE IF EXISTS temp_channels;");
        $this->exec("CREATE TABLE temp_channels (
          source TEXT,
          nid INTEGER,
          frequency INTEGER,
          symbolrate INTEGER
        );");
        $this->exec("
        INSERT INTO temp_channels
        SELECT source, nid, frequency, symbolrate
         FROM chandb.channels c1
         WHERE NOT EXISTS (SELECT * FROM chandb.channels c2 WHERE c1.frequency = c2.frequency AND c1.nid = c2.nid AND c1.symbolrate = c2.symbolrate AND c1.source <> c2.source)
         AND parameter NOT LIKE '%H%'
         AND parameter NOT LIKE '%S1%'
         GROUP BY source, nid, frequency, symbolrate;
        ");
        $this->exec("INSERT INTO fingerprints
        SELECT *
        FROM temp_channels t1
        WHERE t1.frequency = (
          SELECT frequency
          FROM temp_channels t2
          WHERE t2.frequency IN (
            SELECT DISTINCT frequency
            FROM temp_channels t3
            WHERE t3.source=t1.source
          )
          GROUP BY t2.frequency
          ORDER BY COUNT(DISTINCT t2.source) DESC
          LIMIT 1
        );");
        $this->dbh->commit();
        //FIXME: find workaround for problematic drop...
        /*$this->exec("BEGIN EXCLUSIVE TRANSACTION;");
        $this->exec("DROP TABLE temp_channels;");
        $this->exec("COMMIT TRANSACTION;");*/
        $endtime = time();
        $usedtime = $endtime  - $startime ;
        print "Finished in time... (". $usedtime ." seconds)</br>\n";
        //print "Memory used: emalloc: ". memory_get_usage(false) . " bytes / real: " . memory_get_usage( true) . " bytes\n<br/>";
        print "Memory usage peak: emalloc: ". memory_get_peak_usage(false) . " bytes / real: " . memory_get_peak_usage( true) . " bytes\n<br/>";
    }

    private function exec( $statement ){
        $result = $this->dbh->exec( $statement );
        if ($result === false){
            $errorinfo = $this->dbh->errorInfo();
            $errorcode =  $errorinfo[1];
            //print "<pre>ERROR: ". htmlspecialchars($statement) . "</br>". print_r($this->dbh->errorInfo(), true) . "</pre>";
        }
        else{
            //print "<pre>OK: ". htmlspecialchars($statement) . "</pre>";
        }
    }

};
?>