<?php

// This will set up the required configuration for using Flattr from ocPortal

// Only administrators should be able to run this script
if ($GLOBALS['FORUM_DRIVER']->is_super_admin(get_member()))
{
	// We need to make some configuration options for the site //
	require_code('database_action');

	// Salmon, by its very nature, allows interaction with external
	// sites. Administrators may not want this, so we offer an option
	// TODO: Fix the option types to be boolean, and the sections
	//add_config_option($human_name,$name,$type,$eval,$category,$group,$shared_hosting_restricted=0,$data='')
	add_config_option('SALMON_ENABLE_OUT','salmon_enable_out','tick','return \'0\';','FEATURE','NEWS_AND_RSS');
	add_config_option('SALMON_ENABLE_IN','salmon_enable_in','tick','return \'0\';','FEATURE','NEWS_AND_RSS');

	// Do we want to add options for whitelists and blacklists?

	// We need to give users 2 custom profile fields for the 'magic
	// signatures', a public key and a private key
	//install_create_custom_field($name,$length,$locked=1,$viewable=0,$settable=0,$required=0,$description='',$type='long_text',$encrypted=0,$default=NULL)
	$GLOBALS['FORUM_DRIVER']->install_create_custom_field('magic_sig_public_key',100,0,0,0,0,'Public key for verifying Salmon posts.','short_text',0,NULL);
	$GLOBALS['FORUM_DRIVER']->install_create_custom_field('magic_sig_private_key',100,0,0,0,0,'Private key for signing Salmon posts.','short_text',1,NULL);

	echo "Success! Now search for Salmon in your Admin Zone to enable it.";
}
else
{
	echo "Not super-user";
}
