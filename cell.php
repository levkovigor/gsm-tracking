<!DOCTYPE html>
<html>

  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	 <meta name="viewport" content="initial-scale=1.0">
    <meta charset="utf-8">
    <style>
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      #map {
        height: 100%;
      }
    </style>

    <title>GSM Tracker</title>
 
    <?php
 
function geturl()
{
 
    if ($_REQUEST["myl"] != "") {
      $temp = split(":", $_REQUEST["myl"]);
      $mcc = substr("00000000".($temp[0]),-8);
      $mnc = substr("00000000".($temp[1]),-8);
      $lac = substr("00000000".($temp[2]),-8);
      $cid = substr("00000000".($temp[3]),-8);    
    } else {
      $hex = $_REQUEST["hex"];
      //echo "hex $hex";
      if ($hex=="1"){
            //echo "da hex to dec"; 
            $mcc=substr("00000000".hexdec($_REQUEST["mcc"]),-8);
            $mnc=substr("00000000".hexdec($_REQUEST["mnc"]),-8);
            $lac=substr("00000000".hexdec($_REQUEST["lac"]),-8);
            $cid=substr("00000000".hexdec($_REQUEST["cid"]),-8);
 
      }else{
            //echo "lascio dec";    
            $mcc = substr("00000000".$_REQUEST["mcc"],-8);
            $mnc = substr("00000000".$_REQUEST["mnc"],-8);
            $lac = substr("00000000".$_REQUEST["lac"],-8);
            $cid = substr("00000000".$_REQUEST["cid"],-8);
       }
    }
    //echo "MCC : $mcc <br> MNC : $mnc <br>LAC : $lac <br>CID : $cid <br>";
    return array ($mcc, $mnc, $lac, $cid);
}
function decodegoogle($mcc,$mnc,$lac,$cid)
{
 
    $mcch=substr("00000000".dechex($mcc),-8);
    $mnch=substr("00000000".dechex($mnc),-8);
    $lach=substr("00000000".dechex($lac),-8);
    $cidh=substr("00000000".dechex($cid),-8);
 
    echo "<tr><td>Hex </td><td>MCC: $mcch </td><td>MNC: $mnch </td><td>LAC: $lach </td><td>CID: $cidh </td></tr></table>";
 
$data = 
"\x00\x0e". // Function Code?
"\x00\x00\x00\x00\x00\x00\x00\x00". //Session ID?
"\x00\x00". // Contry Code 
"\x00\x00". // Client descriptor
"\x00\x00". // Version
"\x1b". // Op Code?
"\x00\x00\x00\x00". // MNC
"\x00\x00\x00\x00". // MCC
"\x00\x00\x00\x03".
"\x00\x00".
"\x00\x00\x00\x00". //CID
"\x00\x00\x00\x00". //LAC
"\x00\x00\x00\x00". //MNC
"\x00\x00\x00\x00". //MCC
"\xff\xff\xff\xff". // ??
"\x00\x00\x00\x00"  // Rx Level?
;
 
$init_pos = strlen($data);
$data[$init_pos - 38]= pack("H*",substr($mnch,0,2));
$data[$init_pos - 37]= pack("H*",substr($mnch,2,2));
$data[$init_pos - 36]= pack("H*",substr($mnch,4,2));
$data[$init_pos - 35]= pack("H*",substr($mnch,6,2));
$data[$init_pos - 34]= pack("H*",substr($mcch,0,2));
$data[$init_pos - 33]= pack("H*",substr($mcch,2,2));
$data[$init_pos - 32]= pack("H*",substr($mcch,4,2));
$data[$init_pos - 31]= pack("H*",substr($mcch,6,2));
$data[$init_pos - 24]= pack("H*",substr($cidh,0,2));
$data[$init_pos - 23]= pack("H*",substr($cidh,2,2));
$data[$init_pos - 22]= pack("H*",substr($cidh,4,2));
$data[$init_pos - 21]= pack("H*",substr($cidh,6,2));
$data[$init_pos - 20]= pack("H*",substr($lach,0,2));
$data[$init_pos - 19]= pack("H*",substr($lach,2,2));
$data[$init_pos - 18]= pack("H*",substr($lach,4,2));
$data[$init_pos - 17]= pack("H*",substr($lach,6,2));
$data[$init_pos - 16]= pack("H*",substr($mnch,0,2));
$data[$init_pos - 15]= pack("H*",substr($mnch,2,2));
$data[$init_pos - 14]= pack("H*",substr($mnch,4,2));
$data[$init_pos - 13]= pack("H*",substr($mnch,6,2));
$data[$init_pos - 12]= pack("H*",substr($mcch,0,2));
$data[$init_pos - 11]= pack("H*",substr($mcch,2,2));
$data[$init_pos - 10]= pack("H*",substr($mcch,4,2));
$data[$init_pos - 9]= pack("H*",substr($mcch,6,2));
 
if ((hexdec($cid) > 0xffff) && ($mcch != "00000000") && ($mnch != "00000000")) {
  $data[$init_pos - 27] = chr(5);
} else {
  $data[$init_pos - 24]= chr(0);
  $data[$init_pos - 23]= chr(0);
}
 
$context = array (
        'http' => array (
            'method' => 'POST',
            'header'=> "Content-type: application/binary\r\n"
                . "Content-Length: " . strlen($data) . "\r\n",
            'content' => $data
            )
        );
 
$xcontext = stream_context_create($context);
$str=file_get_contents("http://www.google.com/glm/mmap",FALSE,$xcontext);
 
if (strlen($str) > 10) {
  $lat_tmp = unpack("l",$str[10].$str[9].$str[8].$str[7]);
  $lat = $lat_tmp[1]/1000000;
  $lon_tmp = unpack("l",$str[14].$str[13].$str[12].$str[11]);
  $lon = $lon_tmp[1]/1000000;
  $raggio_tmp = unpack("l",$str[18].$str[17].$str[16].$str[15]);
  $raggio = $raggio_tmp[1]/1;
  } else {
  echo "Not found!";
  $lat = 0;
  $lon = 0;
  }
  return array($lat,$lon,$raggio);
 
}
 
list($mcc,$mnc,$lac,$cid)=geturl();
 
echo "<table cellspacing=30><tr><td>Dec</td><td>MCC: $mcc </td><td>MNC: $mnc </td><td>LAC: $lac </td><td>CID: $cid </td></tr>";
 
list ($lat,$lon,$raggio)=decodegoogle($mcc,$mnc,$lac,$cid);
 
  echo "<br>Google result for the main Cell<br>";
  echo "Lat=$lat <br> Lon=$lon <br> Range=$raggio m<br>";
  echo "<a href=\"http://maps.google.it/maps?f=q&source=s_q&hl=it&geocode=&q=$lat,$lon&z=14\" TARGET=\"_blank\" >See on Google maps</a> <BR> <br>";
 

 
?>
  
  </head>
 
 <body>
 
 <div id="map"></div>
 
 
 <script>

function initMap() {

  
var point = new google.maps.LatLng(<?php echo $lat; ?>, <?php echo $lon; ?>);

  var map = new google.maps.Map(document.getElementById('map'), {
    center: point,
	scaleControl: true,
    zoom: 14,
	mapTypeId: google.maps.MapTypeId.ROADMAP
  });

  
var marker = new google.maps.Marker({
  map: map,
  position: new google.maps.LatLng(<?php echo $lat; ?>, <?php echo $lon; ?>),
  title: 'Some location'
});

// Add circle overlay and bind to marker
var circle = new google.maps.Circle({
  map: map,
  radius: <?php echo $raggio; ?>,    // 10 miles in metres
  fillColor: '#AA0000'
});

circle.bindTo('center', marker, 'position');

  }

</script>


<script async defer src="https://maps.googleapis.com/maps/api/js?key=API_KEY&signed_in=true&callback=initMap">

</script>


 
  </body>
</html>