<?PHP

require_once("binance_api2.php");
require_once("binance_api2.php");
require_once('funct.php');
require_once( dirname(__DIR__)."/dbcrypto_binance.php");

 $keyp = ""; 
$secret = "";

\$binanceport = new Binancezz ($keyp, $secret);
$binanceport2 = new Binance\API($keyp,$secret);
 //$binanceport2->useServerTime();
 $output = $binanceport->prices();  
 
 //echo '<pre>'; print_r($output); echo '</pre>';

$tokenofChoice = "ETC";
$marketSecond = "BTC";
//$market = $marketSecond.'_'.$tokenofChoice;
//$market_bittrex_proper = $marketSecond.'-'.$tokenofChoice;
$market_binance_proper = $tokenofChoice. $marketSecond;


//$minimumcanordersize = .06;

 $output2 = $binanceport->bookPrices();  
//echo '<pre>'; print_r($output2); echo '</pre>';


$TOCsec_data_bid = $output2[$market_binance_proper]['bid'];
$TOCsec_data_ask = $output2[$market_binance_proper]['ask'];
$TOCsec_data_askbid_diff = $TOCsec_data_ask - $TOCsec_data_bid;


$ticker = $binanceport2->prices(); // Make sure you have an updated ticker object for this to work
$balances = $binanceport2->balances($ticker);
//print_r($balances);
//echo "BTC owned: ".$balances[$tokenofChoice]['available'].PHP_EOL;
//echo "ETH owned: ".$balances['ETH']['available'].PHP_EOL;
//echo "Estimated Value: ".$binanceport2->btc_value." BTC".PHP_EOL;


//$output3 =  $binanceport2->balances($output);
//echo '<pre>'; print_r($output3); echo '</pre>';

//$balances[$tokenofChoice]['available']










$cash_bal_all = $balances[$marketSecond]['available'];
$cash_bal = $cash_bal_all;//-> Available; 
//if (!is_numeric($cash_bal)) { $cash_bal = 0; }
$toc = $balances[$tokenofChoice]['available'];

$sampleSizeStarting = 120;
$sampleSizeRunning = $sampleSizeStarting; 
//$toc = 0;  $cash_bal = .111; // USD 1120;
$mainmkt = $marketSecond; $market = $tokenofChoice.$mainmkt; 
echo '<BR><BR>',$tokenofChoice, '-', $mainmkt, '<BR><BR>';
$query_string ="SELECT ask, askbid_diff, s_minus_b FROM binance_data where market = 'BTCUSDT'  order by datetime_now DESC Limit 1";
$query = $db->query($query_string) or die($db->errorInfo()[2]);
while( $row = $query->fetch(PDO::FETCH_ASSOC)) {
	$USDBTC_askPrice[] = $row['ask'];}	
$query_string ="SELECT ask, askbid_diff, s_minus_b FROM binance_data where market = '$market'  order by datetime_now  DESC Limit 1";
$query = $db->query($query_string) or die($db->errorInfo()[2]); while( $row = $query->fetch(PDO::FETCH_ASSOC)) {
	$MKTTOC_askPrice[] = $row['ask'];}


$minimumordsize_BTC = (.00050501 /$MKTTOC_askPrice[0]);
if ($mainmkt === 'USDT'){ $ordainOrdAmnt =   680/$MKTTOC_askPrice[0]; } 
elseif  ($mainmkt === 'BTC') { $ordainOrdAmnt =   ($minimumordsize_BTC * 2);  $minimumordsize =   $minimumordsize_BTC;}  
 

$Minutes = -180; 
$query_string ="SELECT ask, askbid_diff, s_minus_b FROM binance_data where market = '$market'  AND  datetime(datetime_now) > datetime('now', '{$Minutes} Minute');";
$query = $db->query($query_string) or die($db->errorInfo()[2]);
while( $row = $query->fetch(PDO::FETCH_ASSOC)) {
	$askPriceFetched[] = $row['ask'];
	$bidPriceFetched[] = $row['ask'] - $row['askbid_diff'];
	$sob[] = $row['s_minus_b'];
	$askbiddiffFetched[] = $row['askbid_diff'];
}
//echo '<pre>' ; print_r($bidPrice, false); echo '</pre>' ;


$weightsarr = createWeightsArray(96, 1.03 ); //$weightsarr = createWeightsArray($sampleSizeStarting, 1.05 );
$weightsarr = array_reverse($weightsarr);

//$weightsarr2 = createWeightsArray(72, 1.15 ); //$weightsarr = createWeightsArray($sampleSizeStarting, 1.05 );
//$weightsarr2 = array_reverse($weightsarr2);

$key = $sampleSizeStarting; //$sampleSizeStarting -2; //$key =0;
$lastvalkey = $key -1; //bc 0 start of array
$sidecount=0; //changed from 1 2-16



$cumcount =  count($bidPriceFetched);//  - $sampleSizeStarting -1;
echo $cumcount;
$cumcountNeg = $cumcount * -1;// + $sampleSizeStarting;
//$iterations_tot = $cumcount;// - $sampleSizeStarting;
$bidPrice = array_slice($bidPriceFetched, $cumcountNeg , $key);
//$askPrice = array_slice($askPriceFetched, $cumcountNeg , $key);
$askbiddiff = array_slice($askbiddiffFetched, $cumcountNeg , $key);
//$bidPrice = array_shift($bidPrice);
while ( $key < $cumcount){
		//echo '<BR>', $key;
		if($key === $sampleSizeStarting) {  }  else    {		
		
		array_push($bidPrice,$bidPriceFetched[$key]); array_shift($bidPrice);    
		//array_push($askPrice,$askPriceFetched[$key]); array_shift($askPrice); 
		array_push($askbiddiff,$askbiddiffFetched[$key]); array_shift($askbiddiff); 

		}
	//$underslice2 = 60 ; $pricesPortion = array_slice($bidPrice, (-1 *$underslice2),$underslice2);
			//$underslice3 = 144 ; $pricesPortion2 = array_slice($bidPrice, (-1 *$underslice3),$underslice3);
			$underslice4 = 96 ; $pricesPortion3 = array_slice($bidPrice, (-1 *$underslice4),$underslice4);
			$underslice5 = 48 ; $pricesPortion4 = array_slice($bidPrice, (-1 *$underslice5),$underslice5);
			$underslice6 = 96 ; $pricesPortion6 = array_slice($bidPrice, (-1 *$underslice6),$underslice6);
		
			$maxBP = max($bidPrice);
				$minBP = min($bidPrice);
				$howfarcurrvalfrommax = $maxBP - $bidPrice[$lastvalkey];
				$howfarcurrvalfrommaxArr[] = $howfarcurrvalfrommax;
				
				$howfarcurrvalfrommin = $minBP - $bidPrice[$lastvalkey];
				$howfarcurrvalfromminArr[] = $howfarcurrvalfrommin;
				$maxminDiff = $maxBP - $minBP;//$minBP - $maxBP;
				$maxminDiffArr[] = $maxminDiff;
							$norm_paramfrommin = ($bidPrice[$lastvalkey] - $minBP) / $maxminDiff;
				$norm_paramfromminArr[] = $norm_paramfrommin;
				
				$norm_paramfrommax = ($maxBP - $bidPrice[$lastvalkey] ) / $maxminDiff;
				$norm_paramfrommaxArr[] = $norm_paramfrommax;
				
				//$zzz = ($sampleSizeRunning/2);
				//$sobma[1][$sidecount] = exponentialMovingAverage($bidPrice, 5);
				$sobma[1][$sidecount] = weightedMovingAverage($pricesPortion3, $weightsarr);
				$sobma[0][$sidecount] = simpleAverage($bidPrice);
				$sobma[2][$sidecount] = simpleAverage($pricesPortion6);
				//$sobma[3][$sidecount] = simpleAverage($pricesPortion);//weightedMovingAverage($pricesPortion, $weightsarr2); //
				//$sobma[3][$sidecount] = simpleAverage($pricesPortion2); //weightedMovingAverage($pricesPortion2, $weightsarr2);
				//$sobma[4][$sidecount] = weightedMovingAverage($pricesPortion3, $weightsarr2);


		
		$masd = sd($bidPrice);
		$col_masd[$sidecount] = $masd;
		$stmasd = $masd;
		$col_stmasd[$sidecount] = $stmasd;
		if($sidecount > 3) {
		$maxstmasd = max($col_stmasd);
		$minstmasd = min($col_stmasd);
		$maxminDiffstmasd = $maxstmasd - $minstmasd;
		
		$norm_paramstmasd = ($stmasd - $minstmasd) / $maxminDiffstmasd;
		$norm_paramstmasdArr[] = $norm_paramstmasd;
		} else {  $norm_paramstmasd =  1; }
				 /*
				$fillin = ( .000250 * $MKTTOC_askPrice[0] );  
		$masd = sd($lastvaluesarr2);
		$col_masd[] = $masd;
		if($masd < $fillin) { $sampleSizeRunning = $sampleSizeRunning +1; }
		if($masd > $fillin && $sampleSizeRunning != $sampleSizeStarting) { $sampleSizeRunning = $sampleSizeRunning -1; }
		//if ($masd < ( $fillin/20 )) { $masd = ( $fillin/20 );} 
		if($sampleSizeRunning < $sampleSizeStarting) {  $sampleSizeRunning = $sampleSizeStarting; }
			
			$sampRunStartDiff = $sampleSizeRunning - $sampleSizeStarting;
			$thiscalc = $thiscalc + 1 ; //(($sidecount - $cumcount) - $sampRunStartDiff);
			if( abs($thiscalc) < $sampleSizeRunning) { $thiscalc = -1 *  $sampleSizeRunning; }
			//TODO: $thiscalc does not have purpose
			
			
			
		$lastvaluesarr2PctChg =  ( end($lastvaluesarr2) - $lastvaluesarr2[$sampleSizeRunning - 2] ) / $lastvaluesarr2[$sampleSizeRunning - 2]; //array keys
		//echo '<pre>';print_r($lastvaluesarr2); echo '<pre>';	
			*/
			$sampleSizeRunning = $sampleSizeStarting;
//echo '<pre>' ; print_r($bidPrice); echo '</pre>' ;
if ($sidecount != 0 ) 
{
	$pctchangebidprice = round((($bidPrice[$lastvalkey] - $bidPrice[$lastvalkey - 1])/$bidPrice[$lastvalkey -1]),5);	}
	$avgpctchangebidprice = round((($bidPrice[$lastvalkey] -$sobma[0][$sidecount]) / $sobma[0][$sidecount]),5);
	$lastthreevalaverage = simpleAverage(array($bidPrice[$lastvalkey], $bidPrice[$lastvalkey - 1],$bidPrice[$lastvalkey - 2],$bidPrice[$lastvalkey - 3],$bidPrice[$lastvalkey - 4],$bidPrice[$lastvalkey - 5]));
	$STavgpctchangebidprice = round((($bidPrice[$lastvalkey] -$lastthreevalaverage) / $lastthreevalaverage),5);
	echo ' <b>', $avgpctchangebidprice, ' ST: '. $STavgpctchangebidprice .'</b> ';


	$portionavgpctchangebidprice = round((($bidPrice[$lastvalkey] -$sobma[2][$sidecount]) / $sobma[2][$sidecount]),3);
	$portionavgpctchangebidpriceArr[] = $portionavgpctchangebidprice;
//$changer_min =36;
//$changer_min = round($maxminDiff * 25000000);
//$changer_min = round(($maxminDiff * 4000),3);
//$changer_min = round(($maxminDiff * 1000),3);
//$changer_min = round(($maxminDiff * 1600000),2);// + abs(round(($maxminDiff * 1600000),8)); //+ .98;

$sell_min =160/100000000;
$buy_min = 180/100000000;
 $resid = sqrt($bidPrice[$lastvalkey] - $sobma[1][$sidecount]);
if(is_nan($resid)){ $resid = 0;}
	$residArr[$sidecount] = $resid;
	
		$sobma['sell_line'][$sidecount] = $sobma[1][$sidecount] +   ($stmasd *.2);
		$sobma['buy_line'][$sidecount] = $sobma[1][$sidecount] - ($stmasd * .5 );
//}

$distaway = $sobma[2][$sidecount] - $sobma['buy_line'][$sidecount];
$distawayArr[] = $distaway;
	
$sobma['sell_line'][$sidecount] = round($sobma['sell_line'][$sidecount],10); $sobma['buy_line'][$sidecount] = round($sobma['buy_line'][$sidecount],10);
$buyselldiff = $sobma['sell_line'][$sidecount] - $sobma['buy_line'][$sidecount];

$buyselllinediffArr[] = $buyselldiff;


	
	//$rando = rand(0,2);
	$ordtype = "11";
	$multidistaway = ($distaway*1000000);
	
	$multidistaway = $multidistaway * -1;
	$multidistawayArr[] =$multidistaway;
	//if($multidistaway > 0) { $multidistaway = $multidistaway * -1; } elseif ($multidistaway < 0) {  $multidistaway = $multidistaway * -1; }
	//if($rando == 0) {   $orderpaddingbuy = $askbiddiff[$lastvalkey] * 0;  } elseif($rando == 1)  { $orderpaddingbuy = $askbiddiff[$lastvalkey] * -.2; } else { $orderpaddingbuy = $askbiddiff[$lastvalkey] * -.4; }
		$orderpaddingbuy = $askbiddiff[$lastvalkey] * $multidistaway;
if($sobma['buy_line'][$sidecount] > $bidPrice[$lastvalkey] ) {
	$zz = abs(( ($bidPrice[$lastvalkey] + $orderpaddingbuy) - $sobma['buy_line'][$sidecount])/$masd);
	$min_ordamnt = (float)round(($ordainOrdAmnt * (($zz + 0 )*3)),2); //changed mar12 for ltc
	$min_ordamnt = $min_ordamnt - ($distaway*2000000);
	if($cash_bal >= ($bidPrice[$lastvalkey] * $min_ordamnt) ){
		//$pctchangebidprice = (($askPrice[$sidecount] - $askPrice[$sidecount - 1])/$askPrice[$sidecount]);
	//if($pctchangebidprice <  ( 0 )) { //if($pctchangebidprice <  ( - .0001)) { //  0.00024
		
		//$ordtype = "22";
		//echo $sidecount,'   ', $min_ordamnt, '<--buyblock--> ',$bidPrice[13], '      ', round($pctchangebidprice, 3), '<BR>';
//	} else {
		$ordtype = "buy";if( $min_ordamnt < $minimumordsize) {$min_ordamnt = $minimumordsize;}
		//$cash_bal = $cash_bal - ($min_ordamnt * ($bidPrice[13] + $orderpaddingbuy));
		//$cash_bal = $cash_bal - ((($min_ordamnt * $askPrice[$sidecount])) * .001);								//cash bal
		//$toc = $toc + ($min_ordamnt);
	//	} 
		
		} else { }
		}
			
	$rando = rand(0,9);
	//$orderpaddingsell = $askbiddiff[$lastvalkey] * 0;
	//if($rando == 0 || $rando == 1 || $rando == 2 || $rando == 3) {  $orderpaddingsell = $askbiddiff[$lastvalkey] * 1.1; } elseif($rando == 4 || $rando == 5 || $rando == 6 ) { $orderpaddingsell = $askbiddiff[$lastvalkey] * 1.3; } 
	//elseif($rando == 7 || $rando == 8) { $orderpaddingsell = $askbiddiff[$lastvalkey] * 1.6; } else  { $orderpaddingsell = $askbiddiff[$lastvalkey] * 2; }
		if($rando == 0 || $rando == 1 || $rando == 2 || $rando == 3) {  $orderpaddingsell = $askbiddiff[$lastvalkey] * .8; } elseif($rando == 4 || $rando == 5 || $rando == 6 ) { $orderpaddingsell = $askbiddiff[$lastvalkey] * 1; } 
	elseif($rando == 7 || $rando == 8) { $orderpaddingsell = $askbiddiff[$lastvalkey] * 1.3; } else  { $orderpaddingsell = $askbiddiff[$lastvalkey] * 1.6; }
if($sobma['sell_line'][$sidecount] <= ($bidPrice[$lastvalkey] )) {  
	$zz = abs(( ($bidPrice[$lastvalkey] + $orderpaddingsell) - $sobma['sell_line'][$sidecount])/$masd);
	$min_ordamnt = (float)round(($ordainOrdAmnt * (($zz + 0)*6)),2);//changed mar12 for ltc
	//$min_ordamnt = $ordainOrdAmnt;
	//$min_ordamnt = $fibmulti * $ordainOrdAmnt;
	//$min_ordamnt = ($norm_paramfrommin+1) * ($norm_paramstmasd+1) * $min_ordamnt * 1.6;
	if( $min_ordamnt < $minimumordsize) {$min_ordamnt = $minimumordsize; }
	
	//if(  $pctchangebidprice <   .00035  ) {   //if($pctchangebidprice <   .00001 ||  $pctchangebidprice >   .00048  ) {

			//	$ordtype = ""; echo $sidecount,'   ', $min_ordamnt, '<--sellblock--> ',$bidPrice[13], '      ', round($pctchangebidprice, 3), '<BR>';
		//} else {
	$ordtype = "sell";
		//}
	
	
	/*
	if($toc >=  $min_ordamnt ){
		//$pctchangebidprice = (($askPrice[$sidecount] - $askPrice[$sidecount - 1])/$askPrice[$sidecount]);
		$ordtype = "sell";
		if($min_ordamnt < $toc) {
			//$cash_bal = $cash_bal + ($min_ordamnt * ($bidPrice[13] + $orderpaddingsell));
			//$cash_bal = $cash_bal - (($min_ordamnt * ($bidPrice[13] + $orderpaddingsell))  * .001);          //cashbal
			//$toc = $toc - $min_ordamnt;}
				}			
		} else {
			$ordtype = "33"; //echo $sidecount,'   ', $min_ordamnt, ' Sell fail, no TOC to sell ',$bidPrice[13], '      ', round($pctchangebidprice, 3), '<BR>';
			}
			*/
			
			}
			

//$finalbal[$sidecount] = round(($cash_bal + ($toc * $bidPrice[$sidecount]  )), 8);
/*
if(!empty($ordtype)){ 
	if($ordtype == "sell") { 
		echo '<p style="color: red;">', $sidecount, ',,', $ordtype, '---',$min_ordamnt, '... (',$bidPrice[$sidecount],')', ($bidPrice[$sidecount] +  $orderpaddingsell), '(', $askPrice[$sidecount], ')', ' ---||| ', $cash_bal, '------(', $toc  ,')---', end($finalbal) , '----clip',$sampleSizeRunning,'|||', $lastvaluesarr2PctChg ,'</p>';
	} 
	if($ordtype == "buy"){
		echo '<p style="color: green;">', $sidecount, ',,', $ordtype, '---', $min_ordamnt, '... (',$bidPrice[$sidecount],')', ($bidPrice[$sidecount] + $orderpaddingbuy), '(', $askPrice[$sidecount], ')', '---||| ', $cash_bal, '------(', $toc  ,')---', end($finalbal)  , '----clip',$sampleSizeRunning,'|||',  $lastvaluesarr2PctChg,'</p>';
	}}
*/		
		
$key++;	 
$sidecount++;		
$sampSizeArr[] = $sampleSizeRunning; 		
			}
//$key = max(array_keys($bidPrice)); //'my_index'array_key_exists('my_index', $array)
var_dump($ordtype);
echo PHP_EOL,
$sobma['buy_line'][$sidecount-1],'********', number_format($bidPrice[$lastvalkey],7),'********', $sobma['sell_line'][$sidecount-1],PHP_EOL;








//die();





if($ordtype === "sell" || $ordtype === "buy"){

//$intz = min(array_keys($finalbal));
//$netprofit = $finalbal[$key] - $finalbal[$intz];
//$finalbal = $cash_bal + ($toc * $askPrice[$k]);
var_dump($orderpaddingbuy);


//TODO
$ptbpbuy = (floor((($TOCsec_data_bid + $orderpaddingbuy) *10000000))/10000000);// 6 decimal places
$ptbpsell = (ceil((($TOCsec_data_bid + $orderpaddingsell) *10000000))/10000000); // 6 decimal places

$ptbpbuy = number_format($ptbpbuy, 7);
$ptbpsell = number_format($ptbpsell, 7);

var_dump($ptbpbuy ,$ptbpsell);

//if(!isset($generatedOrderAmount)  || $generatedOrderAmount === 'NULL' || $generatedOrderAmount == 0 ) { $generatedOrderAmount= .118; }
$generatedOrderAmount = $min_ordamnt;
$quantityAmount = round($generatedOrderAmount,2); //round($generatedOrderAmount,8); //changed for ltc March 12



//$generatedOrderAmount; 
//var_dump($market_bittrex_proper, $quantityAmount, $ptbpbuy, $ptbpsell, $lastordtype);
date_default_timezone_set('America/Los_Angeles');

if($ordtype === "sell") {
	
	$walletbalancetoc_all = $balances[$tokenofChoice]['available'];
	$walletbalancetoc = $walletbalancetoc_all;
	
	//floor below
	
	if($quantityAmount > $walletbalancetoc) { $quantityAmount = ceil($walletbalancetoc*100)/100; } //(floor($walletbalancetoc*100)/100); } //changed mar`2 for LTC
	
	$FFF="";
			//if(  $g->sellLimit((string)$market_binance_proper, (string)$quantityAmount, (string)$ptbpsell)  ){$FFF="ord created"; } else {$FFF=""; }
	$binanceport2->sell($market_binance_proper, $quantityAmount, $ptbpsell);
	$txt = date('Y-m-d H:i:sP') .  $ordtype.'     '.$market_binance_proper.'     '.$quantityAmount.'     '.$ptbpsell.'  '.$walletbalancetoc.'  '. $FFF .'------'.  round($masd,8) ;
 $myfile = file_put_contents(dirname(__DIR__).'/html/hive/gtm_logbin.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);

 }
	
	



	
	
	
	
	
 
if ($ordtype === "buy"){
	$walletbalancemkt_all = $balances[$marketSecond]['available'];
	$walletbalancemkt = $walletbalancemkt_all;
	//sleep(1);
	if(($quantityAmount * $ptbpbuy) > $walletbalancemkt) { } //todo fix
	$FFF="";
			//if(  $g->buyLimit((string)$market_binance_proper, (string)$quantityAmount, (string)$ptbpbuy)){$FFF="ord created"; } else {$FFF=""; }
	$binanceport2->buy($market_binance_proper, $quantityAmount, $ptbpbuy);
	$txt =date('Y-m-d H:i:sP') . $ordtype.'     '.$market_binance_proper.'     '.$quantityAmount.'     '.$ptbpbuy.' ---x--- '. $FFF .'------'.  round($masd,8) ;
 $myfile = file_put_contents(dirname(__DIR__).'/html/hive/gtm_logbin.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
} 

}
//$arr = get_defined_vars();
//print_r($arr);
