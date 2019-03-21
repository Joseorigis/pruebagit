<?php
// http://www.the-art-of-web.com/php/parse-links/
  // Original PHP code by Chirp Internet: www.chirp.com.au
  // Please acknowledge use of this code by including this header.

 $indice = 1;

  $url = "http://www.monederodelahorro.com.mx/emailing/lealtad/primeracompra/primera_compra.html";
  $input = @file_get_contents($url) or die("Could not access file: $url");
  $regexp = "<a\s[^>]*href=([\"\']??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
  if(preg_match_all("/$regexp/siU", $input, $matches, PREG_SET_ORDER)) {
    foreach($matches as $match) {
      // $match[2] = link address
      // $match[3] = link text
	  //echo $indice.".0.".$match[0]."<br>";
	  //echo $indice.".1.".$match[1]."<br>";
	  echo $indice.".2.".$match[2]."<br>";
	  //echo $indice.".3.".$match[3]."<br>";
	  echo "<br>";
	  $indice = $indice + 1;
    }
  }
  
//  // Original PHP code by Chirp Internet: www.chirp.com.au
//  // Please acknowledge use of this code by including this header.
//
//  $url = "http://www.monederodelahorro.com.mx/emailing/lealtad/primeracompra/primera_compra.html";
//  $input = @file_get_contents($url) or die("Could not access file: $url");
//  $regexp = "<a\s[^>]*href=([\"\']??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
//  if(preg_match_all("/$regexp/siU", $input, $matches)) {
//    // $matches[2] = array of link addresses
//    // $matches[3] = array of link text - including HTML code
//	  echo $indice.".2.".$matches[2][0]."<br>";
//	  echo $indice.".2.".$matches[2][1]."<br>";
//	  echo "<br>";
//	  $indice = $indice + 1;
//  }

echo "<hr>";
// ----------------

// http://w-shadow.com/blog/2009/10/20/how-to-extract-html-tags-and-their-attributes-with-php/

//Load the HTML page
$html = file_get_contents("http://www.monederodelahorro.com.mx/emailing/lealtad/primeracompra/primera_compra.html");
//Create a new DOM document
$dom = new DOMDocument;
 
//Parse the HTML. The @ is used to suppress any parsing errors
//that will be thrown if the $html string isn't valid XHTML.
@$dom->loadHTML($html);
 
//Get all links. You could also use any other tag name here,
//like 'img' or 'table', to extract other tags.
$links = $dom->getElementsByTagName('a');
 
//Iterate over the extracted links and display their URLs
foreach ($links as $link){
    //Extract and show the "href" attribute. 
    echo $link->getAttribute('href'), '<br>';
}
?>