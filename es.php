<?php
session_start();
require_once('auth/twitteroauth/twitteroauth.php');
require_once('auth/config.php');
include 'includes/tag/classes/wordcloud.class.php';
if($_GET['unfollow'] && $_GET['unfollow']!=''){
$access_token = $_SESSION['access_token'];
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
$connection->post('friendships/destroy', array('screen_name' => $_GET['unfollow']));
echo "<script type=\"text/javascript\">setTimeout(\"window.location = '?'\",250);</script>";
}


//kārtošana
$ord=$_GET['ord'];
$sort=$_GET['sort'];
if($sort=='')$sort='sk';
if($ord=='')$ord='desc';
if($ord=='desc'){$ord0='asc';}else if($ord=='asc'){$ord0='desc';}else{$ord0='asc';}

//Ja nav pieslēdzies, pārsūta uz pieslēgšanās lapu
if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
?>
<h2 style='margin:auto auto; text-align:center;'>Pieslēdzies un apskati savu Twitter ēšanas statistiku!</h2>
<div style='margin:auto auto; width:151px;'>
<br/>
<a href="login"><img src="./auth/images/lighter.png" alt="Sign in with Twitter"/></a>
</div>
<?php
//Ja ir pieslēdzies
}else{
$access_token = $_SESSION['access_token'];
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
$usr = $connection->get('account/verify_credentials');
$draugs = $usr->{'screen_name'};

//Ja nav pieslēdzies, pārsūta uz pieslēgšanās lapu
if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
//Ja ir pieslēdzies
}else{
$access_token = $_SESSION['access_token'];
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
$usr = $connection->get('users/show', array('screen_name' => $draugs));
$vaards = $usr->{'name'};
}
?>
<script>
$(function() {
$("#tabs").tabs({
                fx: { height: 'toggle', opacity: 'toggle'},
                show: function(event, ui) {
                          if (ui.panel.id == "tabs-4") {
                                  $(ui.panel).css("height","100%")
                                initialize()
                                }}
                });
});
</script>
<h2 style='margin:auto auto; text-align:center;'><a href="https://twitter.com/#!/<?php echo $draugs;?>">@<?php echo $draugs;?></a></h2>
<h4 style='margin:auto auto; text-align:center;'><?php echo $vaards;?>
<br/>
<img style="max-height:128px;" src="https://api.twitter.com/1/users/profile_image?screen_name=<?php echo $draugs;?>&size=original"/></h4>
<br/>
<div id="tabs">
<ul>
	<li><a href="#tabs-1">Tvīti</a></li>
	<li><a href="#tabs-2">Kalendārs</a></li>
	<li><a href="#tabs-3">Vārdi</a></li>
	<li><a href="#tabs-4">Karte</a></li>
	<li><a href="#tabs-5">Statistika</a></li>
</ul>
<div id="tabs-1">
<?php
$q = mysql_query("SELECT id, text, created_at FROM tweets where screen_name='$draugs' order by created_at desc");
if (mysql_num_rows($q)){
//visi savāktie konkrētā lietotāja tvīti
$krasa=TRUE;
echo "<table id='results' class='sortable' style='margin:auto auto;border-spacing:0px;border:1px solid white;'>";
echo "<tr>
<th>Tvīts</th>
<th style='width:135px;'>Ēdieni / dzērieni</th>
<th style='width:135px;'>Laiks</th>
</tr>";
$kopskaits = 0;
			while($r=mysql_fetch_array($q)){
				$kopskaits++;
				$tvid = $r["id"];
				$q2 = mysql_query("SELECT distinct nominativs FROM words where tvits='$tvid' and nominativs!='0'");
				if ($krasa==TRUE) {$kr=" class='even'";}else{$kr="";}
				$teksts=$r["text"];
				$laiks=$r["created_at"];
				$laiks=strtotime($laiks);
				$laiks=date("m.d.Y H:i", $laiks);
				echo "<tr".$kr."><td>".$teksts."</td><td>";
				while($r2=mysql_fetch_array($q2)){
					echo $r2["nominativs"].", ";
					if(!isset($ediens1)) $ediens1 = $r2["nominativs"];
					if(!isset($ediens2) && strcmp($ediens1,$r2["nominativs"])!=0) $ediens2 = ', '.$r2["nominativs"];
					if(!isset($ediens3) && strcmp($ediens1,$r2["nominativs"])!=0 && strcmp($ediens2,$r2["nominativs"])!=0) $ediens3 = ', '.$r2["nominativs"];
					if(isset($ediens3) && strcmp($ediens2, $ediens3)==0){$ediens3 = ', '.$r2["nominativs"];}
				};
				echo "</td><td>".$laiks."</td></tr>";
				$krasa=!$krasa;
			}
echo '<div style="text-align:center;"><a href="https://twitter.com/share" class="twitter-share-button" data-url="http://twitediens.tk" data-text="Es pēdējā laikā par ēšanu tvītoju '.$kopskaits.' reizes. Man garšo: '.$ediens1.$ediens2.$ediens3.' - " data-via="edienbots" data-size="large" data-hashtags="TwitEdiens">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div><br/>';
echo "</table>";
}else{
echo '<div style="text-align:center;">Tu vēl neesi tvītojis par ēšanu!<br/>';
echo '<a href="https://twitter.com/share" class="twitter-share-button" data-url="http://lielakeda.lv" data-text="Es pēdējā laikā par ēšanu neesmu tvītojis ):" data-via="edienbots" data-size="large" data-hashtags="TwitEdiens">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div>';
}
?>
<div style="margin:auto auto; text-align:center;" id="pageNavPosition"></div>
<script type="text/javascript"><!--
	var pager = new Pager('results', 10); 
	pager.init(); 
	pager.showPageNav('pager', 'pageNavPosition'); 
	pager.showPage(1);
//--></script>
</div>
<div id="tabs-2">
<?php
//cikos un kādās dienās tvītots
$q = mysql_query("SELECT created_at FROM `tweets` WHERE screen_name = '$draugs'");
?>
<h2 style='margin:auto auto; text-align:center;'>Ēšanas kalendārs</h2>
<br/>
<div style='margin:auto auto;width:500px;'>
<?php
if (mysql_num_rows($q)){
?>
<h3>Cikos tvīto visbiežāk</h3>
<?php
//jādabū visas dienas Mon-Sun...
//šitā ir pirmdiena...sāksim ar to
$theDate = '2011-10-31';
$timeStamp = StrToTime($theDate);
for($zb=0;$zb<7;$zb++) {
$ddd = date('D', $timeStamp); 
$timeStamp = StrToTime('+1 days', $timeStamp);
$dienas[$ddd][skaits]=0;
}
//dabū šodienas datumu
$menesiss = $menesis = date("m");
$dienasz = $diena = date("d");
$gadss = $gads = date("Y");
//izrēķina datumu pirms mēneša
$menesis--;
if($menesis==0){
	$menesis=12;
	$gads--;
}
$max=0;
$maxd=0;
for($zb=0;$zb<24;$zb++) $stundas[$zb][skaits]=0;

while($r=mysql_fetch_array($q)){
	$laiks=$r["created_at"];
	$laiks=strtotime($laiks);
	$diena=date("D", $laiks);
	$laiks=date("G", $laiks);
	$dienas[$diena][skaits]++;
	$stundas[$laiks][skaits]++;
	if($stundas[$laiks][skaits]>$max) $max=$stundas[$laiks][skaits];
	if($dienas[$diena][skaits]>$maxd) $maxd=$dienas[$diena][skaits];
}
//izdrukā populārākās stundas
for($zb=0;$zb<24;$zb++) {
$percent = round($stundas[$zb][skaits]/$max*100);
if ($percent>0){
?>
<script type="text/javascript">
	$(function(){
		$("#progressbar<?php echo $zb;?>").progressbar({
			value: <?php echo $percent;?>
		});		
	});
</script>
<div style=" font: 50% 'Trebuchet MS', sans-serif;" id="progressbar<?php echo $zb;?>"></div>
<div class="sk" style="margin-left:-110px;"><?php echo $zb.":00 - ".($zb+1).":00";?></div>
</br>
<?php
}
}
?>
</div>
<br/>
<h3>Kurās dienās tvīto visbiežāk</h3>
<div style='margin:auto auto;width:500px;'>
<?php
$theDate = '2011-10-31';
$timeStamp = StrToTime($theDate);
//izdrukā populārākās dienas
for($zb=0;$zb<7;$zb++) {
$ddd = date('D', $timeStamp); 
$timeStamp = StrToTime('+1 days', $timeStamp);
$percent = round($dienas[$ddd][skaits]/$maxd*100);
if ($percent>0){
?>
<script type="text/javascript">
	$(function(){
		$("#progressbar<?php echo $ddd;?>").progressbar({
			value: <?php echo $percent;?>
		});		
	});
</script>
<div style=" font: 50% 'Trebuchet MS', sans-serif;" id="progressbar<?php echo $ddd;?>"></div>
<div class="sk"><?php
switch ($ddd) {
    case 'Mon':
        echo "Pirmdien";
        break;
    case 'Tue':
        echo "Otrdien";
        break;
    case 'Wed':
        echo "Trešdien";
        break;
    case 'Thu':
        echo "Ceturtdien";
        break;
    case 'Fri':
        echo "Piektdien";
        break;
    case 'Sat':
        echo "Sestdien";
        break;
    case 'Sun':
        echo "Svētdien";
        break;
}
?></div>
</br>
<?php
}
}
}else{
echo $draugs." vēl nav tvītojis par ēšanu.";
}
?>
</div>
</div>
<div id="tabs-3">
<h2 style='margin:auto auto; text-align:center;'>Pieminētie ēdieni / dzērieni</h2>
<br/>
<?php
$vardi = mysql_query("select nominativs from tweets, words where tweets.screen_name = '$draugs' and words.tvits = tweets.id and nominativs != '0'");
if (mysql_num_rows($vardi)){
$cloud = new wordCloud();
//jāuztaisa vēl, lai, uzklikojot uz kādu ēdienu, atvērtu visus tvītus, kas to pieminējuši...
while($r=mysql_fetch_array($vardi)){
	$nom = $r["nominativs"];
	$cloud->addWord(array('word' => $nom, 'url' => '/vards/'.urlencode($nom)));
}
$cloud->orderBy('size', 'desc');
$myCloud = $cloud->showCloud('array');
foreach ($myCloud as $cloudArray) {
  echo ' &nbsp; <a href="'.$cloudArray['url'].'" class="word size'.$cloudArray['range'].'">'.$cloudArray['word'].'</a> &nbsp;';
}
}else{
echo $draugs." vēl nav pieminējis nevienu ēdienu vai dzērienu.";
}
?>
</div>
<div id="tabs-4">
<h2 style='margin:auto auto; text-align:center;'><?php echo $draugs;?> tvītu karte</h2>
<?php
//Paņem dažādās vietas
?>
		<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
		<script type="text/javascript">
			function initialize() {
				var latlng = new google.maps.LatLng(56.9465363, 24.1048503);
				var settings = {
					zoom: 7,
					center: latlng,
					mapTypeId: google.maps.MapTypeId.ROADMAP};
				var map = new google.maps.Map(document.getElementById("map_canvas"), settings);
<?php
				$i=0;
				$map = mysql_query("SELECT distinct geo, count( * ) skaits FROM `tweets` WHERE geo!='' and screen_name = '$draugs' GROUP BY geo ORDER BY count( * ) DESC");
				while($r=mysql_fetch_array($map)){
				   $vieta=$r["geo"];
				   $skaits=$r["skaits"];
				   if ($skaits==1) {$tviti=" tvīts";} else {$tviti=" tvīti";}
					$irvieta = mysql_query("SELECT * FROM vietas where nosaukums='$vieta'");
					if(mysql_num_rows($irvieta)==0){
						//ja nav tādas vietas datu bāzē,
						//dabū vietas koordinātas
						$string = file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?address=".str_replace(" ", "%20",$vieta)."&sensor=true");
						$json=json_decode($string, true);
						$lat = $json["results"][0]["geometry"]["location"]["lat"];
						$lng = $json["results"][0]["geometry"]["location"]["lng"];
						if ($lat!=0 && $lng!=0){
							$ok = mysql_query("INSERT INTO vietas (nosaukums, lng, lat) VALUES ('$vieta', '$lng', '$lat')");
						}
						}else{
							$arr=mysql_fetch_array($irvieta);
							//ja ir
							$lat = $arr['lat'];
							$lng = $arr['lng'];
						}
					if ($lat & $lng){
					?>
					//Apraksts
					var contentString<?php echo $i;?> = '<?php echo $vieta." - ".$skaits.$tviti." par ēšanas tēmām";?>';
					var infowindow<?php echo $i;?> = new google.maps.InfoWindow({
						content: contentString<?php echo $i;?>
					});
						
					//Atzīmē vietu kartē
					var parkingPos = new google.maps.LatLng(<?php echo $lat;?>, <?php echo $lng;?>);
					var marker<?php echo $i;?> = new google.maps.Marker({
						position: parkingPos,
						map: map,
						title:"<?php echo $vieta;?>"
					});
					google.maps.event.addListener(marker<?php echo $i;?>, 'click', function() {
					  infowindow<?php echo $i;?>.open(map,marker<?php echo $i;?>);
					});
					<?php
					$i=$i+1;
					}
				}
?>
			}
		</script>
		<div id="map_canvas" style="margin:auto auto; width:900px; height:520px"></div>
</div>
<div id="tabs-5">
<h2 style='margin:auto auto; text-align:center;'>pieminētie ēdieni / dzērieni</h2>
<br/>
<?php
//pozitīvie
$kopa = mysql_query("SELECT count( * ) skaits FROM tweets where emo = 1 and screen_name = '$draugs'");
$r=mysql_fetch_array($kopa);
$poz = $r["skaits"];
//negatīvie
$kopa = mysql_query("SELECT count( * ) skaits FROM tweets where emo = 2 and screen_name = '$draugs'");
$r=mysql_fetch_array($kopa);
$neg = $r["skaits"];
//neitrālie
$kopa = mysql_query("SELECT count( * ) skaits FROM tweets where emo = 3 and screen_name = '$draugs'");
$r=mysql_fetch_array($kopa);
$nei = $r["skaits"];
//Tauki, saldumi
$g1 = mysql_query("SELECT count( * ) skaits FROM words, tweets where tweets.screen_name = '$draugs' and words.tvits = tweets.id and grupa = 1");
$r1=mysql_fetch_array($g1);
$g11 = $r1["skaits"];
//Gaļa, olas, zivis
$g2 = mysql_query("SELECT count( * ) skaits FROM words, tweets where tweets.screen_name = '$draugs' and words.tvits = tweets.id and grupa = 2");
$r2=mysql_fetch_array($g2);
$g21 = $r2["skaits"];
//Piena produkti
$g3 = mysql_query("SELECT count( * ) skaits FROM words, tweets where tweets.screen_name = '$draugs' and words.tvits = tweets.id and grupa = 3");
$r3=mysql_fetch_array($g3);
$g31 = $r3["skaits"];
//Dārzeņi
$g4 = mysql_query("SELECT count( * ) skaits FROM words, tweets where tweets.screen_name = '$draugs' and words.tvits = tweets.id and grupa = 4");
$r4=mysql_fetch_array($g4);
$g41 = $r4["skaits"];
//Augļi, ogas
$g5 = mysql_query("SELECT count( * ) skaits FROM words, tweets where tweets.screen_name = '$draugs' and words.tvits = tweets.id and grupa = 5");
$r5=mysql_fetch_array($g5);
$g51 = $r5["skaits"];
//Maize, graudaugu produkti, makaroni, rīsi, biezputras, kartupeļi
$g6 = mysql_query("SELECT count( * ) skaits FROM words, tweets where tweets.screen_name = '$draugs' and words.tvits = tweets.id and grupa = 6");
$r6=mysql_fetch_array($g6);
$g61 = $r6["skaits"];
//Alkoholisks dzēriens
$g7 = mysql_query("SELECT count( * ) skaits FROM words, tweets where tweets.screen_name = '$draugs' and words.tvits = tweets.id and grupa = 7");
$r7=mysql_fetch_array($g7);
$g71 = $r7["skaits"];
//Bezalkoholisks dzēriens
$g8 = mysql_query("SELECT count( * ) skaits FROM words, tweets where tweets.screen_name = '$draugs' and words.tvits = tweets.id and grupa = 8");
$r8=mysql_fetch_array($g8);
$g81 = $r8["skaits"];
?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load('visualization', '1.0', {'packages':['corechart']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
      var data = new google.visualization.DataTable();
      data.addColumn('string', 'Topping');
      data.addColumn('number', 'Slices');
      data.addRows([
        ['Pozitīvi', <?php echo $poz ?>],
        ['Negatīvi', <?php echo $neg ?>],
        ['Neitrāli', <?php echo $nei ?>]]);
      var options = {'title':'Tvītu noskaņojums',
                     'width':485,
                     'height':300,
                     'backgroundColor':'transparent',
                     'is3D':'true'};
      var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
      chart.draw(data, options);}
</script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load('visualization', '1.0', {'packages':['corechart']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
      var data = new google.visualization.DataTable();
      data.addColumn('string', 'Topping');
      data.addColumn('number', 'Slices');
      data.addRows([
        ['Alkoholisks dzēriens', <?php echo $g71; ?>],
        ['Bezalkoholisks dzēriens', <?php echo $g81; ?>]]);
      var options = {'title':'Dzērieni',
                     'width':450,
                     'height':300,
                     'backgroundColor':'transparent',
                     'is3D':'true'};
      var chart = new google.visualization.PieChart(document.getElementById('chart_div1'));
      chart.draw(data, options);}
</script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load('visualization', '1.0', {'packages':['corechart']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
      var data = new google.visualization.DataTable();
      data.addColumn('string', 'Topping');
      data.addColumn('number', 'Slices');
      data.addRows([
        ['Tauki, saldumi', <?php echo $g11; ?>],
        ['Gaļa, olas, zivis', <?php echo $g21; ?>],
        ['Piena produkti', <?php echo $g31; ?>],
        ['Dārzeņi', <?php echo $g41; ?>],
        ['Augļi, ogas', <?php echo $g51; ?>],
        ['Maize, graudaugu produkti, makaroni, rīsi, biezputras, kartupeļi', <?php echo $g61; ?>]]);
      var options = {'title':'Twitter uztura piramīda',
                     'width':450,
                     'height':300,
                     'backgroundColor':'transparent',
                     'is3D':'true'};
      var chart = new google.visualization.PieChart(document.getElementById('chart_div2'));
      chart.draw(data, options);}
</script>
<div style="text-align:center;">
	<div id="chart_div"></div>
	<div style="float:left;" id="chart_div2"></div>
	<div style="float:right;" id="chart_div1"></div>
</div>
<br style="clear:both;"/>
</div>
</div>
<?php } ?>