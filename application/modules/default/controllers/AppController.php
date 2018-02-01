<?php

class AppController extends AbstractController {
    
    /**
     * Construtor
     */
    public function __construct(\Zend_Controller_Request_Abstract $request, \Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
        
        parent::__construct($request, $response, $invokeArgs);
    }
    
    
    /**
     * Lista os usuários em formato de Json
     */
    public function carregarUsuariosAction() {
        
        //Chave de verificação
        $chave = "gestor-plux-" . date("m-d");
        $chaveCript = sha1($chave);
        if( $this->getParam("chave") != $chaveCript ) {
            $this->returnError("Chave de validação inválida");
            die;
        }
        
        //Carrega os usuários
        $mdlUsuario = new Model_Usuario();
        $usuarios = $mdlUsuario->fetchAll()->toArray();
        Core_Global::encodeListUtf($usuarios, true);
        $this->returnSuccess("", $usuarios);
        die;
    }
    
    /**
     * Sincroniza os dados do app
     */
    public function sincronizarAction() {
        $rs = array();
        
        //Carrega todos os ambientes
        $mdlAmbiente = new Model_Ambiente();
        $mdlEndereco = new Model_Endereco();
        $ambientes = $mdlAmbiente->fetchAll(null, 'nome')->toArray();
        Core_Global::encodeListUtf($ambientes, true);
        foreach( $ambientes as $ambiente ) {
            $enderecos = $mdlEndereco->fetchAll("id = " . $ambiente['id_endereco'])->toArray();
            if( count($enderecos) ) {
                $endereco = $enderecos[0];
                $endereco['google_ref'] = utf8_encode($endereco['google_ref']);
                $endereco['complemento'] = utf8_encode($endereco['complemento']);
                $rs['locations'][] = array(
                    "id" => $ambiente['id'],
                    "name" => $ambiente['nome'],
                    "id_route" => 0,
                    "id_channel" => $ambiente['id_canal'],
                    "latitude" => $endereco['latitude'],
                    "longitude" => $endereco['longitude']
                );
            }
        }
        
        $this->returnSuccess("", $rs);
        die;
    }
}