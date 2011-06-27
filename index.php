<?php
$hostname = 'mysql.BLAH.com';
$username = 'USERNAME';
$password = 'PASSWORD';
$database = 'parxpokerdb';

$arrGames = array();
$arrGames['nl12'] = array('long' => "1-2 No Limit Hold'em", 'short' => '1-2 NL');
$arrGames['nl25'] = array('long' => "2-5 No Limit Hold'em", 'short' => '2-5 NL'); 
$arrGames['nl510'] = array('long' => "5-10 No Limit Hold'em", 'short' => '5-10 NL'); 
$arrGames['l48'] = array('long' => "4-8 Limit Hold'em", 'short' => '4-8 L'); 
$arrGames['l816'] = array('long' => "8-16 Limit Hold'em", 'short' => '8-16 L'); 
$arrGames['l1530'] = array('long' => "15-30 Limit Hold'em", 'short' => '15-30 L'); 
$arrGames['plo12'] = array('long' => "1-2 Pot Limit Omaha", 'short' => '1-2 PLO'); 
$arrGames['ohl48'] = array('long' => "4-8 Omaha HI/LO", 'short' => '4-8 OHL'); 

$db = mysql_connect($hostname, $username, $password);
mysql_select_db($database);

if (isset($_POST['game'])) {
	$game = $_POST['game'];
} elseif (isset($_GET['game'])) {
	$game = $_GET['game'];
} else {
	$game = 'all';
}


$result = mysql_query("SELECT CONVERT_TZ(table_datetime, '-08:00', '-05:00') AS table_datetime, table_count, CONCAT(table_limits, ' ', table_type) AS table_game FROM tables ORDER BY table_datetime", $db);

$arrData = array();
while($row = mysql_fetch_row($result)) {
	$arrdate = date_parse($row[0]);
	$datekey = $arrdate['year'] . ', ' . ($arrdate['month'] - 1) . ', ' . $arrdate['day'] . ', ' . $arrdate['hour'] . ', ' . $arrdate['minute'];
	
	$datetimePoint = array();
	if (array_key_exists($datekey, $arrData)) {
		$datetimePoint = $arrData[$datekey];
	}
	
	$datetimePoint[$row[2]] = $row[1];
	
	$arrData[$datekey] = $datetimePoint;
}
mysql_close($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>PARX Poker Table Log - Timeline - Game: <?php echo $game ?></title>
	<script type="text/javascript" src="https://www.google.com/jsapi?key=YOUROWNKEY"></script>
	<script type='text/javascript'>
		google.load('visualization', '1', {'packages':['annotatedtimeline']});
		google.setOnLoadCallback(drawChart);

		function drawChart() {
			var data = new google.visualization.DataTable();
			data.addColumn('datetime', 'Date');
			<?php
			if ($game == 'all') {
				foreach ($arrGames as $curGameKey => $curGame) {
					echo "data.addColumn('number', '" . $curGame['short'] . "');\n";
				}
			} else {
				echo "data.addColumn('number', '" . $arrGames[$game]['short'] . "');\n";
			}
			?>
			data.addRows([
				<?php
				$count = count($arrData);
				for ($i = 0; $i < $count; $i++) {
					$keys = array_keys($arrData);
					$value = $arrData[$keys[$i]];
					
					echo '[new Date(' . $keys[$i] . '), ';
					if ($game == 'all') {
						$arrPrint = array();
						foreach ($arrGames as $curGameKey => $curGame) {
							if (array_key_exists($curGame['long'], $value)) {
								$arrPrint[] = $value[$curGame['long']];
							} else {
								$arrPrint[] = "0";
							}
						}
						echo implode(", ", $arrPrint);
					} else {
						if (array_key_exists($arrGames[$game]['long'], $value)) {
							echo $value[$arrGames[$game]['long']];
						} else {
							echo "0";
						}
					}

					if ($i < ($count - 1)) {
						echo "],\n";
					} else {
						echo "]\n";
					}
				}
				?>
			]);

			var chart = new google.visualization.AnnotatedTimeLine(document.getElementById('chart_div'));
			chart.draw(data, {displayAnnotations: false});
		}
	</script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$("#game").change(function() {
				$("#formgame").submit();
			});
		});
	</script>
	<script type="text/javascript">
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', 'YOUROWNCODE']);
		_gaq.push(['_trackPageview']);

		(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();
	</script>
</head>
<body>
<h1>PARX poker table logs</h1>
There's a casino in Philadelphia called <a href="http://www.parxcasino.com">Parx Casino</a>. They have a poker room, this is data about the games there.
<div style="width: 980px">
	<!-- div style="float:left">
		[view timeline | <a href="index.php">raw logs</a>]
	</div -->
	<div style="float:right">
		<form id="formgame" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="GET">
			<select id="game" name="game">
				<option value="all"<?php echo ($game == 'all') ? 'selected' : '' ?>>all games</option>
				<?php
				foreach ($arrGames as $curGameKey => $curGame) {
					echo '<option value="' . $curGameKey . '"';
					if ($game == $curGameKey) {
						echo ' selected';
					}
					echo '>' . $curGame['long'] . '</option>';
				}
				?>
			</select>
		</form>
	</div>
	<div style="clear:both" />
</div>
<div id='chart_div' style='width: 980px; height: 500px;'></div>

<h3>Types of games spread, and data points captured</h3>
<table border=1>
<tr>
<td bgcolor=silver class='medium'>table_type</td>
<td bgcolor=silver class='medium'>table_limits</td>
<td bgcolor=silver class='medium'>count(1)</td>
</tr>

<tr>
<td class='normal' valign='top'>No Limit Hold&#39;em</td>
<td class='normal' valign='top'>1-2</td>
<td class='normal' valign='top'>4338</td>
</tr>

<tr>
<td class='normal' valign='top'>No Limit Hold&#39;em</td>
<td class='normal' valign='top'>2-5</td>
<td class='normal' valign='top'>4038</td>
</tr>

<tr>
<td class='normal' valign='top'>Limit Hold&#39;em</td>
<td class='normal' valign='top'>4-8</td>
<td class='normal' valign='top'>2780</td>
</tr>

<tr>
<td class='normal' valign='top'>No Limit Hold&#39;em</td>
<td class='normal' valign='top'>5-10</td>
<td class='normal' valign='top'>2487</td>
</tr>

<tr>
<td class='normal' valign='top'>Limit Hold&#39;em</td>
<td class='normal' valign='top'>15-30</td>
<td class='normal' valign='top'>2065</td>
</tr>

<tr>
<td class='normal' valign='top'>Omaha HI/LO</td>
<td class='normal' valign='top'>4-8</td>
<td class='normal' valign='top'>1608</td>
</tr>

<tr>
<td class='normal' valign='top'>Limit Hold&#39;em</td>
<td class='normal' valign='top'>8-16</td>
<td class='normal' valign='top'>1089</td>
</tr>

<tr>
<td class='normal' valign='top'>O/E</td>
<td class='normal' valign='top'>4-8</td>
<td class='normal' valign='top'>445</td>
</tr>

<tr>
<td class='normal' valign='top'>Pot Limit Omaha</td>
<td class='normal' valign='top'>1-2</td>
<td class='normal' valign='top'>370</td>
</tr>

<tr>
<td class='normal' valign='top'>No Limit Hold&#39;em</td>
<td class='normal' valign='top'>10-25</td>
<td class='normal' valign='top'>103</td>
</tr>

<tr>
<td class='normal' valign='top'>Limit Hold&#39;em</td>
<td class='normal' valign='top'>30-60</td>
<td class='normal' valign='top'>85</td>
</tr>

<tr>
<td class='normal' valign='top'>H.O.S.E.</td>
<td class='normal' valign='top'>8-16</td>
<td class='normal' valign='top'>38</td>
</tr>

<tr>
<td class='normal' valign='top'>Limit Hold&#39;em</td>
<td class='normal' valign='top'>20-40</td>
<td class='normal' valign='top'>30</td>
</tr>

<tr>
<td class='normal' valign='top'>O/E</td>
<td class='normal' valign='top'>6-12</td>
<td class='normal' valign='top'>17</td>
</tr>

<tr>
<td class='normal' valign='top'>Pot Limit Omaha</td>
<td class='normal' valign='top'>2-5</td>
<td class='normal' valign='top'>12</td>
</tr>

<tr>
<td class='normal' valign='top'>NLH/PLO MIx</td>
<td class='normal' valign='top'>2-5</td>
<td class='normal' valign='top'>10</td>
</tr>

<tr>
<td class='normal' valign='top'>Limit Hold&#39;em</td>
<td class='normal' valign='top'>1-2</td>
<td class='normal' valign='top'>5</td>
</tr>

<tr>
<td class='normal' valign='top'>Limit Hold&#39;em</td>
<td class='normal' valign='top'>3-6</td>
<td class='normal' valign='top'>4</td>
</tr>

<tr>
<td class='normal' valign='top'>Stud/ 5-10NL</td>
<td class='normal' valign='top'>30-60</td>
<td class='normal' valign='top'>4</td>
</tr>

<tr>
<td class='normal' valign='top'>Limit Hold&#39;em</td>
<td class='normal' valign='top'>5-10</td>
<td class='normal' valign='top'>2</td>
</tr>

<tr>
<td class='normal' valign='top'>No Limit Hold&#39;em</td>
<td class='normal' valign='top'>4-8</td>
<td class='normal' valign='top'>2</td>
</tr>

<tr>
<td class='normal' valign='top'>No Limit Hold&#39;em</td>
<td class='normal' valign='top'>0-0</td>
<td class='normal' valign='top'>1</td>
</tr>

<tr>
<td class='normal' valign='top'>Omaha HI</td>
<td class='normal' valign='top'>1-2</td>
<td class='normal' valign='top'>1</td>
</tr>
</table>


<div id="disqus_thread"></div>
<script type="text/javascript">
    /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
    var disqus_shortname = 'parxpokerlog'; // required: replace example with your forum shortname

    // The following are highly recommended additional parameters. Remove the slashes in front to use.
    var disqus_identifier = 'parxpokerlog_timeline';
    var disqus_url = 'http://www.YOUROWN';

    /* * * DON'T EDIT BELOW THIS LINE * * */
    (function() {
        var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
        dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
        (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
    })();
</script>
<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
<a href="http://disqus.com" class="dsq-brlink">blog comments powered by <span class="logo-disqus">Disqus</span></a>
</body>
