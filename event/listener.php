<?php
/**
 * toxyy Moderate While Searching
 *
 * @copyright	(c) 2022 toxyy <thrashtek@yahoo.com>
 * @license		GNU General Public License, version 2 (GPL-2.0)
 */

namespace toxyy\moderatewhilesearching\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	protected $auth;
	protected $cache;
	protected $language;
	protected $template;
	protected $user;
	protected $request;
	protected $phpbb_root_path;
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth				$auth
	 * @param \phpbb\cache					$cache
	 * @param \phpbb\language\language		$language
	 * @param \phpbb\template\template		$template
	 * @param \phpbb\user					$user
	 * @param \phpbb\request\request		$request
	 * @param string						$phpbb_root_path
	 * @param string						$php_ext
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\cache\driver\driver_interface $cache,
		\phpbb\language\language $language,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\request\request $request,
		$phpbb_root_path,
		$php_ext
	)
	{
		$this->auth				= $auth;
		$this->cache			= $cache;
		$this->language			= $language;
		$this->template			= $template;
		$this->user				= $user;
		$this->request			= $request;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $php_ext;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup'									=> 'core_user_setup',
			'core.search_native_by_keyword_modify_search_key'	=> 'search_by_keyword_modify_search_key',
			'core.search_mysql_by_keyword_modify_search_key'	=> 'search_by_keyword_modify_search_key',
			'core.search_native_keywords_count_query_before'	=> 'search_keywords_main_query_before',
			'core.search_mysql_keywords_main_query_before'		=> 'search_keywords_main_query_before',
			'core.search_modify_url_parameters'					=> 'search_modify_url_parameters',
			'core.search_modify_rowset'							=> 'search_modify_rowset',
			'core.search_modify_tpl_ary'						=> 'search_modify_tpl_ary'
		];
	}

	public function core_user_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'toxyy/moderatewhilesearching',
			'lang_set' => 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function search_by_keyword_modify_search_key($event)
	{
		// force sort key to be by topic title
		if($this->request->variable('duplicatetopics', 0))
		{
			$event['type'] = 'posts';
			$event['fields'] = 'titleonly';
			$event['sort_key'] = 'i';
		}
	}

	public function search_keywords_main_query_before($event)
	{
		// add some bits to make the query find duplicates
		if($this->request->variable('duplicatetopics', 0))
		{
			$event['sql_match'] = 't.topic_duplicate_ext';
			$event['search_query'] = '+duplicate +topics';
			$sort_by_sql = $event['sort_by_sql'];
			$sort_by_sql['i'] = "t.topic_title, MAX(p.post_id) AS post_id, COUNT(t.topic_title) AS c";
			$event['sql_match_where'] .= " GROUP BY t.topic_title HAVING c > 1";
			$event['sort_by_sql'] = $sort_by_sql;
		}
	}

	public function search_modify_url_parameters($event)
	{
		$u_search = $event['u_search'];
		$u_extra = '&amp;duplicatetopics=1&amp;sk=t&amp;sd=d&amp;st=0&amp;ch=300&amp;t=0';
		$return = '<br /><br /><a href=' . $u_search . $u_extra . '>' . $this->language->lang('MWS_RETURN') . '</a>';
		$submit = $this->request->variable('mws_submit', '');
		$duplicate_topics = $this->request->variable('duplicatetopics', 0);
		// actually topic ids, just called post_id_list for the mark all button
		$topic_ids = $this->request->variable('post_id_list', [0]);
		$errors = [];

		if ($submit)
		{
			if (confirm_box(true))
			{
				include_once($this->phpbb_root_path . 'includes/functions_admin.' . $this->php_ext);
				$errors = delete_topics('topic_id', $topic_ids);
				// need to clear cache to refresh search results
				$this->auth->acl_clear_prefetch();
				$this->cache->destroy('sql', TOPICS_TABLE);
				trigger_error($this->language->lang('MWS_TOPIC_DELETE_SUCCESS') . $return);
			}
			else
			{
				if (empty($topic_ids))
				{
					trigger_error($this->language->lang('MWS_NO_EXIST') . $return, E_USER_WARNING);
				}

				confirm_box(false, $this->language->lang('MWS_TOPIC_DELETE_CONFIRM'), build_hidden_fields([
					'mws_submit'		=> $submit,
					'duplicatetopics'	=> $duplicate_topics,
					'post_id_list'		=> $topic_ids
				]));

				redirect($u_search);
			}
		}

		$this->template->assign_vars([
			'S_ERRORS'			=> ($errors) ? true : false,
			'ERROR_MSG'			=> implode('<br /><br />', $errors)
		]);
	}

	public function search_modify_rowset($event)
	{
		$rowset = $event['rowset'];
		$duplicate_topics = $this->request->variable('duplicatetopics', 0);

		if ($duplicate_topics)
		{
			$vars = array_fill_keys([
				'S_CAN_SPLIT', 'S_CAN_MERGE', 'S_CAN_DELETE', 'S_CAN_APPROVE',
				'S_CAN_RESTORE', 'S_CAN_LOCK', 'S_CAN_REPORT', 'S_CAN_SYNC'
			], false);

			$has_unapproved_posts = $has_deleted_posts = false;
			// first loop to get proper bools for the real loop
			foreach($rowset as $row)
			{
				if ($row['post_visibility'] == ITEM_UNAPPROVED || $row['post_visibility'] == ITEM_REAPPROVE)
				{
					$has_unapproved_posts = true;
				}
		
				if ($row['post_visibility'] == ITEM_DELETED)
				{
					$has_deleted_posts = true;
				}

				if ($has_unapproved_posts && $has_deleted_posts) break;
			}

			foreach($rowset as $row)
			{
				if (!$vars['S_CAN_SPLIT'])		$vars['S_CAN_SPLIT']	= $this->auth->acl_get('m_split', $row['forum_id']);
				if (!$vars['S_CAN_MERGE'])		$vars['S_CAN_MERGE']	= $this->auth->acl_get('m_merge', $row['forum_id']);
				if (!$vars['S_CAN_DELETE'])		$vars['S_CAN_DELETE']	= $this->auth->acl_get('m_delete', $row['forum_id']);
				if (!$vars['S_CAN_APPROVE'])	$vars['S_CAN_APPROVE']	= ($has_unapproved_posts && $this->auth->acl_get('m_approve', $row['forum_id']));
				if (!$vars['S_CAN_RESTORE'])	$vars['S_CAN_RESTORE']	= ($has_deleted_posts && $this->auth->acl_get('m_approve', $row['forum_id']));
				if (!$vars['S_CAN_LOCK'])		$vars['S_CAN_LOCK']		= $this->auth->acl_get('m_lock', $row['forum_id']);
				if (!$vars['S_CAN_REPORT'])		$vars['S_CAN_REPORT'] 	= $this->auth->acl_get('m_report', $row['forum_id']);
				if (!$vars['S_CAN_SYNC'])		$vars['S_CAN_SYNC']		= $this->auth->acl_get('m_', $row['forum_id']);
			}

			$s_hidden_fields = build_hidden_fields([
				'mws_t'	=> array_column($rowset, 'post_id'),
			]);

			$mode = ($event['show_results'] === 'posts') ? 'post' : 'topic';
			$vars += [
				'FIRST_RESULT_ID'	=> reset($rowset)["{$mode}_id"],
				'LAST_RESULT_ID'	=> end($rowset)["{$mode}_id"],
				'S_CAN_MOD'			=> in_array(true, $vars),
				'S_HIDDEN_FIELDS'	=> $s_hidden_fields
			];

			$this->template->assign_vars($vars);

			if ($vars['S_CAN_MOD']) $this->language->add_lang('mcp');
		}
	}

	public function search_modify_tpl_ary($event)
	{
		$row = $event['row'];
		$tpl_ary = $event['tpl_ary'];
		$mcp_url = append_sid("{$this->phpbb_root_path}mcp.{$this->php_ext}");

		$tpl_ary += [
			'U_POST_DETAILS' => "$mcp_url&amp;i=main&amp;mode=post_details&amp;t=$row[topic_id]&amp;p=$row[post_id]",
		];

		$event['tpl_ary'] = $tpl_ary;
	}
}
