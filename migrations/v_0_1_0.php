<?php
/**
 * toxyy Moderate While Searching
 *
 * @copyright	(c) 2022 toxyy <thrashtek@yahoo.com>
 * @license		GNU General Public License, version 2 (GPL-2.0)
 */

namespace toxyy\moderatewhilesearching\migrations;

class v_0_1_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['mws_ext_version']);
	}

	public function update_data()
	{
		return [
			// Add configs
			['config.add', ['mws_ext_version', '0.010']],
		];
	}

	public function update_schema()
	{
		return [
			'add_columns'   => [
				$this->table_prefix . 'topics'   => [
					'topic_duplicate_ext'  => ['VCHAR:20', 'duplicate topics'],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns'  => [
				$this->table_prefix . 'topics'   => [
					'topic_duplicate_ext',
				],
			],
		];
	}
}
