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
        $mdlEmpresa = new Model_Empresa();
        $mdlEndereco = new Model_Endereco();
        $ambientes = $mdlAmbiente->fetchAll(null, 'nome')->toArray();
        
        foreach( $ambientes as $ambiente ) {
            $enderecos = $mdlEndereco->fetchAll("id = " . $ambiente['id_endereco'])->toArray();
            $empresas = $mdlEmpresa->fetchAll("id = " . $ambiente['id_endereco'])->toArray();
            if( count($enderecos) && count($empresas) ) {
                $endereco = $enderecos[0];
                $empresa = $empresas[0];
                $endereco['google_ref'] = $endereco['google_ref'];
                $endereco['complemento'] = $endereco['complemento'];
                if( $ambiente['nome'] == 'Sem nome' ) {
                    $ambiente['nome'] = $empresa['nome_comercial'] . " - " . $endereco['complemento'];
                } else {
                    $ambiente['nome'] = $empresa['nome_comercial'] . " - " . $ambiente['nome'];
                }
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
        Core_Global::encodeListUtf($rs['locations'], true);
        
        //Carrega as campanhas
        $mdlCampanha = new Model_Campanha();
        $campanhas = $mdlCampanha->fetchAll(null, 'nome')->toArray();
        Core_Global::encodeListUtf($campanhas, true);
        foreach( $campanhas as $campanha ) {
            $rs['campaigns'][] = array(
                "id" => $campanha['id'],
                "name" => $campanha['nome']
            );
        }
        
        $this->returnSuccess("", $rs);
        die;
    }
}
