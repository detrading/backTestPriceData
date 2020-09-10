<?PHP
require_once('funct.php');require_once("../../dbcrypto_bittrex.php");

$sampleSizeStarting = 240;
$sampleSizeRunning = $sampleSizeStarting; 
$tokenofChoice = "ETH"; $mainmkt = 'BTC'; $market = $mainmkt.'_'.$tokenofChoice; 
$toc = 0; $usd_bal = 500; $cash_bal = .0503; $starting_cash_bal = $cash_bal; // USD 1120;
echo '<BR><BR>',$market, '<BR><BR>';
$query_string ="SELECT askprice, bidprice FROM snapshots where marketname = 'USD_BTC'  order by dtnow DESC Limit 1";
$query = $db->query($query_string) or die($db->errorInfo()[2]);
while( $row = $query->fetch(PDO::FETCH_ASSOC)) {
	$USDBTC_askPrice[] = $row['askprice'];}	
$query_string ="SELECT askprice, bidprice FROM snapshots where marketname = '$market'  order by dtnow  DESC Limit 1";
$query = $db->query($query_string) or die($db->errorInfo()[2]); while( $row = $query->fetch(PDO::FETCH_ASSOC)) {
	$MKTTOC_askPrice[] = $row['askprice'];}


$minimumordsize_BTC = (.00050501 /$MKTTOC_askPrice[0]);
if ($mainmkt === 'USDT' || $mainmkt === 'USD' ){ $ordainOrdAmnt =   680/$MKTTOC_askPrice[0]; $cash_bal = $usd_bal; $minimumordsize =   $minimumordsize_BTC; } 
elseif  ($mainmkt === 'BTC') { $ordainOrdAmnt =   ($minimumordsize_BTC * 1);  $minimumordsize =   $minimumordsize_BTC;}  
 
$MaxSampleSize = 100;
$Minutes = -720;
if(isset($_GET['m'])) { $Minutes = -1 * $_GET['m']; }
$query_string ="SELECT askprice, bidprice, volincreasepct FROM snapshots where marketname = '$market'  AND  datetime(dtnow) > datetime('now', '{$Minutes} Minute');";
$query = $db->query($query_string) or die($db->errorInfo()[2]);
while( $row = $query->fetch(PDO::FETCH_ASSOC)) {
	$askPriceFetched[] = $row['askprice'];
	//echo $row['askprice'];
	$bidPriceFetched[] = $row['bidprice'];
    $askbiddiffFetched[] = $row['askprice'] - $row['bidprice'] ;
    $volumeFetched[] = $row['volincreasepct'];
}


$query_string ="SELECT bidprice FROM snapshots where marketname = 'USD_BTC'  AND  datetime(dtnow) > datetime('now', '{$Minutes} Minute');";
$query = $db->query($query_string) or die($db->errorInfo()[2]);
while( $row = $query->fetch(PDO::FETCH_ASSOC)) {
	$BTCUSDT_bidpriceFetched[] = str_replace( ',', '', $row['bidprice']);

}

//echo '<pre>' ; print_r($BTCUSDT_bidpriceFetched); echo '</pre>' ;
//die(); ///storing commas in the database


$buyOrders = 0;
$sellOrders = 0;
$printEnable = 0;

$weightsarr = createWeightsArray(150, 1.01 ); //$weightsarr = createWeightsArray($sampleSizeStarting, 1.05 );
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
$askPrice = array_slice($askPriceFetched, $cumcountNeg , $key);
$askbiddiff = array_slice($askbiddiffFetched, $cumcountNeg , $key);
$volume = array_slice($volumeFetched, $cumcountNeg , $key);
$BTCUSDT_bidprice = array_slice($BTCUSDT_bidpriceFetched, $cumcountNeg , $key);

//$bidPrice = array_shift($bidPrice);
while ( $key < $cumcount){
		//echo '<BR>', $key;
		if($key === $sampleSizeStarting) {  }  else    {		
		
		array_push($bidPrice,$bidPriceFetched[$key]); array_shift($bidPrice);    
		array_push($askPrice,$askPriceFetched[$key]); array_shift($askPrice); 
		array_push($askbiddiff,$askbiddiffFetched[$key]); array_shift($askbiddiff); 
		array_push($volume,$volumeFetched[$key]); array_shift($volume);
		array_push($BTCUSDT_bidprice,$BTCUSDT_bidpriceFetched[$key]); array_shift($BTCUSDT_bidprice);

		}
	//$underslice2 = 60 ; $pricesPortion = array_slice($bidPrice, (-1 *$underslice2),$underslice2);
			//$underslice3 = 144 ; $pricesPortion2 = array_slice($bidPrice, (-1 *$underslice3),$underslice3);
			$underslice4 = 150 ; $pricesPortion3 = array_slice($bidPrice, (-1 *$underslice4),$underslice4);
			$underslice5 = 36 ; $pricesPortion4 = array_slice($bidPrice, (-1 *$underslice5),$underslice5);
			$underslice6 = 36 ; $BTCpricesPortion5 = array_slice($BTCUSDT_bidprice, (-1 *$underslice6),$underslice6);
			
			
                $volincreaseonly[$sidecount] = $volume[$lastvalkey] - $volume[$lastvalkey - 1];
				if($volincreaseonly[$sidecount] < 0) { $volincreaseonly[$sidecount] = 0;}
				if($sidecount > 36){
					/*
				$volAVG[] = simpleAverage(array($volincreaseonly[$sidecount], $volincreaseonly[$sidecount - 1],
												$volincreaseonly[$sidecount - 2],$volincreaseonly[$sidecount - 3],
												$volincreaseonly[$sidecount - 4],$volincreaseonly[$sidecount - 5],
												$volincreaseonly[$sidecount - 6],$volincreaseonly[$sidecount - 7])
											);
											*/
											$volPortion4 = array_slice($volincreaseonly, (-1 * 36),36);
				$volAVG[$sidecount]= simpleAverage($volPortion4);
				}
				//$volumeArr[] = $volincreaseonly;
				//var_dump($lastvalkey, $sidecount); die();


				
                
				$askbiddiffArr[] = $askbiddiff[$lastvalkey];
				
        
				$maxBP = max($bidPrice);
				$minBP = min($bidPrice);
				$howfarcurrvalfrommax = $maxBP - $bidPrice[$lastvalkey];
				$howfarcurrvalfrommaxArr[] = $howfarcurrvalfrommax;
				$maxminDiff = $maxBP - $minBP;//$minBP - $maxBP;
				$maxminDiffArr[] = $maxminDiff;
				
				//$zzz = ($sampleSizeRunning/2);
				//$sobma[1][$sidecount] = exponentialMovingAverage($bidPrice, 5);
				$sobma[1][$sidecount] = weightedMovingAverage($pricesPortion3, $weightsarr);
				$sobma[0][$sidecount] = simpleAverage($bidPrice);
				$sobma[2][$sidecount] = simpleAverage($pricesPortion4);

				//$sobma[3][$sidecount] = simpleAverage($pricesPortion);//weightedMovingAverage($pricesPortion, $weightsarr2); //
				//$sobma[3][$sidecount] = simpleAverage($pricesPortion2); //weightedMovingAverage($pricesPortion2, $weightsarr2);
				//$sobma[4][$sidecount] = weightedMovingAverage($pricesPortion3, $weightsarr2);
                $distFromPriceArr[$sidecount] = $bidPrice[$lastvalkey] - $sobma[0][$sidecount];

$masd = sd($pricesPortion3); //$masd = sd($bidPrice);
		$col_masd[$sidecount] = $masd;
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
	//$x[$sidecount]= simpleAverage($BTCpricesPortion5);
	//var_dump($BTCpricesPortion5); die();
	//current average minus the current price
	//$BTCUSDT_changefrmAVG = round(($BTCUSDT_bidprice[$lastvalkey] - $x),8);
	//var_dump($x[$sidecount], $BTCUSDT_bidprice[$lastvalkey]); //die();
	 //var_dump($BTCUSDT_changefrmAVG); //echo '<BR>';
	 $BTCUSDT_changefrmAVG[$sidecount] = $BTCUSDT_bidprice[$lastvalkey] - $BTCUSDT_bidprice[$lastvalkey - 30]; //- 30];
	 //$BTCUSDT_changefrmAVG[$sidecount] = $BTCUSDT_changefrmAVG[$sidecount] *100000000;
	 //var_dump($BTCUSDT_changefrmAVG[$sidecount]);
	 //var_dump($BTCUSDT_bidprice[$lastvalkey], $BTCUSDT_bidprice[$lastvalkey - 30]);
	 //var_dump($BTCUSDT_changefrmAVG[$sidecount]);

	$pctchangebidprice = round((($bidPrice[$lastvalkey] - $bidPrice[$lastvalkey - 1])/$bidPrice[$lastvalkey -1]),5);	}
	$avgpctchangebidprice = round((($bidPrice[$lastvalkey] -$sobma[0][$sidecount]) / $sobma[0][$sidecount]),5);
	$lastthreevalaverage = simpleAverage(array($bidPrice[$lastvalkey], $bidPrice[$lastvalkey - 1],$bidPrice[$lastvalkey - 2],$bidPrice[$lastvalkey - 3],$bidPrice[$lastvalkey - 4],$bidPrice[$lastvalkey - 5]));
	$STavgpctchangebidprice = round((($bidPrice[$lastvalkey] -$lastthreevalaverage) / $lastthreevalaverage),5);
	if($printEnable ==1){ echo ' <b>', $avgpctchangebidprice, ' ST: '. $STavgpctchangebidprice .'</b> ';}

//$changer_min =36;
//$changer_min = round($maxminDiff * 25000000);
//$changer_min = round(($maxminDiff * 4000),3);
//$changer_min = round(($maxminDiff * 1000),3);
//$changer_min = round(($maxminDiff * 1600000),2);// + abs(round(($maxminDiff * 1600000),8)); //+ .98;

//$sell_min =.2;
//var_dump($changer_min);
//if($avgpctchangebidprice >  0) {$sell_min =0 + ($avgpctchangebidprice *20);} else { $sell_min =0;}

//if($avgpctchangebidprice > 0) { $buy_min =1 + (-1 * ($avgpctchangebidprice *25)); } else { $buy_min = 1;}
//if($avgpctchangebidprice < 0) { $buy_min =1 + abs( ($avgpctchangebidprice *25)); } else { $buy_min = 1;}

//$buy_min = .2;

//this one to change //forces whole line down when neg
/*
	$changer_max =800;$sell_max =2;$buy_max =2;
	
	$changer_diff = $changer_max - $changer_min;$sell_diff = $sell_max - $sell_min;$buy_diff = $buy_max - $buy_min;
	if($sampleSizeRunning > $MaxSampleSize ){  $sampleSizeRunning = $MaxSampleSize; }
	$bigclipdiff = $MaxSampleSize - $sampleSizeStarting;$clip_diff = $sampleSizeRunning - $sampleSizeStarting;$pctratio = ($clip_diff/$bigclipdiff);
	$opt_changer = $changer_min + ($changer_diff * $pctratio) ;$opt_sell = $sell_min + ($sell_diff * $pctratio) ;$opt_buy = $buy_min + ($buy_diff * $pctratio) ;
    */
    

	//$sobma['sell_line'][$sidecount] = $sobma[0][$sidecount] + ($sell_min * ($masd*3)); 
   // $sobma['buy_line'][$sidecount] = $sobma[0][$sidecount] - ($buy_min *($masd*3)); 
	
   //$BTCUSDT_changefrmAVG[$sidecount]
   //$sobma['sell_line'][$sidecount] = $sobma[1][$sidecount] + ($masd* 5) +    ($volAVG[$sidecount]/20000000) -  ($BTCUSDT_changefrmAVG[$sidecount]/9000000 ) ;//-    ($volincreaseonly/200000); 
  // $sobma['buy_line'][$sidecount] = $sobma[1][$sidecount] - ( $masd * 5) -   ($volAVG[$sidecount]/80000000) -  ($BTCUSDT_changefrmAVG[$sidecount]/9000000 )  ;//  -    ($volincreaseonly/400000); ; 
  // $sobma['sell_line'][$sidecount] = $sobma[1][$sidecount] + ($masd* 2.5) +    ($volAVG[$sidecount]/10000000); // -  ($BTCUSDT_changefrmAVG[$sidecount]/30000000 );
   //$sobma['buy_line'][$sidecount] = $sobma[1][$sidecount] - ( $masd * 2.5) + ($volAVG[$sidecount]/20000000);
   $sobma['sell_line'][$sidecount] = $sobma[1][$sidecount] + ($masd* 2.5) ;
   $sobma['buy_line'][$sidecount] = $sobma[1][$sidecount] - ( $masd * 2)- ($volAVG[$sidecount]/30000)-  ($BTCUSDT_changefrmAVG[$sidecount]/1000000 );


$buyselldiff = $sobma['sell_line'][$sidecount] - $sobma['buy_line'][$sidecount];
$buyselllinediffArr[] = $buyselldiff;

/*
	if(($buyselldiff) < ($MKTTOC_askPrice[0] *$opt_changer)) {	//changed	
	$diff = ($MKTTOC_askPrice[0]*$opt_changer) - $buyselldiff; //changed here
	$whattominus = $diff /2;
	
	$sobma['sell_line'][$sidecount] = number_format(($sobma['sell_line'][$sidecount] + $whattominus),8); 
	$sobma['buy_line'][$sidecount] = number_format(($sobma['buy_line'][$sidecount] - $whattominus),8); 
	
	} 
	*/
	

		
		$ordArr[$sidecount]=0;
		
		
	
		
			$ordtype = "11";
    $orderpaddingbuy = $askbiddiff[$lastvalkey] * 1; 
    //if($distFromPriceArr[$sidecount] < .0000001){
if($sobma['buy_line'][$sidecount] > $bidPrice[$lastvalkey] ) {
	$zz =  round((($sobma['buy_line'][$sidecount] - $bidPrice[$lastvalkey] ) / $masd));
	echo '@', $sidecount, ' <b>', $zz, '</b>'; 
	//$zz = abs(( ($bidPrice[$lastvalkey] + $orderpaddingbuy) - $sobma['buy_line'][$sidecount])/$masd);
    $min_ordamnt = (float)round(($ordainOrdAmnt * (($zz + 0 )*5)),8);
    //$min_ordamnt = $ordainOrdAmnt;
	if($cash_bal >= ($bidPrice[$lastvalkey] * $min_ordamnt) ){
		//$pctchangebidprice = (($askPrice[$sidecount] - $askPrice[$sidecount - 1])/$askPrice[$sidecount]);
	
	
	//if( $avgpctchangebidprice <   -0.002 && $STavgpctchangebidprice < .00075 || ($STavgpctchangebidprice <   0 && $avgpctchangebidprice > - .01)  || $avgpctchangebidprice >   .002 && $STavgpctchangebidprice < 0  ) {   //if($avgpctchangebidprice <  ( -.0008 )  ) { //	if($avgpctchangebidprice <=  ( 0 ) || $pctchangebidprice > .00064 ) {  //if($pctchangebidprice <  ( - .0001)) { //  0.00024
	//if in significant downtrend block buys	
		//$ordtype = ""; echo $sidecount,'   ', $min_ordamnt, '<--buyblock--> ',$bidPrice[$lastvalkey], '      ', round($pctchangebidprice, 3), '<BR>';
	//} else {
		$ordtype = "buy";
		$ordArr[$sidecount]= (0 - 1);
        $buyOrders++;
        if( $min_ordamnt < $minimumordsize) {$min_ordamnt = $minimumordsize;}
		$cash_bal = $cash_bal - ($min_ordamnt * ($bidPrice[$lastvalkey] + $orderpaddingbuy));
		$cash_bal = $cash_bal - ((($min_ordamnt * $askPrice[$lastvalkey])) * .002);								//cash bal
		$toc = $toc + ($min_ordamnt);
	//	} //ending bracket for buy block
		
		} else { }
        }
   // }
		
		
		
		

		
			

$orderpaddingsell = $askbiddiff[$lastvalkey] * 0;
//if($distFromPriceArr[$sidecount] > .00000015){
if($sobma['sell_line'][$sidecount] <= ($bidPrice[$lastvalkey] )) {  
	//$zz = abs(( ($bidPrice[$lastvalkey] + $orderpaddingsell) - $sobma['sell_line'][$sidecount])/$masd);
    //$min_ordamnt = (float)round(($ordainOrdAmnt * (($zz + 0)*4)),8);
    $min_ordamnt = 5000 * $ordainOrdAmnt;
	if( $min_ordamnt < $minimumordsize) {$min_ordamnt = $minimumordsize;}
	
	//if(  $pctchangebidprice <   .00035  ) {   //if($pctchangebidprice <   .00001 ||  $pctchangebidprice >   .00048  ) {
		//		$ordtype = ""; echo $sidecount,'   ', $min_ordamnt, '<--sellblock--> ',$bidPrice[$lastvalkey], '      ', round($pctchangebidprice, 3), '<BR>';
		//} else {
			
	if($toc >=  $minimumordsize ){
		//$pctchangebidprice = (($askPrice[$sidecount] - $askPrice[$sidecount - 1])/$askPrice[$sidecount]);
		
		
		$ordtype = "sell";
		
		if($min_ordamnt > $toc) { $min_ordamnt = $toc; }
		if($min_ordamnt <= $toc) {
			$cash_bal = $cash_bal + ($min_ordamnt * ($bidPrice[$lastvalkey] + $orderpaddingsell));
			$cash_bal = $cash_bal - (($min_ordamnt * ($bidPrice[$lastvalkey] + $orderpaddingsell))  * .002);          //cashbal
			$toc = $toc - $min_ordamnt;} 
			
			$ordArr[$sidecount]=1;
       		 $sellOrders++;
		} 
		else{
		if($printEnable ==1){ 	$ordtype = ""; echo $sidecount,'   ', $min_ordamnt, ' Sell fail, no TOC to sell ',$bidPrice[$lastvalkey], '      ', $pctchangebidprice, '<BR>'; }
			}
			
		//}
            }
       // }
			

$finalbal = round(($cash_bal + ($toc * $bidPrice[$lastvalkey]  )), 8);
$finalbalarr[] = $finalbal;


if($printEnable ==1){ 
if(!empty($ordtype)){ 
	if($ordtype == "sell") { 
		echo '<p style="color: red;">', $sidecount, ',,', $ordtype, '---',$min_ordamnt, '... (',$bidPrice[$lastvalkey],')', ($bidPrice[$lastvalkey] +  $orderpaddingsell), '(',$askPrice[$lastvalkey],
		')', ' ---||| ', $cash_bal, '------(', $toc  ,')---', $finalbal , '|||pctchange ',$pctchangebidprice,'|||', 
		//$lastvaluesarr2PctChg ,
        '</p>';
        
	} 
	if($ordtype == "buy"){
		echo '<p style="color: green;">', $sidecount, ',,', $ordtype, '---', $min_ordamnt, '... (',$bidPrice[$lastvalkey],')', ($bidPrice[$lastvalkey] + $orderpaddingbuy), '(',$askPrice[$lastvalkey], 
		')', '---||| ', $cash_bal, '------(', $toc  ,')---', $finalbal  , '|||pctchange ',$pctchangebidprice,'|||',  
		//$lastvaluesarr2PctChg,
        '</p>';
        
	}}
}
		
		
$key++;
$sidecount++;		
$sampSizeArr[] = $sampleSizeRunning; 
$tocArr[] = $toc;
$cashArr[] = $cash_bal;
//if(($thiscalc * -1) == $sampleSizeStarting) { break 1;} 	

}



//echo '<pre>'; print_r($sobma['sell_line']); echo '</pre>';


//$intz = min(array_keys($finalbal));
$netprofit = $finalbal - $starting_cash_bal;//$netprofit = end($finalbal) - $finalbal[$intz];
if ($mainmkt === 'USDT'){ echo '<BR><BR> PROFIT USDT $', number_format($netprofit,2) , ' ( $'.number_format($finalbal,2).' )<BR>'; }
elseif  ($mainmkt === 'BTC') { echo '<BR><BR> PROFIT BTC ', number_format($netprofit,8) , '('.$finalbal.')<BR>'; }  
echo '<BR> <b>BUY ORDERS:</b>',$buyOrders,' <b>SELL ORDERS:',$sellOrders,'</b> <BR>';

$end = microtime(true); $diff = $end - $start; echo $diff,'sec';
echo PHP_EOL;
echo '<BR><BR>',$tokenofChoice, '-', $mainmkt, '<BR><BR>';
?>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['line']});
      google.charts.setOnLoadCallback(drawChart);
    function drawChart() {
      var data = new google.visualization.DataTable();
      data.addColumn('number', '20 SEC INCREMENT');
      data.addColumn('number', 'Starting Bid Price'); 
      data.addColumn('number', 'SELL ABOVE THIS');
      data.addColumn('number', 'BUY BELOW THIS');
	 // data.addColumn('number', 'period MA');
      data.addRows([
	  <?PHP
	  $counterq =0;
	  while($counterq <= $sampleSizeStarting){
		  $counterq++;
	  array_shift($bidPriceFetched);
	  }
	
	  
	  
	//  echo '<pre>';print_r($sobma['sell_line']); echo '<pre>';	 
	 //echo '<pre>';print_r($bidPrice); echo '<pre>';	
		foreach ($bidPriceFetched as  $k => $ind) {
		//$spkey = $k + ($sampleSizeStarting -2);
		//echo $k, ' '; //918 
		
		
		
		//if($k != 0 && $k < 915) {   //TODO: this is cheat method to get google charts to work, trying
		//if($k < 915) {   //TODO: this is cheat method to get google charts to work, trying
		if($k != 0) { 
		$data[] = '['.$k.','.number_format($ind, 8) .',' . $sobma['sell_line'][$k] .','.$sobma['buy_line'][$k].']'; //','.$sobma[0][$k].
		$bp[] = $ind;
		$sl[] = $sobma['sell_line'][$k];
		$bl[] = $sobma['buy_line'][$k];
		}
		
		
		}
		echo implode(",",$data);
		?>
      ]);

      var options = {
        chart: {
          title: <?PHP echo " ' ", "Last ", $Minutes, " Minutes", " ' "; ?>// minutes'//<?PHP echo " ' ", "Last ", $Minutes, " Minutes", " ' "; ?>
          subtitle: 'in (USD)'
        },
        width: 1515,
        height: 450,
		
            colors: ['darkred', 'red', 'green', 'darkgreen', '#610B0B'],

		vAxis: { format:'#,########0.00000000'  }, //vAxis: { format:'#,########0.00000000'  },
        axes: {
          x: {
            0: {side: 'top'}
          }
        }
      };

      var chart = new google.charts.Line(document.getElementById('line_top_x'));

      chart.draw(data, google.charts.Line.convertOptions(options));
    }
  </script>
</head>








  <div id="line_top_x"></div>


<?PHP
/*
echo '<table><tbody><td>';
foreach ($bp as $v){
	echo $v, '<BR>';
}
echo '</td><td>';
foreach ($sl as $v){
	echo $v, '<BR>';
}
echo '</td><td>';
foreach ($bl as $v){
	echo $v, '<BR>';
}
echo '</td></tbody></table>';
*/



$graphsize1 = 1204;
$graphsize2 = 280;



echo '<style>.myClass
{
   background-color:#eee;
   color:#1dd;
}

.myColor
{
   color:#111;
   padding-left: 18px;
}
.myColor1
{
   color:green;
   background-color: white;
}
.myColor2
{
   color:red;
}
.myColor3
{
   color:blue;
   background-color: white;
}
</style>';


/*


echo '<div class="myClass myColor"><BR>distFromPriceArr<HR>';
generateDygraph(0,$graphsize1, $graphsize2,$distFromPriceArr);
echo '</div>';
echo '<div class="myClass myColor"><BR>lines for buy sell diff<HR>';
generateDygraph(0,$graphsize1, $graphsize2,$buyselllinediffArr);
echo '</div>';
echo '<div class="myClass myColor"><BR>ask bid diff<HR>';
generateDygraph(0,$graphsize1, $graphsize2,$askbiddiffArr);
echo '</div>';
*/

echo '<div>';
echo '<div class="myClass myColor"><BR>volumeArr<HR>';
generateDygraph(0,$graphsize1, $graphsize2,$volAVG);//$volumeArr);
echo '</div>';


echo '
<div class="myClass myColor"><BR>buy sell graph<HR>';
generateDygraph(0,$graphsize1, $graphsize2,$ordArr);
echo '</div>';


echo '
<div class="myClass myColor"><BR>running bal<HR>';
generateDygraph(0,$graphsize1, $graphsize2,$finalbalarr);
echo '</div>';





echo '<div class="myClass myColor2"><BR>Cash<HR>';
generateDygraph(0,$graphsize1, $graphsize2,$cashArr);
echo '</div></div>';

/*
echo '<div class="myClass myColor2"><BR>sob<HR>';
generateDygraph(0,$graphsize1, $graphsize2,$sob);
echo '</div>';

echo '<div class="myClass myColor2"><BR>TOC<HR>';
generateDygraph(0,$graphsize1, $graphsize2,$tocArr);
echo '</div></div>';
echo '<div class="myClass myColor2"><BR>maxminDiffArr<HR>';
generateDygraph(0,$graphsize1, $graphsize2,$maxminDiffArr);
echo '</div></div>';
echo '<div class="myClass myColor2"><BR>masd<HR>';
generateDygraph(0,$graphsize1, $graphsize2,$howfarcurrvalfrommaxArr);
echo '</div></div>';
*/
