<?php
/**
 * toxyy Moderate While Searching
 *
 * @copyright	(c) 2022 toxyy <thrashtek@yahoo.com>
 * @license		GNU General Public License, version 2 (GPL-2.0)
 */

namespace toxyy\moderatewhilesearching\tests\functional;

/**
 * @group functional
 */
class mcf_functional_test extends \phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return ['toxyy/moderatewhilesearching'];
	}

	protected function setUp(): void
	{
		echo parent::setUp();
		$this->add_lang_ext('toxyy/moderatewhilesearching', 'info_acp_moderatewhilesearching');
		$this->login();
		$this->admin_login();
	}

	public function test_edit_forum_installed()
	{
		$crawler = self::request('GET', "adm/index.php?i=acp_forums&icat=7&mode=manage&parent_id=0&f=2&action=edit&sid=={$this->sid}");
		$this->assertStringContainsString($this->lang('ACP_MCF'), $crawler->filter('.mcf_label')->text());
	}
}
