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

//TODO: autoload these rule files
require_once PATH_TO_CLASSES . '../grouping_rules/base/class.ruleBase.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.GermanyEssentials.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.GermanySatNonEssentials.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.GermanySky.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.GermanyKabelBW.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.GermanyKabelBWSwiss.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.GermanyKabelBWFrench.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.GermanyKabelDeutschland.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.GermanyWilhelmTel.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.GermanyUnityMedia.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.GermanyTeleColumbus.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.ScotlandEssentials.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.WalesEssentials.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.NorthernIrelandEssentials.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.UKEssentials.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.IrelandEssentials.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.AustriaSatEssentials.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.SwitzerlandSatEssentials.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.SpainSatEssentials.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.PolandSatEssentials.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.FranceSatEssentials.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.NetherlandsSatEssentials.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.BelgiumSatEssentials.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.ItalyEssentials.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.GreeceSatEssentials.php';
require_once PATH_TO_CLASSES . '../grouping_rules/class.Uncategorized.php';

define("HD_CHANNEL"," ( name LIKE '% HD%' OR name LIKE 'HD %' ) ");
// OR UPPER(parameter) LIKE '%S1%'

define("DE_PRIVATE_PRO7_RTL"," (".
    "provider = 'ProSiebenSat.1' OR ".
    "provider = 'Pro7 & Sat.1' OR ".
    "provider = 'RTL World' OR ".
    "provider = 'RTL' OR ".
    "provider = 'CBC' OR ".
	"provider = 'MTV Networks'".
") ");

define("DE_PROVIDER_ARD", "(".
    "provider LIKE 'ARD%' OR ".
    "provider = 'SWR' OR ".
    "provider = 'BR' OR ".
    "provider = 'HR' OR ".
    "provider = 'NDR'".
") ");

define("DE_PUBLIC_PROVIDER", " (".DE_PROVIDER_ARD." OR provider LIKE 'ZDF%') ");

define("DE_PUBLIC_REGIONAL", " (".
    DE_PROVIDER_ARD." AND
    (name LIKE 'Bayerisches%' OR
    name LIKE 'BR%' OR
    name LIKE 'hr-%' OR
    name LIKE 'MDR%' OR
    name LIKE 'NDR%' OR
    name LIKE 'Radio Bremen%' OR
    name LIKE 'rbb%' OR
    name LIKE 'SR%' OR
    name LIKE 'SWR%' OR
    name LIKE 'WDR%')) "
);


define("AUSTRIA", " (".
    " LOWER(name) LIKE '%sterreich' OR".
    " LOWER(name) LIKE '%austria%' OR".
    " UPPER(name) LIKE '% A' OR".
    " UPPER(name) LIKE '% AUT' OR".
    " UPPER(name) LIKE '%TIROL%' OR".
    " LOWER(provider) LIKE '%sterreich' OR".
    " UPPER(provider)='AUSTRIA' OR".
    " UPPER(provider) = '-' OR".
    " UPPER(provider)='ORF' OR".
    " UPPER(provider) = 'ORS' OR".
    " UPPER(provider) LIKE 'ATV%'".
") ");

define("SWITZERLAND",       " (UPPER(name) LIKE '% CH' OR UPPER(name) LIKE '% CH %' OR LOWER(name) LIKE '% Schweiz' OR UPPER(name) LIKE 'SF%') ");
define("FRANCE_CSAT",       " (upper(provider)='CSAT') ");
define("SPAIN_DIGITALPLUS", " (UPPER(provider) = 'DIGITAL +' OR UPPER(provider) = 'DIGITAL+') ");
define("NL_PROVIDERS",      " (UPPER(provider) = 'CANALDIGITAAL' OR UPPER(provider) = 'CANAALDIGITAAL') ");


define("FILTER_ASTRA1_FTA", " ((tid != '1092' AND tid != '1113' AND provider != '-') OR (name = 'DMAX')) AND provider != 'SKY' ".
                    " AND NOT (".
                    " UPPER(name) = '.' OR ".
                    " UPPER(name) LIKE '%CHAT%' OR ".
                    " UPPER(name) LIKE '%SEX%' OR ".
                    " UPPER(name) LIKE '%GIRL%' OR ".
                    " UPPER(name) LIKE '%BABE%' OR ".
                    " UPPER(name) LIKE '%DAMEN%' OR ".
                    " UPPER(name) LIKE '%DATE%' OR ".
                    " UPPER(name) LIKE '%DATING%' OR ".
                    " UPPER(name) LIKE '%MAENNER%' OR ".
                    " UPPER(name) LIKE '%BOYS%' OR ".
                    " UPPER(name) LIKE '%BUNNY%' OR ".
                    " UPPER(name) LIKE '%BIZARR%' OR ".
                    " UPPER(name) LIKE '%KONTAKT%' OR ".
                    " UPPER(name) LIKE '%VENUS%' OR ".
                    " UPPER(name) LIKE '%VOYEUR%' OR ".
                    " UPPER(name) LIKE '%HEISS%' OR ".
                    " UPPER(name) LIKE '%HOT%' OR ".
                    " UPPER(name) LIKE '%P*RN%' OR ".
                    " UPPER(name) LIKE '%KAMASU%' OR ".
                    " UPPER(name) LIKE '%ERO%' OR ".
                    " UPPER(name) LIKE '%FLIRT%' OR ".
                    " UPPER(name) LIKE '%LUST%' OR ".
                    " UPPER(name) LIKE '%LIEB%' OR ".
                    " UPPER(name) LIKE '%LOVE%' OR ".
                    " UPPER(name) LIKE '%GL_CK%' OR ".
                    " UPPER(name) LIKE '%FRIEND%' OR ".
                    " UPPER(name) LIKE '%GEF_HL%' OR ".
                    " UPPER(name) LIKE '%PARTNER%' OR ".
                    " UPPER(name) LIKE '%SINGLE%' OR ".
                    " UPPER(name) LIKE '%HANDY%' OR ".
                    " UPPER(name) LIKE '%AMORE%'".
                    " )"
);

class channelGroupingManager{

    private
        $groupinglog,
         $db,
         $config,
         $rulesets;

    private static $instance = null;

    private function __clone(){}

    public static function getInstance(){
        if ( self::$instance == null){
            self::$instance = new channelGroupingManager();
        }
        return self::$instance;
    }

    protected function __construct(){
        $this->config = config::getInstance();
        $this->db = dbConnection::getInstance();
        $debuglogfile = $this->config->getValue("userdata")."groupinglog.txt";
        $this->groupinglog = fopen( $debuglogfile, "w");
        $this->addToGroupingLog("Grouping-session started.");
        $this->db->getDBHandle()->sqliteCreateFunction('getcpid', 'global_convertChannelNameToCPID', 2);
        $this->rulesets = array(

            "AustriaSatEssentials"     => new AustriaSatEssentials(),
            "SwitzerlandSatEssentials" => new SwitzerlandSatEssentials(),
            "GermanySky"               => new GermanySky(),
            "GermanyEssentials"        => new GermanyEssentials(),
            "GermanySatNonEssential"   => new GermanySatNonEssentials(),

            "GermanyKabelBWFrench"     => new GermanyKabelBWFrench(),
            "GermanyKabelBWSwiss"      => new GermanyKabelBWSwiss(),
            "GermanyKabelBW"           => new GermanyKabelBW(),

            "GermanyKabelDeutschland"  => new GermanyKabelDeutschland(),
            "GermanyWilhelmTel"        => new GermanyWilhelmTel(),
            "GermanyUnityMedia"        => new GermanyUnityMedia(),
            "GermanyTeleColumbus"      => new GermanyTeleColumbus(),

            "ScotlandEssentials"       => new ScotlandEssentials(),
            "WalesEssentials"          => new WalesEssentials(),
            "NorthernIrelandEssentials"=> new NorthernIrelandEssentials(),
            "IrelandEssentials"        => new IrelandEssentials(),
            "UKIrelandEssentials"      => new UKEssentials(),

            "SpainSatEssentials"       => new SpainSatEssentials(),
            "PolandSatEssentials"      => new PolandSatEssentials(),
            "FranceSatEssentials"      => new FranceSatEssentials(),
            "NetherlandsSatEssentials" => new NetherlandsSatEssentials(),
            "BelgiumSatEssentials"     => new BelgiumSatEssentials(),
            "ItalyEssentials"          => new ItalyEssentials(),
            "GreeceSatEssentials"      => new GreeceSatEssentials(),

            //last not least uncategorized
            "uncategorized"            => new Uncategorized(),
        );
    }

    private function addToGroupingLog( $line ){
        fputs( $this->groupinglog, date(DATE_ATOM, time()) . " " . $line ."\n");
    }

    public function updateAllLabels(){
        $this->addToGroupingLog("Update of all labels starting...");
        foreach ($this->config->getValue("sat_positions") as $sat => $languages){
            $this->updateAllLabelsOfSource($sat, $languages);
        }
        foreach ($this->config->getValue("cable_providers") as $cablep => $languages){
            $this->updateAllLabelsOfSource("C[$cablep]", $languages);
        }
        foreach ($this->config->getValue("terr_providers") as $terrp => $languages){
            $this->updateAllLabelsOfSource("T[$terrp]", $languages);
        }

        //cpid stuff
        $result = $this->db->query("UPDATE channels SET x_xmltv_id = ''" );
        $sqlquery = "UPDATE channels SET x_xmltv_id = getcpid( lower(name), x_label )
            WHERE
                ( x_label LIKE 'de.%' OR (x_label LIKE 'sky_de.%' AND x_label NOT LIKE '%FEED%') OR x_label LIKE 'at.%' OR x_label LIKE 'ch.%' 
                 OR (x_label LIKE 'uk.%' AND x_label LIKE '%freesat%' AND x_label LIKE '%FTA%'))
                AND x_label NOT LIKE '%uncategorized%'
                AND name NOT LIKE '.%'
                AND name NOT LIKE '%*'
                AND name NOT LIKE '%test%'
                AND name NOT LIKE '%\_alt'
        ";
        $this->addToGroupingLog( "Now updating cpids" );
        $result = $this->db->query($sqlquery);
        $this->addToGroupingLog( "Now finished updating cpids" );
    }

    public function updateAllLabelsOfSource( $source, $languages ){
        $this->addToGroupingLog( "Updating labels for channels belonging to $source." );
        //reset all labels in DB to empty strings before updating them
        $temp = $this->db->query("UPDATE channels SET x_label='' WHERE source = ".$this->db->quote($source));
        $query = $this->db->beginTransaction();
        $sourcetype = substr($source, 0, 1);
        foreach ( $this->rulesets as $title => $object){
            $config = $object->getConfig();
            if ($config["lang"] === "uncategorized") $object->setSource($source);
            switch ($sourcetype){
                case "S":
                    $validFor = "validForSatellites";
                    break;
                case "C":
                    $validFor = "validForCableProviders";
                    break;
                case "T":
                    $validFor = "validForTerrProviders";
                    break;
                case "A":
                    $validFor = "validForATSCProviders";
                    throw new Exception("updateAllLabelsOfSource: sourcetype ATSC not yet fully implemented");
                    break;
                default:
                    throw new Exception("updateAllLabelsOfSource: invalid sourcetype ");
                    break;
            }
            if (
                ($config[$validFor] === "all" && ( $config["lang"] == "uncategorized" || in_array( $config["country"], $languages ))) ||
                ( is_array( $config[$validFor] ) && in_array( $source, $config[$validFor], true))
            ){
                foreach ($object->getGroups() as $groupsettings){
                    $this->updateLabelsOfChannelSelection(
                        $label = $config["country"] . ".". str_pad($groupsettings["outputSortPriority"], 3, "0", STR_PAD_LEFT) . "." . $groupsettings["title"],
                        $source,
                        $outputSortPriority = $groupsettings["outputSortPriority"],
                        $caidMode           = $groupsettings["caidMode"],
                        $mediaType          = $groupsettings["mediaType"],
                        $language           = array_key_exists ("languageOverrule",$groupsettings) ? $groupsettings["languageOverrule"] : $config["lang"],
                        $customwhere        = $groupsettings["customwhere"],
                        $title
                    );
                }
            }
        }
        $query = $this->db->commit();
    }

    /*
     * tags specific channels in the db
     *
     * label (string, used in file name of newly generated channels file, use it to distinguish between different channels files)
     * source (string, satellite position, cable, terrestial, empty string means: show all. Example: "S28.2E", "S19.2E", "C", no lists allowed)
     *
     * caidMode
     *     0 = show all CAIDs including FTA,
     *     1 = show only channels FTA channels,
     *     2 = show only encrypted channels)
     *
     * mediaType
     *     0 = show all media types (radio + tv + other stuff),
     *     1 = show only TV channels (both SDTV + HDTV),
     *     2 = show only radio channels,
     *     3 = show only SDTV channels,
     *     4 = show only HDTV channel
     *
     * language (string with comma separated list of languages that should be displayed, empty string means all languages)
     */

    private function updateLabelsOfChannelSelection(
        $label,
        $source = "",
        $outputSortPriority = 0,
        $caidMode = 0,
        $mediaType = 0,
        $language = "",
        $customwhere = "",
        $title = ""
    ){
        $label_suffixes = array();
        $where = array();

        if ($source != "")
            $where[] = "source = ". $this->db->quote( $source );

        switch ($mediaType) {
            case 0:
                $label_suffixes[] = "TV+Radio";
                break;
            case 1:
                $where[] = "vpid != '0'";
                $label_suffixes[] ="TV";
                break;
            case 2:
                $where[] = "vpid = '0' AND apid != '0'";
                $label_suffixes[] ="Radio";
                break;
            case 3:
                $where[] = "vpid != '0'";
                $where[] = "NOT " . HD_CHANNEL;
                $label_suffixes[] ="SDTV";
                break;
            case 4:
                $where[] = "vpid != '0'";
                $where[] = HD_CHANNEL;
                $label_suffixes[] ="HDTV";
                break;
            case 5:
                $where[] = "vpid == '0' AND apid == '0'";
                $label_suffixes[] ="Data";
                break;
            case 6:
                $where[] = "vpid != '0' AND parameter LIKE '%S1' AND source LIKE 'S%'";
                $label_suffixes[] ="TV@DVBS2";
                break;
        }

        if ($caidMode != 0){
            $where[] = "caid ". ($caidMode === 2 ? "!= '0'" : "= '0'");
            $label_suffixes[] = ($caidMode === 2 ? "scrambled" : "FTA");
        }
        else{
            $label_suffixes[] = "scrambled+FTA";
        }

        if (count($label_suffixes) > 0){
            //$label = $label . " <div class=\"box\">".implode("</div><div class=\"box\">",$label_suffixes)."</div>";
            $label = $label . " ".implode(" ",$label_suffixes)."";
        }

        if ($language !== ""){
            $languages = explode( ",", strtoupper($language) );
            $where_lang = array();
            foreach( $languages as $curLanguage){
                $where_lang[] = "UPPER(apid) LIKE '%".trim($curLanguage)."%'";
            }
            $where[] = "( " . implode( " OR ", $where_lang) . " )";
        }

        //update label tag in the selected channels
        if (count($where) > 0){
            $where  = "WHERE " . implode( $where, " AND " ) . $customwhere;
            $where2 = $where . " AND ";
            $where .= " AND x_label = ''";
        }
        else {
            $where .= "WHERE x_label = ''";
            $where2 = "WHERE ";
        }

        //this should only be written to grouping logfile in verbose mode
        /*
        if (substr($label,0, 13) !== "uncategorized"){
            $sqlquery = "SELECT * FROM channels $where2 x_label != '' AND x_label != ". $this->db->quote($label);
            $result = $this->db->query($sqlquery);
            foreach ($result as $row){
                $this->addToGroupingLog( "Notice: Channel '".$row["name"]."' is already tagged with '".$row["x_label"]."'. We just tried to tag it with '$label'" );
            }
        }*/

        //now only update channels with EMPTY x_label field!
        $sqlquery = "UPDATE channels SET x_label=". $this->db->quote($label) ." $where";
        $result = $this->db->query($sqlquery);
        $this->addToGroupingLog( "Updating labels for channels belonging to $title / $source / $label." );
        $this->addToGroupingLog( "Query: $sqlquery" );
    }
}

?>
