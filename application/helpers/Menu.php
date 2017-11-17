<?php

/**
 * Helper responsável pela exibição dos menus
 */
class Zend_View_Helper_Menu extends Zend_View_Helper_Abstract {

    /**
     * Construtor
     */
    public function Menu() {
        return $this;
    }
    
    /**
     * Carrega o menu principal
     */
    public function main() {
        //Recupera o nome da action
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $action = $request->getActionName();
        
        //Valida se o usuário está autenticado e se não ocorreu erro
        if( Core_Seguranca::autenticado() && $action != 'error' && $action != 'login' ) {
            return $this->view->render('menu-main.phtml');
        }
    }
}

?>