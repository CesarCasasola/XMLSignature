<?php

#Set output stream to xml
//header('Content-type: text/xml');


/**
 *
 *
 *USING DOMDOCUMENT
 *
 *
 *
 * */

echo "<h4>Using DOMDocument</h4>";
/*
 *Create a DomDocument object and load and xml file *
 **/ 
$xml_dom = new DOMDocument;
$xml_dom->formatOutput = true;
$xml_dom->load('plantilla_factura_electronica.xml');
echo("Printing a domDocumuent from an xml file...<br\>");
echo $xml_dom->saveXML();
echo("<br/><br>");
 
/**
 *Iterando sobre los hijos
 * */
foreach($xml_dom->getElementsByTagName('*') as $item){
	echo '<b>'.$item->nodeName.'</b><br/>';
	echo $item->nodeValue.'<br/>';
	echo $item->nodeType.'<br/>';
}

$price_node = $xml_dom->getElementsByTagName('Precio')[0];
echo "Childrens of node Price<br/>";
foreach($price_node->childNodes as $pc){
	echo $pc->nodeName.'<br/>';
}

/**
 * Create and append nodes
 * */
$new_price = $xml_dom->createElement("NewPrice");
$new_price->nodeValue = 548.45;
$price_node->appendChild($new_price);
echo "Childrens of node Price<br/>";
foreach($price_node->childNodes as $pc){
        echo $pc->nodeName.'<br/>';
}
echo "<br/><b>XML string print:</b><br/><br/>";
echo $xml_dom->saveXML(). "<br/>";


/**
 *Get elements by tag name using namespaces
 * */
$node_ns = $xml_dom->getElementsByTagNameNS('http://www.sat.gob.gt/dte/fel/0.1.0', 'DatosEmision');
echo "<br/><b>Nodes with datosemision tagName and Name Space uri: http://www.sat.gob.gt/dte/fel/0.1.0/v1</b><br/>";
foreach($node_ns as $n){
	/*
	echo $n->nodeName." has a value of: $n->nodeValue, ". $n->hasAttributes() ? "has ": "has no ". "attributes, its parent node is ".$n->parentNode->nodeName.
		", has ".$n->childNodes->length ." child nodes which are: </br>";
	 */
	echo "<br/>Nodo: $n->nodeName  has a value of: $n->nodeValue, its parent node is ".$n->parentNode->nodeName.", has ".(floor($n->childNodes->length/2))." child nodes which are: </br>";
	foreach($n->childNodes as $nc){
		echo "Hijo: $nc->nodeName, valor: $nc->nodeValue<br/>";
	}
	echo "<br/>";
}

/**
 *
 *USING SIMPLE XML
 *
 * */

/*
 * 
 *Load an xml string as an object :SimpleXMLElement
 *
 */
$sxml = simplexml_load_file("plantilla_factura_electronica.xml");
echo $sxml->Item[0]->Precio;
foreach($sxml->xpath('//dte:Item') as $child){
	echo $child;
}
/**
 * Shows information about the namespaces
 
var_dump($xml_dom>getDocNamespaces());
print("<br/>");
var_dump($xml_dom->getDocNamespaces(true));
print("<br/>");
 */


/**
 *
 *
 * USING XMLToArray
 *
 * */
/*
include_once('XmlToArray.php');
$xmls = simplexml_load_file('plantilla_factura_electronica.xml');
$xml2a = new XmlToArray($xmls->asXML());
$xml_array = $xml2a->toArray();
print_r($xml_array);
 */
