<?php
if ( $_GET["key"] == "4o8kihts832c50holeahh7rpn0" ) {
	$payload = json_decode( $_POST["payload"], true );

	$path = dirname( __FILE__ );
	error_log( "GIT Update: " . exec( "cd " . $path . " && /usr/bin/git pull" ) );
} else {
	header( "HTTP/1.0 404 Not Found" );
	exit();
}
?>