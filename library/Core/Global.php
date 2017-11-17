<?php

/**
 * Classe utilitária genérica
 *
 * @author Gustavo
 */
class Core_Global {

    /**
     * Armazena as cota�oes
     * @var array
     */
    static $rates = array();

    /**
     * Converte a data para o formato y-MM-dd HH:mm:ss
     * @param string $data
     * @return string
     */
    public static function dataHoraIso($data) {
        $data = new Zend_Date($data);
        
        return $data->toString('y-MM-dd HH:mm:ss');
    }

    /**
     * Converte a data para o formato dd/MM/y HH:mm:ss
     * @param string $data
     * @return string
     */
    public static function dataHoraBr($data) {
        if( $data && $data != '0000-00-00 00:00:00' ) {
            $data = new Zend_Date($data);

            return $data->toString('dd/MM/y HH:mm:ss');
        } else {
            return '';
        }
    }

    /**
     * Converte a data e hora para o formato YYYY-mm-dd H:i:s
     * @param string $data
     * @return string
     */
    public static function dataIso($data) {
        $data = new Zend_Date($data);
        return $data->toString(Zend_Date::YEAR . '-' . Zend_Date::MONTH . '-' . Zend_Date::DAY);
    }

    /**
     * Converte a data para o formato dd/mm/YYYY
     * @param string $data
     * @return string
     */
    public static function dataBr($data) {
        $data = new Zend_Date($data);
        return $data->toString(Zend_Date::DAY . '/' . Zend_Date::MONTH . '/' . Zend_Date::YEAR);
    }

    /**
     * Funcao gen�rica para o envio de emails
     * @param array $destinatarios
     * @param string $assunto
     * @param string $mensagem
     */
    public static function enviarEmail($destinatarios, $assunto, $mensagem) {
        require_once(APPLICATION_PATH . "/../library/PHPMailer-master/PHPMailerAutoload.php");
        
        //Recupera os dados de configuracao de email
        $smtp = new Zend_Config_Ini(APPLICATION_PATH . "/configs/mail.ini", "marktvpix");
      
        //Seta as configuracoes do servico de SMTP
        $mail = new PHPMailer;
        //$mail->SMTPDebug = 4;                                 // Enable verbose debug output

        $mail->isSMTP();
        $mail->Host         = $smtp->host;
        $mail->SMTPAuth     = $smtp->auth;
        $mail->Username     = $smtp->username;
        $mail->Password     = $smtp->password;
        $mail->SMTPSecure   = $smtp->ssl;
        $mail->Port         = $smtp->port;
                
        //Possibilita trabalhar com um ou varios destinatarios
        if (!isset($destinatarios[0])) {
            $tmp = $destinatarios;
            $destinatarios = array();
            $destinatarios[0] = $tmp;
        }
        
        //Rementente
        $mail->setFrom($smtp->username, $smtp->from);

        //Adiciona os destinatarios
        foreach ($destinatarios as $destinatario) {
            $mail->addAddress($destinatario);
        }
        $mail->Subject = utf8_decode($assunto);
        $mail->Body = utf8_decode($mensagem);
        $mail->isHTML(true);

        if( $mail->send() ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retorna apenas n�meros
     * @param string $telefone
     */
    public static function limparTelefone($telefone) {
        $filter = new Zend_Filter_Alnum();
        return $filter->filter($telefone);
    }

    /**
     * Converte o valor atributo interno em chave do array
     * @param array $arr Lista com as entidade em formato de array
     * @param string $attr Atributo da entidade que ser� convertido em chave do array
     * @return string $string
     */
    public static function attrToKey(&$arr, $attr) {
        $newArray = array();
        foreach ($arr as $subArr) {
            if (!isset($subArr[$attr])) {
                throw new Exception("A chave '{$attr}' n�o foi encontrada no array");
            }
            $newArray[$subArr[$attr]] = $subArr;
        }
        $arr = $newArray;
    }

    /**
     * Converte dolar em real
     * @param number $valor
     */
    public static function moneyReal($valor, $moeda = "") {
        $valor = number_format($valor, 2, ",", ".");
        if ($moeda) {
            switch ($moeda) {
                case 'BRL':
                    $prefix = "R$";
                    break;
                case 'USD':
                    $prefix = "$";
                    break;
                default:
                    $prefix = "($moeda) ";
                    break;
            }
        } else {
            $prefix = "R$";
        }

        return $prefix . $valor;
    }

    /**
     * Converte real em valor internacional
     * @param number $valor
     */
    public static function moneyToNumber($valor) {
        $valor = trim(str_replace("R$", "", $valor));
        $valor = str_replace(".", "", $valor);
        $valor = (float) str_replace(",", ".", $valor);

        return number_format($valor, 2, '.', '');
    }

    /**
     * Retorna o usu�rio da sess�o
     */
    public static function getUser() {
        $sesUsuario = new Zend_Session_Namespace('usuario');
        return $sesUsuario->data;
    }

    /**
     * Adiciona a m�scara ao telefone
     * @param numeric $numero 
     * @return string
     */
    public function formatarTelefone($numero) {
        $strNumero = "(" . substr($numero, 0, 2) . ")";
        $strNumero .= " " . substr($numero, 2, 4);
        if (strlen($numero) == 10) {
            $strNumero .= "-" . substr($numero, 6, 4);
        } else {
            $strNumero .= "-" . substr($numero, 6, 5);
        }

        return $strNumero;
    }

    /**
     * Descodifica uma lista em UTF8
     * @param array $list
     */
    static function decodeListUtf(&$list) {
        foreach ($list as &$item) {
            if (is_string($item)) {
                $item = utf8_decode($item);
            }
        }
    }

    /**
     * Codifica uma lista em UTF8
     * @param array $list
     * @param boolean $matriz
     */
    static function encodeListUtf(&$list, $matriz = false) {
        if (!$matriz) {
            foreach ($list as &$item) {
                if (is_string($item)) {
                    $item = utf8_encode($item);
                }
            }
        } else {
            foreach ($list as &$list2) {
                foreach ($list2 as &$item) {
                    if (is_string($item)) {
                        $item = utf8_encode($item);
                    }
                }
            }
        }
    }

    /**
     * Imprime um conteúdo em Javascript
     * @param string $cmd
     */
    static function toJs($cmd) {
        echo '<script type="text/javascript">';
        echo $cmd;
        echo '</script>';
    }

    /**
     * Método genérico de upload
     * @param string $sub Subdiretório
     */
    static function upload() {

        //Resposta
        $resp = array();
        $folder = isset($_POST['folder']) ? $_POST['folder'] : '';
        $prefix = isset($_POST['prefix']) ? $_POST['prefix'] : '';
        
        foreach ($_FILES as $key => $FILE) {
            if( isset($_POST['nameFile']) ) {
                $nameFile = $_POST['nameFile'];
            } else {
                $tmpName = substr($FILE['name'], 0, strpos($FILE['name'], "."));
                $tmpName = self::formatFileName($tmpName);
                $nameFile = time() . "_" . $tmpName;
            }
            $k = str_replace("_file", "", $key);
            

            //Verifica se o arquivo existe
            if (is_uploaded_file($_FILES[$key]['tmp_name'])) {

                //Extrai a extensão
                $exp = explode('.', $_FILES[$key]['name']);
                $ext = end($exp);

                //Define o diretório
                $path = './upload/';
                $dir = $path . $folder;
                if ($prefix) {
                    $file = $dir . '/' . $prefix . '_' . $nameFile . '.' . $ext;
                } else {
                    $file = $dir . '/' . $nameFile . '.' . $ext;
                }

                //Cria o diretório
                if (!is_dir($dir)) {
                    mkdir($dir, 0777);
                    chown($dir, 'apache');
                    chmod($dir, 0777);
                }

                //Tranfere o arquivo
                if (move_uploaded_file($_FILES[$key]['tmp_name'], $file)) {

                    //Retorna os dados
                    $resp[$k]['status'] = 'success';
                    $resp[$k]['src'] = $file;
                    $resp[$k]['filesize'] = filesize($file);
                } else {
                    $resp[$k]['error'] = "Falha ao transferir o arquivo";
                    $resp[$k]['status'] = 'fail';
                    break;
                }
            } else {
                $resp[$k]['error'] = "Arquivo não encontrado";
                $resp[$k]['status'] = 'fail';
                break;
            }
        }

        return $resp;
    }
    
    /**
     * Remove os acentos de uma string
     */
    static function formatFileName($string){
        $str = preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/"),explode(" ","a A e E i I o O u U n N"),$string);
        $str = strtolower($str);
        $str = str_replace(" ", "_", $str);
        return $str;
    }

    /**
     * Remover um arquivo do diretório upload
     * @param string $src
     */
    static function removeFile($src) {
        $folders = explode('/', $src);
        $resp = array();

        //Confirma se o diretório raiz é o de upload
        if (isset($folders[1]) && $folders[1] == 'upload') {
            $src = '.'.$src;
            if (is_file($src)) {
                unlink($src);
                $resp['status'] = 'success';
            } else {
                $resp['status'] = 'fail';
                $resp['msg'] = 'Esse arquivo não existe';
            }
        } else {
            $resp['status'] = 'fail';
            $resp['msg'] = 'Você não pode excluir arquivos de outros diretórios';
        }

        return $resp;
    }

    /**
     * Verifica se o módulo existe
     * @param string $module
     */
    static function moduleExists($module) {
        $modules = Zend_Controller_Front::getInstance()->getControllerDirectory();
        return isset($modules[$module]);
    }

    /**
     * Executa uma requisição assíncrona
     */
    static function postAsync($url, $post_array = array(), $timeout = 2, $error_report = FALSE) {
        // PREPARE THE POST STRING
        $post_string = NULL;
        foreach ($post_array as $key => $val) {
            $post_string .= $key . '=' . urlencode($val) . '&';
        }
        $post_string = rtrim($post_string, '&');

        // PREPARE THE CURL CALL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Circle)");

        // EXECUTE THE CURL CALL
        $htm = curl_exec($curl);
        $err = curl_errno($curl);
        $inf = curl_getinfo($curl);

        // ON FAILURE
        if (!$htm) {
            // PROCESS ERRORS HERE
            if ($error_report) {
                echo "CURL FAIL: $url TIMEOUT=$timeout, CURL_ERRNO=$err";
                echo "<pre>\n";
                var_dump($inf);
                echo "</pre>\n";
            }
            curl_close($curl);
            die;
            return FALSE;
        }

        // ON SUCCESS
        curl_close($curl);
        return $htm;
    }
    
    /**
     * Substitui o método nativo file_get_content
     */
    static function request($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($ch);
        curl_close($ch);
        
        return $content;
    }
    
    /**
     * Redimensiona um arquivo
     */
    static function crop($src, $config, $widthFinal, $heightFinal) {
        $src = "." . $src;
        $rs = array();
        if( !is_file($src) ) {
            throw new Exception('Arquivo não encontrado');
        }
        
        //Bibliote de crop
        require_once('Class/m2brimagem.class.php');
        
        $oImg = new m2brimagem( $src );
        if( $oImg->valida() == 'OK' ) {
            
            //Recorta a imagem
            $oImg->posicaoCrop( $config['x'], $config['y'] );
            $oImg->redimensiona( $config['width'], $config['height'], 'crop' );
            
            //Redimensiona para o tamanho final
            $oImg->redimensiona( $widthFinal, $heightFinal );
            $oImg->grava($src);
            
            $rs['status'] = 'success';
        } else {
            throw new Exception('Imagem inválida');
        }
        
        echo json_encode($rs);
    }
    
    /**
     * Aplica o padrão aos nomes das classes
     */
    static function ucFirstNameClass($str) {
        $arr = explode('_', $str);
        foreach( $arr as &$item ) {
            $item = ucfirst($item);
        }
        $nameClass = implode('_', $arr);
        return $nameClass;
    }
    
    /**
     * Verifica se o objeto de configuração do template possui imagem
     */
    static function templateGetFiles($fields, &$reg) {
        
        //Tipos de arquivos compatíveis
        $lstTypes = array('image', 'video');
        $mdlArquivo = new Model_Arquivo();
        
        //Percorre os campos em busca de um arquivo
        foreach( $fields as $k=>$field ) {
            if( isset($field->type) && in_array($field->type, $lstTypes ) !== FALSE ) {
                $name = $field->name;
                if( isset($reg[$name]) ) {
                    $value = $reg[$name];
                    $lst = $mdlArquivo->find($value)->toArray();
                    if( count($lst) ) {
                        $arquivo = $lst[0];
                        $reg[$name] = $arquivo;
                    }
                }
            }
        }
        
    }
    
    /**
     * Copia a pasta
     */
    static function syncFolder($src,$dst) { 
        
        //Carrega os arquivos da fonte
        $dirSrc = opendir($src); 
        $dirDst = opendir($dst); 
        
        //Cria a pasta de destino
        @mkdir($dst, 0777);
        chown($dst, 'apache');
        chmod($dst, 0777);
        $lstFilesDstDel = array();
        
        //Lista todos os arquivos da pasta destino
        while(false !== ( $file = readdir($dirDst)) ) { 
            if (( $file != '.' ) && ( $file != '..' )) { //Ignora os 2 primeiros itens
                if ( is_dir($src) ) {
                    $lstFilesDstDel[] = $file;
                }
            }
        }
        
        //Percorre os arquivos da fonte
        while(false !== ( $file = readdir($dirSrc)) ) { 
            if (( $file != '.' ) && ( $file != '..' )) { //Ignora os 2 primeiros itens
                $fileSrc = $src . '/' . $file;
                $fileSrcSize = filesize($fileSrc);
                
                //Verifica se é um diretório
                if ( is_dir($src . '/' . $file) ) { //Se for um diretório copia por inteiro
                    recurse_copy($src . '/' . $file,$dst . '/' . $file); 
                } 
                else { //Copia o arquivo
                    $allowCopy = true;
                    $fileDst = $dst . '/' . $file;
                    
                    //Se o arquivo estiver na lista de exclusão remove do array
                    if( array_search($file, $lstFilesDstDel) !== FALSE ) {
                        $k = array_search($file, $lstFilesDstDel);
                        unset($lstFilesDstDel[$k]);
                    }
                    
                    //Verifica se houve alteração
                    if( is_file($fileDst) ) {
                        $fileDstSize = filesize($fileDst);
                        if( $fileSrcSize == $fileDstSize ) {
                            $allowCopy = false;
                        }
                    }
                    
                    //Se o arquivo for diferente, sobrescreve
                    if( $allowCopy ) {
                        copy($fileSrc,$dst . '/' . $file); 
                    }
                } 
            } 
        } 

        //Sincroniza as exclusões
        foreach( $lstFilesDstDel as $fileDst ) {
            $file = $dst . '/' . $fileDst;
            if( is_file($file) ) {
                unlink($file);
            }
        }
        
        closedir($dirSrc); 
        closedir($dirDst); 
    } 
    
    /**
     * Força a exclusão de um diretório
     */
    public static function rm($dirPath, $force = false) {
        if( is_dir($dirPath) ) {
            $listFiles = scandir($dirPath);
            array_shift($listFiles);
            array_shift($listFiles);
            
            if( count($listFiles) ) { //Possui arquivos
                foreach( $listFiles as $file ) {
                    $realFile = $dirPath . DIRECTORY_SEPARATOR . $file;
                    if( is_file($realFile) ) {
                        unlink($realFile);
                    } else if( is_dir($realFile) ) {
                        self::rm($realFile, true);
                    }
                }
            }
            
            //Remove o diretório
            rmdir($dirPath);
        }
        //unlink($dirPath);
    }
    
    /**
     * Retorna a página anterior
     */
    public static function back() {
        echo "<script>history.back()</script>";
        die;
    }
    
    /**
     * Envia uma mensagem ao usuário através do sistema
     */
    public static function enviarNotificacao($usuarioId, $assunto, $texto, $usuarioIdRemetente) {
        $mdlUsuario = new Model_Usuario();
        $usuarios = $mdlUsuario->find($usuarioId)->toArray();
        if( count($usuarios) ) {
            $mdlNotificacao = new Model_Notificacao();
            $mdlNotificacao->insert(array(
                "id_usuario" => $usuarioIdRemetente,
                "assunto" => $assunto,
                "texto" => $texto,
                "horario" => date("Y-m-d H:i:s"),
                "id_usuario_receptor" => $usuarioId));
        }
    }
    
    /**
     * Ordena uma matriz de acordo com uma chave interna
     */
    public static function sortBy($array, $key, $order = 'ASC') {
        $sorter = array();
        $ret = array();
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii] = $va[$key];
        }
        if( $order == 'ASC' ) {
            asort($sorter);
        } else {
            arsort($sorter);
        }
        foreach ($sorter as $ii => $va) {
            $ret[$ii] = $array[$ii];
        }
        $array = $ret;
        return $array;
    }
    
    /**
     * Une duas listas intercalando os elementos
     */
    public static function mergeDistributed($lista1, $lista2) {
        if( count($lista1) > count($lista2) ) {
            $listaMaior = $lista1;
            $listaMenor = $lista2;
        } else {
            $listaMaior = $lista2;
            $listaMenor = $lista1;
        }
        $merge = array();
        $div = count($listaMaior) / count($listaMenor);
        $intervaloPadrao = $intervalo = (int)$div;
        $sobra = $div - $intervaloPadrao;
        $count = 0;
        
        $j = 0;
        foreach( $listaMaior as $i => $item ) {
            $count++;
            $merge[] = $item;
            if( $count >= $intervalo ) {
                $tmp = $sobra + $div;
                $intervalo = (int)$tmp;
                $sobra = $tmp - $intervalo;
                $count = 0;
                
                if( isset($listaMenor[$j]) ) {
                    $merge[] = $listaMenor[$j];
                }
                $j++;
            }
        }
        return $merge;
    }
}

?>
