<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2010

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license		Public Domain
 * @copyright	Chris Warburton
 * @package		salmon
 */

class Hook_symbol_SALMON_ENABLED
{

	/**
	 * Standard modular run function for symbol hooks. Searches for tasks to perform.
    *
    * @param  array		Symbol parameters
    * @return string		Result
	 */
	function run($param)
	{
		if (array_key_exists(0,$param) && array_key_exists(1,$param))
		{
			$type = $param[0];
			$id = $param[1];

			// TODO: Make this actually test something
			return '_true';
		}

		return '_false';
	}

}
