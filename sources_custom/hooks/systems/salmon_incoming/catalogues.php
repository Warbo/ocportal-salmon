<?php 

class Hook_catalogues
{
	
	/**
	 * Takes Salmon values from POST and handles their assimilation into the
	 * site.
	 */
	function handle_salmon($id)
	{
		// Find out what the type our feedback should be for
		require_code('hooks/systems/content_meta_aware/catalogue_entry');
		$cma = object_factory('Hook_content_meta_aware_catalogue_entry');
		$info = $cma->info();

		$db_id = $info['id_field_numeric']? intval($id) : $id;
		$_title = $GLOBALS['SITE_DB']->query_value_null_ok($info['table'],$info['title_field'],array($info['id_field']=>$db_id));
		if (is_null($_title))
		{
			warn_exit('');
		}
		$title = $info['title_field_dereference']? get_translated_text($_title) : $_title;

		require_code('urls');
		list($zone, $attributes, $_) = page_link_decode(str_replace('_WILD',$id,$info['view_pagelink_pattern']));
		$url = build_url($attributes,$zone)->evaluate();

		require_code('salmon/salmon');
		parse_salmon_post($info['feedback_type_code'], filter_naughty($id), $url, $title);
	}
	
	/**
	 * Whether we accept Salmon entries; replies to these posts from downstream
	 * consumers and aggregators.
	 *
	 * @return boolean		Whether a Salmon link should be made available for this feed.
	 */
	function salmon_enabled()
	{
		return true;
	}

}


