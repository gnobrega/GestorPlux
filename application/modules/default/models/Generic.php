<?php

/**
 * Casse modelo da entidade Funcionario
 *
 * @author gustavonobrega
 */
class Model_Generic extends Model_Abstract {
    
    /** Table name */
    protected $_name    = null;
    
    // Mapeia as constantes com suas respectivas labels que serão exibidas na grid */
    public $_constMapper;
    
    public function __construct($name) {
        parent::__construct();
        $this->_name = $name;
    }
    
    public function setName($name) {
        $this->_name = $name;
    }
    
}