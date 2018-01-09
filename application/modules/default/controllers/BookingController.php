<?php

class BookingController extends AbstractController {
    
    /**
     * Construtor
     * @param \Zend_Controller_Request_Abstract $request
     * @param \Zend_Controller_Response_Abstract $response
     * @param array $invokeArgs
     */
    public function __construct(\Zend_Controller_Request_Abstract $request, \Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
        parent::__construct($request, $response, $invokeArgs);
        
        //Breadcrumb
        $this->addBreadcrumb("Booking", "/".$this->_entity);
    }
    
    /**
     * Gestão de usuários
     */
    public function indexAction() {
    }
    
    /**
     * Armazena as informações do novo booking em sessão
     */
    public function manterEmSessaoAction() {
        $bookSession = new Zend_Session_Namespace('booking');
        $bookSession->form = $_GET;
        $this->returnSuccess();
    }
    
    /**
     * Carrega as imagens do bucket
     */
    public function carregarImagensS3($limite = 10, $ultimoRegistro = null) {
        if( !$ultimoRegistro ) {
            $limite ++;
        }
        
        //Se conecta ao bucket
        $s3Client = $this->getClientS3();
        
        //Carrega os dados das imagens
        $result = $s3Client->ListObjects([
            'Bucket' => AWS_S3_CHECKIN_BUCKET,
            'Prefix' => "photos/",
            'Marker' => $ultimoRegistro,
            'MaxKeys' => $limite
        ]);
        $list = $result->toArray();
        $imagens = array();

        if( !$ultimoRegistro ) {
            array_shift($list['Contents']);
        }
        
        //Adiciona a url
        foreach( $list['Contents'] as $i => $imagem ) {
            $imagens[$i] = $imagem;
            $imagens[$i]['url'] = AWS_S3_URL . AWS_S3_CHECKIN_BUCKET . "/" . $imagem['Key'];
            //$imagens[$i]['preview'] = "<img src='{$imagens[$i]['url']}' />";
        }
        
        //Retorna a lista de imagens
        return $imagens;
    }
    
    /**
     * Estabelece a conexão com o servidor S3/AWS
     */
    public function getClientS3() {
        require APPLICATION_PATH . "/../library/Aws/vendor/autoload.php";
        $sdk = new Aws\Sdk([
            'region'   => 'us-east-1',
            'version'  => 'latest'
        ]);
        $s3Client = $sdk->createS3();
        return $s3Client;
    }
    
    /**
     * Carrega as imagens via ajax
     */
    public function carregarImagensAction() {
        
        //Valida os parâmetros
        if( !isset($_GET['data_inicio']) || !isset($_GET['data_fim']) ) {
            $this->returnError("Faltam parâmetros para carregar as imagens");
            die;
        }
        $dataInicio = Core_Global::dataIso($_GET['data_inicio']);
        $dataFim = Core_Global::dataIso($_GET['data_fim']);
        $pagina = $_GET['pagina'];
        $ambienteId = ( isset($_GET['ambiente_id']) ) ? $_GET['ambiente_id'] : null;
        
        //Carrega a lista de ambientes
        $mdlAmbiente = new Model_Ambiente();
        $ambientes = $mdlAmbiente->fetchAll()->toArray();
        Core_Global::attrToKey($ambientes, "id");
        Core_Global::encodeListUtf($ambientes, true);
        
        //Carrega a lista de canais
        $mdlCanal = new Model_Canal();
        $canais = $mdlCanal->fetchAll(null, 'nome')->toArray();
        Core_Global::attrToKey($canais, "id");
        Core_Global::encodeListUtf($canais, true);

        //Calcula o número de fotos por ambiente
        $mdlIndices = new Model_S3BookingIndices();
        $rsSoma = $mdlIndices->calcularFotoAmbientes($dataInicio, $dataFim);
        Core_Global::attrToKey($rsSoma, "id_ambiente");
        foreach( $rsSoma as $totalAmbiente ) {
            $ambId = $totalAmbiente['id_ambiente'];
            if( isset($ambientes[$ambId]) ) {
                
                //Adiciona a camapanha à lista de canal
                $canalId = $ambientes[$ambId]['id_canal'];
                if( isset($canais[$canalId]) ) {
                    if( !isset($canais[$canalId]['ambientes'][$ambId]['indices']) ) {
                        $canais[$canalId]['ambientes'][$ambId] = $ambientes[$ambId];
                        $canais[$canalId]['ambientes'][$ambId]['indices'] = $rsSoma[$ambId]['total'];
                    }
                }
               
            }
        }
        
        //Carrega as imagens a partir dos índices
        $rs = $mdlIndices->pesquisar($dataInicio, $dataFim, 56, $pagina, $ambienteId);
        foreach( $rs['indices'] as $i => $indice ) {
            $ambienteId = $indice['id_ambiente'];
            $ambiente = "Ambiente não encontrado";
            $rs['indices'][$i]['ambiente'] = $ambiente;
            $rs['indices'][$i]['data_foto'] = Core_Global::dataBr($rs['indices'][$i]['data_foto']);
        }

        //Ordena os canais
        $rs['canais'] = array();
        foreach( $canais as $canal ) {
            $rs['canais'][] = $canal;
        }
        
        //Retorno
        $this->returnSuccess(null, $rs);
    }
    
    /**
     * Carrega os filtros de fotos
     */
    public function carregarFiltrosAction() {
        
        //Recupera os parâmetros
        $campanhaId = $_GET['campanhaId'];
        $canaisIds = $_GET['canaisIds'];
        
        //Carrega a relação entre campanha e ambiente
        $mdlRel = new Model_Generic("campanha_ambiente");
        $rels = $mdlRel->fetchAll("id_campanha = " . $campanhaId)->toArray();
        $ambientesIds = array();
        foreach( $rels as $rel ) {
            $ambientesIds[] = $rel['id_ambiente'];
        }
        $ambientesIds = implode(",", $ambientesIds);
 
        //Carrega os canais
        $mdlCanal = new Model_Canal();
        $mdlAmbiente = new Model_Ambiente();
        $canais = $mdlCanal->fetchAll("id IN ({$canaisIds})", "nome")->toArray();
        Core_Global::encodeListUtf($canais, true);
        $treeTmp = array();
        foreach( $canais as $canal ) {
            
            //Carrega os ambientes do canal
            $ambientes = $mdlAmbiente->fetchAll("id IN({$ambientesIds}) AND id_canal = {$canal['id']}")->toArray();
            Core_Global::encodeListUtf($ambientes, true);
            $subitens = array();
            foreach( $ambientes as $ambiente ) {
                $subitens[] = array(
                    "id" => "ambiente-".$ambiente['id'],
                    "text" => $ambiente['nome'],
                    "type" => "ambiente"
                );
            }
            
            $item = array(
                "id" => "canal-".$canal['id'],
                "text" => $canal['nome'],
                "children" =>$subitens
            );
            
            $treeTmp[] = $item;
        }
        
        //Remove os canais sem ambientes
        $tree = array();
        foreach( $treeTmp as $item ) {
            if( isset($item['children']) && count($item['children']) ) {
                $tree[] = $item;
            }
        }
        
        echo json_encode($tree);
        die;
    }
    
    /**
     * Gera os índices de todas as imagens no BucktS3 e armazendo no banco
     */
    public function gerarIndicesAction() {
        set_time_limit(0);
        $ultimoRegistro = null;
        $id = 0;
        
        //Se conecta ao bucket
        $s3Client = $this->getClientS3();
        
        //Limpa os índice antigos
        $mdlIndices = new Model_Generic("s3_booking_indices");
        $mdlIndices->delete("1 = 1");
        
        //Carrega os dados das imagens
        while( true ) {
            $result = $s3Client->ListObjects([
                'Bucket' => AWS_S3_CHECKIN_BUCKET,
                'Prefix' => "photos/",
                'Marker' => $ultimoRegistro,
                'MaxKeys' => 100
            ]);
            $list = $result->toArray();
            
            //Percorre a lista de arquivos
            foreach( $list['Contents'] as $i => $imagem ) {
                
                //Verifica se é uma imagem
                if( strrpos($imagem['Key'], ".jpg") === false ) {
                    continue;
                }
                
                $id ++;
                $arrModificacao = (array)$imagem['LastModified'];
                $indice = $this->extrairParametrosS3Key($imagem['Key']);
                $indice['id'] = $id;
                $indice['key'] = $imagem['Key'];
                $indice['data_modificacao'] = $arrModificacao['date'];
                $indice['url'] = AWS_S3_URL . AWS_S3_CHECKIN_BUCKET . "/" . $imagem['Key'];
                
                //Tratamento de erro
                if( ( $indice['id_usuario'] == "" ) ) {
                    echo "Arquivo fora do padrão: {$indice['key']}<br />";
                    continue;
                }
                
                //Persiste no banco
                $mdlIndices->insert($indice);
                $ultimoRegistro = $imagem['Key'];
            }
            
            //Interrompe o loop
            if( count($list['Contents']) < 100 ) {
                break;
            }
            
        } //endwhile
         
        //Retorno
        $this->returnSuccess("Índices criados com sucesso");
        die;
    }
    
    /**
     * Extrai os parâmetros da Key do arquivos S3
     */
    public function extrairParametrosS3Key($key) {
        $arr = explode("/", $key);
        $params = array();
        
        //Id do ambiente
        $params['id_ambiente'] = str_replace("location_", "", $arr[1]);
        
        //Data da foto
        if( count($arr) >= 5 ) {
            $params['data_foto'] = "{$arr[2]}-{$arr[3]}-{$arr[4]}";
            
            //Id do usuário
            $arr2 = explode("_", $arr[5]);
            $params['id_usuario'] = $arr2[2];

            //Identificação da tela
            $params['tela'] = $arr2[4];
        }
                
        return $params;
    }
    
    /**
     * Move os arquivos da estrutura antiga de diretórios para a nova
     */
    public function moverArquivosS3Action() {
        set_time_limit(0);
        $imagens = array();
        $ultimoRegistro = null;
        $total = 0;
        
        //Se conecta ao bucket
        $s3Client = $this->getClientS3();
        
        //Cria iterações de 100 itens para não sobrecarrega a memória
        while( true ) {
            $result = $s3Client->ListObjects([
                'Bucket' => AWS_S3_CHECKIN_BUCKET,
                'Prefix' => "photos/",
                'Marker' => $ultimoRegistro,
                'MaxKeys' => 100
            ]);
            $list = $result->toArray();
            
            //Adiciona a url
            foreach( $list['Contents'] as $i => $imagem ) {
                
                //Verifica se é uma imagem
                $key = $imagem['Key'];
                $ext = substr($key, -3);
                if( $ext == "jpg" ) {
                    //Move os arquivos
                    $params = explode("/", $key);
                    
                    //Trata apenas os arquivos no formato antigo
                    if( count($params) == 3 ) {
                        echo "Movendo o arquivo: {$key}<br />";

                        //Checa a chave do arquivo antes de transferir
                        $tmp = explode("_", $params[2]);
                        if( count($tmp) >= 8 ) {

                            //Extrai a data
                            $date = substr($params[2], 0, 10);
                            $date = str_replace("-", "/", $date);
                            $keyDst = $params[0]."/".$params[1]."/".$date."/".$params[2];

                            //Move a imagem para o novo diretório
                            $rs = $s3Client->copyObject(array(
                                'Bucket'     => AWS_S3_CHECKIN_BUCKET,
                                'Key'        => $keyDst,
                                'CopySource' => AWS_S3_CHECKIN_BUCKET . "/" . $key
                            ));
                            
                            //Remove a imagem original
                            if( $rs ) {
                                $s3Client->DeleteObject(array(
                                    'Bucket'     => AWS_S3_CHECKIN_BUCKET,
                                    'Key'        => $key,
                                ));
                            }
                            
                            //Contador
                            $total ++;
                        }
                    }
                }
                $ultimoRegistro = $imagem['Key'];
            }
            
            //Interrompe o loop
            if( count($list['Contents']) < 100 ) {
                break;
            }
        } //endwhile
        
        $this->returnSuccess("Fim da conversão. {$total} arquivos movidos");
        die;
    }
}
