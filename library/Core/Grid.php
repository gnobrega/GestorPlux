<?php

/**
 * Classe de manipulação de tabelas
 *
 * @author Gustavo
 */
class Core_Grid {
    
    public $_id;
    private $_columns;
    private $_actionCustom = array();
    private $_src;
    private $_entity;
    private $_module = 'default';
    public $_config = array();
    public $_editAjax = 'false';
    public $_actEdit = true;
    public $_actDel = true;
    public $_defaultOrder = array();
    
    /**
     * Atributos que serão convertidos em javascript
     */
    public $_attrs = array();
    
    /**
     * Construtor
     */
    public function __construct($entity, $module = 'default') {
        $this->_id = 'tb-' . rand(100, 999);
        $this->_entity = $entity;
        if( $module == 'default' ) {
            $url = "/{$entity}/load-grid";
        } else {
            $url = "/{$module}/{$entity}/load-grid";
            $this->_module = $module;
        }
        $this->_attrs['ajax'] = $url;
        $this->_columns = array();
    }
    
    /**
     * Seta o template em edição
     */
    public function setTemplate($template) {
        $this->_attrs['ajax'] .= '?tpl=' . $template;
    }
    
    /**
     * Adicionar uma coluna
     * @param string $name
     * @param string $key
     * @param array $attrs
     */
    public function addColumn($label, $key, $attrs = array(), $table = '') {
        $i = count($this->_columns);
        $this->_columns[$i] = array(
            'key' => $key,
            'label' => $label
        );
        
        //Faz referência à tabela estrangeira
        if( $table ) {
            $this->_columns[$i]['table'] = $table;
        }
        
        //Possibilita sobrescrever os atributos
        if( $attrs ) {
            foreach( $attrs as $key=>$value) {
                $this->_columns[$i][$key] = $value;
            }    
        }
    }

    /**
     * Exibe o html
     */
    public function html() {
        
        //Monta o html
        $dom = new DOMDocument();
        
        //Table
        $tableDom = $dom->createElement('table');
        $tableDom->setAttribute("class", "footable table table-stripped toggle-arrow-tiny");
        $tableDom->setAttribute("id", $this->_id);
        $dom->appendChild($tableDom);
        
        //Thead
        $theadDom = $dom->createElement('thead');
        $tableDom->appendChild($theadDom);
        $trHeadDom = $dom->createElement('tr');
        $theadDom->appendChild($trHeadDom);
        
        //Colunas
        foreach( $this->_columns as $col ) {
            $thColDom = $dom->createElement('th', $col['label']);
            $trHeadDom->appendChild($thColDom);
        }
        //Ações
        if( $this->_actDel != false || $this->_actEdit  != false ) {
            $thColDom = $dom->createElement('th', 'Ações');
            $trHeadDom->appendChild($thColDom);
        }
        
        //Container Ajax
        $dvAjax = $dom->createElement('div');
        $dvAjax->setAttribute("class", "container-ajax");
        $dom->appendChild($dvAjax);
        
        //Exibe o html
        echo (string)$dom->saveHTML();
        echo Core_Global::toJs($this->getScript());
        
        //Spinner
        echo ' 
            <div class="spiner-example grid-spiner">
                <div class="sk-spinner sk-spinner-wave">
                    <div class="sk-rect1"></div>
                    <div class="sk-rect2"></div>
                    <div class="sk-rect3"></div>
                    <div class="sk-rect4"></div>
                    <div class="sk-rect5"></div>
                </div>
            </div>';
    }
    
    /**
     * Retorna o array de colunas
     */
    public function getColumns() {
        return $this->_columns;
    }
    
    /**
     * Recupera o script
     */
    public function getScript() {
        
        //Carregas as colunas enviar para o Javascript
        $cols = $this->loadColumnsJs();
                
        //Seta os atributos no Javascript
        $this->setAttrsJs();
        
        //Lista os comando que serão executados
        $cmds = array();
        
        //Inicia o plugin
        $cmds[] = "Grid.init('{$this->_id}', '{$this->_entity}', '{$this->_module}');\n";
        
        //Seta as colunas
        $cmds[] = "Grid.config.columns = {$cols};\n";
        
        //Seja a ordenação inicial
        //$this->_defaultOrder = array(array("0","asc")); //Exemplo
        if( count($this->_defaultOrder) ) {
            $defaultOrder = json_encode($this->_defaultOrder);
            $cmds[] = "Grid.config.order = {$defaultOrder};\n";
        }
        
        //Carrega a grid
        $cmds[] = "Grid._objGrid = $('#{$this->_id}').DataTable(Grid.config);\n";
        
        //Evento após o carregamento
        $cmds[] = "Grid._objGrid.on('draw', function() {\n";
        $cmds[] = "$('div.grid-spiner').hide();\n";
        $cmds[] = "});\n";
        
        //Monta o javascript
        $js = "$(function () {";
        foreach( $cmds as $cmd ) {
            $js .= $cmd;
        }
        $js .= "});";
                
        return $js;
    }
    
    /**
     * Converte os atributos em Javascript
     */
    private function setAttrsJs() {
        $cmd = "";
        foreach( $this->_attrs as $attr=>$val ) {
            $cmd .= "Grid.config.{$attr} = '{$val}'; \n";
        }
        
        Core_Global::toJs($cmd);
    }
    
    /**
     * Seta as colunas no Javascript
     */
    private function loadColumnsJs() {
                
        //Extrai as colunas
        $cols = array();
        $i = 0;
        
        foreach( $this->_columns as $k=>$col ) {
            $cols[$i] = array(
                'data' => $col['key']
            );
            
            //Verifica se foi mensionado a tabela estrangeira (apenas quando o nome da coluna fk nao corresponde com o nome da tabela
            if( isset($col['table']) ) {
                $cols[$i]['data'] .= "|".$col['table'];
            }
            
            //Verifica se foi setada como ordenação inicial
            if( isset($col['order']) ) {
                $this->_defaultOrder[] = array($k, $col['order']);
            }
            if( isset($col['orderable']) ) {
                $cols[$i]['orderable'] = $col['orderable'];
            }
            $i ++;
        }
        
        //Coluna de ações
        if( $this->_actDel != false || $this->_actEdit != false ) {
            $actContent = $this->getActionCustom() . $this->getActionDefault();
            $actWidth = 10 + count( $this->_actionCustom ) * 2;
            $cols[] = array('orderable'=>false, 'data'=>null, 'defaultContent'=>$actContent, 'width'=>$actWidth.'%');
        }

        //Define a largura padrão da coluna Id
        $nCols = 0;
        $widthUsed = 0;
        foreach( $cols as &$col ) {
            if( substr($col['data'], 0, 2) == 'id' ) {
                $col['width'] = '10%';
            }
            //Conta quantas colunas ainda já possuem largura definida
            if( isset($col['width']) ) {
                $widthUsed += str_replace("%", "", $col['width']);
                $nCols++;
            }
        }
        $widthAv = 100 - $widthUsed;
        $widthDefault = $widthAv / (count($cols) - $nCols);
        
        //Define a largura das demais colunas
        foreach( $cols as &$col ) {
            if( !isset($col['width']) ) {
                $col['width'] = $widthDefault . '%';
            }
        }
   
        //Converte em json
        $jsonCols = json_encode($cols);
        
        return $jsonCols;
    }
    
    /**
     * Retorna as ações padrões
     */
    private function getActionDefault() {
        $html = '';
        
        //Edit
        if( $this->_actEdit ) {
            $html .= "<a href='' title='Editar' class='grid-act grid-act-edit' ><i class='fa fa-pencil'></i></a>";
        }
        
        //Del
        if( $this->_actDel ) {
            $html .= "<a href='' title='Excluir' class='grid-act grid-act-del'><i class='fa fa-trash'></i></a>";
        }
        return $html;
    }
    
    /**
     * Adiciona uma coluna customizada
     */
    public function addActionCustom($title, $class, $icon, $params = array()) {
        $html = "<a href='' title='" . $title . "' class='grid-act " . $class . "'><i class='" . $icon . "'></i></a>";
        $this->_actionCustom[] = $html;
    }
    
    /**
     * Retorna as colunas customizadas
     */
    public function getActionCustom() {
        $html = '';
        foreach( $this->_actionCustom as $columnHtml ) {
            $html .= $columnHtml;
        }
        return $html;
    }
}