<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Jaxon
{
    use \Jaxon\Framework\PluginTrait;

    /**
     * Initialise the Jaxon library.
     *
     * @return void
     */
    public function setup()
    {
        $this->view = new \Jaxon\CI\View();
        // Load Jaxon config settings
        $ci = get_instance();
        $ci->config->load('jaxon', true);

        // Jaxon library default options
        $this->jaxon->setOptions(array(
            'js.app.extern' => !$ci->config->item('debug'),
            'js.app.minify' => !$ci->config->item('debug'),
            'js.app.uri' => $ci->config->item('base_url') . 'jaxon/js',
            'js.app.dir' => FCPATH . 'jaxon/js',
        ));

        // Jaxon library settings
        $libConfig = $ci->config->item('lib', 'jaxon');
        $this->jaxon->setOptions($libConfig);

        // Jaxon application settings
        $appConfig = $ci->config->item('app', 'jaxon');
        $controllerDir = (array_key_exists('dir', $appConfig) ? $appConfig['dir'] : APPPATH . 'jaxon');
        $namespace = (array_key_exists('namespace', $appConfig) ? $appConfig['namespace'] : '\\Jaxon\\App');
        $excluded = (array_key_exists('excluded', $appConfig) ? $appConfig['excluded'] : array());
        // The public methods of the Controller base class must not be exported to javascript
        $controllerClass = new \ReflectionClass('\\Jaxon\\CI\\Controller');
        foreach ($controllerClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $xMethod)
        {
            $excluded[] = $xMethod->getShortName();
        }

        // Set the request URI
        if(!$this->jaxon->getOption('core.request.uri'))
        {
            $this->jaxon->setOption('core.request.uri', 'jaxon');
        }
        // Register the default Jaxon class directory
        $this->jaxon->addClassDir($controllerDir, $namespace, $excluded);
    }

    /**
     * Wrap the Jaxon response into an HTTP response.
     *
     * @param  $code        The HTTP Response code
     *
     * @return HTTP Response
     */
    public function httpResponse($code = '200')
    {
        // Send HTTP Headers
        $this->response->sendHeaders();
        // Create and return a CodeIgniter HTTP response
        get_instance()->output
            ->set_status_header($code)
            // ->set_content_type($this->response->getContentType(), $this->response->getCharacterEncoding())
            ->set_output($this->response->getOutput())
            ->_display();
    }
}
