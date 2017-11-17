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
    public function header() {
        
        //Carrega os dados do cliente
        $sesUsuario = new Zend_Session_Namespace('usuario');
        /*if( isset($sesUsuario->clientes) && isset($sesUsuario->clientes[0]) ) {
            $clienteId = $sesUsuario->clientes[0];
            $mdlCliente = new Model_Cliente();
            $lst = $mdlCliente->find($clienteId)->toArray();
            if( count($lst) ) {
                $this->view->cliente = $lst[0];
            }
        }*/
        
        return $this->view->render('page-header.phtml');
    }

}

?>