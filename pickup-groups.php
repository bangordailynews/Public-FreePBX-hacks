<?php
//A graphical interface for seeing which extensions are in which call groups/pickup groups.

/*
A call group is a group of extensions that can be answered remotely.

A pickup group is a group of extensions that can answer a call group remotely.

These options are set under an extension's settings. Both values are numeric, with a maximum value of 60.

So, for example: Extensions a, b, c and d are in call group one. Extension d is in pickup group 1. Extension d can pickup calls when extensions a, b, c and d are ringing by lifting the handset and pressing *8, but extensions a, b and c can not pickup calls to extension d. If extensions a, b and c are added to pickup group 1, everybody can pick up everybody else's calls.
*/

$groups = array();
$extensions = array();
$lines = explode( "\n", file_get_contents( '/etc/asterisk/sip_additional.conf' ) );

foreach( $lines as $line ) {
	if( substr( $line[ 0 ], 0, 1 ) == '[' ) {
		$extension = $line;
		continue;
	}
	
	if( strpos( trim( $line ), 'callgroup=' ) === 0 && trim( $line ) != 'callgroup=' ) {
		$callgroups = explode( ',', str_replace( 'callgroup=', '', trim( $line ) ) );
		foreach( $callgroups as $group ) {
			$groups[ $group ][ 'callgroup' ][] = $extension;
		}
	}
	
	if( strpos( trim( $line ), 'pickupgroup=' ) === 0 && trim( $line ) != 'pickupgroup=' ) {
		$callgroups = explode( ',', str_replace( 'pickupgroup=', '', trim( $line ) ) );
		foreach( $callgroups as $group ) {
			$groups[ $group ][ 'pickupgroup' ][] = $extension;
		}
	}
	
	if( strpos( trim( $line ), 'callerid=' ) === 0 && trim( $line ) != 'callerid=' ) {
		$extensions[ $extension ] = str_replace( 'callerid=', '', trim( $line ) );
	}
}

foreach( $groups as $group => $types ) {
	
	echo '<h1>Group ' . $group . '</h1>';
	echo '<h4>Following users in group</h4><ul>';
	foreach( $types[ 'callgroup' ] as $extension ) {
		echo '<li>' . $extensions[ $extension ] . '</li>';
	}
	echo '</ul><h4>Following users can pick up group</h4><ul>';
	foreach( $types[ 'pickupgroup' ] as $extension ) {
		echo '<li>' . $extensions[ $extension ] . '</li>';
	}
	echo '</ul>';
	
}
