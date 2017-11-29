<?php

/**
 * Classe Abstrata
 */
abstract class Model_Abstract extends Zend_Db_Table_Abstract {
    
    /**
     * Representa o campo que será exibido em grids e combos
     */
    public $_label = 'nome';
    protected $_pk;
    public $msgErro;
    
    /**
     * Construtor
     */
    public function __construct($config = array()) {

        //Define a primary
        if (!$this->_pk) {
            $this->_pk = "id";
        }
        parent::__construct($config);
    }

    /**
     * Calcula a quantidade de registros
     * @param string $where
     * @return int
     */
    public function count($where = null, $fks = array()) {
        $adpt = $this->getAdapter();
        $select = $adpt->select()
            ->from($this->_name, "COUNT({$this->_name}.{$this->_pk}) total");
            
        //Considera os relacionamentos na contagem
        foreach( $fks as $fk ) {
           
            //Busca o nome da tabela
            $mdlEstrang = $this->getModelFk($fk);
            $tbEstrang = $mdlEstrang->getName();
            $mdlFk[$fk] = $mdlEstrang;

            //Cria o join
            $select->joinLeft($tbEstrang, 
                $mdlEstrang->getName() .".". $mdlEstrang->getPrimary() . "=" . $this->getName() .".". $mdlEstrang->getPrimary(), 
                array());
        }

        if( $where ) {
            $select->where($where);
        }
        
        //Debug
        //echo $select->__toString();die;
        
        $rs = $adpt->fetchAll($select);
        return $rs[0]['total'];
    }
    
    /**
     * Recupera o modelo da FK
     */
    public function getModelFk($col) {
        if( strrpos($col, "|") !== false ) {
            $posSep = strrpos($col, "|");
            $entityEstrang = substr($col, $posSep+1);
        } else {
            $entityEstrang = substr($col, 3);
        }
        
        $nmModel = 'Model_'.ucfirst($entityEstrang);
        $mdlEstrang = new $nmModel;
        return $mdlEstrang;
    }
    
    /**
     * Fornece dados para a montagem da Grid de qualquer entidade
     */
    public function loadGrid($params, $json = true) {

        //Inicia a query
        $adpter = $this->getAdapter();
        $query = $adpter->select();
        
        //Define as colunas
        $cols = array();
        $fks = array();
        $mdlFk = array();
        
        foreach( $params['columns'] as $i => $col ) {
            //Isola as fks
            if( substr($col['data'], 0, 3) == 'fk_' ) {
                $fks[] = $col['data'];
                
            } elseif( substr($col['data'], 0, 6) == 'blank_' ) {
              //Ignora os campos em brancos
            } else {
                $cols[] = $col['data'];
            }
        }
        
        //Seleciona a tabela
        $query->from($this->_name, $cols);
        
        //Calcula o total de registros
        $total = $this->count(null, $fks);
        $totalFilter = $total;

        //Cria os relacionamentos (fks)
        foreach( $fks as $fk ) {
            
            //Busca o nome da tabela
            $mdlEstrang = $this->getModelFk($fk);
            $tbEstrang = $mdlEstrang->getName();
            $mdlFk[$fk] = $mdlEstrang;

            //Cria o join
            $colFkName = $mdlEstrang->getPrimary() . "_" . $mdlEstrang->getName();
            $colFkParams = explode("|", $fk);
            if( count($colFkParams) > 1 ) {
                $colFkName = $mdlEstrang->getPrimary() . "_" . substr($colFkParams[0], 3);
            }
            $query->joinLeft($tbEstrang, 
                    $mdlEstrang->getName() .".". $mdlEstrang->getPrimary() 
                    . "=" . $this->getName() .".". $colFkName, 
                    array($fk => $mdlEstrang->_label));
        }
        
        //Aplica o filtro
        $filter = $params['search'];
        if( $filter['value'] != "" || isset($params['where']) ) {
            $where = "";
                
            //Campo de filtro
            if( $filter['value'] != "" ) {
                $valFilter = $adpter->quote('%'.utf8_decode($filter['value']).'%');

                //Percorre as colunas filtráveis
                $j = 0;
                foreach( $params['columns'] as $i => $col ) {
                    if( $col['data'] == '' ) continue;
                    if( substr($col['data'], 0, 6) == 'blank_' ) continue;

                    if( isset($col['searchable']) && $col['searchable'] && $col['searchable'] != 'false' ) {
                        $where .= ( $j > 0 ) ? ' || ' : '';
                        $j++;
                        if( substr($col['data'], 0, 3) != 'fk_' ) {
                            $where .= "{$this->_name}.{$col['data']} LIKE {$valFilter}";
                        } else {
                            $fk = $col['data'];
                            //Tabela estrangeira
                            $where .= $mdlFk[$fk]->getName().".".$mdlFk[$fk]->_label." LIKE {$valFilter}";
                        }
                    }
                }
            }
            
            //Condição adicional
            if( isset($params['where']) ) {
                if( $where != '' ) {
                    $where .= ' AND ';
                }
                $where .= $params['where'];
            }
            
            $query->where($where);
            $totalFilter = $this->count($where, $fks);
        }
                
        //Limit/Offset
        $query->limit($params['length'], $params['start']);
        
        //Ordenação
        $order = $params['order'][0];
        $columOrder = $params['columns'][$order['column']]['data'];
        $query->order("{$columOrder} {$order['dir']}");
        
        //Debug
        //echo $query->__toString();die;
        
        //Carrega os dados
        $lstData = $adpter->fetchAll($query);
        Core_Global::encodeListUtf($lstData, true);
        //echo "<pre>";print_r($lstData);die;

        //Percorre a lista de resultados
        foreach( $lstData as $r=>&$item ) {

            //Seta o id da linha (tr)
            $item['DT_RowId'] = "grid-row-{$this->_name}-" . $item[$this->_pk];
            
            //Percorre os campos fazendo ajustes nos valores
            foreach( $item as $campo=>$valor ) {

                //Verifica se há alguma constante que deva ser substituída por label
                if( isset($this->_constMapper) && isset($this->_constMapper[$campo]) ) {
                    
                    //Verifica se existe alguma variável inserida no campo
                    $constMapper = $this->_constMapper[$campo][$valor];
                    foreach( $item as $coluna => $v ) {
                        if( strpos($constMapper, '$'.$coluna) ) {
                            $constMapper = str_replace('$'.$coluna, $v, $constMapper);
                        }
                    }
                  
                    $lstData[$r][$campo] = $constMapper;
                }

                //Verifica se existe alguma data que deva ser formatada
                if( isset($this->_datetime) && in_array($campo, $this->_datetime) !== FALSE ) { 
                    $lstData[$r][$campo] = Core_Global::dataHoraBr($valor);
                }
            }
            
        }
        
        //Converte para o padrão do plugin jquery.dataTable
        $resp = array();
        $resp['draw'] = $params['draw'];
        $resp['recordsTotal'] = $total;
        $resp['recordsFiltered'] = $totalFilter;
        $resp['data'] = $lstData;
        //Zend_Debug::dump($resp);die;
        
        if( $json ) {
            return json_encode($resp);
        } else {
            return $resp;
        }
    }
    
    /**
     * Carrega os dados de uma combo
     * @param array $params
     */
    public function loadCombo($params) {
        $adpter = $this->getAdapter();
        $cols = array('id'=>$this->getPrimary(), 'label'=>$this->_label);
        
        //Verifica se foram solicitados dados extras
        if( isset($params['extra']) ) {
            foreach( $params['extra'] as $colExtra ) {
                $cols[] = $colExtra;
            }
        }
        
        //Consulta os dados
        $query = $adpter->select()
                        ->from($this->_name, $cols)
                        ->order($this->_label);

        //Aplica os filtros
        foreach( $params['filter'] as $type=>$filter ) {

            //Filtro por uma combo pai
            if( $type == 'entityParent' ) {
                $nmModel = 'Model_' . ucfirst($filter['entity']);
                $mdlParent = new $nmModel;
                $fk = $mdlParent->getPrimary();
                
                //Cria a condição
                if( $filter['id'] ) {
                    $query->where($this->_name .".". $mdlParent->getPrimary() . "=" . $filter['id']);
                } else {
                    //Retorna uma lista vazia se não for passado o valor do filtro
                    return array();
                }
            }
        }
                        
        $lst = $adpter->fetchAll($query);
        Core_Global::encodeListUtf($lst, true);
        
        return $lst;
    }
    
    /**
     * Método genérico
     * @param array $registro
     */
    public function salvar(&$registro) {
        Core_Global::decodeListUtf($registro);
        
        try {
            if( isset($registro[$this->_pk]) ) {
                $id = $registro[$this->_pk];
                
                //Update
                $this->update($registro, "{$this->_pk} = {$id}");
            } else {

                //Insert
                $registro[$this->_pk] = $this->insert($registro);
                
            }
            
            return true;
        } catch (Exception $exc) {
            $this->msgErro = $exc->getMessage();
            return false;
        }
    }
    
    /**
     * Excluir o registro a partir do Id
     */
    public function deleteById($id) {
        return $this->delete($this->_pk. " = " . $id);
    }
    
    /**
     * Carrega a lista de registros para popular a combo
     */
    public function loadComboData($where = null) {
        $adpter = $this->getAdapter();
        
        //Gera a query
        $query = $adpter->select()
                        ->from($this->_name, array($this->_pk, $this->_label))
                        ->order($this->_label);
        if( $where ) {
            $query->where($where);
        }
        
        $lstResult = $adpter->fetchAll($query);
        Core_Global::encodeListUtf($lstResult, true);
        
        //Retorna a lista
        return $lstResult;
    }
    
    /**
     * Retorna a chave primária
     */
    public function getPrimary() {
        return $this->_pk;
    }
    
    /**
     * Retorna o nome da tabela
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * Gera o xml do conteúdo
     */
    public function toXml($tpl, $idCliente = null, $filter = 'publicado = 1', $contentShared = false) {
        
        //Carrega os dados do template
        $mdlTemplate = new Model_Template();
        $template = $mdlTemplate->fetchAll("chave = '{$tpl}'")->current()->toArray();
        Core_Global::encodeListUtf($template);
        
        //Carrega a configuração do template
        $tplConfig = json_decode($template['config']);
        $fields = $tplConfig->fields;
        
        //Verifica se necessita de algum provedor de dados
        $lstProvedor = array();
        foreach( $fields as $field ) {
            if( $field->type == 'provider' ) {
                
                //Verifica se foi passada a categoria
                if( !isset($field->category) ) {
                    return false;
                }
                
                //Carrega a lista de registros do provedor
                $fieldName = substr($field->name, 3);
                $mdlProvedor = new Model_Provedor();
                $lst = $mdlProvedor->fetchAll("categoria = '{$field->category}'", 'valor')->toArray();
                Core_Global::encodeListUtf($lst, true);
                Core_Global::attrToKey($lst, 'id');
                $lstProvedor[$fieldName] = $lst;
            }
        }
       
        //Carrega os dados do cliente
        $mdlCliente = new Model_Cliente();
        if( !$idCliente ) {
            $idCliente = $template['id_cliente'];
        }
        $cliente = $mdlCliente->find($idCliente)->current()->toArray();
        
        //Filtra o conteúdo pelo cliente
        if( !$contentShared ) {  
            $filter .= " AND id_cliente = " . $idCliente;
        }

        //Carrega os registros publicados
        $lstRegistros = $this->fetchAll($filter)->toArray();
        Core_Global::encodeListUtf($lstRegistros, true);
        
        //Inicia o xml
        $xmlTemplate = new SimpleXMLElement("<?xml version=\"1.0\"?><template></template>");
        $xmlTemplate->addAttribute('id', $template['id']);
        $xmlTemplate->addAttribute('nome', $template['nome']);
        $xmlTemplate->addAttribute('chave', $template['chave']);
        
        //Separa os registros com pesos maiores
        if( $this->hasColumn("peso") ) {
            $lstTmp = $lstRegistros;
            $lstRegistros = array();
            $lstRegistrosComPeso = array();
            foreach( $lstTmp as $registro ) {
                if( $registro['peso'] > 1 ) {
                    $lstRegistrosComPeso[] = $registro;
                } else {
                    $lstRegistros[] = $registro;
                }
            }
            $lstRegistrosComPeso = Core_Global::sortBy($lstRegistrosComPeso, "peso");
        }
        
        //Adiciona os registros com peso se houver
        if( isset($lstRegistrosComPeso) && count($lstRegistrosComPeso) ) {
            foreach( $lstRegistrosComPeso as $registroComPeso ) {
                
                //Cria uma lista temporária simulando os registros repetidos
                $lstTmp = array();
                for( $p = 0; $p < $registroComPeso['peso']; $p++ ) {
                    $lstTmp[] = $registroComPeso;
                }
                
                //Junta a lista de registros com os registros repetido
                $lstRegistros = Core_Global::mergeDistributed($lstRegistros, $lstTmp);
            }
        }
        
        //Percorre todos registros
        foreach( $lstRegistros as $registro ) {
            
            //Carrega as imagens relacionadas
            Core_Global::templateGetFiles($fields, $registro);
            
            $registroXml = $xmlTemplate->addChild('registro');
            foreach( $registro as $campo => $valor ) {
                $this->addXmlNode($campo, $valor, $registroXml, $lstProvedor);
                
            }
        }
        
        //Cria os diretórios caso não existam
        if( !is_dir('./upload/templates/' . $cliente['chave'] . '/') ) {
            mkdir('./upload/templates/' . $cliente['chave'] . '/');
        }
        if( !is_dir('./upload/templates/' . $cliente['chave'] . '/' . $template['chave'] . '/') ) {
            mkdir('./upload/templates/' . $cliente['chave'] . '/' . $template['chave'] . '/');
        }
        
        //Tratamento anti-cache. Força a alteração do tamanho no arquivo
        $ct = rand(1, 100);
        $nocache = "";
        for( $i = 1; $i <= $ct; $i ++ ) {
            $nocache .= ".";
        }
        $xmlTemplate->addChild("nocache", $nocache);

        //Grava o arquivo
        $file = './upload/templates/' . $cliente['chave'] . '/' . $template['chave'] . '/data.xml';
        $xmlTemplate->asXML($file);
        //echo $xmlTemplate->asXML();die;
    }
    
    /**
     * Adiciona o nó ao xml
     */
    private function addXmlNode($campo, $valor, $registroXml, $lstProvedor) {
        if( !is_array($valor) ) {
            //Adiciona o valor ao xml
            $registroXml->addAttribute($campo, $valor);

            //Verifica se é um registro de provedor de dados
            if( $valor && substr($campo, 0, 3) == 'id_' ) {
                $campoNome = substr($campo, 3);
                foreach( $lstProvedor as $p => $provedor ) {
                    if( $campoNome == $p ) {
                        $registro = $provedor[$valor];
                        //Adiciona a label do registro vindo do provedor ao xml
                        $cmpLabel = (string)$registro['valor'];
                        $registroXml->addAttribute($campoNome . '_label', $cmpLabel);
                    }
                }
            }
        } else {
            //Adiciona as imagens
            if( isset($valor['local']) ) {
                $registroXml->addAttribute(substr($campo, 3), $valor['local']);
            }
        }
    }
    
    /**
     * Verifica a coluna existe
     */
    public function hasColumn($col) {
        $sql = " 
            SELECT * 
            FROM information_schema.COLUMNS 
            WHERE 
                TABLE_NAME = '{$this->_name}' 
                AND COLUMN_NAME = '{$col}'";
        $rs = $this->getAdapter()->fetchAll($sql);
        return count($rs) > 0;
    }
}
