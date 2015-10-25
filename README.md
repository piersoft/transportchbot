
Data from http://transport.opendata.ch/ in opendata lic.

In localhost is possible to launch
php start.php 'sethook' to set start.php as webhook
php start.php 'removehook' to remove start.php as webhook
php start.php 'getupdates' to run getupdates.php

After setup webhook is possible to use telegram managed by webhost


Rename settings_t_template.php in settings_t.php and
1) insert API Google Key for shortner url
2) Token Telegram Bot
3) optionally public url for https connection with webhook to start.php file
