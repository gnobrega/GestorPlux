<?php

/**
 * Casse modelo da entidade
 *
 * @author gustavonobrega
 */
class Model_Usuario extends Model_Abstract {
    
    /** Table name */
    protected $_name    = 'usuario';
    
    /**
     * Autentica o usuário
     * @param string $usuario
     * @param string $senha
     */
    public function autenticar($usuario, $senha, $senhaCript = "") {
        $usuario = $this->getAdapter()->quote($usuario);
        if( $senhaCript == "" ) {
            $senhaCript = sha1(md5($senha));
        }
        
        //Condição de autenticação
        $where = "login = {$usuario} AND senha = '{$senhaCript}'";
        
        //Verifica se o usuário existe
        $lstUsuario = $this->fetchAll($where)->toArray();
        Core_Global::encodeListUtf($lstUsuario, true);
        
        if( count($lstUsuario) ) {
            
            //Carrega os dados do perfil
            $mdlPerfil = new Model_Perfil();
            $lstPerfil = $mdlPerfil->find($lstUsuario[0]['id_perfil'])->toArray();
            
            //Disponibiliza os dados para a sessão
            $sesUsuario             = new Zend_Session_Namespace('usuario');
            $sesUsuario->id         = $lstUsuario[0]['id'];
            $sesUsuario->login      = $lstUsuario[0]['login'];
            $sesUsuario->nome       = $lstUsuario[0]['nome'];
            $sesUsuario->perfil     = $lstPerfil[0]['nome'];
            $sesUsuario->idPerfil   = $lstPerfil[0]['id'];

            return true;
        } else {
            
            return false;
        }
    }
    
}