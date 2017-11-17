<?php

//SERVIDOR
DEFINE('SERVER_HOST', 'www.wikipix.com.br');

//Firebase
DEFINE('GOOGLE_AUTHENTICATOR_SECRET', 'ORNR57RTITLIB2U3');
DEFINE('FIREBASE_DATABASE_URL', 'https://markswitch-7db7a.firebaseio.com/');
DEFINE('FIREBASE_DATABASE_MARKPLAYER_URL', 'https://markplayer-4733c.firebaseio.com/');
DEFINE('FIREBASE_NOTIFICATION_API_KEY', 'AIzaSyDGfwITwJI9jsV_13uUoHHD1rf85bxNbAM' );

//Mensagens
DEFINE('MSG_SALVO_SUCESSO', 'Registro salvo com sucesso');
DEFINE('MSG_CODIGO_ATIVACAO', 'Codigo de ativacao: %s');
DEFINE('MSG_SEM_PERMISSAO', 'Você não tem permissão para editar esse registro');
DEFINE('MSG_TPL_NAO_ENCONTRADO', 'Template não encontrado');

//Perfis de usuários
DEFINE('PERFIL_ADMINISTRADOR', 1);
DEFINE('PERFIL_CLIENTE', 2);

//DIRETÓRIOS
define("PATH_PUBLIC", realpath(".") . DIRECTORY_SEPARATOR);
define("PATH_UPLOAD", PATH_PUBLIC . "upload" . DIRECTORY_SEPARATOR);
define("PATH_GRUPOS", PATH_UPLOAD . "grupos" . DIRECTORY_SEPARATOR);
define("PATH_TEMPLATES", "./upload/templates/");
define("PATH_TEMP", "./upload/tmp/");

//TIPO DE PUBLICAÇÃO DE COMUNICADOS ALLEGRO
define("ALLEGRO_TIPO_PUBLICACAO_BLOCOS", '1');
define("ALLEGRO_TIPO_PUBLICACAO_PESSOAS", '2');

//Clientes
define("CLIENTE_WIKIPIX", 1);

//Helpdesk
define("TICKET_PRIORIDADE_BAIXA", 1);
define("TICKET_PRIORIDADE_MEDIA", 2);
define("TICKET_PRIORIDADE_ALTA", 3);
define("TICKET_STATUS_NOVO", 1);
define("TICKET_STATUS_EM_ANDAMENTO", 2);
define("TICKET_STATUS_RESOLVIDO", 3);

//Templates customizados
define("TEMPLATE_ANIVERSARIO_ID", "aniversario");

//Usuário de sistema
define("USUARIO_SISTEMA_ID", 45);

//Lista de valores
class Constants {
    static $PAGAMENTO_FREQUENCIA = array(
        1 => "Único",
        2 => "Mensal"
    );
    static $PAGAMENTO_SITUACAO = array(
        1 => "Aberto",
        2 => "Quitado"
    );
    static $PLAYER_STATUS = array(
        0 => "Não homologado",
        1 => "Homologado",
        2 => "Preparado",
        3 => "Instalado",
        4 => "Defeito",
        5 => "Em manutenção",
    );
    static $NOTIFICACAO_TIPO_REMETENTE = array(
        0 => "Sistema"
    );
    static $PLAYER_LOG_TIPO = array(
        1 => "DS_OFFLINE"
    );
    
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