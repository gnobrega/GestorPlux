<?php

class IndexController extends AbstractController {

    /**
     * Dashboard
     */
    public function indexAction() {
        
        //Cabeçalho da página
        $this->view->header = array (
            "categoria" => "Resumo",
            "acao" => "Templates",
            "iconClass" => "fa fa-table"
        );
        
    }
}
