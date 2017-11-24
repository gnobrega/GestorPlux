<?php

/**
 * Helper responsável pela funções referentes às páginas internas
 */
class Zend_View_Helper_Page extends Zend_View_Helper_Abstract {

    /**
     * Construtor
     */
    public function Page() {
        return $this;
    }
    
    /**
     * Carrega o cabeçalho da página
     * @param sttring $titulo
     */
    public function header($title) {
        
        //Carrega os dados do cliente
        $sesUsuario = new Zend_Session_Namespace('usuario');
        $this->view->headerTitle = $title;
        return $this->view->render('page-header.phtml');
    }

}

?>