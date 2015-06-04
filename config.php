<?php

/* CONNECTION */
//define('CONNECTION_PROTOCOL','2'); //1 - use file_get_contents, 2 - use curl 

/* BOT */
//define('BOT_DELAY','1'); 				//time to delay between plugin
//define('BOT_SAVE_DB','1'); 				//write result to db, 0 - no; 1 - yes
//define('BOT_MAX_RETRY','10'); 			//max retry connection
//define('BOT_MAX_EXECUTION_TIME','3000');              //max time out - some host not accept set max execution time

define('IMAGE_PATH', '/img/');
//define('TV_IMAGE_FOLDER',dirname(__FILE__).'/../../assets/images/show/');

/* YOUTUBE API */
//define('YOUTUBE_API_KEY','AIzaSyCPDdUit8HAaQ2jaeCbEGNC9SnUws1vsso');


/* LOG */
define('LOG_ENABLED', '1');
define('LOG_CLEAN_DAY', '30');
define('LOG_PRINT_SCREEN','1'); //print to screen

/* MAIL */
define('SEND_SUPPORT_MAIL_ENABLED', '');
define('SUPPORT_MAIL_TO', 'huytv593@gmail.com');
define('SUPPORT_MAIL_FROM','huytv593@gmail.com');