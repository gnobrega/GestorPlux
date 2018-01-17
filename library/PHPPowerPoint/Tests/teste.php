<?php

error_reporting(E_ALL);
set_include_path(get_include_path() . PATH_SEPARATOR . '../Classes/');
include 'PHPPowerPoint.php';
include 'PHPPowerPoint/IOFactory.php';
echo date('H:i:s') . " Create new PHPPowerPoint object\n";
$objPHPPowerPoint = new PHPPowerPoint();
$objPHPPowerPoint->getLayout()->setDocumentLayout(PHPPowerPoint_DocumentLayout::LAYOUT_A4);
$currentSlide = $objPHPPowerPoint->getActiveSlide();

### Página 1 ###
$shape = $currentSlide->createDrawingShape();
$shape->setPath('./images/lookindoorlogotipo.jpg');
$shape->setHeight(70);
$shape->setOffsetX(320);
$shape->setOffsetY(200);

//Dados
$shape = $currentSlide->createRichTextShape();
$shape->setHeight(300);
$shape->setWidth(600);
$shape->setOffsetX(270);
$shape->setOffsetY(300);
$shape->getActiveParagraph()->getAlignment()->setHorizontal( PHPPowerPoint_Style_Alignment::HORIZONTAL_CENTER );
$lin1 = $shape->createTextRun('Cliente: xxxxxxx xxxxxxx xxxxxxx xxxxxxx xxxxxxx ');
$lin1->getFont()->setBold(true);
$lin1->getFont()->setSize(16);
$shape->createBreak();

$lin2 = $shape->createTextRun('Campanha: xxxxxxx xxxxxxx xxxxxxx ');
$lin2->getFont()->setBold(true);
$lin2->getFont()->setSize(16);
$shape->createBreak();

$lin3 = $shape->createTextRun('Período: xx/xx/xxxx a xx/xx/xxxx');
$lin3->getFont()->setBold(true);
$lin3->getFont()->setSize(16);
$shape->createBreak();

$lin4 = $shape->createTextRun('PI: xxx.xxx');
$lin4->getFont()->setBold(true);
$lin4->getFont()->setSize(16);
$shape->createBreak();

$lin5 = $shape->createTextRun('Veiculação: Shopping');
$lin5->getFont()->setBold(true);
$lin5->getFont()->setSize(16);
$shape->createBreak();

//Autoria
exibirAutoria($currentSlide, 12, 550);
$shape->createBreak();

### Página 2 ###
$currentSlide = $objPHPPowerPoint->createSlide();

//Logo
$shape = $currentSlide->createDrawingShape();
$shape->setPath('./images/lookindoorlogotipo.jpg');
$shape->setHeight(25);
$shape->setOffsetX(825);
$shape->setOffsetY(640);

//Foto 01
exibirFoto($currentSlide, './images/user_27_20160811_142337.jpg', 150, 130);

//Foto 02
exibirFoto($currentSlide, './images/user_27_20160811_142337.jpg', 650, 130);

//Foto 03
exibirFoto($currentSlide, './images/user_27_20160811_142337.jpg', 150, 380);

//Foto 04
exibirFoto($currentSlide, './images/user_27_20160811_142337.jpg', 650, 380);

//Carimbo
exibirCarimbo($currentSlide);

//Topo
$shape = $currentSlide->createRichTextShape();
$shape->setHeight(100);
$shape->setWidth(600);
$shape->setOffsetX(270);
$shape->setOffsetY(50);
$shape->getActiveParagraph()->getAlignment()->setHorizontal( PHPPowerPoint_Style_Alignment::HORIZONTAL_CENTER );
$topo = $shape->createTextRun('Cliente: xxxxxxx xxxxxxx xxxxxxx xxxxxxx xxxxxxx ');
$topo->getFont()->setBold(true);
$topo->getFont()->setSize(14);
$shape->createBreak();
$topo = $shape->createTextRun('Campanha: xxxxx xxxx xxxx');
$topo->getFont()->setBold(true);
$topo->getFont()->setSize(14);

//Legenda 01
$shape = $currentSlide->createRichTextShape();
$shape->setWidth(350);
$shape->setOffsetX(150);
$shape->setOffsetY(325);
$shape->getActiveParagraph()->getAlignment()->setHorizontal( PHPPowerPoint_Style_Alignment::HORIZONTAL_CENTER );
$legenda = $shape->createTextRun('LIBERTY MALL (1)');
$legenda->getFont()->setBold(true);
$legenda->getFont()->setSize(16);

//Legenda 02
$shape = $currentSlide->createRichTextShape();
$shape->setWidth(350);
$shape->setOffsetX(650);
$shape->setOffsetY(325);
$shape->getActiveParagraph()->getAlignment()->setHorizontal( PHPPowerPoint_Style_Alignment::HORIZONTAL_CENTER );
$legenda = $shape->createTextRun('LIBERTY MALL (1)');
$legenda->getFont()->setBold(true);
$legenda->getFont()->setSize(16);

//Legenda 03
$shape = $currentSlide->createRichTextShape();
$shape->setWidth(350);
$shape->setOffsetX(150);
$shape->setOffsetY(575);
$shape->getActiveParagraph()->getAlignment()->setHorizontal( PHPPowerPoint_Style_Alignment::HORIZONTAL_CENTER );
$legenda = $shape->createTextRun('LIBERTY MALL (1)');
$legenda->getFont()->setBold(true);
$legenda->getFont()->setSize(16);

//Legenda 04
$shape = $currentSlide->createRichTextShape();
$shape->setWidth(350);
$shape->setOffsetX(650);
$shape->setOffsetY(575);
$shape->getActiveParagraph()->getAlignment()->setHorizontal( PHPPowerPoint_Style_Alignment::HORIZONTAL_CENTER );
$legenda = $shape->createTextRun('LIBERTY MALL (1)');
$legenda->getFont()->setBold(true);
$legenda->getFont()->setSize(16);

//Data 01
$shape = $currentSlide->createRichTextShape();
$shape->setWidth(350);
$shape->setOffsetX(152);
$shape->setOffsetY(301);
$shape->getActiveParagraph()->getAlignment()->setHorizontal( PHPPowerPoint_Style_Alignment::HORIZONTAL_RIGHT );
$data = $shape->createTextRun('10/08/2016');
$data->getFont()->setBold(true);
$data->getFont()->setSize(10);
$data->getFont()->setColor( new PHPPowerPoint_Style_Color( 'ffffd700' ) );

//Data 02
$shape = $currentSlide->createRichTextShape();
$shape->setWidth(350);
$shape->setOffsetX(652);
$shape->setOffsetY(301);
$shape->getActiveParagraph()->getAlignment()->setHorizontal( PHPPowerPoint_Style_Alignment::HORIZONTAL_RIGHT );
$data = $shape->createTextRun('10/08/2016');
$data->getFont()->setBold(true);
$data->getFont()->setSize(10);
$data->getFont()->setColor( new PHPPowerPoint_Style_Color( 'ffffd700' ) );

//Data 03
$shape = $currentSlide->createRichTextShape();
$shape->setWidth(350);
$shape->setOffsetX(152);
$shape->setOffsetY(551);
$shape->getActiveParagraph()->getAlignment()->setHorizontal( PHPPowerPoint_Style_Alignment::HORIZONTAL_RIGHT );
$data = $shape->createTextRun('10/08/2016');
$data->getFont()->setBold(true);
$data->getFont()->setSize(10);
$data->getFont()->setColor( new PHPPowerPoint_Style_Color( 'ffffd700' ) );

//Data 04
$shape = $currentSlide->createRichTextShape();
$shape->setWidth(350);
$shape->setOffsetX(652);
$shape->setOffsetY(551);
$shape->getActiveParagraph()->getAlignment()->setHorizontal( PHPPowerPoint_Style_Alignment::HORIZONTAL_RIGHT );
$data = $shape->createTextRun('10/08/2016');
$data->getFont()->setBold(true);
$data->getFont()->setSize(10);
$data->getFont()->setColor( new PHPPowerPoint_Style_Color( 'ffffd700' ) );

//Autoria
exibirAutoria($currentSlide, 8, 630);

//Gera o numero da pagina
$pagina = 0;
exibirPagina($currentSlide, $pagina);

// Gera o arquivo
echo date('H:i:s') . " Write to PowerPoint2007 format\n";
$objWriter = PHPPowerPoint_IOFactory::createWriter($objPHPPowerPoint, 'PowerPoint2007');
$objWriter->save('abc.pptx');

// Echo memory peak usage
echo date('H:i:s') . " Peak memory usage: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB\r\n";

// Echo done
echo date('H:i:s') . " Done writing file.\r\n";

//Preenche os dados de autoria
function exibirAutoria($currentSlide, $size, $y) {
    
    //Autoria
    $shape = $currentSlide->createRichTextShape();
    $shape->setHeight(100);
    $shape->setWidth(200);
    $shape->setOffsetX(190);
    $shape->setOffsetY($y);
    $shape->getActiveParagraph()->getAlignment()->setHorizontal( PHPPowerPoint_Style_Alignment::HORIZONTAL_CENTER );

    $lin1 = $shape->createTextRun('RAFAEL RODRIGO');
    $lin1->getFont()->setBold(true);
    $lin1->getFont()->setSize($size);
    $lin1->getFont()->setColor( new PHPPowerPoint_Style_Color( 'FF555555' ) );
    $shape->createBreak();

    $lin2 = $shape->createTextRun('OPEC');
    $lin2->getFont()->setBold(true);
    $lin2->getFont()->setSize($size);
    $lin2->getFont()->setColor( new PHPPowerPoint_Style_Color( 'FF555555' ) );
    $shape->createBreak();

    $lin3 = $shape->createTextRun('CPF: 736.922.981-53');
    $lin3->getFont()->setBold(true);
    $lin3->getFont()->setSize($size);
    $lin3->getFont()->setColor( new PHPPowerPoint_Style_Color( 'FF555555' ) );
    $shape->createBreak();

    $lin4 = $shape->createTextRun('RG: 2.378.507 SSP-DF');
    $lin4->getFont()->setBold(true);
    $lin4->getFont()->setSize($size);
    $lin4->getFont()->setColor( new PHPPowerPoint_Style_Color( 'FF555555' ) );
}

//Gera o número da página
function exibirPagina($currentSlide, $pagina) {
    $pagina ++;
    
    //Página
    $shape = $currentSlide->createRichTextShape();
    $shape->setHeight(30);
    $shape->setWidth(30);
    $shape->setOffsetX(980);
    $shape->setOffsetY(670);
    $shape->getActiveParagraph()->getAlignment()->setHorizontal( PHPPowerPoint_Style_Alignment::HORIZONTAL_CENTER );

    $lin1 = $shape->createTextRun($pagina);
    $lin1->getFont()->setBold(true);
    $lin1->getFont()->setSize(12);
    $lin1->getFont()->setColor( new PHPPowerPoint_Style_Color( 'FF555555' ) );
    $shape->createBreak();
}

//Carrega a foto
function exibirFoto($currentSlide, $src, $x, $y) {
    
    $foto01 = $currentSlide->createDrawingShape();
    $foto01->setPath($src);
    $foto01->setWidth(350);
    $foto01->setOffsetX($x);
    $foto01->setOffsetY($y);
}

//Exibe carimbo no slide
function exibirCarimbo($currentSlide) {
    
    $carimbo = $currentSlide->createDrawingShape();
    $carimbo->setPath('./images/assinatura-look.png');
    $carimbo->setWidth(200);
    $carimbo->setOffsetX(450);
    $carimbo->setOffsetY(600);
}