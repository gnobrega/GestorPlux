<?php

/**
 * Helper responsável pela exibição das dashboarda
 */
class Zend_View_Helper_Contact extends Zend_View_Helper_Abstract {

    /**
     * Construtor
     */
    public function Contact() {
        return $this;
    }
    
    /**
     * Carrega o formulário
     */
    public function form() {
        return $this->view->partial('contact-form.phtml');
    }

}

?>