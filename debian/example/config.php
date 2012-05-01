<?php

/*
 *  CUSTOM_PATH (path to parent folder of gen and userdata)
 *  Default: "" (in this case gen and userdata are
 *               located in web root)
 *
 * This is the path to the folder
 * where the following subfolders are located:
 *
 *     a) gen (generated HTML and raw channel files)
 *     b) userdata (sqlite database file + upload folders
 *        for users uploading their channel.conf's
 *
 * What's special about these sub-folders is that they
 * contain dynamic content that is regularly changed
 * by user uploads and the channelpedia rendering engine.
 * Therefore those folders need to be writeable for the
 * web server user (usually www-data).
 *
 * Normally, if used on shared webspace, there is no
 * better choice as to have these folders in the web root
 * of channelpedia together with all the other folders
 * like classes, config, etc.).
 *
 * Security remark:
 * The folders which don't need be publicly accessible
 * are protected with a .htaccess files denying access via HTTP.
 * This only works with Apache. Please check that these
 * precautions work on your installation!
 *
 * If you use channelpedia on a dedicated web server where
 * you have the freedom to set everything up in the way you want,
 * you can take several folders out of the public htdocs folder
 * for enhanced security. Folders that don't necessarily need to
 * be exposed worldwide by putting them into htdocs are:
 * classes, cli, config, epg_mappings, grouping_rules and
 * userdata. A number of require_once statements need to be
 * tweaked then to make channelpedia work again...
 *
 * If you use channelpedia locally and without a HTTP server
 * from the command line (PHP CLI), you may want to have
 * a different path for convenience reasons.
 */

define("CUSTOM_PATH", "/var/lib/vdr-channelpedia/");

//path to channelpedia engine (classes, grouping_rules, cli, etc.)
define("ENGINE_PATH", "/usr/share/vdr-channelpedia/");


define("PIWIK_TRACKING_ENABLED", false);
//define("PIWIK_TRACKING_REMOTE_URL", "");
//define("PIWIK_TRACKING_IDSITE", 0);
//define("PIWIK_TRACKING_AUTH_TOKEN", "");


/* on calling cli/update.php, all channels.conf.old files that were already parsed
 * will be reparsed if FORCE_REPARSING is set to true
 */
define("FORCE_REPARSING", false);
define("DELETE_OUTDATED", false);
define("CUT_OFF_INDEX_HTML", false); //only set to true if serving rendered pages via HTTP, beautyfies urls



$default_lang_de_cable_provider = array("de");

$global_sat_long_names = array(
        "S1W"  => array(
            "name" => "Intelsat 10-02, Thor 5, Thor 6 (0.8°W)",
            "descr" => ""
        ),
        "S4.8E"  => array(
            "name" => "Astra 4A",
            "descr" => ""
        ),
        "S9E"    => array(
            "name" => "Eutelsat 9A",
            "descr" => ""
        ),
        "S13E"   => array(
            "name" => "Hot Bird 13A, Hot Bird 13B, Hot Bird 13C",
            "descr" => ""
        ),
        "S19.2E" => array(
            "name" => "Astra 1H, Astra 1KR, Astra 1L, Astra 1M, Astra 2C",
            "descr" => ""
        ),
        "S26E"   => array(
            "name" => "Badr 4, Badr 5, Badr 6",
            "descr" => ""
        ),
        "S28.2E" => array(
            "name" => "Astra 1N, Astra 2A, Astra 2B, Astra 2D, Eutelsat 28A (28.5°)",
            "descr" => ""
        ),
        "S36E"   => array(
            "name" => "Eutelsat 36A, Eutelsat 36B",
            "descr" => ""
        ),
    );

/*
 *  $global_source_config
 *
 *  associative array
 *  Lists all sources that are valid within this channelpedia.
 *  Grouped by source type (DVB-S, DVB-T, DVB-C).
 *  Order of providers determines the order of providers in the HTML menu output.
 *  For each source the available language/region groups are also being
 *  specified. Each language group must exist in the grouping_rules classes.
 *  Even if no languages are assigned, "uncategorized" will always be added
 *  automatically as a group for the remaining ungrouped channels.
 */

$global_source_config = array(
    "DVB-S" => array(

        //"S9E"    => array(),
        //"S13E"   => array( "gr", "it", "pl" ), //"de",
        "S19.2E" => array( "de", "at", "ch", "es", "fr", "pl", "nl", "be" ),
        //"S26E"   => array(),
        //"S28.2E" => array( "uk", "ie", "northern_ireland", "scotland", "wales" ),

    ),
    "DVB-C" => array(
/*
        "de_KabelBW_Heidelberg"         => $default_lang_de_cable_provider,
        "de_KabelDeutschland_Flensburg" => $default_lang_de_cable_provider,
        "de_KabelDeutschland_Muenchen"  => $default_lang_de_cable_provider,
        "de_KabelDeutschland_Nuernberg" => $default_lang_de_cable_provider,
        "de_KabelDeutschland_Speyer"    => $default_lang_de_cable_provider,
        "de_Primacom_Halberstadt"       => $default_lang_de_cable_provider,
        "de_TeleColumbus_Magdeburg"     => $default_lang_de_cable_provider,
        "de_UnityMediaNRW"              => $default_lang_de_cable_provider,
        "de_WilhelmTel"                 => $default_lang_de_cable_provider,
        "at_salzburg-ag"                => $default_lang_de_cable_provider
*/
    ),
    "DVB-T" => array(
/*
        "at_Linz"                       => array(),
        "at_Wien"                       => array(),
        "de_Flensburg"                  => $default_lang_de_cable_provider,
        "de_Heidelberg"                 => $default_lang_de_cable_provider,
        "de_Muenchen"                   => $default_lang_de_cable_provider,
        "de_NordrheinWestfalen"         => $default_lang_de_cable_provider,
        "dk_Tondern"                    => $default_lang_de_cable_provider,
*/
    )
);

define("SOURCETYPE_INACTIVE", "none");

$global_user_config = array(

/*
    "username" => array(
        "ignoreSources" => array(),
        "announcedProviders" => array(
            "C" => SOURCETYPE_INACTIVE,
            "T" => SOURCETYPE_INACTIVE,
            "A" => SOURCETYPE_INACTIVE,
            "S" => array()
        ),
        "visibleName" => "",

        "password" => "",
        "email" => "",
        "trust_status" => 0,
        "reupload_delay_timespan" => 0
    ),
*/

    "example" => array(
        "ignoreSources" => array(),
        "announcedProviders" => array(
            "C" => SOURCETYPE_INACTIVE,
            "T" => SOURCETYPE_INACTIVE,
            "A" => SOURCETYPE_INACTIVE,
            "S" => array("S19.2E")
        ),
        "visibleName" => "example",

        "password" => "",
        "email" => "",
        "trust_status" => 0,
        "reupload_delay_timespan" => 0
    ),


);

?>
