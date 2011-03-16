<?php

class Hook_config_default_salmon_enable_in
{
	/**
	 * Gets the details relating to the config option.
	 *
	 * @return ?array               The details (NULL: disabled)
	 */
	function get_details()
	{
		require_lang('salmon');
		return array(
			'human_name'=>'SALMON_ENABLE_IN',
			'the_type'=>'tick',
			'the_page'=>'FEATURES',
			'section'=>'NEWS_AND_RSS',
			'explanation'=>'CONFIG_OPTION_salmon_enable_in',
			'shared_hosting_restricted'=>'0',
			'c_data'=>'',

			'addon'=>'salmon',
		);
	}

	/**
	 * Gets the default value for the config option.
	 *
	 * @return ?string              The default value (NULL: option is disabled)
	 */
	function get_default()
	{
		return '0';
	}

}
