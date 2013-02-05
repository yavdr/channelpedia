<?php

/**
 * Sources resource
 *
 * @namespace channelpedia/sources
 * @uri /sources
 *
 */

class sourcesResource extends Resource {

    /**
     * Handle a GET request for this resource
     * @param Request request
     * @return Response
     */
    function get() {
        $config = config::getInstance();

        $response = new Response($request);
        $response->code = Response::OK;
        $response->addHeader('Content-type', 'application/json');
        $response->body = json_encode( array("result" => $config->getSourceList()) )."\n";
        //attempt to add list of satellite names to sources - but this breaks yavdr web frontend
        //$response->body = json_encode( array("result" => $config->getSourceListWithLongSatNames()) )."\n";
        return $response;
    }
}
?>