<?php
date_default_timezone_set("America/Guatemala");

$fact = new DomDocument;
$fact->load('plantilla_factura_electronica.xml');

$sig_dom = new DomDocument;
$sig_dom->load('signature_template.xml');

/**MANIPULACION
 * */
//  1ST DIGEST VALUE
//1. Canonicalizar documento
//2. HASH usando sha256
//3. Convertir a base64

$digest_value = base64_encode(hash('sha256', $fact->C14N(), true));
$sig_dom->getElementsByTagNameNS('http://www.w3.org/2000/09/xmldsig#', 'DigestValue')->item(0)->nodeValue = $digest_value;

//INSERT SIGNATURE INTO THE DOCUMENT
$sign = $sig_dom->getElementsByTagNameNS('http://www.w3.org/2000/09/xmldsig#', 'Signature')->item(0);
$gtd = $fact->getElementsByTagName('GTDocumento')[0];
$gtd->appendChild($fact->importNode($sign, true));

//2ND DIGEST VALUE
//1. Asignar id a keyInfo
//2. Apuntar URI del segundo nodo Reference (del segundo DigestValue) a #id del keyInfo
//3. Computar y agregar segundo digest value sobre el nodo KeyInfo
$kid = "ki-".rand(1000, 9999);
$fact->getElementsByTagNameNS('http://www.w3.org/2000/09/xmldsig#', 'KeyInfo')->item(0)->attributes->item(0)->value = $kid;
$fact->getElementsByTagName('Reference')->item(1)->attributes->item(0)->value = "#".$kid;
$key_info_element = $fact->getElementsByTagName('KeyInfo')->item(0);
$digest_val2 = base64_encode(hash('sha256', $key_info_element->C14N(), true));
$fact->getElementsByTagName('DigestValue')->item(1)->nodeValue = $digest_val2;


/**
 *COMPLETAR EL CONTENIDO DE DS:OBJECT
  * */
//xades:SigningTime, dte:FechaHoraCertificacion
$stime = date("Y-m-d")."T".date("H:i:s")."-06:00";
$fact->getElementsByTagNameNS('http://uri.etsi.org/01903/v1.2.2#', 'SigningTime')->item(0)->nodeValue = $stime;
$fact->getElementsByTagNameNS('http://www.sat.gob.gt/dte/fel/0.1.0', 'FechaHoraCertificacion')->item(0)->nodeValue = $stime;

// 4TO DIGEST
// Del certificado publico  <ds:X509Certificate en <ds:KeyInfo
// 
//
$pub_cert = $fact->getElementsByTagNameNS('http://www.w3.org/2000/09/xmldsig#', 'X509Certificate')->item(0)->nodeValue;
$digest4 = base64_encode(hash('sha256', $pub_cert, true));
$digest4_node = $fact->getElementsByTagNameNS('http://www.w3.org/2000/09/xmldsig#', 'DigestValue')->item(3);
$digest4_p_node = $fact->getElementsByTagNameNS('http://uri.etsi.org/01903/v1.2.2#', 'CertDigest')->item(0);
//verificar que el nodo digest4 elegido sea el correcto: debe ser hijo del nodo <xades:CertDigest
if($digest4_node->parentNode === $digest4_p_node){
	$digest4_node->nodeValue = $digest4;
}else{
	echo var_dump($digest4_node);
}

//agregar id a SignedProperties
$spid = "sp-".rand(1000, 9999);
$fact->getElementsByTagNameNS('http://uri.etsi.org/01903/v1.2.2#', 'SignedProperties')->item(0)->attributes->item(0)->value = $spid;

//3RD DIGEST VALUE
//FROM SIGNED PROPERTIES
//Primero asignar URI="#signed-propertiesid" a tercer nodo Reference
$fact->getElementsByTagNameNS('http://www.w3.org/2000/09/xmldsig#', 'Reference')->item(2)->attributes->item(1)->value = 
	"#".$fact->getElementsByTagNameNS('http://uri.etsi.org/01903/v1.2.2#', 'SignedProperties')->item(0)->attributes->item(0)->value;
$sp_element = $fact->getElementsByTagNameNS('http://uri.etsi.org/01903/v1.2.2#', 'SignedProperties')->item(0);
$digest3 = base64_encode(hash('sha256', $sp_element->C14N(), true));
$fact->getElementsByTagNameNS('http://www.w3.org/2000/09/xmldsig#', 'DigestValue')->item(2)->nodeValue = $digest3;


//SIGNATURE VALUE
$signed_info_node = $fact->getElementsByTagNameNS('www.w3.org/2000/09/xmldsig#', 'SignedInfo')->item(0);
echo var_dump($signed_info_node);
//
//$signed_info_node = $signed_info_node->C14N();
/*
 * openssl_sign($signed_info_node, $signature, $priv_key, 'sha256');
 * $fact->getElementsByTagNameNS('www.w3.org/2000/09/xmldsig#', 'SignedValue')->item(0)->nodeValue = $signature;
 */
echo $fact->C14N(); //canonicalizes nodes into string


