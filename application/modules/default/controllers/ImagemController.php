<?php

use MetzWeb\Instagram\Instagram;

class ImagemController extends AbstractController {
    
    public $PATH_SHARE;

    /**
     * Construtor
     * @param Zend_Controller_Request_Abstract $request
     * @param Zend_Controller_Response_Abstract $response
     * @param array $invokeArgs
     */
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
        parent::__construct($request, $response, $invokeArgs);
    }

    /**
     * Exibe a miniatura de uma imagem
     */
    public function thumbAction() {
        //$url = "https://s3.amazonaws.com/checkin-fotografico/photos-plux/location_114/2018/04/24/2018-04-24_user_9_station_0_IMG_20180424_1027132.jpg";
        $url = urldecode($_GET['url']);
        
        $filename = $url;
       
        $pos = strrpos($filename, ".") + 1;
        $ext = strtolower(substr($filename, $pos));
        if( $ext != "jpg" && $ext != "png" && $ext != "jpeg" ) {
            throw new Exception("Formato não suportado");
        }

        #pegando as dimensoes reais da imagem, largura e altura
        list($width, $height) = getimagesize($filename);

        #setando a largura da miniatura
        $new_width = (isset($_GET['width'])) ? $_GET['width'] : 200;
        #setando a altura da miniatura
        $new_height = $height * $new_width / $width;
        //$new_height = (isset($_GET['height'])) ? $_GET['height'] : 100;

        #gerando a a miniatura da imagem
        $image_p = imagecreatetruecolor($new_width, $new_height);
        if( $ext != 'png' ) {
            $image = imagecreatefromjpeg($filename);
            imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            header('Content-type: image/jpeg');
            imagejpeg($image_p, null, 50);
        } else {
            $image = imagecreatefrompng( $filename ); 
            imagealphablending( $image_p, false );
            imagesavealpha( $image_p, true );
            imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            header('Content-type: image/png');
            imagepng($image_p, null, 9);
        }
        imagedestroy($image_p);

        die;
    }
    
    /**
     * Exporta a imagem do template
     */
    public function templateToJpgAction() {
        $filePng = time() . ".png";
        $fileJpg = time() . ".jpg";
        
        //Gera a imagem em Png
        $data = $_POST['content'];
        list($type, $data) = explode(';', $data);
        list(, $data)      = explode(',', $data);
        $data = base64_decode($data);
        file_put_contents($this->PATH_SHARE . $filePng, $data);
        
        //Converte para Jpg
        $image = imagecreatefrompng($this->PATH_SHARE . $filePng);
        @unlink($this->PATH_SHARE . $filePng);
        imagejpeg($image, $this->PATH_SHARE . $fileJpg, 100);
        imagedestroy($image);
        echo $fileJpg;
        die;
    }
    
    /**
     * Baixa uma imagem temporária
     */
    public function downloadTmpAction() {
        $file = $this->getParam("file");
        $file = realpath("../../wikipix_portal/publicacoes") . "/" . $file;
        
        if( !is_file($file) ) {
            echo "Arquivo não encontrado: " . $file;
            die;
        }

        $quoted = sprintf('"%s"', addcslashes(basename($file), '"/'));
        $size   = filesize($file);

        header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
        header("Cache-Control: public"); // needed for internet explorer
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length:".filesize($file));
        header("Content-Disposition: attachment; filename=".$this->getParam("file"));
        readfile($file);
        
        die;
    }
}
