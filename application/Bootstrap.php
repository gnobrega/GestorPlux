<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    public function _initAutoloader() {
        date_default_timezone_set('America/Sao_Paulo');

        // Create an resource autoloader component
        $autoloader = new Zend_Loader_Autoloader_Resource(array(
            'basePath' => APPLICATION_PATH,
            'namespace' => ''
        ));

        // Adiciona a camada model
        $autoloader->addResourceTypes(array(
            'models' => array(
                'path' => 'modules/default/models',
                'namespace' => 'Model'
            )
        ));

        //Multi modules
        //Define o módulo padrão
        $this->_controller = Zend_Controller_Front::getInstance();
        $this->_controller->setDefaultModule("default");
        $this->_controller->addModuleDirectory(APPLICATION_PATH . '/modules');

        // Adiciona o diretorio de helpers
        Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH . '/controllers/helpers');

        // Return to bootstrap resource registry
        return $autoloader;
    }

    /**
     * Inicia as rotas
     */
    protected function _initRoutes() {
        $front = Zend_Controller_Front::getInstance();
        $router = $front->getRouter();

        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/routes.ini', 'production');
        $router->addConfig($config, 'routes');
    }

    /**
     * Força a exibição de erros
     */
    protected function _initDisplayErrors() {
        if (APPLICATION_ENV == 'development') {
            error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);
        }
    }

    /**
     * Inclusão de classes
     */
    protected function _initIncludes() {

        //AbstractController
        require_once( APPLICATION_PATH . '/modules/default/controllers/AbstractController.php');
        require_once( APPLICATION_PATH . '/configs/constants.php');
    }

    /**
     * Carrega os módulos
     */
    protected function _initFrontController() {
        $front = Zend_Controller_Front::getInstance();
        $front->setControllerDirectory(array(
            'default' => APPLICATION_PATH . '/modules/default/controllers'
        ));
        $front->registerPlugin(new Plugins_App());

        return $front;
    }

    /**
     * Adequa o layout de acordo com o domínio acessado
     */
    protected function _initTheme() {
        
    }

}
