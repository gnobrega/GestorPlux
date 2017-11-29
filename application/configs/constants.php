<?php

//SERVIDOR
DEFINE('SERVER_HOST', 'www.wikipix.com.br');

//Mensagens
DEFINE('MSG_SALVO_SUCESSO', 'Registro salvo com sucesso');
DEFINE('MSG_CODIGO_ATIVACAO', 'Codigo de ativacao: %s');
DEFINE('MSG_SEM_PERMISSAO', 'Você não tem permissão para editar esse registro');
DEFINE('MSG_TPL_NAO_ENCONTRADO', 'Template não encontrado');

//Opção nula das combos
DEFINE('SELECT_VALUE_NULL', '[*SELECT_NULL*]');

//Perfis de usuários
DEFINE('PERFIL_ADMINISTRADOR', 1);
DEFINE('PERFIL_CLIENTE', 2);

//DIRETÓRIOS
define("PATH_PUBLIC", realpath(".") . DIRECTORY_SEPARATOR);
define("PATH_UPLOAD", PATH_PUBLIC . "upload" . DIRECTORY_SEPARATOR);
define("PATH_GRUPOS", PATH_UPLOAD . "grupos" . DIRECTORY_SEPARATOR);
define("PATH_TEMP", "./upload/tmp/");

//Lista de valores
class Constants {
    static $CONTATO_TIPO = array(
        1 => "Telefone",
        2 => "Email"
    );
        
    /**
     * Recupera um valor específico
     */
    public static function get($const) {
        return Constants::$$const;
    }
    
    /**
     * Retorna a chave do item
     */
    public static function getKey($const, $label) {
        return array_search($label, Constants::$$const);
    }
};