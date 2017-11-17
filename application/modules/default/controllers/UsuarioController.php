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
    }


    /**
     * Login
     */
    public function loginAction() {
        
        //Cabeçalho da página
        $this->view->header = array (
            "categoria" => "",
            "acao" => "Login",
            "iconClass" => "fa fa-lock"
        );
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
        //Cabeçalho da página
        $this->view->header = array (
            "categoria" => "Usuários",
            "acao" => "Lista",
            "iconClass" => "fa fa-users"
        );
        
        //Gera a tabela
        $this->view->grid = new Core_Grid('usuario');
        $this->view->grid->addColumn("Id", "id");
        $this->view->grid->addColumn("Nome", "nome");
    }
    
    /**
     * Cadastra um novo registro
     */
    public function cadastrarAction() {
        $this->view->titulo = "Cadastro de usuário";
        $this->view->actionSave =  "/usuario/salvar";
        
        //Renderiza a view
        $this->renderScript('usuario/form.phtml');
    }
    
    /**
     * Método Editar
     */
    public function editarAction() {
        $this->view->titulo = "Edição de usuário";
        $this->view->actionSave = "/usuario/salvar";
        
        //Carrega os dados
        $this->view->registro = $this->_model->find($this->getParam('id'))->current()->toArray();
        Core_Global::encodeListUtf($this->view->registro);
        
        //Carrega as relações com os clientes
        $mdlUsuarioCliente = new Model_Generic('usuario_cliente');
        $lstUsuarioCliente = $mdlUsuarioCliente->fetchAll("id_usuario = " . $this->getParam('id'))->toArray();
        $this->view->lstIdCliente = array();
        foreach( $lstUsuarioCliente as $usuarioCliente ) {
            $this->view->lstIdCliente[] = $usuarioCliente['id_cliente'];
        }
        
        //Renderiza a view
        $this->renderScript('usuario/form.phtml');
    }
    
    public function salvarAction($return = false) {
        if( isset($_POST['_id_cliente']) ) {
            $lstIdClientes = $_POST['_id_cliente'];
            unset($_POST['_id_cliente']);
        }
        if( $_POST['_senha'] ) {
            $_POST['_senha'] = sha1(md5($_POST['_senha']));
        } else {
            unset($_POST['_senha']);
        }
        $rs = parent::salvarAction(true);
        $idUsuario = $rs['id'];
        
        //Relaciona com os clientes
        $mdlUsuarioCliente = new Model_Generic('usuario_cliente');
        $mdlUsuarioCliente->delete("id_usuario = " . $idUsuario);
        if( isset($lstIdClientes) ) {
            foreach( $lstIdClientes as $idCliente ) {
                $mdlUsuarioCliente->insert(array(
                    "id_usuario" => $idUsuario,
                    "id_cliente" => $idCliente
                ));
            }
        }
        echo json_encode($rs);
    }
    
    /**
     * Google Authenticator
     */
    public function googleAuthAction() {
        //Cabeçalho da página
        $this->view->header = array (
            "categoria" => "2 etapas",
            "acao" => "Login",
            "iconClass" => "fa fa-lock"
        );
    }
    
    /**
     * Verifica o código do Google Authenticator
     */
    public function verifyGoogleAuthAction() {
        if( !$_POST['codigo'] ) {
            throw new Exception("Código não informado");
        }
        $code = str_replace("-", "", $_POST['codigo']);
        
        //Válida o código enviado
        require APPLICATION_PATH . "/../library/GoogleAuthenticator/vendor/autoload.php" ; 
        $ga = new PHPGangsta_GoogleAuthenticator();
        $checkResult = $ga->verifyCode(GOOGLE_AUTHENTICATOR_SECRET, $code, 2);
        
        if( $checkResult || APPLICATION_ENV == "development" ) {
            
            //Sucesso
            $sesUsuario = new Zend_Session_Namespace('usuario');
            $sesUsuario->googleAuthVerify = true;
            $this->redirect("/");
        } else {
            Core_Notificacao::adicionarMensagem("Código inválido", "warning");
            $this->redirect("/usuario/google-auth");
        }
        die;
    }
    
    /**
     * Gera o QR Code
     */
    public function googleAuthQrcodeAction() {

        //Google Authenticator
        if( APPLICATION_ENV == "development" ) {
            require APPLICATION_PATH . "/../library/GoogleAuthenticator/vendor/autoload.php" ; 
            $ga = new PHPGangsta_GoogleAuthenticator();
            $website = 'http://gestor.wikipix.com.br';
            $title = 'MarkTv';
            $qrCodeUrl = $ga->getQRCodeGoogleUrl($title, GOOGLE_AUTHENTICATOR_SECRET,$website);
            echo $qrCodeUrl;
        }
        die;
    }
}
