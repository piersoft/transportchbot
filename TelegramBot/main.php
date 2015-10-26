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

		$text = $update["message"] ["text"];
		$chat_id = $update["message"] ["chat"]["id"];
		$user_id=$update["message"]["from"]["id"];
		$location=$update["message"]["location"];
		$reply_to_msg=$update["message"]["reply_to_message"];

		$this->shell($telegram, $db,$data,$text,$chat_id,$user_id,$location,$reply_to_msg);
    $db = NULL;
	}

	//gestisce l'interfaccia utente
	 function shell($telegram,$db,$data,$text,$chat_id,$user_id,$location,$reply_to_msg)
	{
		date_default_timezone_set('Europe/Rome');
		$today = date("Y-m-d H:i:s");

		if ($text == "/start") {
				$log=$today. ";new chat started;" .$chat_id. "\n";
			}
 if ($text == "stazione più vicina"){

   $reply = ("Invia la tua posizione cliccando sulla graffetta (📎) in basso e, se vuoi, puoi cliccare due volte sulla mappa e spostare il Pin Rosso in un luogo di cui vuoi conoscere le stazioni più vicine");
   $content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
   $telegram->sendMessage($content);
   $log=$today. ";stazioneinfo sent;" .$chat_id. "\n";
 }
 else if ($text == "viaggio"){

   $reply = ("Scrivi stazionepartenza%stazionearrivo%data dove data è nel formato anno-mese-giorno. Per esempio: Lecce%Roma%2015-10-30");
   $content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
   $telegram->sendMessage($content);
   $log=$today. ";stazioneinfo sent;" .$chat_id. "\n";
 }
			//crediti
	elseif ($text == "/informazioni" || $text == "informazioni") {
				 $reply = ("Transport CH Bot e' un servizio sperimentale e dimostrativo per orari, tratte e stazioni dei Trasporti Svizzeri, che rilasciano in licenza opendata tutti i propri dati. Tra le stazioni censite ci sono anche dati di stazioni italiane. Basta inviare la propria posizione (graffetta) ed avere la stazione più vicina e quindi tutti i prossimi treni in partenza");
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
        if (strpos($text,'/') !== false) {
          $reply = $data->get_startcompleto($text);

        }else if (strpos($text,'%') !== false) {
          $option1= explode("%", $text);

          $reply = $data->get_connection(utf8_decode($option1[0]),utf8_decode($option1[1]),$option1[2]);

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
				$option = array(["stazione più vicina","viaggio"],["informazioni"]);
				$keyb = $telegram->buildKeyBoard($option, $onetime=false);
				$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "[seleziona un comando]");
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
        $option = array($option1);
				$keyb = $telegram->buildKeyBoard($option, $onetime=false);
				$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "[seleziona una stazione:]");
        }
        $telegram->sendMessage($content);


  	}


  }

  ?>
