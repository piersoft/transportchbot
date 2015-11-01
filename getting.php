 <?php

class getdata {

  //monitoraggio temperatura
	public function get_connection($partenza,$destinazione,$data)
	{
$partenza1=str_replace(" ","%20",$partenza);
$destinazione1=str_replace(" ","%20",$destinazione);
echo $destinazione;
echo $partenza;


      $temp_c1 ="Ricerca delle prossime corse per raggiungere ".$destinazione." partendo da ".$partenza." in data ".$data."\n\n";
			$json_string = file_get_contents("http://transport.opendata.ch/v1/connections?from=".$partenza1."&to=".$destinazione1."&date=".$data."&limit=6");
			$parsed_json = json_decode($json_string);
      $count=0;
      if ($parsed_json->{'connections'}[0]->{'from'}->{'station'}->{'name'} == NULL){
        $temp_c1 .="Non ci sono stazioni";
      }
      foreach($parsed_json->{'connections'}[0]->{'from'}->{'station'} as $data=>$csv1){
    	   $count = $count+1;
    	}

      //$count=6;
    	for ($i=0;$i<=$count;$i++){
      if ($parsed_json->{'connections'}[$i]->{'from'}->{'station'}->{'name'}){
      echo "parsed: ".$parsed_json->{'connections'}[$i]->{'to'}->{'station'}->{'name'};
      $h = "1";// Hour for time zone goes here e.g. +7 or -4, just remove the + or -
      $hm = $h * 60;
      $ms = $hm * 60;
      date_default_timezone_set('UTC');
      $time =$parsed_json->{'connections'}[$i]->{'from'}->{'departureTimestamp'}; //registro nel DB anche il tempo unix
      $timef=floatval($time);
      $timeff = time();
      $timec =gmdate('H:i:s d-m-Y', $timef+$ms);
      $time2 =$parsed_json->{'connections'}[$i]->{'to'}->{'arrivalTimestamp'}; //registro nel DB anche il tempo unix          $timef2=floatval($time2);
      $timef2=floatval($time2);
      $timeff2 = time();
      $timec2 =gmdate('H:i:s d-m-Y', $timef2+$ms);
      $temp_c1 .= "Partenza da ".$parsed_json->{'connections'}[$i]->{'from'}->{'station'}->{'name'};
      $temp_c1 .= " alle ore ".$timec."\n";
      $temp_c1 .= "Arrivo a ".$parsed_json->{'connections'}[$i]->{'to'}->{'station'}->{'name'};
      $temp_c1 .= " alle ore ".$timec2."\n\n";

      }
    }
echo   $temp_c1;
	   return $temp_c1;

	}
  //monitoraggio temperatura
	public function get_stations($lat,$lon)
	{

      $temp_c1="";
			$json_string = file_get_contents("http://transport.opendata.ch/v1/locations?x=".$lat."&y=".$lon);
			$parsed_json = json_decode($json_string);
      $count=0;
if ($parsed_json->{'stations'} == NULL){
  $temp_c1 .="Non ci sono stazioni";
}
      foreach($parsed_json->{'stations'} as $data=>$csv1){
    	   $count = $count+1;
    	}
    	for ($i=0;$i<$count;$i++){
		//	$temp_c1 .= $parsed_json->{'stations'}[$i]->{'name'}." distante: ".$parsed_json->{'stations'}[$i]->{'distance'};
      $temp_c1 .= $parsed_json->{'stations'}[$i]->{'name'}."\n";
      }

	   return $temp_c1;

	}

  public function get_start($where)
  {
    $html =str_replace(" ","%20",$where);
      $temp_c1="Ecco le 20 prossime partenze da: ".urldecode($html)."\n";
      $json_string = file_get_contents("http://transport.opendata.ch/v1/stationboard?station=".$html."&limit=20");
      $parsed_json = json_decode($json_string);
      //var_dump($parsed_json);
      $count=0;
$x=$parsed_json->{'stationboard'}[0]->{'stop'}->{'station'}->{'coordinate'}->{'x'};
$y=$parsed_json->{'stationboard'}[0]->{'stop'}->{'station'}->{'coordinate'}->{'y'};

$longUrl="http://www.openstreetmap.org/?mlat=".$x."&mlon=".$y."#map=19/".$x."/".$y."/";
$apiKey = API;

$postData = array('longUrl' => $longUrl, 'key' => $apiKey);
$jsonData = json_encode($postData);

$curlObj = curl_init();

curl_setopt($curlObj, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url?key='.$apiKey);
curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($curlObj, CURLOPT_HEADER, 0);
curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
curl_setopt($curlObj, CURLOPT_POST, 1);
curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

$response = curl_exec($curlObj);

// Change the response json string to object
$json = json_decode($response);

curl_close($curlObj);
//  $reply="Puoi visualizzarlo su :\n".$json->id;
$shortLink = get_object_vars($json);
//return $json->id;

$temp_c1 .="Mappa: ".$shortLink['id']."\n\n";
if ($parsed_json->{'stationboard'}[0]->{'name'} == NULL){
  $temp_c1 .="Non ci sono corse nelle prossime ore";
}
      foreach($parsed_json->{'stationboard'} as $data=>$csv1){
         $count = $count+1;
      }
  //    echo $parsed_json->{'stationboard'}[0]->{'name'};
  //    $risp=$parsed_json->{'stationboard'}[1]->{'passList'}[1]->{'arrivalTimestamp'};
  //    echo $risp;
      for ($i=0;$i<$count;$i++){
        $h = "1";// Hour for time zone goes here e.g. +7 or -4, just remove the + or -
        $hm = $h * 60;
        $ms = $hm * 60;
        date_default_timezone_set('UTC');
          $time =$parsed_json->{'stationboard'}[$i]->{'stop'}->{'departureTimestamp'}; //registro nel DB anche il tempo unix
          $timef=floatval($time);
          $timeff = time();
          $timec =gmdate('H:i:s d-m-Y', $timef+$ms);


if (strpos($parsed_json->{'stationboard'}[$i]->{'name'},'BUS') !== false){
  $temp_c1 .= "🚌 ".$parsed_json->{'stationboard'}[$i]->{'name'}."\n";
}else $temp_c1 .= "Treno: 🚄 ".$parsed_json->{'stationboard'}[$i]->{'name'}."\n";

      $temp_c1 .= "In partenza alle: ".$timec."\n";
      $temp_c1 .= "Destinazione: ".$parsed_json->{'stationboard'}[$i]->{'to'}."\n";
      $temp_c1 .= "\n";
      $counts=0;

      }
      $temp_c1 .="Se digiti /".$where." avrai tutti i treni completi di tutte le fermate ed orari intermedi verso la destinazione.";
      echo $temp_c1;
     return $temp_c1;

  }


  public function get_startcompleto($where)
  {

      $temp_c1="Ecco le 20 prossime partenze da: ".$where."\n\n";
      $json_string = file_get_contents("http://transport.opendata.ch/v1/stationboard?station=".$where."&limit=20");
      $parsed_json = json_decode($json_string);
      //var_dump($parsed_json);
      $count=0;
      $x=$parsed_json->{'stationboard'}[0]->{'stop'}->{'station'}->{'coordinate'}->{'x'};
      $y=$parsed_json->{'stationboard'}[0]->{'stop'}->{'station'}->{'coordinate'}->{'y'};

      $longUrl="http://www.openstreetmap.org/?mlat=".$x."&mlon=".$y."#map=19/".$x."/".$y."/";
      $apiKey = API;

      $postData = array('longUrl' => $longUrl, 'key' => $apiKey);
      $jsonData = json_encode($postData);

      $curlObj = curl_init();

      curl_setopt($curlObj, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url?key='.$apiKey);
      curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($curlObj, CURLOPT_HEADER, 0);
      curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
      curl_setopt($curlObj, CURLOPT_POST, 1);
      curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

      $response = curl_exec($curlObj);

      // Change the response json string to object
      $json = json_decode($response);

      curl_close($curlObj);
      //  $reply="Puoi visualizzarlo su :\n".$json->id;
      $shortLink = get_object_vars($json);
      //return $json->id;

      $temp_c1 .="Mappa: ".$shortLink['id']."\n\n";

      foreach($parsed_json->{'stationboard'} as $data=>$csv1){
         $count = $count+1;
      }
  //    echo $parsed_json->{'stationboard'}[0]->{'name'};
  //    $risp=$parsed_json->{'stationboard'}[1]->{'passList'}[1]->{'arrivalTimestamp'};
  //    echo $risp;
      for ($i=0;$i<$count;$i++){
        $h = "1";// Hour for time zone goes here e.g. +7 or -4, just remove the + or -
        $hm = $h * 60;
        $ms = $hm * 60;
        date_default_timezone_set('UTC');
          $time =$parsed_json->{'stationboard'}[$i]->{'stop'}->{'departureTimestamp'}; //registro nel DB anche il tempo unix
          $timef=floatval($time);
          $timeff = time();
          $timec =gmdate('H:i:s d-m-Y', $timef+$ms);

          if (strpos($parsed_json->{'stationboard'}[$i]->{'name'},'BUS') !== false){
            $temp_c1 .= "\n🚌 ".$parsed_json->{'stationboard'}[$i]->{'name'}."\n";
          }else $temp_c1 .= "\nTreno: 🚄 ".$parsed_json->{'stationboard'}[$i]->{'name'}."\n";

      $temp_c1 .= "In partenza alle: ".$timec."\n";
      $temp_c1 .= "Destinazione: ".$parsed_json->{'stationboard'}[$i]->{'to'}."\n";
      $temp_c1 .= "Passa da:\n";
      $counts=0;
      foreach($parsed_json->{'stationboard'}[$i]->{'passList'} as $data11=>$csv11){
         $counts = $counts+1;
      }
    //  echo "\n".$counts;


      for ($ii=0;$ii<$counts;$ii++){
        $time1 =$parsed_json->{'stationboard'}[$i]->{'passList'}[$ii]->{'arrivalTimestamp'}; //registro nel DB anche il tempo unix
      //  echo "\n<br>timestamp:".$time1."senza pulizia dati";
        $timef1=floatval($time1);
        $timeff1 = time();
        $timec1 =gmdate('H:i:s d-m-Y', $timef1+$ms);
        $temp_c1 .=$parsed_json->{'stationboard'}[$i]->{'passList'}[$ii]->{'station'}->{'name'};
        $temp_c1 .= " arrivo previsto alle: ".$timec1."\n";

      }

      }

echo $temp_c1;
     return $temp_c1;

  }



  }


?>
