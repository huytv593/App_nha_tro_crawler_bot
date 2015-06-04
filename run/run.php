<?php
header('Content-Type: text/html; charset=utf-8');
define('VBOT', 1); //init bot

chdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..');

require_once('config.php');
require_once('bot.php');
require_once('helper/vlog.php');

$plugin = 'BATDONGSAN';

$arr_link = array(
    strtoupper($plugin)
);

//* PARAM *//
//plugin to run bot
$arr_plugin = array(
    0 => 'BATDONGSAN',
);

//* BOT PROCESS *//
//init log object
$log = Log::log(LOG_ENABLED, LOG_PRINT_SCREEN);

/* START  */
$log->info('--- Bat dau chay bot ---'.'<br>');

//init bot
$bot = new vBot();
//remove old log
$bot->clean(LOG_CLEAN_DAY);

//get content
$bot->load_plugin($arr_plugin);
$bot->run($arr_link);

/* FINISH */
$log->info('--- Ket thuc bot ---');