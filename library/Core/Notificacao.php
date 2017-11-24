<?php

/**
 * Classe utilit�ria respons�vel pelo registro de notifica��es
 *  Tipos de mensagens
 *      success, info, warning, danger
 * @author Gustavo
 */
class Core_Notificacao {

    /**
     * Conte�do a ser renderizado pela p�gina
     * @var string
     */
    private static $script = "";

    /**
     * @param string $msg
     * @param string $tipo Criticidade da informação
     */
    static function adicionarMensagem($msg, $tipo = 'info') {
        $dataSession = new Zend_Session_Namespace('notificacao');
        $arrMsg = array(
            'mensagem' => $msg,
            'tipo' => $tipo
        );
        $dataSession->data[] = $arrMsg;
    }

    /**
     * Exibe as mensagens salvas na sess�o
     */
    static function exibir() {
        //Recupera as mensagens da sessão e gera o javascript de exibi��o
        $dataSession = new Zend_Session_Namespace('notificacao');
        if (count($dataSession->data)) {
            foreach ($dataSession->data as $msg) {
                self::gerarScript($msg);
            }
        }

        //Limpa a sess�o
        $dataSession->unsetAll();

        //Renderiza o conte�do
        self::render();
    }

    /**
     * Gera o comando em javascript que exibir� as mensagens
     * @param array $msg
     */
    private static function gerarScript($msg) {
        self::$script .= "toastr.{$msg['tipo']}(\"{$msg['mensagem']}\");\n";
    }

    /**
     * Renderiza o javascript na tela
     */
    private static function render() {
        echo "<script>";
        echo self::$script;
        echo "</script>";
    }

}

?>
