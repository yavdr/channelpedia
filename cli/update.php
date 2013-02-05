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

$startime = time();

//input: reads channel.conf from path and put channels into db
require_once( dirname(__FILE__) . '/../classes/class.config.php');

ini_set("max_execution_time", 240); //safety buffer
$config = config::getInstance();

try {
    importFromAllChannelSources($config);
} catch (Exception $e) {
    $config->addToDebugLog( 'Caught exception: '. $e->getMessage() );
    $config->addToDebugLog( 'Backtrace: '. print_r( $e->getTrace(), true) );
    print "An exception occured.\n";
    if ( !array_key_exists('SERVER_SOFTWARE',$_SERVER)){
        print $e->getMessage()."\n";
        print_r( $e->getTrace(), false);
    }

}

//if ( array_key_exists('SERVER_SOFTWARE',$_SERVER)) print "</pre>";

$endtime = time();
$usedtime = $endtime  - $startime ;
print "Finished in time... (". $usedtime ." seconds)</br>\n";


function importFromAllChannelSources($config){

    //delete outdated logs to keep db small
    $db = dbConnection::getInstance();
    $twomonthsago = time() - (60 * 24 * 60 * 60);
    $query = $db->beginTransaction();
    $query = $db->exec( "DELETE FROM channel_update_log WHERE timestamp <= " . $twomonthsago );
    $query = $db->exec( "DELETE FROM upload_log WHERE timestamp <= " . $twomonthsago );
    $query = $db->commit();
    //update wikipedia metadata in sql database from file system
    $wikipedia_metadata = dirname(__FILE__) . '/../userdata/wikipedia_metadata.sql';
    if (file_exists( $wikipedia_metadata ))
        $query = $db->exec( file_get_contents( $wikipedia_metadata ) );

    $query = $db->exec( "VACUUM" );

    $dir = new DirectoryIterator( $config->getValue("userdata")."sources/" );
    foreach ($dir as $fileinfo) {
        if ( $fileinfo->isDir() && !$fileinfo->isDot()){
            $metaData = new channelImportMetaData( $fileinfo->getFilename() );
            $metaData->setForceReparsing( FORCE_REPARSING );
            $metaData->setDeleteOutdated( DELETE_OUTDATED );
            if ( $metaData->userNameExists()){
                $importer = new channelImport( $metaData );
                $importer->addToUpdateLog( "-", "Manually forced update: Checking for presence of unprocessed channels.conf to analyze.");
                $importer->insertChannelsConfIntoDB();
            }
            else
                print "user account for folder " . $fileinfo->getFilename() . " does not exist in global_user_data!\n";
        }
    }
    $labeller = channelGroupingManager::getInstance();
    $labeller->updateAllLabels(); //for the time being includes unique id update

    $x = new semanticDataManager();

    $x = new rawOutputRenderer();
    $x->writeRawOutputForAllSources();

    $x = new HTMLOutputRenderer();
    $x->renderAllHTMLPages();
}
?>