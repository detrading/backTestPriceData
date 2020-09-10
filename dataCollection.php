<?php

$timestart = microtime(true);
require_once("dbcrypto_bittrex.php");
require_once("bittrex_api.php");

$key = ""; 
$secret = "";

$bittrexport = new Client ($key, $secret);
 
 $output = $bittrexport->getMarketSummaries ();
 
 $array = json_decode(json_encode($output), True);
//$output = $bittrexport->prices();

//echo '<pre>'; print_r($output); echo '</pre>';

$k=0;

$query = "BEGIN TRANSACTION;";
//die();
foreach ($array as $key => $value) {

// var_dump($key, $value);
$market = SQLite3::escapeString($array[$key]['MarketName']);
$market = str_replace("-","_", $market);
$marketarr[] = $market;
$bidprice  = SQLite3::escapeString($array[$key]['Bid']);
//$num_bid  = SQLite3::escapeString($array[$key]['OpenBuyOrders']);
$askprice  = SQLite3::escapeString($array[$key]['Ask']);
//$num_ask  = SQLite3::escapeString($array[$key]['OpenSellOrders']);
$volume  = SQLite3::escapeString($array[$key]['Volume']);
 //if ((($askprice - $bidprice)*100000000) < 1) { $askbid_diff_sats = 1; } else { $askbid_diff_sats = ($askprice - $bidprice)*100000000; } $askbid_diff_sats = round($askbid_diff_sats);
 //if (($askprice - $bidprice) < 1) { $askbid_diff_sats = 1; } else { $askbid_diff_sats = ($askprice - $bidprice); } 
//$askbid_diff_sats = ($askprice - $bidprice);
//if	(  $num_ask == 0 || $num_bid == 0 ){ 

//$saleminusbuy = 0;
//} else { $saleminusbuy = $num_ask - $num_bid;  } $saleminusbuy = round($saleminusbuy,5);


//$market = (string)$market;

$bidprice = (string)number_format($bidprice,8);
$askprice = (string)number_format($askprice,8);


//$volumeIncreaseIfAny = 
//$volumearr[] = $volume;

/*
$sql = "INSERT INTO snapshots ( marketname,
                             askprice,
                             bidprice,
                             dtnow
                             ) VALUES ( '$market', '$askprice', '$bidprice', datetime('now') );";

if($db->exec($sql) ) {echo $marketarr[$key], 'pass'; } else { echo $market, $askprice, $bidprice, PHP_EOL; }
*/

$query .= "INSERT INTO snapshots ( marketname,askprice,bidprice,dtnow, volincreasepct) VALUES ( '$market', '$askprice', '$bidprice', datetime('now'),'$volume');";

	$k++;
 }


$query .= "END TRANSACTION;";



 $db->exec($query) or  die(print_r($db->errorInfo(), true));

 //$sqlzz->free_result();
 // var_dump($binance_market, $bidprice ,$askprice,$saleminusbuy, $askbid_diff_sats);
 //$query = $mysqli->prepare($sqlzz);// or trigger_error($mysqli->error."[$sqlzz]");
 //$query->bind_param("sdddi",  $binance_market, $bidprice , $askprice , $saleminusbuy, $askbid_diff_sats);
 //while ($mysqli->next_result()) {;} 
 
 $timeend= microtime(true);
 $timetaken = $timeend- $timestart;
 echo PHP_EOL, $timetaken;
 //die(print_r($db->errorInfo(), true));

 //sleep(1);
 
 /*
$fp = fopen('market.txt', 'w+');
fwrite($fp, print_r($marketarr));
fclose($fp);
*/
 
//$jsonData = json_encode($marketarr);
//file_put_contents('market.txt', $jsonData);

//$fp = fopen('volume.json', 'w');
//fwrite($fp, json_encode($volume));
//fclose($fp);


//$db->exec('PRAGMA journal_mode = wal; pragma synchronous = off;');

/*
 sleep(1);
 
 
 
 $query_string =<<<EOF
	  DELETE FROM binance_data
      WHERE datetime_now <= '2019-05-016 17:51:02';
EOF;
 $db->exec($query_string) or die($db->errorInfo()[2]);

 //datetime(datetime_now) > datetime('now', '-480 Minute')
 */
 


  $query_string ="DELETE FROM snapshots WHERE dtnow <= datetime('now','-1 day');";
 $db->exec($query_string);
 $db->exec("vacuum") or die($db->errorInfo()[2]);
 
 
