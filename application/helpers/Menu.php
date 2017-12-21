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
        
        //Disponibiliza os menus
        $this->view->mainMenu = $this->buildMenu();
        
        //Valida se o usuário está autenticado e se não ocorreu erro
        if( Core_Seguranca::autenticado() && $action != 'error' && $action != 'login' ) {
            return $this->view->render('menu-main.phtml');
        }
    }
    
    /**
     * Cria os menus
     */
    public function buildMenu() {
        $uriAction = Core_Global::getUriAction();
        $menu = new Menu();
        $menu->addItem('Resumo', '/default/index/index', 'fa fa-table');
        $menu->addItem('Publicidade', null, 'fa fa-bullhorn')
            ->addSubitem('Campanhas', '/default/campanha/index')
            ->addSubitem('Ambientes', '/default/ambiente/index')
            ->addSubitem('Canais', '/default/canal/index')
            ->addSubitem('Segmentos', '/default/empresa-segmento/index')
            ->addSubitem('Booking', '/default/booking/index');
        $menu->addItem('Empresas', '/default/empresa/index', 'fa fa-building-o');
        $menu->addItem('Segurança', null, 'fa fa-lock')
                ->addSubitem('Usuários', '/default/usuario/index')
                ->addSubitem('Perfis', '/default/perfil/index');
        
        return $menu;
    }
}

/**
 * Representa o menu completo
 */
class Menu {
    private $_itens = array();
    
    /**
     * Adiciona um item ao menu
     */
    public function addItem($name, $action, $icon = '') {
        $item = new MenuItem($name, $action, $icon);
        $this->_itens[] = $item;
        return $item;
    }
    
    /**
     * Recupera a lista de itens
     */
    public function getItens() {
        return $this->_itens;
    }
}

/**
 * Representa o item do menu
 */
class MenuItem {
    public $name;
    public $link;
    public $icon;
    private $subitens = array();
    private $active = false;
    private $parent = null;
    
    public function __construct($name, $action, $icon = '', $parent = null) {
        $this->name = $name;
        $this->action = $action;
        $this->icon = $icon;
        $this->parent = $parent;
        
        //Verifica se o item está ativo
        $currentAction = Core_Global::getUriAction();
        if( $currentAction == $this->action ) {
            $this->setActive(true);
            if( $this->getParent() ) {
                $this->getParent()->setActive(true);
            }
        }
    }
    
    /**
     * Adiciona um subitem
     */
    public function addSubitem($name, $action, $icon = '') {
        $subitem = new MenuItem($name, $action, $icon, $this);
        $this->subitens[] = $subitem;
        return $this;
    }
    
    /**
     * Retorna os subitens
     */
    public function getSubitens() {
        return $this->subitens;
    }
    
    /**
     * Mantém o menu ativo
     */
    public function setActive($flag) {
        $this->active = $flag;
        return $this;
    }
    
    /**
     * Verifica se o menu está ativo
     */
    public function isActive() {
        return $this->active;
    }
    
    /**
     * Recupera o item pai
     */
    public function getParent() {
        return $this->parent;
    }
}
    
?>