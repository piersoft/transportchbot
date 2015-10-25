<?php
//modulo delle KEYs per funzionamento dei bot (da non committare)

//Telegram
define('API',''); //inserire google API per gli shortner url
define('TELEGRAM_BOT',''); // inserire il token del Telegram bot
define('BOT_WEBHOOK', 'https://..........start.php'); // inserire url pubblico https per start.php
define('LOG_FILE', 'telegram.log');

$db_path=dirname(__FILE__).'/./db/italia.sqlite'; 
$csv_path=dirname(__FILE__).'/./db/map_data.csv';
define ('DB_NAME', "sqlite:". $db_path);
define('DB_TABLE',"user");
define('DB_TABLE_GEO',"segnalazioni");
define('DB_CONF', 0666);
define('DB_ERR', "errore database SQLITE");

// Your Openstreetmap Query settings
define('AROUND', 5000);						//Number of meters to calculate radius to search
define('MAX', 30);							//max number of points to search
//define('TAG','"amenity"="pharmacy"');

?>
