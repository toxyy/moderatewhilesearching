<?php
/**
 * toxyy Moderate While Searching
 *
 * @copyright	(c) 2022 toxyy <thrashtek@yahoo.com>
 * @license		GNU General Public License, version 2 (GPL-2.0)
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
    'MWS_RETURN'                    => 'Return to search results',
    'MWS_TOPIC_DELETE_CONFIRM'      => 'Are you sure you want to delete these topics?',
    'MWS_TOPIC_DELETE_SUCCESS'      => 'Selected topics successfully deleted.',
	'MWS_NO_EXIST'				    => 'There are no selected items, please go back and select something before submitting.',
]);
