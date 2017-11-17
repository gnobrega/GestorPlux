<?php

/**
 * Caching plugin
 * 
 * @uses Zend_Controller_Plugin_Abstract
 */
class Plugins_App extends Zend_Controller_Plugin_Abstract {

    public function routeStartup(Zend_Controller_Request_Abstract $request) {
        
    }

    public function routeShutdown(Zend_Controller_Request_Abstract $request) {
        
    }

    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
        
    }

    /**
     * Antecipa o carregamento
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request) {

        //Verifica as permissões antes de carregar a action
        Core_Seguranca::validarAcesso($request);
    }

    public function postDispatch(Zend_Controller_Request_Abstract $request) {

    }

    public function dispatchLoopShutdown() {
        
    }

}
