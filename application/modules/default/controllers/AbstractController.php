<?php

class AbstractController extends Zend_Controller_Action {

    /**
     * Model da entidade
     * @var \Model_Abstract
     */
    protected $_model;

    /**
     * Armazena o id do item em edição
     * @var int
     */
    public $_currentId;
    
    /**
     * Construtur
     */
    public function __construct(\Zend_Controller_Request_Abstract $request, \Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
        parent::__construct($request, $response, $invokeArgs);
        
        //Desabilita o layout para respostas em ajax
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $this->_helper->viewRenderer->setNoRender(true);
            $this->_helper->layout->disableLayout();
        }
        
        //Importa as views das helpers
        $this->view->addScriptPath( APPLICATION_PATH . "/helpers/views/");
        
        //Seta a entidade
        if( $this->_model ) {
            $this->view->entity = $this->_model->getName();
        }
    }
    
    /**
     * Desabilita o layout
     */
    public function noLayout() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
    }

    /**
     * Carregamento genérico via ajax para a montagem da grid
     */
    public function loadGridAction() {

        //Carrega a model dinamicamente
        $params = $_GET;
        $result = $this->_model->loadGrid($params);

        echo $result;
    }

    /**
     * Método genérico
     */
    public function salvarAction($return = false) {
        
        //Monta a entidade
        $registro = array();
        foreach ($_POST as $k => $value) {

            //Converte as datas
            if( substr($k, 0, 4) == '_dt_') {
                $value = Core_Global::dataIso($value);
            }
            
            //O caracter "_" identifica que é um atributo da entidade
            if (substr($k, 0, 1) == '_') {
                $attr = substr($k, 1);
                $registro[$attr] = $value;
            }
            
        }

        //Salva o registro
        $resp = $this->_model->salvar($registro);
        if( $resp ) {
            //Recupera o id
            $_pk = $this->_model->getPrimary();
            $this->_currentId = $registro[$_pk];

            //Repostas
            $resp = array("status" => "success", "id" => $this->_currentId);
            if( isset($registro['nome']) ) {
                $resp['nome'] = utf8_encode($registro['nome']);
            }
            
            //Gera a notificação
            Core_Notificacao::adicionarMensagem("Registro salvo com sucesso", "success");
        } else {
            
            //Repostas
            $resp ["status"] = "fail";
            $resp ["msgErro"] = $this->_model->msgErro;
            
            //Gera a notificação
            Core_Notificacao::adicionarMensagem("Erro ao salvar o registro", "error");
            if( APPLICATION_ENV == "development" ) {
                Core_Notificacao::adicionarMensagem($this->_model->msgErro, "error");
            }
        }
        
        if( !$return ) {
            echo json_encode($resp);
        } else {
            return $resp;
        }
    }
    
    /**
     * Carrega os dados para popular uma combo
     */
    public function loadComboAction() {
        //Carrega a model dinamicamente
        $params = $_POST;
        $resp = array();
        
        try {
            $resp['data'] = $this->_model->loadCombo($params);
            $resp['status'] = 'success';
        } catch (Exception $exc) {
            $resp['status'] = 'fail';
            $resp['msgErro'] = $exc->getMessage();
        }

        //Json
        echo json_encode($resp);
    }

    /**
     * Método genérico
     */
    public function excluirAction() {
        $id = $_POST['id'];
        
        //Carrega os dados do registro
        $registro = $this->_model->find($id)->current()->toArray();
        
        //Verifica se possui imagem
        if( isset($registro['foto']) ) {
            $imagem = $registro['foto'];
        }

        //Exclui o registro
        $rs = $this->_model->deleteById($id);
        
        if( $rs ) {
            //Se a exclusão for bem sucedida remove a imagem
            if( isset($imagem) ) {
                Core_Global::removeFile($imagem);
            }

            //Reposta
            $resp = array("status" => "success", "msg" => "Registro removido com sucesso");
            echo json_encode($resp);
        }
    }

    /**
     * Método genérico de upload de arquivo
     */
    public function uploadFileAction($return = false) {
        
        //Executa o upload
        $upResp = Core_Global::upload();
        
        //Salva o arquivo na tabela de arquivos (casos exclusivos)
        if( isset($_POST['type']) ) {
            $mdlArquivo = new Model_Arquivo();
            foreach( $upResp as $k=>$item ) {
                $arquivo = array(
                    "local" => substr($item['src'], 1)
                );
                
                $mdlArquivo->salvar($arquivo);
                $upResp[$k]['id'] = $arquivo['id'];
            }
        }
        
        if( !$return ) {
            echo json_encode($upResp);
        } else {
            return $upResp;
        }
    }
    
    /**
     * Remove o arquivo
     */
    public function removerArquivoAction() {
        $delResp = Core_Global::removeFile($_POST['src']);
        
        echo json_encode($delResp);
    }
    
    /**
     * Retorna sucesso
     */
    public function returnSuccess($msg = "") {
        $opt = array('status'=>'success');
        if( $msg ) {
            $opt['msg'] = $msg;
        }
        echo json_encode($opt);
    }
    
    /**
     * Retorna erro
     */
    public function returnError($msg = "") {
        $opt = array('status'=>'fail');
        if( $msg ) {
            $opt['msgErro'] = $msg;
        }
        echo json_encode($opt);
    }
    
    /**
     * Redimensiona um arquivo
     */
    public function cropAction() {
        if( !isset($_POST['src']) ) {
            throw new Exception('Arquivo não encontrado');
        }
        
        //Recupera os parâmetros
        $src = $_POST['src'];
        $config = $_POST['config'];
        $widthFinal = $_POST['widthFinal'];
        $heightFinal = $_POST['heightFinal'];
        
        //Executa o corte
        Core_Global::crop($src, $config, $widthFinal, $heightFinal);
    }
    
    /**
     * Retorna à página anterior via comando javascript
     */
    public function voltar() {
        echo "<script>history.go(-1);</script>";
        die;
    }
    
    /**
     * Adiciona um breadcrumb
     */
    public function addBreadcrumb($label, $link = "") {
                
        //Inicia o breadcrumb
        if( !isset($this->view->breadcrumbs) ) {
            $this->view->breadcrumbs = array();
            $this->view->breadcrumbs = array(
                array("label" => "Home", "link" => "/")
            );
        }
        
        //Adiciona o item
        $this->view->breadcrumbs[] = array(
            "label" => $label,
            "link" => $link
        );
    }
    
}
