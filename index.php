<?php

	error_reporting(E_ALL);

	session_start();

	include("class.MusicLibrary.php");

	$thelibrary  = new MusicLibrary();
	$libraylist = $thelibrary->decode();

?>
<html>

	<head>
	<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
		<h1>Collection</h1>

		<div id="MusicLibrary">
		
			<?php 
			
				$thelibrary  = new MusicLibrary();
				
				$lijst = $thelibrary->decode();

				if($lijst !== null) {

					$libraylist = usort($lijst, $thelibrary->sortISBN('isbn'));
					
					$music = array();
					
					$i = 0;
						foreach($lijst as $c) {	
							array_push($music,$c);
							$thelibrary->cleanInput($c['title']);
							$i++;
						}
					
					echo '<table border="0" cellpadding="3" cellspacing="5" width="100%">';
					
					echo '<tr><td width="90">Status</td><td>Title</td><td>Artist</td><td>Format</td><td>Price</td></tr>';
					
					$i = count($music)-1;
					
					if($i >= 0) { 
						while($i >= 0) {
							if($music[$i]['status'] == 'Sold') {
								$status_color = 'status-red';
								} else {
								$status_color = 'status-green';
							}
							echo "<tr><td width=\"90\"><div class=".$status_color.">".$music[$i]['status']."</div></td><td><a href=\"\">".$thelibrary->cleanInput($music[$i]['title']).' </a> </td><td> '.$music[$i]['artist']."</td><td>".$music[$i]['format']."</td><td>".$music[$i]['price']."</td></tr>";
						$i--;
						}
					}
					
					echo '</table>';

				} else {
					echo "<p class='book'><em>Library is empty...</em></p>";
				}
			?>
		
		<div id="output">
		</div>
	</body>

</html>
