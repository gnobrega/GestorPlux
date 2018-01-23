<?php

//SERVIDOR
DEFINE('SERVER_HOST', 'www.wikipix.com.br');

//Mensagens
DEFINE('MSG_SALVO_SUCESSO', 'Registro salvo com sucesso');
DEFINE('MSG_CODIGO_ATIVACAO', 'Codigo de ativacao: %s');
DEFINE('MSG_SEM_PERMISSAO', 'Você não tem permissão para editar esse registro');
DEFINE('MSG_TPL_NAO_ENCONTRADO', 'Template não encontrado');

//Dados de conexão ao banco de dados do Gestor antigo
DEFINE('GESTOR_LOOK_DB_HOST', 'tresmidia-db.ccoqe3p4mmiy.sa-east-1.rds.amazonaws.com');
DEFINE('GESTOR_LOOK_DB_USER', 'tresmidia');
DEFINE('GESTOR_LOOK_DB_PASS', 'xsara-99');
DEFINE('GESTOR_LOOK_DB_NAME', 'li_gestor');

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

//AWS
define("AWS_S3_CHECKIN_URL", "https://s3.amazonaws.com/checkin-fotografico/");
define("AWS_S3_CHECKIN_BUCKET", "checkin-fotografico");
define("AWS_S3_URL", "https://s3.amazonaws.com/");

//Lista de valores
class Constants {
    static $CONTATO_TIPO = array(
        1 => "Telefone",
        2 => "Email"
    );
    static $BOOKING_LAYOUT = array(
        1 => "Padrão"
    );
    static $LOOK_CANAIS = array(
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