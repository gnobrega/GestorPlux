<?php

/**
 * Helper responsável pela exibição das dashboarda
 */
class Zend_View_Helper_Dashboard extends Zend_View_Helper_Abstract {

    /**
     * Construtor
     */
    public function Dashboard() {
        return $this;
    }
    
    /**
     * Carrega o dashboad principal
     */
    public function main() {
        return $this->view->partial('dashboard-main.phtml');
    }

}

?>