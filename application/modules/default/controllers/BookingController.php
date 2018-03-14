<?php

class BookingController extends AbstractController {
    
    /**
     * Construtor
     * @param \Zend_Controller_Request_Abstract $request
     * @param \Zend_Controller_Response_Abstract $response
     * @param array $invokeArgs
     */
    public function __construct(\Zend_Controller_Request_Abstract $request, \Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
        
        //Inicia a model
        $this->_model = new Model_Booking();
        $this->_entity = $this->_model->getName();
        
        parent::__construct($request, $response, $invokeArgs);
        
        //Breadcrumb
        $this->addBreadcrumb("Booking", "/".$this->_entity);
    }
    
    /**
     * Lista de booking
     */
    public function indexAction() {

        //Gera a tabela
        $this->view->grid = new Core_Grid($this->_entity);
        $this->view->grid->addColumn("Id", "id", array("order"=>"desc"));
        $this->view->grid->addColumn("Campanha", "fk_campanha");
    }
    
    /**
     * Cadastra um novo registro
     */
    public function cadastrarAction() {
        
        //Breadcrumb
        $this->addBreadcrumb("Cadastro");
        
        //Renderiza a view
        $this->renderScript($this->_entity.'/form.phtml');
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
        if( !isset($_GET['data_inicio']) || !isset($_GET['data_fim']) || !isset($_GET['canais']) ) {
            $this->returnError("Faltam parâmetros para carregar as imagens");
            die;
        }
        $dataInicio = Core_Global::dataIso($_GET['data_inicio']);
        $dataFim = Core_Global::dataIso($_GET['data_fim']);
        $pagina = $_GET['pagina'];
        $canaisIds = $_GET['canais'];
        $campanhaId = $_GET['campanha_id'];
        $ambienteId = ( isset($_GET['ambiente_id']) ) ? $_GET['ambiente_id'] : null;

        //Carrega a lista de ambientes
        $mdlAmbiente = new Model_Ambiente();
        $ambientes = $mdlAmbiente->fetchAll()->toArray();
        Core_Global::attrToKey($ambientes, "id");
        Core_Global::encodeListUtf($ambientes, true);
        
        //Carrega os ambientes da campanha
        $mdlCampAmbientes = new Model_Generic("campanha_ambiente");
        $relCampAmbientes = $mdlCampAmbientes->fetchAll("id_campanha = ".$campanhaId)->toArray();
        $campAmbientesIds = array();
        foreach( $relCampAmbientes as $rel ) {
            $campAmbientesIds[] = $rel['id_ambiente'];
        }
        
        //Carrega a lista de empresas
        $mdlEmpresa = new Model_Empresa();
        $empresas = $mdlEmpresa->fetchAll()->toArray();
        Core_Global::attrToKey($empresas, "id");
        Core_Global::encodeListUtf($empresas, true);
        
        //Carrega a lista de endereços
        $mdlEndereco = new Model_Endereco();
        $enderecos = $mdlEndereco->fetchAll()->toArray();
        Core_Global::attrToKey($enderecos, "id");
        Core_Global::encodeListUtf($enderecos, true);
        
        //Carrega a lista de canais
        $mdlCanal = new Model_Canal();
        $canais = $mdlCanal->fetchAll(null, 'nome')->toArray();
        Core_Global::attrToKey($canais, "id");
        Core_Global::encodeListUtf($canais, true);

        //Calcula o número de fotos por ambiente
        $mdlIndices = new Model_S3BookingIndices();
        $rsSoma = $mdlIndices->calcularFotoAmbientes($canaisIds, $campAmbientesIds, $dataInicio, $dataFim);
        Core_Global::attrToKey($rsSoma, "id_ambiente");
        foreach( $rsSoma as $totalAmbiente ) {
            $ambId = $totalAmbiente['id_ambiente'];
            if( isset($ambientes[$ambId]) ) {
                
                //Adiciona a campanha à lista de canal
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
        $rs = $mdlIndices->pesquisar($canaisIds, $campAmbientesIds, $dataInicio, $dataFim, 56, $pagina, $ambienteId);
        Core_Global::encodeListUtf($rs['indices'], 1);
        foreach( $rs['indices'] as $i => $indice ) {
            $ambienteId = $indice['id_ambiente'];
            $ambiente = "Sem nome";
            if( isset($ambientes[$ambienteId]) ) {
                $ambiente = $ambientes[$ambienteId]['nome'];
                if( $ambientes[$ambienteId]['nome'] == 'Sem nome' ) {
                    $empresaId = $ambientes[$ambienteId]['id_empresa'];
                    if( isset($empresas[$empresaId]) ) {
                        $ambiente = $empresas[$empresaId]['nome_comercial'];
                        $enderedoId =  $ambientes[$ambienteId]['id_endereco'];
                        if( isset($enderecos[$enderedoId]) ) {
                            $ambiente .= " - " . $enderecos[$enderedoId]['complemento'];
                        }
                    }
                }
                
                
            }
            $rs['indices'][$i]['ambiente'] = $ambiente;
            $rs['indices'][$i]['data_foto'] = Core_Global::dataBr($rs['indices'][$i]['data_foto']);
        }

        //Ordena os canais
        $rs['canais'] = array();
        foreach( $canais as $canal ) {
            if( in_array($canal['id'], $canaisIds) !== false ) {
                $rs['canais'][] = $canal;
            }
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
                'Prefix' => "photos-plux/",
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
    
    /**
     * Exporta o booking
     */
    public function exportarAction() {
        $this->_helper->layout->disableLayout();
        $campanhaId                 = $_GET['campanhaId'];
        $canaisIds                  = $_GET['canaisIds'];
        $this->view->constTipo      = $_GET['constTipo'];
        $this->view->constLayout    = $_GET['constLayout'];
        $this->view->fotos          = $_GET['fotos'];
        $this->view->assinatura     = $_GET['assinatura'];
        $this->view->ambientes      = array();

        //Carrega os detalhes das fotos
        $mdlIndices = new Model_S3BookingIndices();
        $this->view->indices = array();
        $this->view->indices = $mdlIndices->fetchAll("`key` IN ('" . implode("','", $this->view->fotos) . "')", array("id_ambiente","data_foto"))->toArray();
        
        //Carrega os ambientes
        $mdlAmbiente = new Model_Ambiente();
        $ambientesIds = array();
        foreach( $this->view->indices as $indice ) {
            $ambientesIds[$indice['id_ambiente']] = $indice['id_ambiente'];
        }
        $ambientes = $mdlAmbiente->fetchAll("id IN (" . implode(",", $ambientesIds) . ")")->toArray();
        $this->view->ambientes = array();
        foreach( $ambientes as $ambiente ) {
            $this->view->ambientes[$ambiente['id_canal']][] = $ambiente;
        }
                
        //Recupera o nome dos ambientes
        Core_Global::attrToKey($ambientes, "id");
        foreach( $this->view->indices as $i => $indice ) {
            if( isset($ambientes[$indice['id_ambiente']]) ) {
                $this->view->indices[$i]['nome_ambiente'] = $ambientes[$indice['id_ambiente']]['nome'];
            } else {
                $this->view->indices[$i]['nome_ambiente'] = "Não identificado";
            }
        }
        
        //Carrega os detalhes da campanha
        $mdlCampanha = new Model_Campanha();
        $this->view->campanha = $mdlCampanha->find($campanhaId)->current()->toArray();
        
        //Carrega os detalhes do cliente
        $mdlEmpresa = new Model_Empresa();

        //Cliente
        if( $this->view->campanha['id_empresa_cliente'] ) {
            $this->view->cliente = $mdlEmpresa->find($this->view->campanha['id_empresa_cliente'])->current()->toArray();
        }
        
        //Agência
        if( $this->view->campanha['id_empresa_agencia'] ) {
            $this->view->agencia = $mdlEmpresa->find($this->view->campanha['id_empresa_agencia'])->current()->toArray();
        }
        
        //Carrega os canais
        $mdlCanal = new Model_Canal();
        $this->view->canais = $mdlCanal->fetchAll("id IN (" . implode(',', $canaisIds) . ")")->toArray();
        
        //Baixa as imagens temporárias
        $this->baixarFotos($this->view->indices);
        
        //Importa a view
        $this->renderScript("booking/layouts/default.phtml");
    }
    
    /**
     * Baixa as fotos temporariamente
     */
    public function baixarFotos(&$indices) {
        
        //Limpa os arquivos antigos
        $path = './tmp/booking_fotos/';
        $arquivos = scandir($path);
        foreach( $arquivos as $arquivo ) {
            if( $arquivo != '.' && $arquivo != '..' ) {
                unlink($path . $arquivo);
            }
        }
        
        //Baixa a novas imagens
        foreach( $indices as $i => $indice ) {
            $idx = strrpos($indice['url'], "/") + 1;
            $nomeArquivo = substr($indice['url'], $idx);
            $conteudo  = file_get_contents($indice['url']);
            $handle = fopen($path . $nomeArquivo, 'w');
            fwrite($handle, $conteudo);
            fclose($handle);
            
            $indices[$i]['fotoLocal'] = $path . $nomeArquivo;
        }
    }
    
    /**
     * Salva o registro
     */
    public function salvarAction() {
       
        $canais = $_POST['canais_ids'];
        unset($_POST['canais_ids']);
        
        //Salva o registro
        $booking = array(
            "id_campanha" => $_POST['campanha_id'],
            "tipo" => $_POST['tipo'],
            "layout" => $_POST['layout'],
            "assinatura" => $_POST['assinatura'],
            "fotos" => $_POST['fotos']
        );
        $rs = $this->_model->salvar($booking);
        if( $rs ) {
            //Salva a relação com os canais
            $mdlBookCanal = new Model_Generic("booking_canal");
            $mdlBookCanal->delete("id_booking = " . $booking['id']);
            foreach( $canais as $canalId ) {
                $mdlBookCanal->insert(array(
                    "id_booking" => $booking['id'],
                    "id_canal" => $canalId
                ));
            }
            
            //Gera a notificação
            Core_Notificacao::adicionarMensagem("Registro salvo com sucesso", "success");
        }
        
        //Resposta
        $this->returnSuccess();
    }
    
    /**
     * Método Editar
     */
    public function editarAction() {
        
        //Breadcrumb
        $this->addBreadcrumb("Edição");
        
        //Carrega os dados
        $registro = $this->_model->find($this->getParam('id'))->current()->toArray();
        Core_Global::encodeListUtf($registro);
        
        //Carrega os relacionamentos com os canais
        $modRel = new Model_Generic("booking_canal");
        $bookCanais = $modRel->fetchAll("id_booking = " . $this->getParam('id'))->toArray();
        foreach( $bookCanais as $bookCanal ) {
            $registro['canais'][] = $bookCanal['id_canal'];
        }
        
        $this->view->registro = $registro;
        
        //Renderiza a view
        $this->renderScript($this->_entity.'/form.phtml');
    }
}
