<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

use Jaxon\Features\App;
use Jaxon\CI\View;
use Jaxon\CI\Session;
use Jaxon\CI\Logger;

class Jaxon
{
    use App;

    public function __construct()
    {
        // Initialize the Jaxon plugin
        $this->setup();
    }

    /**
     * Set the module specific options for the Jaxon library.
     *
     * @return void
     */
    protected function setup()
    {
        // Load Jaxon config settings
        $ci = get_instance();
        $ci->config->load('jaxon', true);
        $aLibOptions = $ci->config->item('lib', 'jaxon');
        $aAppOptions = $ci->config->item('app', 'jaxon');

        // Jaxon library default settings
        $bIsDebug = $ci->config->item('debug');
        $sJsUrl = rtrim($ci->config->item('base_url'), '/') . '/jaxon/js';
        $sJsDir = rtrim(FCPATH, '/') . '/jaxon/js';

        $jaxon = jaxon();
        $di = $jaxon->di();
        $viewManager = $di->getViewManager();
        // Set the default view namespace
        $viewManager->addNamespace('default', '', '', 'codeigniter');
        // Add the view renderer
        $viewManager->addRenderer('codeigniter', function() {
            return new View();
        });

        // Set the session manager
        $di->setSessionManager(function() {
            return new Session();
        });

        // Set the logger
        $this->setLogger(new Logger());

        $this->bootstrap()
            ->lib($aLibOptions)
            ->app($aAppOptions)
            // ->uri($sUri)
            ->js(!$bIsDebug, $sJsUrl, $sJsDir, !$bIsDebug)
            ->run();

        // Prevent the Jaxon library from sending the response or exiting
        $jaxon->setOption('core.response.send', false);
        $jaxon->setOption('core.process.exit', false);
    }

    /**
     * Get the HTTP response
     *
     * @param string    $code       The HTTP response code
     *
     * @return mixed
     */
    public function httpResponse($code = '200')
    {
        $jaxon = jaxon();
        // Get the reponse to the request
        $jaxonResponse = $jaxon->di()->getResponseManager()->getResponse();
        if(!$jaxonResponse)
        {
            $jaxonResponse = $jaxon->getResponse();
        }

        // Create and return a CodeIgniter HTTP response
        get_instance()->output
            ->set_status_header($code)
            ->set_content_type($jaxonResponse->getContentType(), $jaxonResponse->getCharacterEncoding())
            ->set_output($jaxonResponse->getOutput());
            // ->_display();
    }

    /**
     * Process an incoming Jaxon request, and return the response.
     *
     * @return mixed
     */
    public function processRequest()
    {
        // Process the jaxon request
        jaxon()->processRequest();

        // Return the reponse to the request
        $this->httpResponse();
    }
}
