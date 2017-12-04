<?php

/**
 * Gera formulários dinamicamente
 *
 * @author gustavonobrega
 */
class Core_Form {
    private $_action;
    private $_method = "post";
    private $_fields = array();
    private $_data = array();
    
    /**
     * Construtor
     */
    public function __construct($action) {
        $this->_action = $action;
    }
    
    /**
     * Adiciona um campo
     */
    public function addField($type) {
        $field = null;
        switch ( $type ) {
            case Core_Form_Field::$TYPE_TEXT:
                $field = new Core_Form_Field_Text();
                break;
            case Core_Form_Field::$TYPE_SELECT:
                $field = new Core_Form_Field_Select();
                break;
            case Core_Form_Field::$TYPE_PASSWORD:
                $field = new Core_Form_Field_Password();
                break;
            case Core_Form_Field::$TYPE_HIDDEN:
                $field = new Core_Form_Field_Hidden();
                break;
            case Core_Form_Field::$TYPE_RADIO:
                $field = new Core_Form_Field_Radio();
                break;
            default:
                throw new Exception("Tipo de campo de formulário inválido ($type)");
        }
        
        $field->setForm($this);
        $this->_fields[] = $field;
        return $field;
    }
    
    /**
     * Exibe o html
     */
    public function render() {
        
        //Topo do formulário
        $html = "<form id=\"form-registro\" action=\"{$this->_action}\" method=\"post\" class=\"form-horizontal\" >";
        
        //Recupera o código html dos campos
        foreach( $this->_fields as $field ) {
            
            //Autopreenche os campos
            $field->autoLoadData();
            
            //Html
            $html .= $field->getHtml();
        }
        
        //Gera o html dos botões
        $htmlButtons = $this->getHtmlButtons();
        $html .= $htmlButtons;
        
        //Encerra o formulário
        $html .= "</form>";
        echo $html;
    }
    
    /**
     * Gera o html dos botões
     */
    public function getHtmlButtons() {
        $html = "<div class='hr-line-dashed'></div>
                    <div class='form-group'>
                        <div class='col-sm-4 col-sm-offset-2'>
                            <button class='btn btn-primary' type='submit'>Salvar</button>
                            <button class='btn btn-white btn-voltar' >Cancel</button>
                        </div>
                    </div>";
        return $html;
    }
    
    /**
     * Alimenta as informações para autopreencher os campos
     */
    public function setData($data) {
        $this->_data = $data;
    }
    
    /**
     * Recupera as informações de preenchiemnto do formulário
     */
    public function getData() {
        return $this->_data;
    }
}

/**
 * Classe genérica de campo (field)
 */
class Core_Form_Field {
    public static $TYPE_TEXT = "TYPE_TEXT";
    public static $TYPE_SELECT = "TYPE_SELECT";
    public static $TYPE_PASSWORD = "TYPE_PASSWORD";
    public static $TYPE_HIDDEN = "TYPE_HIDDEN";
    public static $TYPE_RADIO = "TYPE_RADIO";
    public $_id;
    public $_name;
    public $_label;
    public $_attrs = array();
    public $_form;
    public $_class = "form-control";
    
    /**
     * Construtor
     */
    public function __construct() {
    }
    
    /**
     * Seta o nome
     */
    public function setName($name) {
        $this->_name = $name;
        $this->_id = "input-{$name}-".rand(0, 1000);
        $this->_attrs['name'] = $name;

        return $this;
    }
    
    /**
     * Seta o atributo
     */
    public function setAttr($key, $val) {
        $this->_attrs[$key] = $val;

        return $this;
    }
    
    /**
     * Preenche o valor do campo
     */
    public function autoLoadData() {
        $data = $this->_form->getData();
        $colName = substr($this->_name, 1);

        //Verifica se já possui o valor do campo disponível
        if( isset($data[$colName]) ) {
            if( !isset($this->_attrs['value']) ) {
                $this->_attrs['value'] = $data[$colName];
            }
        }
    }
    
    /**
     * Seta o atributo placeholder
     */
    public function setPlaceholder($val) {
        $this->_attrs['placeholder'] = $val;
    }
    
    /**
     * Seta o texto de exibição
     */
    public function setLabel($label) {
        $this->_label = $label;
        return $this;
    }
    
    /**
     * Seta o valor
     */
    public function setValue($value) {
        $this->_value = $value;
        $this->_attrs['value'] = $value;
        return $this;
    }
    
    /**
     * Seta o auto-foco do campo
     */
    public function setAutofocus($flag) {
        $this->_attrs['autofocus'] = $flag;
        return $this;
    }
    
    /**
     * Torna o campo obrigatório
     */
    public function setRequired($flag) {
        $this->_attrs['required'] = $flag;
        return $this;
    }
    
    /**
     * Gera o html do container
     */
    public function getHtmlContainer($htmlField) {
        $label = $this->_label;
        if( isset($this->_attrs['required']) ) {
            $label = "* " . $label;
        }
        $html = "<div class='form-group'>";
        $html .= "<label for='{$this->_id}' class='col-sm-2 control-label'>{$label}</label>";
        $html .= "<div class='col-sm-10'>";
        $html .= $htmlField;
        $html .= "</div>";
        $html .= "</div>";
        
        return $html;
    }
    
    /**
     * Permite o acesso à propriedade do formulário
     */
    public function setForm($form) {
        $this->_form = $form;
    }
    
    /**
     * Adiciona uma classe nova
     */
    public function addClass($class) {
        $this->_class .= " " . $class;
        return $this;
    }
}

/**
 * Campo de Texto
 */
class Core_Form_Field_Text extends Core_Form_Field {
    protected $_type = "text";
    
    /**
     * Gera o html do campo
     */
    public function getHtml() {
        $htmlField = "<input ";
        
        //Adiciona os atributos extras
        foreach( $this->_attrs as $key=>$value ) {
            $htmlField .= "$key = '$value' ";
        }
        $htmlField .= "type='$this->_type' class='$this->_class' name='{$this->_name}' />";
        $html = $this->getHtmlContainer($htmlField);
        
        return $html;
    }
}

/**
 * Campo Select
 */
class Core_Form_Field_Select extends Core_Form_Field {
    private $_table;
    private $_empty = false;
    
    /**
     * Gera o html do campo
     */
    public function getHtml() {
        if( !$this->_table ) {
            $this->_table = $this->_name;
        }
        
        //Verifica se o campo value foi preenchido
        $data = $this->_form->getData();
        $colName = "id_" . $this->_name;
        if( $data && isset($data[$colName]) && !isset($this->_attrs['value']) ) {
            $this->_attrs['value'] = $data[$colName];
        }
        
        //Nome personalizado para o campo
        if( $this->_table != $this->_name && $this->_name != '' ) {
            $this->_attrs['name'] = $this->_name;

        }
        
        //Verifica se a combo foi setada como vazia
        if( $this->_empty ) {
            $this->_table = null;
        }
                
        //Html
        $view = Zend_Layout::getMvcInstance()->getView();
        $htmlField = $view->Component()->combo($this->_table, $this->_attrs);
        $html = $this->getHtmlContainer($htmlField);
        
        return $html;
    }
    
    /**
     * Define a tabela
     */
    public function setTable($table) {
        $this->_table = $table;
        return $this;
    }
    
    /**
     * Define a combo como vazia
     */
    public function setEmpty($flag) {
        $this->_empty = $flag;
        return $this;
    }
    
    /**
     * Define um filtro
     */
    public function setFilter($where) {
        $this->_attrs['where'] = $where;
        return $this;
    }
    
    /**
     * Habilita a múltipla seleção
     */
    public function setMultiple($flag) {
        $this->_attrs['multiple'] = $flag;
        return $this;
    }
    
    /**
     * Exclui itens específicos da lista
     */
    public function exclude( $ids ) {
        if( $this->_attrs['where'] ) {
            $this->_attrs['where'] .= " AND ";
        } else {
            $this->_attrs['where'] = "";
        }
        $this->_attrs['where'] .= "id NOT IN (" . implode(",", $ids) . ")";
    }
}

/**
 * Campo password
 */
class Core_Form_Field_Password extends Core_Form_Field_Text {
    
    /**
     * Construtor
     */
    public function __construct() {
        $this->_type = "password";
    }
}

/**
 * Campo hidden
 */
class Core_Form_Field_Hidden extends Core_Form_Field_Text {
    
    /**
     * Construtor
     */
    public function __construct() {
        $this->_type = "hidden";
    }
    
    /**
     * Gera o html do container
     */
    public function getHtmlContainer($htmlField) {
        
        //Não gera container
        return $htmlField;
    }
}

/**
 * Campos de múltiplas opções
 */
class Core_Form_Field_Multi extends Core_Form_Field {
    protected $_itens = array();
    
    /**
     * Adiciona um item à lista
     */
    public function addItem($label, $value) {
        $this->_itens[$value] = $label;
        return $this;
    }
}

/**
 * Campo Radio Button
 */
class Core_Form_Field_Radio extends Core_Form_Field_Multi {
    protected $_type = "radio";
    
    /**
     * Gera o html do campo
     */
    public function getHtml() {
        
        $htmlField = "";
        foreach( $this->_itens as $value => $label ) {
            $itemId = "item-{$this->_name}-{$value}";
            $checked = '';
            if( $value == $this->_value ) {
                $checked = 'CHECKED';
            }
            $htmlField .= 
                "<div class='radio radio-inline'>
                    <input type='radio' id='{$itemId}' value='{$value}' name='{$this->_name}' {$checked}/>
                    <label for='{$itemId}'> {$label} </label>
                </div>";
        }
        $html = $this->getHtmlContainer($htmlField);
        
        return $html;
    }
}