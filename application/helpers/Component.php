<?php

/**
 * Manipula componentes de formulários
 */
class Zend_View_Helper_Component extends Zend_View_Helper_Abstract {

    /**
     * Model da entidade
     * @var \Model_Abstract
     */
    protected $_model;
    
    /**
     * Construtor
     * @return Zend_View_Helper_Component Description
     */
    public function Component() {
        return $this;
    }
    
    /**
     * Monta a combo
     * @param string $entity
     * @param array $attrs Sobrepões os atributos
     */
    public function combo($entity, $attrs = array()) {
        $module = 'default';
        
        //Carrega a lista de registros
        $nmModel = 'Model_' . ucfirst($entity);
        
        //Trata os nomes compostos
        if( strrpos($entity, "_") !== false ) {
            $entitySplit = explode("_", $entity);
            $nmModel = "Model_" . ucfirst($entitySplit[0]) . ucfirst($entitySplit[1]);
        }
        
        $this->_model = new $nmModel;
        $this->view->comboConfig = array();
        $where = ( isset($attrs['where']) ) ? $attrs['where'] : null;
        
        //Configuração padrão da combo
        $this->view->comboConfig['data'] = ( isset($attrs['data']) ) ? $attrs['data'] : $this->_model->loadComboData($where);
        $this->view->comboConfig['label'] = $this->_model->_label;
        $this->view->comboConfig['name'] = '_'.$this->_model->getPrimary() . '_' . $this->_model->getName();
        $this->view->comboConfig['class'] = 'form-control';
        $this->view->comboConfig['pk'] = $this->_model->getPrimary();
        $this->view->comboConfig['firstNull'] = true;
        $this->view->comboConfig['entity'] = $entity;
        //Altera o nome do campos para as combos Multi
        if( isset($attrs['multiple']) ) {
            $this->view->comboConfig['name'] .= '[]';
        }
        
        //Sobreposição dos atributos
        foreach( $attrs as $attr=>$val ) {
            $this->view->comboConfig[$attr] = $val;
        }

        //Html
        return $this->view->render('component-combo.phtml');
    }
    
    /**
     * Monta a combo
     * @param string $entity
     * @param array $attrs Sobrepões os atributos
     */
    public function comboClassificacao($attrs) {
        
        $this->view->comboConfig = array();
        $this->view->comboConfig['required']    = $attrs['required'];
        $this->view->comboConfig['name']        = '_classificacao';
        $this->view->comboConfig['class']       = 'form-control';
        $this->view->comboConfig['pk']          = 'key';
        $this->view->comboConfig['firstNull']   = true;
        $this->view->comboConfig['entity']      = null;
        $this->view->comboConfig['label']       = 'label';
        $this->view->comboConfig['value']       = $attrs['value'];
        $this->view->comboConfig['data'] = array(
            array("label" => "Livre", "key" => "0"),
            array("label" => "10", "key" => "10"),
            array("label" => "12", "key" => "12"),
            array("label" => "14", "key" => "14"),
            array("label" => "16", "key" => "16"),
            array("label" => "18", "key" => "18")
        );
        
        //Html
        return $this->view->render('component-combo.phtml');
    }
    
    /**
     * Monta a combo de meses
     * @param string $entity
     * @param array $attrs Sobrepões os atributos
     */
    public function comboMes($attrs) {
        
        $this->view->comboConfig = array();
        $this->view->comboConfig['required']    = $attrs['required'];
        $this->view->comboConfig['name']        = '_mes';
        $this->view->comboConfig['class']       = 'form-control';
        $this->view->comboConfig['pk']          = 'key';
        $this->view->comboConfig['firstNull']   = true;
        $this->view->comboConfig['entity']      = null;
        $this->view->comboConfig['label']       = 'label';
        $this->view->comboConfig['value']       = $attrs['value'];
        $this->view->comboConfig['data'] = array(
            array("label" => "Selecione", "key" => "0"),
            array("label" => "Janeiro", "key" => "1"),
            array("label" => "Fevereiro", "key" => "2"),
            array("label" => "Março", "key" => "3"),
            array("label" => "Abril", "key" => "4"),
            array("label" => "Março", "key" => "5"),
            array("label" => "Junho", "key" => "6"),
            array("label" => "Julho", "key" => "7"),
            array("label" => "Agosto", "key" => "8"),
            array("label" => "Setembro", "key" => "9"),
            array("label" => "Outubro", "key" => "10"),
            array("label" => "Novembro", "key" => "11"),
            array("label" => "Dezembro", "key" => "12"),
        );
        
        //Html
        return $this->view->render('component-combo.phtml');
    }
    
    /**
     * Gera os radioButtons padrões
     * @param string $name
     * @param array $data
     * @param int $value
     * @param array $attrs
     */
    public function radioButton($name, $data, $value = null, $attrs = array()) {
        
        //Configuração padrão 
        $this->view->radioConfig = array();
        $this->view->radioConfig['name'] = $name;
        $this->view->radioConfig['data'] = $data;
        $this->view->radioConfig['value'] = $value;
        $this->view->radioConfig['class'] = 'btn btn btn-default';
        $this->view->radioConfig['required'] = 1;
        
        //Sobreposição dos atributos
        foreach( $attrs as $attr=>$val ) {
            $this->view->radioConfig[$attr] = $val;
        }
        
        //Html
        return $this->view->render('component-radio.phtml');
    }
    
    /**
     * Gera um campo do tipo File
     * @param string $name Nome do input file
     * @param string $folder Subdiretório na pasta upload
     * @param string $prefix Prefixo do nome do arquivo
     * @param string $value Caminho da imagem já salva
     */
    public function file($name, $folder = '', $prefix = '', $value = '', $attrs = array()) {
        
        //Configuração padrão 
        $this->view->fileConfig = array();
        $this->view->fileConfig['name']         = $name;
        $this->view->fileConfig['value']        = $value;
        $this->view->fileConfig['controller']   = 'index';
        $this->view->fileConfig['uploadUrl']    = "/index/upload-file";
        $this->view->fileConfig['accept']       = "image/*";
        $this->view->fileConfig['caption']      = "Selecione uma imagem...";
        $this->view->fileConfig['showPreview']  = true;
        $this->view->fileConfig['extraData']    = array();
        if( $folder ) {
            $this->view->fileConfig['extraData']['folder'] = $folder;
            $this->view->fileConfig['extraData']['prefix'] = $prefix;
            if( isset($attrs['extraData']) ) {
                $this->view->fileConfig['extraData'] = array_merge($this->view->fileConfig['extraData'], $attrs['extraData']);
                unset($attrs['extraData']);
            }
        }
        if( isset($attrs['type']) ) {
            $this->view->fileConfig['extraData']['type'] = $attrs['type'];
        }

        //Sobreposição dos atributos
        foreach( $attrs as $attr=>$val ) {
            $this->view->fileConfig[$attr] = $val;
        }
        
        //Html
        return $this->view->render('component-file.phtml');
    }
    
    /**
     * Monta a combo
     * @param string $entity
     * @param array $attrs Sobrepões os atributos
     */
    public function comboConstant($constant, $name, $attrs = array()) {
        $data = Constants::get($constant);
        $this->view->comboConfig = array();
        $this->view->comboConfig['data'] = array();
        $attrs['name'] = $name;
        foreach( $data as $i => $item ) {
            $this->view->comboConfig['data'][] = array(
                "pk" => $i,
                "label" => $item
            );
        }
        $this->view->comboConfig['required']    = ( isset($attrs['required']) ) ? $attrs['required'] : null;
        $this->view->comboConfig['firstNull']   = ( isset($attrs['firstNull']) ) ? $attrs['firstNull'] : true;
        $this->view->comboConfig['name']        = $attrs['name'];
        $this->view->comboConfig['label']       = 'label';
        $this->view->comboConfig['value']       = @$attrs['value'];
        $this->view->comboConfig['class']       = 'form-control';
        $this->view->comboConfig['pk']          = 'pk';
        
        $this->view->comboConfig['entity']      = null;
        
        //Html
        return $this->view->render('component-combo.phtml');
    }

}

?>