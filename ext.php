<?php
/**
 * toxyy Moderate While Searching
 *
 * @copyright	(c) 2022 toxyy <thrashtek@yahoo.com>
 * @license		GNU General Public License, version 2 (GPL-2.0)
 */

namespace toxyy\moderatewhilesearching;

use phpbb\extension\base;

class ext extends base
{
	/**
	 * phpBB >=3.2.x and PHP 7+
	 */
	public function is_enableable()
	{
		$config = $this->container->get('config');

		$is_enableable = (phpbb_version_compare($config['version'], '3.2', '>=') && version_compare(PHP_VERSION, '7.1.3', '>='));

		return $is_enableable;
	}
}
