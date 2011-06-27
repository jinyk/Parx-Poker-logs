<?php
$hostname = 'mysql.BLAH.com';
$username = 'USERNAME';
$password = 'PASSWORD';
$database = 'parxpokerdb';

$db = mysql_connect($hostname, $username, $password);
mysql_select_db($database);

$result = mysql_query("SELECT id, datetime, html FROM purelog WHERE processed < '2010-11-29'", $db);
while($row = mysql_fetch_assoc($result)) {
        echo 'Processing: ' . $row['id'];

        $pattern = '/<tbody>(.*)<\/tbody>/s';
	$html = $row['html'];
        preg_match($pattern, $html, $matches);

	if (count($matches) != 2) {
		mail('jinyoungkim@gmail.com', 'Parx Import Busted', 'It busted yo.');
	} else {
		$arrhtml = explode('</td></tr>', $matches[1]);
        	foreach($arrhtml as $index => $chunk) {
		   $chunk = str_replace('<tr>', '', $chunk);
		   $chunk = str_replace('<td>', '', $chunk);
		   $thisgame = explode('</td>', $chunk);

         	   $limit = trim(str_replace('$', '', $thisgame[0]));
       	           $type = trim($thisgame[1]);
                   $count = trim($thisgame[2]);

	          if ((strlen($limit) > 0) && (strlen($type) > 0) && (intval($count) > 0)) {
               	   $sql = sprintf("INSERT INTO tables (table_datetime, table_limits, table_type, table_count) VALUES ('%s', '%s', '%s', %s)", $row['datetime'], mysql_real_escape_string($limit), mysql_real_escape_string($type), $count);
                   $insert_result = mysql_query($sql, $db);
                   if (!$insert_result) {
                      echo 'Error: ' . mysql_error() . "\nQuery: " . $sql;
                      exit();
                   }
                  }
                }
        $sql = 'UPDATE purelog SET processed = current_timestamp WHERE id = ' . $row['id'];
        $update_result = mysql_query($sql, $db);
        if (!$update_result) {
                echo 'Error: ' . mysql_error() . "\nQuery: " . $sql;
                exit();
        }
   }

}
?>
