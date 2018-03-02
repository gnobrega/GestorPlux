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
        $this->view->grid->addColumn("Id", "id", array("order"=>"desc"));
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
        $importacao = null;
        if( isset($_GET['importacao']) ) {
            $importacao = array(
                "nome"      => $_GET['importacao']['nome'],
                "inicio"    => Core_Global::dataBr($_GET['importacao']['inicio']),
                "fim"       => Core_Global::dataBr($_GET['importacao']['fim']),
                "pi"        => $_GET['importacao']['pi']
            );
        }
        
        //Monta o formulário
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
        
        //Nome
        $campoNome = $this->view->form->addField(Core_Form_Field::$TYPE_TEXT)
                ->setName("_nome")
                ->setLabel("Nome")
                ->setRequired(true)
                ->setAutofocus(true);
                
        //Cliente
        $this->view->form->addField(Core_Form_Field::$TYPE_SELECT)
                ->setTable("empresa")
                ->setName("_id_empresa_cliente")
                ->setRequired(true)
                ->setLabel("Cliente")
                ->setFilter("anunciante = 1");
        
        //Agência
        $this->view->form->addField(Core_Form_Field::$TYPE_SELECT)
                ->setEmpty(true)
                ->setName("_id_empresa_agencia")
                ->setLabel("Agência");
        
        //PI
        $campoPi = $this->view->form->addField(Core_Form_Field::$TYPE_TEXT)
                ->setName("_pi")
                ->setLabel("PI");
        
        //Número de peças
        $this->view->form->addField(Core_Form_Field::$TYPE_TEXT)
                ->setName("_n_pecas")
                ->setLabel("Número de peças");
        
        //Produto
        $this->view->form->addField(Core_Form_Field::$TYPE_TEXT)
                ->setName("_produto")
                ->setLabel("Produto");
        
        //Canais
        $campoCanal = $this->view->form->addField(Core_Form_Field::$TYPE_SELECT)
                ->setTable("canal")
                ->setName("canais[]")
                ->setLabel("Canais")
                ->setMultiple(true);
        if( isset($registro['canais']) ) {
            $campoCanal->setValue($registro['canais']);
        }
        
        
        //Ambientes
        $this->view->form->addField(Core_Form_Field::$TYPE_SELECT)
                ->setName("ambientes[]")
                ->setLabel("Ambientes")
                ->setAttr("firstNull", false)
                ->setMultiple(true)
                ->setEmpty(true);
        
        //Início
        $campoInicio = $this->view->form->addField(Core_Form_Field::$TYPE_TEXT)
                ->setName("_inicio")
                ->setLabel("Início")
                ->addClass("datepicker");
        
        //Fim
        $campoFim = $this->view->form->addField(Core_Form_Field::$TYPE_TEXT)
                ->setName("_fim")
                ->setLabel("Fim")
                ->addClass("datepicker");
        
        //Preenche os campos com os dados vindo de importação
        if( $importacao ) {
            $campoNome->setValue($importacao['nome']);
            $campoInicio->setValue($importacao['inicio']);
            $campoFim->setValue($importacao['fim']);
            $campoPi->setValue($importacao['pi']);
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
        $registro['inicio'] = Core_Global::dataBr($registro['inicio']);
        $registro['fim'] = Core_Global::dataBr($registro['fim']);
        Core_Global::encodeListUtf($registro);
        
        //Carrega os relacionamentos com os canais
        $modRel = new Model_Generic("campanha_canal");
        $campCanais = $modRel->fetchAll("id_campanha = " . $this->getParam('id'))->toArray();
        foreach( $campCanais as $campCanal ) {
            $registro['canais'][] = $campCanal['id_canal'];
        }
        
        //Carrega os relacionamentos com os ambientes
        $modRel = new Model_Generic("campanha_ambiente");
        $campAmbientes = $modRel->fetchAll("id_campanha = " . $this->getParam('id'))->toArray();
        foreach( $campAmbientes as $campAmbiente ) {
            $registro['ambientes'][] = $campAmbiente['id_ambiente'];
        }
        
        //Monta o formulário
        $this->montarForm($registro);
        $this->view->registro = $registro;
        
        //Renderiza a view
        $this->renderScript($this->_entity.'/form.phtml');
    }
    
    public function salvarAction($return = false) {
        $_POST['_inicio'] = Core_Global::dataIso($_POST['_inicio']);
        $_POST['_fim'] = Core_Global::dataIso($_POST['_fim']);
        $rs = parent::salvarAction(true);
        
        //Cria o relacionamento com os canais
        $modRel = new Model_Generic("campanha_canal");
        $modRel->delete("id_campanha = " . $rs['id']);
        if( isset($_POST['canais']) ) {
            foreach( $_POST['canais'] as $canalId ) {
                $campCanal = array(
                    "id_canal" => $canalId,
                    "id_campanha" => $rs['id']
                );
                $modRel->insert($campCanal);
            }
        }
        
        //Cria o relacionamento com os ambientes
        $modRel = new Model_Generic("campanha_ambiente");
        $modRel->delete("id_campanha = " . $rs['id']);
        if( isset($_POST['ambientes']) ) {
            foreach( $_POST['ambientes'] as $ambienteId ) {
                $campAmbiente = array(
                    "id_ambiente" => $ambienteId,
                    "id_campanha" => $rs['id']
                );
                $modRel->insert($campAmbiente);
            }
        }

        //Redireciona a página
        $this->redirect("/".$this->_entity);
    }
    
    /**
     * Carrega os canais da campanha
     */
    public function canaisPorCampanhaAction() {
        if( !isset($_POST['filter']['entityParent']['id']) ) {
            $this->returnError("Informe o id da campanha");
            die;
        }
        $agenciaId = $_POST['filter']['entityParent']['id'];
        
        //Carrega os relacionamento com os canais
        $mdlRel = new Model_Generic("campanha_canal");
        $rels = $mdlRel->fetchAll("id_campanha = " . $agenciaId)->toArray();
        $canais = array();
        
        $modelCanal = new Model_Canal();
        foreach( $rels as $rel ) {
            $rs = $modelCanal->find($rel['id_canal'])->toArray();
            if( count($rs) ) {
                $canais[] = array(
                    "id" => $rs[0]['id'],
                    "label" => $rs[0]['nome']
                );
            }
        }
        Core_Global::encodeListUtf($canais, true);
        $this->returnSuccess(null, $canais);
        die;
    }
    
    /**
     * Carrega os ambientes da campanha
     */
    public function ambientesPorCampanhaAction() {
        if( !isset($_POST['filter']['entityParent']['id']) ) {
            $this->returnError("Informe o id da campanha");
            die;
        }
        if( !isset($_POST['extra']['canais']) ) {
            $this->returnError("Informe os canais");
            die;
        }
        $agenciaId = $_POST['filter']['entityParent']['id'];
        $canais = implode(",", $_POST['extra']['canais']);
                
        //Carrega os relacionamento com os ambientes
        $mdlRel = new Model_Generic("campanha_ambiente");
        $rels = $mdlRel->fetchAll("id_campanha = " . $agenciaId)->toArray();
        $ambientes = array();
        
        $modelAmbiente = new Model_Ambiente();
        foreach( $rels as $rel ) {
            $rs = $modelAmbiente->fetchAll("id = " . $rel['id_ambiente'] . " AND id_canal IN ({$canais})")->toArray();
            if( count($rs) ) {
                $ambientes[] = array(
                    "id" => $rs[0]['id'],
                    "label" => $rs[0]['nome']
                );
            }
        }
        Core_Global::encodeListUtf($ambientes, true);
        $this->returnSuccess(null, $ambientes);
        die;
    }
    
    /**
     * Importa os registros do sistema antigo
     */
    public function importarAction() {
        
        //Breadcrumb
        $this->addBreadcrumb("Importação");
        
        //Se conecta ao gestor antigo
        $con = mysqli_connect(GESTOR_LOOK_DB_HOST, GESTOR_LOOK_DB_USER, GESTOR_LOOK_DB_PASS, GESTOR_LOOK_DB_NAME);
        if( !$con ) {
            echo mysqli_error($con);
            die;
        }
        
        //Carrega os parceiros
        $sql = " 
                SELECT 
                    campanha.id,
                    campanha.campanha, 
                    campanha.inicio,
                    campanha.fim,
                    campanha.numeropi
                FROM 
                    campanha
                ORDER BY
                    campanha.id DESC
            ";
        $query = mysqli_query($con, $sql);
        $campanhas = array();
        
        //Carrega as empresas do novo gestor
        $mdlCampanha = new Model_Campanha();
        $campanhasLook = $mdlCampanha->fetchAll()->toArray();
        while( $campanha = mysqli_fetch_array($query) ) {
            $campanhas[] = array(
                "id"        => $campanha['id'],
                "campanha"  => utf8_encode($campanha['campanha']),
                "inicio"    => Core_Global::dataBr($campanha['inicio']),
                "fim"       => Core_Global::dataBr($campanha['fim']),
                "pi"        => $campanha['numeropi']
            );
        }
        $this->view->campanhas = $campanhas;
    }
    
    /**
     * Importa todas as campanhas do Gestor Look
     */
    public function importarTodosAction() {

        //Models
        $mdlCampanha = new Model_Campanha();
        $mdlCampanhaAmbiente = new Model_Generic("campanha_ambiente");
        $mdlCampanhaCanal = new Model_Generic("campanha_canal");
        
        //Verifica se a tabela está limpa
        $campanhas = $mdlCampanha->fetchAll()->toArray();
        if( count($campanhas) ) {
            Core_Notificacao::adicionarMensagem("Para a importação completa, a tabela campanha deve estar vazia");
            $this->redirect("/campanha");
        }
        
        //Se conecta ao gestor antigo
        $con = mysqli_connect(GESTOR_LOOK_DB_HOST, GESTOR_LOOK_DB_USER, GESTOR_LOOK_DB_PASS, GESTOR_LOOK_DB_NAME);
        if( !$con ) {
            echo mysqli_error($con);
            die;
        }
        
        //Carrega os parceiros
        $sql = " 
                SELECT 
                    *
                FROM 
                    campanha
                WHERE
                    inicio >= '2017-01-01'
                ORDER BY
                    id DESC
            ";
        $query = mysqli_query($con, $sql);
        $relCampanhaAmbiente = array();
        
        $idAutoIncrement = 1;
        $mdlAmbiente = new Model_Ambiente();
        while( $campanha = mysqli_fetch_array($query) ) {
            
            //Carrega a relação com os pontos
            $sql = " 
                SELECT 
                    *
                FROM 
                    campanha_location
                WHERE
                    campanhaId = {$campanha['id']}
            ";
            $query2 = mysqli_query($con, $sql);
            $canaisLook = array();
            while( $rel = mysqli_fetch_array($query2) ) {
                
                //Recupera o novo id do ambiente
                $ambientes = $mdlAmbiente->fetchAll("id_ponto_look = " . $rel['locationId'])->toArray();
                if( count($ambientes) ) {
                    $canalId = $ambientes[0]['id_canal'];
                    $canaisLook[$canalId] = $canalId;
                    $relCampanhaAmbiente[] = array(
                        "id_ambiente" => $ambientes[0]['id'],
                        "id_campanha" => $idAutoIncrement
                    );
                }
            }
            
            //Cria as relações com os canais
            $relCampanhaCanal = array();
            foreach( $canaisLook as $canalLook ) {
                if( isset(Constants::$LOOK_CANAIS[$canalLook]) ) {
                    $relCampanhaCanal[] = array(
                        "id_campanha" => $idAutoIncrement,
                        "id_canal" => Constants::$LOOK_CANAIS[$canalLook]
                    );
                }
            }
            
            //Monta o array da campanha            
            $campanhaTmp = array(
                "id" => $idAutoIncrement,
                "nome" => $campanha['campanha'],
                "inicio" => $campanha['inicio'],
                "fim" => $campanha['fim'],
                "pi" => $campanha['numeropi']
            );
            
            //Insere a campanha
            $mdlCampanha->insert($campanhaTmp);
            
            //Insere os relacionamentos com os ambientes
            foreach( $relCampanhaAmbiente as $campAmbiente ) {
                $mdlCampanhaAmbiente->insert($campAmbiente);
            }
            
            //Insere os relacionamentos com os canais
            foreach( $relCampanhaCanal as $campCanal ) {
                $mdlCampanhaCanal->insert($campCanal);
            }
            
            $idAutoIncrement++;
        }
        
        $this->returnSuccess("Importação realizada com sucesso");
        die;
    }
}
