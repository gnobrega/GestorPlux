<?php

class EmpresaController extends AbstractController {
    
    /**
     * Construtor
     * @param \Zend_Controller_Request_Abstract $request
     * @param \Zend_Controller_Response_Abstract $response
     * @param array $invokeArgs
     */
    public function __construct(\Zend_Controller_Request_Abstract $request, \Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
        
        //Inicia a model
        $this->_model = new Model_Empresa();
        $this->_entity = $this->_model->getName();
                
        parent::__construct($request, $response, $invokeArgs);
        
        //Breadcrumb
        $this->addBreadcrumb("Empresas", "/".$this->_entity);
    }
    
    /**
     * Gestão de usuários
     */
    public function indexAction() {

        //Gera a tabela
        $this->view->grid = new Core_Grid($this->_entity);
        $this->view->grid->addColumn("Id", "id");
        $this->view->grid->addColumn("Nome Comercial", "nome_comercial");
    }
    
    /**
     * Cadastra um novo registro
     */
    public function cadastrarAction() {
        
        //Breadcrumb
        $this->addBreadcrumb("Cadastro");
        
        //Monta o formulário
        $this->montarForm();
        
        //Renderiza a view
        $this->renderScript($this->_entity.'/form.phtml');
    }
    
    /**
     * Monta o formulário
     */
    public function montarForm($registro = array()) {
        $this->view->form = new Core_Form("/".$this->_entity."/salvar");
        $this->view->form->setData($registro);
        
        //Id
        if( isset($registro['id']) ) {
            $fieldId = $this->view->form->addField(Core_Form_Field::$TYPE_HIDDEN)
                    ->setName("_id");
        }
        
        //Nome Comercial
        $this->view->form->addField(Core_Form_Field::$TYPE_TEXT)
                ->setName("_nome_comercial")
                ->setLabel("Nome Comercial")
                ->setRequired(true)
                ->setAutofocus(true);
        
        //Empresa Matriz
        $campoMatriz = $this->view->form->addField(Core_Form_Field::$TYPE_SELECT)
                ->setTable("empresa")
                ->setName("_id_empresa_matriz")
                ->setFilter("id_empresa_matriz IS NULL")
                ->setLabel("Empresa Matriz");
        if( isset($registro['id']) ) {
            $campoMatriz->exclude(array($registro['id']));
        }
        
        //Razão Social
        $this->view->form->addField(Core_Form_Field::$TYPE_TEXT)
                ->setName("_razao_social")
                ->setLabel("Razão Social");
        
        //Cnpj
        $this->view->form->addField(Core_Form_Field::$TYPE_TEXT)
                ->setName("_cnpj")
                ->setLabel("Cnpj")
                ->addClass("cnpj");
        
        //Inscrição Estadual
        $this->view->form->addField(Core_Form_Field::$TYPE_TEXT)
                ->setName("_inscricao_estadual")
                ->setLabel("Inscrição Estadual");
        
        //Segmento
        $this->view->form->addField(Core_Form_Field::$TYPE_SELECT)
                ->setTable("empresa_segmento")
                ->setName("_id_empresa_segmento")
                ->setLabel("Segmento");
        
        //Anunciante
        $anuncValor = ( isset($registro['anunciante']) ) ? $registro['anunciante'] : '0';
        $this->view->form->addField(Core_Form_Field::$TYPE_RADIO)
                ->setName("_anunciante")
                ->setLabel("É um anunciante?")
                ->setValue($anuncValor)
                ->addItem("Sim", 1)
                ->addItem("Não", 0);
        
        //Agências
        $campoAgencias = $this->view->form->addField(Core_Form_Field::$TYPE_SELECT)
                ->setTable("empresa")
                ->setFilter("agencia = 1")
                ->setName("agencias[]")
                ->setLabel("Atendido por")
                ->setMultiple(true);
        if( isset($registro['agencias']) ) {
            $campoAgencias->setValue($registro['agencias']);
        }
        
        //Exibe publicidade
        $exibPubValor = ( isset($registro['exibe_publicidade']) ) ? $registro['exibe_publicidade'] : '0';
        $this->view->form->addField(Core_Form_Field::$TYPE_RADIO)
                ->setName("_exibe_publicidade")
                ->setLabel("Exibe publicidade?")
                ->setValue($exibPubValor)
                ->addItem("Sim", 1)
                ->addItem("Não", 0);
                
        //É uma agência
        $agencia = ( isset($registro['agencia']) ) ? $registro['agencia'] : '0';
        $this->view->form->addField(Core_Form_Field::$TYPE_RADIO)
                ->setName("_agencia")
                ->setLabel("É uma agência?")
                ->setValue($agencia)
                ->addItem("Sim", 1)
                ->addItem("Não", 0);
    }
    
    /**
     * Método Editar
     */
    public function editarAction() {
        
        //Breadcrumb
        $this->addBreadcrumb("Edição");
                
        //Carrega os dados
        $registro = $this->_model->find($this->getParam('id'))->current()->toArray();
        Core_Global::encodeListUtf($registro);
        
        //Carrega os relacionamentos com as agências
        $modRel = new Model_Generic("empresa_agencia");
        $empAgencias = $modRel->fetchAll("id_empresa_cliente = " . $this->getParam('id'))->toArray();
        foreach( $empAgencias as $empAgencia ) {
            $registro['agencias'][] = $empAgencia['id_empresa_agencia'];
        }
        
        //Monta o formulário
        $this->montarForm($registro);
        
        //Renderiza a view
        $this->renderScript($this->_entity.'/form.phtml');
    }
    
    public function salvarAction($return = false) {
        $rs = parent::salvarAction(true);
        
        //Cria o relacionamento com as agências
        $modRel = new Model_Generic("empresa_agencia");
        $modRel->delete("id_empresa_cliente = " . $rs['id']);
        if( isset($_POST['agencias']) ) {
            foreach( $_POST['agencias'] as $agenciaId ) {
                $empAgencia = array(
                    "id_empresa_agencia" => $agenciaId,
                    "id_empresa_cliente" => $rs['id']
                );
                $modRel->insert($empAgencia);
            }
        }
        
        $this->redirect("/".$this->_entity);
    }
}
