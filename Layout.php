<?php

class Layout{

	public function printBeforeContent(){
		echo"
		<!DOCTYPE HTML><html><head>
		<META http-equiv=\"Content-type\" content=\"text/html; charset=utf-8\">
		<title>Schemat.nu - Visar schemat snabbt & smidigt.</title>
		<link rel=\"stylesheet\" href=\"css/timeedit.css\" type=\"text/css\">
		<link rel=\"stylesheet\" href=\"css/design.css\" type=\"text/css\">
		<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
		<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no\" />
		<body>
		";
	}

	public function printAfterContent(){
		echo"
		</body>
		</html>";
	}
}


?>