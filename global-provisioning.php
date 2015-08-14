<?php
//This file will set global variables in the automatic provisioning
//So these variables are set no matter which template you choose
//It also allows us to use some internal variables in the template thing
//Require this file on line 324 of /var/www/html/admin/modules/xepm/provisioners/yealink.php
//(Inside the generate function)
//Like so:
//require( __DIR__ . '/../../../../hacks/global_provisioning.php' );
//
//This is only for Yealink phones, but you could easily adapt it for other brands


//These are variables that will be set for every template.
//Yealink auto-provisioning guide: http://www.yealink.com/Upload/T2X/2014102/Yealink_SIP-T2_Series_T19P_T4_Series_IP_Phones_Auto_Provisioning_Guide_V72_1.pdf
$global_provisioning = array(
	//Tell the phone where it can get new configs on startup, even if it's not on a network with DHCP Option 66 set
	'auto_provision.server.url' => 'http://url/xepm-provision/',
	'autoprovision.1.url' => 'http://url/xepm-provision/',
	//Disable the super-annoying tones every few minutes if you have a voicemail
	'features.voice_mail_tone_enable' => 'disable',
	'voice.tone.stutter' => 0,
	//Auto-generate a company directory
	'remote_phonebook.data.1.url' => 'http://url/hacks/directory/directory.php',
	'remote_phonebook.data.1.name' => 'Directory',
	'directory_setting.url' => 'http://url/hacks/directory/favorite_setting.xml',
	'super_search.url' => 'http://url/hacks/directory/favorite_setting.xml',
	'features.remote_phonebook.enable' => 1,
	//Voicemail access number
	'voice_mail.number.1' =>  '*97',
);

$settings = array_merge( $settings, $global_provisioning );


$variables = array();

//Variables we can use in the admin, like so: %account.1.account%
foreach ( $this->extensions() as $extension ) {
	$index = $extension->extension_index + 1;
	$variables[ 'account.' . $index . '.account' ] = $extension->account;
}

//Replace any variables with the value
foreach( $settings as $key => $value ) {
	foreach( $variables as $find => $replace ) {
		$settings[ $key ] = str_replace( '%' . $find . '%', $replace, $value );
	}
}
