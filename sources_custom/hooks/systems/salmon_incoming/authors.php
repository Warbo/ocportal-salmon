<?php 

class Hook_authors
{

	/**
	 * Takes Salmon values from POST and handles their assimilation into the
	 * site.
	 */
	function handle_salmon($id)
	{
		
	}
	
	/**
	 * Whether we accept Salmon entries; replies to these posts from downstream
	 * consumers and aggregators.
	 *
	 * @return boolean		Whether a Salmon link should be made available for this feed.
	 */
	function salmon_enabled()
	{
		return false;
	}

}


