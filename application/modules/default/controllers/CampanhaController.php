<?php

class CampanhaController extends AbstractController {
    
    /**
     * Construtor
     * @param \Zend_Controller_Request_Abstract $request
     * @param \Zend_Controller_Response_Abstract $response
     * @param array $invokeArgs
     */
    public function __construct(\Zend_Controller_Request_Abstract $request, \Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
        
        //Inicia a model
        $this->_model = new Model_Campanha();
        $this->_entity = $this->_model->getName();
                
        parent::__construct($request, $response, $invokeArgs);
        
        //Breadcrumb
        $this->addBreadcrumb("Campanhas", "/".$this->_entity);
    }
    
    /**
     * Gestão de usuários
     */
    public function indexAction() {

        //Gera a tabela
        $this->view->grid = new Core_Grid($this->_entity);
        $this->view->grid->addColumn("Id", "id");
        $this->view->grid->addColumn("Nome", "nome");
        $this->view->grid->addColumn("Cliente", "fk_empresa_cliente", null, "empresa");
        $this->view->grid->addColumn("PI", "pi");
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
        
        //Nome
        $this->view->form->addField(Core_Form_Field::$TYPE_TEXT)
                ->setName("_nome")
                ->setLabel("Nome")
                ->setRequired(true)
                ->setAutofocus(true);
                
        //Cliente
        $this->view->form->addField(Core_Form_Field::$TYPE_SELECT)
                ->setTable("empresa")
                ->setName("_id_empresa_cliente")
                ->setRequired(true)
                ->setLabel("Cliente");
        
        //Agência
        $this->view->form->addField(Core_Form_Field::$TYPE_SELECT)
                ->setTable("empresa")
                ->setName("_id_empresa_agencia")
                ->setLabel("Agência");
        
        //PI
        $this->view->form->addField(Core_Form_Field::$TYPE_TEXT)
                ->setName("_pi")
                ->setLabel("PI");
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
        
        //Monta o formulário
        $this->montarForm($registro);
        
        //Renderiza a view
        $this->renderScript($this->_entity.'/form.phtml');
    }
    
    public function salvarAction($return = false) {
        $rs = parent::salvarAction(true);
        $this->redirect("/".$this->_entity);
    }
}
