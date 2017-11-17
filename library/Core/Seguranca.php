<?php

/**
 * Classe utilit�ria respons�vel pela seguran�a do sistema
 * 
 * @author Gustavo
 */
class Core_Seguranca {
    
    /* Perfis utilizados pelo antigo Gestor 3 Mídia */
    static $PERFIL_ADMIN            = 1;
    static $PERFIL_CLIENTE          = 2;
    
    //Arquivo de permissões
    static $aclIni = null;
    
    /**
     * Verifica se o usuário tem acesso à página
     * @param Zend_Controller_Request_Abstract $request
     */
    static function validarAcesso(Zend_Controller_Request_Abstract $request) {
        
        //Libera os acessos via CLI da validação
        if( php_sapi_name() === 'cli' ) {
            return true;
        }
     
        //Flag de autenticação
        $autenticado = false;
        
        //Recupera os dados de acesso
        $module = ( $request->getModuleName() ) ? $request->getModuleName() : 'default';
        $controller = $request->getControllerName();
        $action = $module . '/' . $controller . '/' . $request->getActionName();

        //Lista de páginas públicas
        $aclIni = self::getAcl('public');
        $pagesPublic = $aclIni->access->toArray();
        
        //Dispensa a validação para as páginas públicas
        if( in_array($action, $pagesPublic) ) {
            return;
        }

        //Verifica as permissões antes de carregar a action
        $sesUsuario = new Zend_Session_Namespace('usuario');

        if( !$sesUsuario->id ) {
            
            //Usuário não autenticado
            $autenticado = false;
        } else {
            
            //Usuario autenticado
            $autenticado = true;
        }

        //Redireciona para página inicial
        if (!$autenticado) {
            if( $action != 'default/index/index' ) {
                Core_Notificacao::adicionarMensagem("A sua sessão expirou, realize o login novamente.", "warning");
            }
            $front = Zend_Controller_Front::getInstance();
            $front->getResponse()->setRedirect("/login");
            $front->getResponse()->sendResponse();
            die;
        }
        
        //Verifica o acesso
        $acesso = self::check($action, $sesUsuario->idPerfil);
        if( !$acesso ) {
            //Acesso negado
            $msg = "Você não possui permissão para executar essa operação.";

            //Ajax
            if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                throw new Exception($msg);
            } else {
                //Acesso direto
                throw new Exception($msg);
                Core_Notificacao::adicionarMensagem($msg, "danger");
                $front = Zend_Controller_Front::getInstance();
                $front->getResponse()->setRedirect("/default/error/acesso");
                $front->getResponse()->sendResponse();
                die;
            }
        }
    }
    
    /**
     * Carrega o arquivo acl.ini
     */
    static function loadAcl() {
        if( !self::$aclIni ) {
            
        }
    }
    
    /**
     * Retorna os dados do arquivo acl.ini
     */
    static function getAcl($nivel) {
        return self::$aclIni = new Zend_Config_Ini(APPLICATION_PATH . '/configs/acl.ini', $nivel);
    }
    
    /**
     * Verifica se o usuário está autenticado
     * 
     * @return boolean
     */
    static function autenticado() {
        
        //Verifica a sessão
        $usuarioSes = new Zend_Session_Namespace('usuario');
        if( !$usuarioSes->id ) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Verifica se o perfil tem acesso a uma ação
     * @param string $action
     * @param string $perfil
     */
    static function check($action, $perfil) {
        
        //Normaliza o formato da action
        if( substr($action, 0, 1) == '/' ) {
            $action = substr($action, 1);
        }
        
        //Administrador
        if( $perfil == self::$PERFIL_ADMIN ) {
            return true;
        } else { //Demais perfis
            
            //Carrega as acls
            $aclIni = self::getAcl('restrict');
            $pages = $aclIni->access->toArray();
            
           //Verifica se a action foi registrada
           $arr = explode('/', $action);
           
           if( isset($pages[$arr[0]]) && isset($pages[$arr[0]][$arr[1]]) && isset($pages[$arr[0]][$arr[1]][$arr[2]]) ) {
               $aclAction = $pages[$arr[0]][$arr[1]][$arr[2]];
               
               //Verifica se o perfil possui a permissão
               if( array_search($perfil, $aclAction) !== FALSE ) {
                   return true;
               }
           }
        }
     
        //Permissão negada
        return false;
    }

    /**
     * Criptografa uma senha
     * Algorítimo: Base64 < Inverte o resultado < Base64 < Acrescenta 4 caracteres
     * aletírios antes e depois
     * @param string $senha
     */
    static function encrypt($senha) {
        $senhaEncrypt = base64_encode($senha);
        $senhaEncrypt = base64_encode(strrev($senhaEncrypt));
        $prefix = self::geraSenha(4);
        $sufix = self::geraSenha(4);
        $senhaEncrypt = $prefix . $senhaEncrypt . $sufix;
        return $senhaEncrypt;
    }
    
    /**
     * Decriptografa uma senha
     * @param string $senha
     */
    static function decrypt($senhaEncrypt) {
        $senha = substr($senhaEncrypt, 4, -4);
        $senha = base64_decode($senha);
        $senha = strrev($senha);
        $senha = base64_decode($senha);
        
        return $senha;
    }

    /**
     * Função para gerar senhas aleatórias
     *
     * @author    Thiago Belem <contato@thiagobelem.net>
     *
     * @param integer $tamanho Tamanho da senha a ser gerada
     * @param boolean $maiusculas Se ter� letras mai�sculas
     * @param boolean $numeros Se ter� n�meros
     * @param boolean $simbolos Se ter� s�mbolos
     *
     * @return string A senha gerada
     */
    static function geraSenha($tamanho = 8, $maiusculas = true, $numeros = true, $simbolos = false) {
        $lmin = 'abcdefghijklmnopqrstuvwxyz';
        $lmai = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $num = '1234567890';
        $simb = '!@#$%*-';
        $retorno = '';
        $caracteres = '';

        $caracteres .= $lmin;
        if ($maiusculas)
            $caracteres .= $lmai;
        if ($numeros)
            $caracteres .= $num;
        if ($simbolos)
            $caracteres .= $simb;

        $len = strlen($caracteres);
        for ($n = 1; $n <= $tamanho; $n++) {
            $rand = mt_rand(1, $len);
            $retorno .= $caracteres[$rand - 1];
        }
        return $retorno;
    }
    
    /**
     * Recupera o usuário da sessão
     */
    static function getUser() {
        return new Zend_Session_Namespace('usuario');
    }

}

?>
