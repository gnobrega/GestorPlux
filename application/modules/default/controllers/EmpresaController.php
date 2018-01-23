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
        $this->view->grid->addColumn("Razão Social", "razao_social");
    }
    
    /**
     * Cadastra um novo registro
     */
    public function cadastrarAction() {
        
        //Breadcrumb
        $this->addBreadcrumb("Cadastro");
        
        //Monta o formulário
        $importacao = null;
        if( isset($_GET['importacao']) ) {
            $importacao = array(
                "nome_comercial"        => $_GET['importacao']['nome_comercial'],
                "razao_social"          => $_GET['importacao']['razao_social'],
                "cnpj"                  => $_GET['importacao']['cnpj'],
                "inscricao_estadual"    => $_GET['importacao']['inscricao_estadual']
            );
        }
        $this->montarForm(array(), $importacao);
        
        //Renderiza a view
        $this->renderScript($this->_entity.'/form.phtml');
    }
    
    /**
     * Monta o formulário
     */
    public function montarForm($registro = array(), $importacao = null) {
        $this->view->form = new Core_Form("/".$this->_entity."/salvar");
        $this->view->form->setData($registro);
        
        //Id
        if( isset($registro['id']) ) {
            $fieldId = $this->view->form->addField(Core_Form_Field::$TYPE_HIDDEN)
                    ->setName("_id");
        }
        
        //Nome Comercial
        $campoNomeComercial = $this->view->form->addField(Core_Form_Field::$TYPE_TEXT)
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
        $campoRazaoSocial = $this->view->form->addField(Core_Form_Field::$TYPE_TEXT)
                ->setName("_razao_social")
                ->setLabel("Razão Social");
        
        //Cnpj
        $campoCnpj = $this->view->form->addField(Core_Form_Field::$TYPE_TEXT)
                ->setName("_cnpj")
                ->setLabel("Cnpj")
                ->addClass("cnpj");
        
        //Inscrição Estadual
        $campoInscEstadual = $this->view->form->addField(Core_Form_Field::$TYPE_TEXT)
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
        $campoPublicidade = $this->view->form->addField(Core_Form_Field::$TYPE_RADIO)
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
        
        //Preenche os campos com os dados vindo de importação
        if( $importacao ) {
            $campoNomeComercial->setValue($importacao['nome_comercial']);
            $campoRazaoSocial->setValue($importacao['razao_social']);
            $campoCnpj->setValue($importacao['cnpj']);
            $campoInscEstadual->setValue($importacao['inscricao_estadual']);
            $campoPublicidade->setValue(1);
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
    
    /**
     * Salva o registro no banco
     */
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
    
    /**
     * Carrega as agências que atende um cliente
     */
    public function agenciasPorClienteAction() {
        if( !isset($_POST['id_empresa_cliente']) ) {
            $this->returnError("Informe o id do cliente");
            die;
        }
        $clienteId = $_POST['id_empresa_cliente'];
        
        //Carrega os relacionamento com as agências
        $mdlRelAgn = new Model_Generic("empresa_agencia");
        $rels = $mdlRelAgn->fetchAll("id_empresa_cliente = " . $clienteId)->toArray();
        $agencias = array();
        foreach( $rels as $rel ) {
            $rsAgn = $this->_model->find($rel['id_empresa_agencia'])->toArray();
            if( count($rsAgn) ) {
                $agencias[] = $rsAgn[0];
            }
        }
        Core_Global::encodeListUtf($agencias, true);
        $this->returnSuccess(null, $agencias);
        die;
    }
    
    /**
     * Importa os registros do sistema antigo
     */
    public function importarAction() {
        
        //Breadcrumb
        $this->addBreadcrumb("Importação");
        
        $this->view->parceiros = $this->carregarPontosLook();
    }
    
    /**
     * Carrega os pontos do sistema da Look
     */
    public function carregarParceirosLook() {
        
        //Se conecta ao gestor antigo
        $con = mysqli_connect(GESTOR_LOOK_DB_HOST, GESTOR_LOOK_DB_USER, GESTOR_LOOK_DB_PASS, GESTOR_LOOK_DB_NAME);
        if( !$con ) {
            echo mysqli_error($con);
            die;
        }
        
        //Carrega os parceiros
        $sql = " 
                SELECT 
                    partner.id,
                    partner.nome, 
                    contract.nome razaoSocial,
                    contract.cpfCnpj,
                    contract.inscricaoEstadual
                FROM 
                    partner
                INNER JOIN
                    contract ON contract.partnerId = partner.id
                ORDER BY
                    partner.id DESC
            ";
        $query = mysqli_query($con, $sql);
        $parceiros = array();
        
        //Carrega as empresas do novo gestor
        $mdlEmpresa = new Model_Empresa();
        $empresas = $mdlEmpresa->fetchAll()->toArray();
        while( $parceiro = mysqli_fetch_array($query) ) {
            
            //Trata o cnpj
            $cnpj = $parceiro['cpfCnpj'];
            if( strlen(trim($cnpj)) <= 11 ) {
                $cnpj = "";
            } else {
                $cnpj = str_replace(".", "", $cnpj);
                $cnpj = str_replace("/", "", $cnpj);
                $cnpj = str_replace("-", "", $cnpj);
                switch( strlen(trim($cnpj)) ) {
                    case 13:
                        $cnpj = "00".$cnpj;
                        break;
                    case 14:
                        $cnpj = "0".$cnpj;
                        break;
                }
                Core_Global::formatCnpj($cnpj);
            }
                $continue = false;
            foreach( $empresas as $empresa ) {
                if( $empresa['razao_social'] == $parceiro['razaoSocial'] ) {
                    $continue = true;
                    break;
                }
            }

            if( $continue ) {
                continue;
            }
            $parceiros[] = array(
                "id"                    => $parceiro['id'],
                "nome_comercial"        => utf8_encode($parceiro['nome']),
                "razao_social"          => utf8_encode($parceiro['razaoSocial']),
                "cnpj"                  => $cnpj,
                "inscricao_estadual"    => $parceiro['inscricaoEstadual']
            );
        }
        
        return $parceiros;
    }
    
    /**
     * Importa todos os ambientes/pontos
     */
    public function importarTodosAction() {
        
        //Verifica se a tabela está limpa
        $mdlEmpresa = new Model_Empresa();
        $empresas = $mdlEmpresa->fetchAll()->toArray();
        if( count($empresas) ) {
            Core_Notificacao::adicionarMensagem("Para a importação completa, a tabela empresa deve estar vazia");
            $this->redirect("/empresa");
        }
        
        //Carrega os parceiros
        $parceiros = $this->carregarParceirosLook();
        
        //Realiza as inserções
        $mdlEmpresa = new Model_Empresa();
        $mdlAmbiente = new Model_Ambiente();
        $idIncrement = 1;
        foreach( $parceiros as $parceiro ) {
            $parceiro['id_parceiro_look']  = $parceiro['id'];
            $parceiro['exibe_publicidade'] = 1;
            $parceiro['nome_comercial']    = utf8_decode($parceiro['nome_comercial']);
            $parceiro['razao_social']      = utf8_decode($parceiro['razao_social']);
            $parceiro['id']                = $idIncrement;
            $mdlEmpresa->insert($parceiro);
            $idIncrement ++;
        }
        $this->returnSuccess("Importação realizada com sucesso");
        die;
    }
}
