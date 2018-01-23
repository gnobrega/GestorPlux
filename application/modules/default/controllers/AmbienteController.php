<?php

class AmbienteController extends AbstractController {
    
    /**
     * Construtor
     * @param \Zend_Controller_Request_Abstract $request
     * @param \Zend_Controller_Response_Abstract $response
     * @param array $invokeArgs
     */
    public function __construct(\Zend_Controller_Request_Abstract $request, \Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
        
        //Inicia a model
        $this->_model = new Model_Ambiente();
        $this->_entity = $this->_model->getName();
                
        parent::__construct($request, $response, $invokeArgs);
        
        //Breadcrumb
        $this->addBreadcrumb("Ambientes", "/".$this->_entity);
    }
    
    /**
     * Gestão de usuários
     */
    public function indexAction() {

        //Gera a tabela
        $this->view->grid = new Core_Grid($this->_entity);
        $this->view->grid->addColumn("Id", "id");
        $this->view->grid->addColumn("Nome", "nome");
        $this->view->grid->addColumn("Empresa", "fk_empresa");
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
        $fieldName = $this->view->form->addField(Core_Form_Field::$TYPE_TEXT)
                ->setName("_nome")
                ->setLabel("Nome")
                ->setRequired(true)
                ->setAutofocus(true);
        
        //Empresa
        $this->view->form->addField(Core_Form_Field::$TYPE_SELECT)
                ->setTable("empresa")
                ->setName("_id_empresa")
                ->setRequired(true)
                ->setFilter("exibe_publicidade = 1")
                ->setLabel("Empresa");
        
        //Canais
        $this->view->form->addField(Core_Form_Field::$TYPE_SELECT)
                ->setTable("canal")
                ->setName("_id_canal")
                ->setLabel("Canal");
        
        //Telas
        $fieldTelas = $this->view->form->addField(Core_Form_Field::$TYPE_TEXT)
                ->setName("_telas")
                ->setLabel("Telas");

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
    
    /**
     * Persiste o registro no banco
     */
    public function salvarAction($return = false) {
        $rs = parent::salvarAction(true);
        $this->redirect("/".$this->_entity);
    }
    
    /**
     * Retorna a lista em formato de Json
     */
    public function listJsonAction() {
        $ambienteModel = new Model_Ambiente();
        $where = ( isset($_GET['filter']) ) ? $_GET['filter'] : null;
        $lst = $ambienteModel->fetchAll($where)->toArray();
        Core_Global::encodeListUtf($lst, true);
        echo json_encode($lst);
        die;
    }
    
    /**
     * Importa todos os pontos do Gestor Look
     */
    public function importarTodosAction() {
        
        //Verifica se a tabela está limpa
        $mdlAmbiente = new Model_Ambiente();
        $ambientes = $mdlAmbiente->fetchAll()->toArray();
        if( count($ambientes) ) {
            Core_Notificacao::adicionarMensagem("Para a importação completa, a tabela ambiente deve estar vazia");
            $this->redirect("/ambiente");
        }
        
        //Se conecta ao gestor antigo
        $con = mysqli_connect(GESTOR_LOOK_DB_HOST, GESTOR_LOOK_DB_USER, GESTOR_LOOK_DB_PASS, GESTOR_LOOK_DB_NAME);
        if( !$con ) {
            echo mysqli_error($con);
            die;
        }
        
        //Mapea os canais
        $canais = array(
            4 => 14, //ACADEMIAS
            5 => 13, //BARES
            6 => 12, //SHOPPING
            7 => 13, //RESTAURANTES
            8 => 10, //ELEVADORES LOOK
            10 => 9, //EDUCAÇÃO
            11 => 8, //SAÚDE
            12 => 7, //AGÊNCIAS DE PUBLICIDADE
            13 => 6, //EMPRESAS
            14 => 5, //SUPERMERCADO
            15 => 4, //LED
            16 => 3, //ELEVADORES ELEVAMEDIA
            17 => 2, //METRÔ DF
            18 => 1 //MÍDIA FIXA
        );
        
        //Carrega os parceiros
        $mdlEmpresa = new Model_Empresa();
        $sql = " 
                SELECT 
                    *
                FROM 
                    location
                WHERE
                    statusLookupItemId = 1
            ";
        $query = mysqli_query($con, $sql);
        $ambientes = array();
        $idAutoIncrement = 1;
        while( $ponto = mysqli_fetch_array($query) ) {
            if( !$ponto['branch'] ) {
                $ponto['branch'] = "Sem nome";
            }

            //Se não encontrar o parceiro, recupera o do contrato
            $contract = null;
            if( !$ponto['partnerId'] ) {
                $sql = " 
                    SELECT 
                        *
                    FROM 
                        contract
                    WHERE
                        id = {$ponto['contractId']}
                ";
                $query = mysqli_query($con, $sql);
                while( $contract = mysqli_fetch_array($query) ) {
                    $ponto['partnerId'] = $contract['partnerId'];
                    break;
                }
            }

            //Carrega a empresa
            $empresas = $mdlEmpresa->fetchAll("id_parceiro_look = " . $ponto['partnerId'])->toArray();
            if( count($empresas) ) {
                $ambiente = array(
                    "id"            => $idAutoIncrement,
                    "id_ponto_look" => $ponto['id'],
                    "nome"          => $ponto['branch'],
                    "telas"         => $ponto['totalDisplays'],
                    "id_canal"      => $canais[$ponto['channelId']],
                    "id_empresa"    => $empresas[0]['id']
                );
                $mdlAmbiente->insert($ambiente);
                $idAutoIncrement++;
            }
        }
        
        $this->returnSuccess("Importação realizada com sucesso");
        die;
    }
}
