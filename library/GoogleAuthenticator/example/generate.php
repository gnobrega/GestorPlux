<pre>
<?php
require 'vendor/autoload.php';
$authenticator = new PHPGangsta_GoogleAuthenticator();
$secret = $authenticator->createSecret();
echo "Secret: ".$secret."\n";;


$website = 'http://gestor.wikipix.com.br'; //Your Website
$title= 'Gestor Wikipix';
$qrCodeUrl = $authenticator->getQRCodeGoogleUrl($title, $secret,$website);
echo "Open this link in browser & scan with Google Authenticator App \n";
echo $qrCodeUrl."\n";

?>
