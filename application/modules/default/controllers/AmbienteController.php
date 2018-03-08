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
        $this->addBreadcrumb("Ambientes", "/" . $this->_entity);
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
        $this->view->grid->addColumn("Canal", "fk_canal");
        $this->view->grid->addColumn("Telas", "telas");
    }

    /**
     * Cadastra um novo registro
     */
    public function cadastrarAction() {

        //Breadcrumb
        $this->addBreadcrumb("Cadastro");

        //Renderiza a view
        $this->renderScript($this->_entity . '/form.phtml');
    }

    /**
     * Método Editar
     */
    public function editarAction() {

        //Breadcrumb
        $this->addBreadcrumb("Edição");

        //Carrega os dados
        $this->view->registro = $this->_model->find($this->getParam('id'))->current()->toArray();
        Core_Global::encodeListUtf($this->view->registro);
        
        //Recupera os dados do endereço
        $this->view->endereco = array();
        if( $this->view->registro['id_endereco'] ) {
            $mdlEndereco = new Model_Endereco();
            $enderecos = $mdlEndereco->find($this->view->registro['id_endereco'])->toArray();
            if( count($enderecos) ) {
                $this->view->endereco = $enderecos[0];
                Core_Global::encodeListUtf($this->view->endereco);
            }
        }
        

        //Renderiza a view
        $this->renderScript($this->_entity . '/form.phtml');
    }

    /**
     * Persiste o registro no banco
     */
    public function salvarAction($return = false) {

        //Extrai os dados de endereço
        $latitude = $_POST['_latitude'];
        $longitude = $_POST['_longitude'];
        unset($_POST['_latitude']);
        unset($_POST['_longitude']);

        //Salva o ambiente
        $rs = parent::salvarAction(true);

        //Salva o endereço
        if ($rs['id']) {

            //Recupera o registro completo do ambiente
            $mdlEndereco = new Model_Endereco();
            $ambientes = $this->_model->find($rs['id'])->toArray();
            if (count($ambientes)) {
                $ambiente = $ambientes[0];
                $endereco = array();
                
                //Recupera o endereço já salvo
                if ($ambiente['id_endereco']) {
                    $enderecos = $mdlEndereco->find($ambiente['id_endereco'])->toArray();
                    if( count($enderecos) ) {
                        $endereco = $enderecos[0];
                    }
                }
                
                //Atualiza o endereço
                $endereco['latitude'] = $latitude;
                $endereco['longitude'] = $longitude;
                $mdlEndereco->update($endereco, "id = " . $endereco['id']);
            }
        }

        $this->redirect("/" . $this->_entity);
    }

    /**
     * Retorna a lista em formato de Json
     */
    public function listJsonAction() {
        $ambienteModel = new Model_Ambiente();
        $where = ( isset($_GET['filter']) ) ? $_GET['filter'] : null;
        $lst = $ambienteModel->fetchAll($where)->toArray();
        Core_Global::encodeListUtf($lst, true);

        //Carrega a lista das empresa
        $mdlEmpresa = new Model_Empresa();
        $empresas = $mdlEmpresa->fetchAll()->toArray();
        Core_Global::encodeListUtf($empresas, true);
        Core_Global::attrToKey($empresas, 'id');

        //Gera o nome
        foreach ($lst as $i => $ambiente) {
            $empresaId = $ambiente['id_empresa'];
            if (isset($empresas[$empresaId])) {
                if ($ambiente['nome'] == 'Sem nome') {
                    $lst[$i]['nome'] = $empresas[$empresaId]['nome_comercial'];
                } else {
                    $lst[$i]['nome'] = $empresas[$empresaId]['nome_comercial'] . ' - ' . $lst[$i]['nome'];
                }
            }
        }

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
        if (count($ambientes)) {
            Core_Notificacao::adicionarMensagem("Para a importação completa, a tabela ambiente deve estar vazia");
            $this->redirect("/ambiente");
        }

        //Se conecta ao gestor antigo
        $con = mysqli_connect(GESTOR_LOOK_DB_HOST, GESTOR_LOOK_DB_USER, GESTOR_LOOK_DB_PASS, GESTOR_LOOK_DB_NAME);
        if (!$con) {
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
        while ($ponto = mysqli_fetch_array($query)) {
            if (!$ponto['branch']) {
                $ponto['branch'] = "Sem nome";
            }

            //Se não encontrar o parceiro, recupera o do contrato
            $contract = null;
            if (!$ponto['partnerId']) {
                $sql = " 
                    SELECT 
                        *
                    FROM 
                        contract
                    WHERE
                        id = {$ponto['contractId']}
                ";
                $query = mysqli_query($con, $sql);
                while ($contract = mysqli_fetch_array($query)) {
                    $ponto['partnerId'] = $contract['partnerId'];
                    break;
                }
            }

            //Carrega a empresa
            $empresas = $mdlEmpresa->fetchAll("id_parceiro_look = " . $ponto['partnerId'])->toArray();
            if (count($empresas)) {
                $ambiente = array(
                    "id" => $idAutoIncrement,
                    "id_ponto_look" => $ponto['id'],
                    "nome" => $ponto['branch'],
                    "telas" => $ponto['totalDisplays'],
                    "id_canal" => $canais[$ponto['channelId']],
                    "id_empresa" => $empresas[0]['id']
                );
                $mdlAmbiente->insert($ambiente);
                $idAutoIncrement++;
            }
        }

        $this->returnSuccess("Importação realizada com sucesso");
        die;
    }

    /**
     * Importa o endereço da base da Look
     */
    public function importarEnderecosAction() {

        //Se conecta ao gestor antigo
        $con = mysqli_connect(GESTOR_LOOK_DB_HOST, GESTOR_LOOK_DB_USER, GESTOR_LOOK_DB_PASS, GESTOR_LOOK_DB_NAME);
        if (!$con) {
            echo mysqli_error($con);
            die;
        }

        //Models
        $mdlAmbiente = new Model_Ambiente();
        $mdlEndereco = new Model_Endereco();

        //Carrega os pontos
        $sql = " 
            SELECT 
                *
            FROM 
                location
            WHERE
                statusLookupItemId = 1
        ";
        $query = mysqli_query($con, $sql);
        while ($ponto = mysqli_fetch_array($query)) {
            $ambientes = $mdlAmbiente->fetchAll("id_ponto_look = " . $ponto['id'])->toArray();
            if (count($ambientes)) {
                $ambiente = $ambientes[0];
                if (!$ambiente['id_endereco']) {

                    //Cadastra o endereço
                    $endereco = array(
                        "latitude" => $ponto['latitude'],
                        "longitude" => $ponto['longitude'],
                        "google_ref" => $ponto['address'],
                        "complemento" => $ponto['complement']
                    );

                    //Insere o endereço
                    $enderecoId = $mdlEndereco->insert($endereco);
                    $ambiente['id_endereco'] = $enderecoId;
                    $mdlAmbiente->update($ambiente, "id = " . $ambiente['id']);
                }
            }
        }
        $this->returnSuccess("Importação realizada com sucesso");
        die;
    }
    
    /**
     * Exclui um ambiente e o seu endereço
     */
    public function excluirAction() {
        
        //Exclui o endereço
        $ambientes = $this->_model->find($_POST['id'])->toArray();
        if( count($ambientes) ) {
            $enderecoId = $ambientes[0]['id_endereco'];
            if( $enderecoId ) {
                $mdlEndereco = new Model_Endereco();
                $mdlEndereco->delete("id = " . $enderecoId);
            }
        }
        
        //Exclui o ambiente
        parent::excluirAction();
    }

}
