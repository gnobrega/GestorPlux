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
                ->setLabel("Cliente")
                ->setFilter("anunciante = 1");
        
        //Agência
        $this->view->form->addField(Core_Form_Field::$TYPE_SELECT)
                ->setEmpty(true)
                ->setName("_id_empresa_agencia")
                ->setLabel("Agência");
        
        //PI
        $this->view->form->addField(Core_Form_Field::$TYPE_TEXT)
                ->setName("_pi")
                ->setLabel("PI");
        
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
}
