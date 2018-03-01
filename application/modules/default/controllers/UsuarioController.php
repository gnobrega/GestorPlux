<?php

class UsuarioController extends AbstractController {
    
    /**
     * Construtor
     * @param \Zend_Controller_Request_Abstract $request
     * @param \Zend_Controller_Response_Abstract $response
     * @param array $invokeArgs
     */
    public function __construct(\Zend_Controller_Request_Abstract $request, \Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
        
        //Inicia a model
        $this->_model = new Model_Usuario();
                
        parent::__construct($request, $response, $invokeArgs);
        
        //Breadcrumb
        $this->addBreadcrumb("Usuários", "/usuario");
    }


    /**
     * Login
     */
    public function loginAction() {
        $this->_helper->layout->setLayout("basic");
    }
    
    /**
     * Autenticar
     */
    public function autenticarAction() {
       
        //Remove o usuário autenticado
        $sesUsuario = new Zend_Session_Namespace('usuario');
        if( $sesUsuario->id ) {
            //Zend_Session::destroy( true );
            $sesUsuario->unsetAll();
        }
        
        //Model
        $mdlUsuario = new Model_Usuario();
        $result = $mdlUsuario->autenticar($_POST['usuario'], $_POST['senha']);

        //Redireciona para home
        $this->redirect("/");
    }
    
    /**
     * Realiza o logout
     */
    public function sairAction() {
        Zend_Session::destroy( true );
        $this->redirect("/");
    }
    
    /**
     * Gestão de usuários
     */
    public function indexAction() {

        //Gera a tabela
        $this->view->grid = new Core_Grid('usuario');
        $this->view->grid->addColumn("Id", "id");
        $this->view->grid->addColumn("Nome", "nome");
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
        $this->renderScript('usuario/form.phtml');
    }
    
    /**
     * Monta o formulário
     */
    public function montarForm($registro = array()) {
        
        $this->view->form = new Core_Form("/usuario/salvar");
        $this->view->form->setData($registro);
        
        //Id
        if( isset($registro['id']) ) {
            //Senha
            $fieldId = $this->view->form->addField(Core_Form_Field::$TYPE_HIDDEN)
                    ->setName("_id");
        }
        
        //Nome
        $fieldName = $this->view->form->addField(Core_Form_Field::$TYPE_TEXT)
                ->setName("_nome")
                ->setLabel("Nome completo")
                ->setRequired(true)
                ->setAutofocus(true);
        
        //Login
        $fieldLogin = $this->view->form->addField(Core_Form_Field::$TYPE_TEXT)
                ->setName("_login")
                ->setLabel("Login")
                ->setRequired(true);
        
        //Perfil
        $this->view->form->addField(Core_Form_Field::$TYPE_SELECT)
                ->setTable("perfil")
                ->setName("_id_perfil")
                ->setRequired(true)
                ->setLabel("Perfil");
        
        
        //Senha
        $fieldSenha = $this->view->form->addField(Core_Form_Field::$TYPE_PASSWORD)
                ->setName("_senha")
                ->setLabel("Senha");
        if( isset($registro['id']) ) {
            $fieldSenha->setPlaceholder("Mantenha esse campo em branco para preservar a senha");
        }
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
        unset($registro['senha']);
        
        //Monta o formulário
        $this->montarForm($registro);
        
        //Renderiza a view
        $this->renderScript('usuario/form.phtml');
    }
    
    /**
     * Salva o usuário no banco
     */
    public function salvarAction($return = false) {
        if( $_POST['_senha'] ) {
            $_POST['_senha'] = sha1(md5($_POST['_senha']));
        } else {
            unset($_POST['_senha']);
        }
        $rs = parent::salvarAction(true);
        $this->redirect("/usuario");
    }
}
