<?php
/**
* Telegram Bot example for Public Transport of CH
* @author @Piersoft
Funzionamento
- invio location
- invio fermata più vicina come risposta
*/

include('settings_t.php');
include(dirname(dirname(__FILE__)).'/getting.php');
include("Telegram.php");


class main{

const MAX_LENGTH = 4096;

 function start($telegram,$update)
	{

		date_default_timezone_set('Europe/Rome');
		$today = date("Y-m-d H:i:s");

		// Instances the class
		$data=new getdata();

		$db = new PDO(DB_NAME);
    $log="";
		/* If you need to manually take some parameters
		*  $result = $telegram->getData();
		*  $text = $result["message"] ["text"];
		*  $chat_id = $result["message"] ["chat"]["id"];
		*/
	  $inline_query = $update["inline_query"];
		$text = $update["message"] ["text"];
		$chat_id = $update["message"] ["chat"]["id"];
		$user_id=$update["message"]["from"]["id"];
		$location=$update["message"]["location"];
		$reply_to_msg=$update["message"]["reply_to_message"];

		$this->shell($inline_query,$telegram, $db,$data,$text,$chat_id,$user_id,$location,$reply_to_msg);
    $db = NULL;
	}

	//gestisce l'interfaccia utente
	 function shell($inline_query,$telegram,$db,$data,$text,$chat_id,$user_id,$location,$reply_to_msg)
	{
		date_default_timezone_set('Europe/Rome');
		$today = date("Y-m-d H:i:s");
//    if (strpos($text,'@TransportCHBot') !== false) $text=str_replace("@TransportCHBot ","",$text);

		if ($text == "/start") {
				$log=$today. ";new chat started;" .$chat_id. "\n";
			}else	if (strpos($inline_query["location"],'.') !== false){

    			$this->location_manager_inline($inline_query,$telegram,$user_id,$chat_id,$location);
    			exit;
    		}

 if ($text == "stazione più vicina" || $text == "near station"){
/*
   $reply = ("Invia la tua posizione cliccando sulla graffetta (📎) in basso e, se vuoi, puoi cliccare due volte sulla mappa e spostare il Pin Rosso in un luogo di cui vuoi conoscere le stazioni più vicine");
$reply .="\nSend your location by clicking on the paperclip (📎) at the bottom and, if you want, you can double click on the map and move the Pin Red wine in a place that you want to know the nearest stations";
   $content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
   $telegram->sendMessage($content);
   $option = array(array($telegram->buildKeyboardButton("Invia la tua posizione / send your location", false, true)) //this work
 */
 $option = array(array($telegram->buildKeyboardButton("Invia la tua posizione / send your location", false, true)) //this work
                   );
  // Create a permanent custom keyboard
  $keyb = $telegram->buildKeyBoard($option, $onetime=true);
  $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "Attiva la localizzazione sul tuo smartphone / Turn on your GPS");
  $telegram->sendMessage($content);
  exit;

   $log=$today. ";stazioneinfo sent;" .$chat_id. "\n";
 }
 else if ($text == "/start"){

   $reply = ("Benvenuto, selezione un comando oppure digita il nome della stazione");
$reply .="\nWelcome, select a command or enter the station name";
   $content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
   $telegram->sendMessage($content);
   $log=$today. ";start sent;" .$chat_id. "\n";
 }
 else if ($text == "viaggio" || $text == "travel"){

   $reply = ("Scrivi stazionepartenza%stazionearrivo%data dove data è nel formato anno-mese-giorno. Per esempio: Lecce%Roma%2015-10-30");
$reply .="\nWrite departurestation%destinationstation%date where date is in the format year-month-day. For example: Lecce%Roma%10/30/2015";
   $content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
   $telegram->sendMessage($content);
   $log=$today. ";stazioneinfo sent;" .$chat_id. "\n";
 }
			//crediti
	elseif ($text == "/info" || $text == "info") {
				 $reply = ("Transport CH Bot e' un servizio sperimentale e dimostrativo per orari, tratte e stazioni dei Trasporti Svizzeri, che rilasciano in licenza opendata tutti i propri dati. Tra le stazioni censite ci sono anche dati di stazioni italiane. Basta inviare la propria posizione (graffetta) ed avere la stazione più vicina e quindi tutti i prossimi treni in partenza. Puoi anche scrivere direttamente la stazione esempio Bari Centrale oppure se inserisci lo / hai tutte le fermate intermedie: esempio /Parma. Infine puoi controllare i treni tra due stazioni inserendo le stazioni separate da % e data. esempio << Parma%Bologna Centrale%2015-10-30 >> dove la data è inserita nel formato anno-mese-giorno");
$reply .="\nTransport CH Bot is experimental and demonstration service for schedules, routes and stations of Transport Swiss, releasing licensed OpenData all their data. Among the surveyed stations there are also data of Italian and European stations. Simply send your position (paper clip) and have the closest station and then all the next trains departing. You can also write directly to the example Bari Central Station, or if you enter it / you all the stops: as /Parma. Finally you can check the trains between two stations by entering the stations separated by % to date. << example Parma%Bologna Centrale%10/30/2015 >> where the date is inserted in the year-month-day format";
         $content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
				 $telegram->sendMessage($content);
				 $log=$today. ";crediti sent;" .$chat_id. "\n";
			}

	    elseif($location!=null)
			{
        $this->location_manager($db,$telegram,$user_id,$chat_id,$location);
          exit;

    		}
			//ricerca partenze da stazione
			else{
        $reply ="Attendere prego.. / Wait please ...";
                 $content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
        				 $telegram->sendMessage($content);
        if (strpos($text,'/') !== false) {
          $reply = $data->get_startcompleto($text);

        }else if (strpos($text,'%') !== false) {
          $option1= explode("%", $text);

          $reply = $data->get_connection(utf8_encode($option1[0]),utf8_encode($option1[1]),$option1[2]);

        }else {
        $reply = $data->get_start($text);
        }


         $chunks = str_split($reply, self::MAX_LENGTH);
     	  foreach($chunks as $chunk) {
     	 	// $forcehide=$telegram->buildForceReply(true);
     	 		 //chiedo cosa sta accadendo nel luogo
     	 		 $content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);
     	 		 $telegram->sendMessage($content);

     	  }

	  	//	 $content = array('chat_id' => $chat_id, 'text' => $reply);
			//	 $telegram->sendMessage($content);
				 $log=$today. ";start command sent;" .$chat_id. "\n";
			 }


			//aggiorna tastiera
			$this->create_keyboard($telegram,$chat_id);

			//log
			file_put_contents(dirname(__FILE__).'/./telegram.log', $log, FILE_APPEND | LOCK_EX);


	}


	// Crea la tastiera
	 function create_keyboard($telegram, $chat_id)
		{
				$option = array(["near station","travel"],["info"]);
				$keyb = $telegram->buildKeyBoard($option, $onetime=false);
				$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "[seleziona un comando / Select a command]");
				$telegram->sendMessage($content);
		}


  function location_manager($db,$telegram,$user_id,$chat_id,$location)
  	{
      	$data=new getdata();
  			$lng=$location["longitude"];
  			$lat=$location["latitude"];

  			$response=$telegram->getData();
  			$bot_request_message_id=$response["message"]["message_id"];
  			$time=$response["message"]["date"];

        $nome = $data->get_stations($lat,$lng);
        $option1= explode("\n", $nome);
        if ($nome =="Non ci sono stazioni"){
        $content = array('chat_id' => $chat_id, 'text' => $nome,'disable_web_page_preview'=>true);

        }else{
      //  $option = array($option1);
        $optionf=array([]);
        		for ($i=0;$i<count($option1)-1;$i++){
    			array_push($optionf,[" ".$option1[$i]]);
            }
				$keyb = $telegram->buildKeyBoard($optionf, $onetime=false);
				$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "[seleziona una stazione: / select a station:]");
        }
        $telegram->sendMessage($content);


  	}
    function location_manager_inline($inline_query,$telegram,$data)
      {


          $data=new getdata();
          $trovate=0;
          $res=[];
          $id="";
          $i=0;
          $idx=[];
          $distanza=[];
          $id3="";
          $id1="";
          $inline="";
        $id=$inline_query['id'];
        $lat=$inline_query["location"]['latitude'];
        $lon=$inline_query["location"]['longitude'];


          $nome = $data->get_stations($lat,$lon);
          $option1= explode("\n", $nome);
          if ($nome =="Non ci sono stazioni")
        {

        //  $content = array('chat_id' => $chat_id, 'text' => $nome,'disable_web_page_preview'=>true);
          $id3 = $telegram->InlineQueryResultLocation($id."/0", $lat,$lon, "Nessuna stazione in questo luogo\nNo Rail Station around you in this place");
          $res= array($id3);
          $content=array('inline_query_id'=>$inline_query['id'],'results' =>json_encode($res));
          $telegram->answerInlineQuery($content);
          $this->create_keyboard($telegram,$chat_id);
          exit;
          }else
            {
        //    $content = array('chat_id' => 69668132,'text' => json_encode($inline_query["location"]));
        //     $telegram->sendMessage($content);

        //  $option = array($option1);
        //  $optionf=array([]);
          for ($i=0;$i<count($option1)-1;$i++)
            {
        //    array_push($optionf,[" ".$option1[$i]]);

        //    $location =preg_replace('/\s+?(\S+)?$/', '', substr(trim($option1[$i]), 0, 45));
            $idx[$i] = $telegram->InlineQueryResultArticle($id."/".$i, $option1[$i], array('message_text'=>$option1[$i],'disable_web_page_preview'=>true),"http://www.piersoft.it/transportchbot/bus.png");
            array_push($res,$idx[$i]);
              }

              $content=array('inline_query_id'=>$inline_query['id'],'results' =>json_encode($res));
              $telegram->answerInlineQuery($content);

          }




      }

  }

  ?>
