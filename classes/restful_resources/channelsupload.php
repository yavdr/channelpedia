<?php

/**
 * Sources resource
 *
 * @namespace channelpedia/channelsupload
 * @uri /channelsupload
 *
 */

ini_set("max_execution_time", 240); //safety buffer

class channelsupload extends Resource {

    /**
     * Handle a GET request for this resource
     * @param Request request
     * @return Response
     */
    function get() {
        $response = new Response($request);
        $response->code = Response::OK;
        $response->body = "This resource is not usable with a GET request.\n";
    }

    /**
     * Handle a POST request for this resource
     * @param Request request
     * @return Response
     */
    function post() {
        $config = config::getInstance();

        $response = new Response($request);
        $response->code = Response::OK;

        //FIXME: Add security checks!!!!
        $response->body = "Welcome to channelpedia channels.conf upload, let's see what we can do....\n";

        $password = isset($_POST["password"]) ? $_POST["password"] : "";
        $user = isset($_POST["user"]) ? $_POST["user"] : "";
        //prevent directory traversal: user name is not allowed to contain dots or slashes
        if ( $user == "" || strstr($user,".") || strstr($user,"/") || strstr($user,"\\") )
            $response->body .= "Error. File upload canceled: Invalid username.\n";
        else{
            if (isset($_FILES["channels"]["name"]) && $_FILES["channels"]["name"] == "channels.conf"){
                try {
                    $metaData = new channelImportMetaData($user);
                    if ( $metaData->userNameExists() && $metaData->isAuthenticated( $password ) ){
                        $checkpath = $config->getValue("userdata"). "sources/$user/";
                        if (move_uploaded_file( $_FILES["channels"]["tmp_name"], $checkpath."channels.conf" )){
                            $response->body .= "Upload successful.\n";
                            $importer = new channelImport( $metaData );
                            $importer->addToUpdateLog( "-", "channels.conf was uploaded and is now being processed.");
                            $importer->insertChannelsConfIntoDB();
                            $response->body .= $importer->getTextualSummary() . "\n";
                            $importer->updateAffectedDataAndFiles();
                            $importer->addToUpdateLog( "-", "Processing finished.");
                            $importer->renderGlobalStuff(); //global reports and global index page
                            unset($importer);
                        }
                        else{
                            $response->body .= "Error. Couldn't put uploaded file in the right place.\n";
                        }
                    }
                    else{
                        $response->body .= "Error. File upload canceled.\n";
                    }
                }
                catch (Exception $e) {
                    $config->addToDebugLog( 'Caught exception: '. $e->getMessage() );
                    $response->body .= "An exception occured during import.\n";
                    if (SEND_ADMIN_EMAIL){
                        mail(
                            ADMIN_EMAIL,
                            "Channelpedia: Exception on channel upload for ". $user ,
                            $e->getMessage() . "\n\n". $e->getTrace()
                        );
                    }
                }
            }
            else
                $response->body .= "Error. Uploaded file should exist and be called channels.conf\n";
        }
        return $response;
    }
}
?>