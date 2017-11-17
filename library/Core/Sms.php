<?php

/**
 * Classe utilitaria responsavel pelo envio de SMS
 * @author Gustavo
 */
class Core_Sms {

    static $url = "http://cdn.3midia.com.br:9710/http/send-message?username=3midia&password=teste123&message-type=sms.automatic&to=%s&message=%s";

    /**
     * Dispara um sms
     */
    static function send($to, $msg) {
        
        $url = sprintf(self::$url, urlencode("+".$to), urlencode($msg));
        $rs = file_get_contents($url);
        $mdlLogSms = new Model_LogSms();
        $mdlLogSms->add($to);
    }
}

?>
