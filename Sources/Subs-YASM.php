<?php
/**********************************************************************************
* Subs-YASM.php
***********************************************************************************
* This mod is licensed under the 2-clause BSD License, which can be found here:
*	http://opensource.org/licenses/BSD-2-Clause
***********************************************************************************
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
**********************************************************************************/
if (!defined('SMF'))
	die('Hacking attempt...');

/**********************************************************************************
/* Hooks that deal with mod setup and user deletion:
**********************************************************************************/
function YASM_Load()
{
	global $context, $settings;

	// Get our language strings!
	loadLanguage('YASM');

	// Are we running SMF 2.1 or 2.0.x?  Makes a difference, ya know.... :p
	if (function_exists('loadCSSFile'))
	{
		// SMF 2.1: Include our CSS and JS in the header....
		loadJavaScriptFile('YASM.js', array(), 'YASM');
		loadCSSFile('YASM.css', array(), 'YASM');
	}
	else
	{
		// SMF 2.0: Include our CSS and JS in the header....
		$context['html_headers'] .= '
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/YASM.js?fin20"></script>
		<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/YASM.css" />';
	}
}

function YASM_Actions(&$is_actions)
{
	$is_actions['YASM_log'] = array('Subs-YASM.php', 'YASM_Add_Member');
}

function YASM_Delete_user($id_member)
{
	YASM_Delete_Spoilers($id_members);
}

/**********************************************************************************
/* Functions that handle BBCode declarations and buttons:
**********************************************************************************/
function YASM_tags()
{
	return array('spoiler', 'changelog', 'offtopic');
}

function YASM_BBCode(&$bbc)
{
	global $txt, $modSettings, $user_info;

	// Gather up all the block-level bbcode tags for later:
	$disallowed = array();
	foreach ($bbc as $code)
		if (!empty($code['block_level']))
			$disallowed[] = $code['tag'];
	$disallowed = array_unique($disallowed);

	// Setup everything else we need for these bbcodes:
	$parameters = array(
		'text' => array('optional' => true),
		'quote' => array('optional' => true, 'quoted' => true),
		'show' => array('optional' => true),
		'hide' => array('optional' => true),
		'guests' => array('optional' => true, 'match' => '(y|yes|true|n|no|false)'),
		'expand' => array('optional' => true, 'match' => '(y|yes|true|n|no|false)'),
		'log' => array('optional' => true, 'match' => '(y|yes|true|n|no|false)'),
		'log_id' => array('optional' => true, 'match' => '(\d+)'),
	);
	$content_param = '{' . implode('}|{', array_keys($parameters)) . '}';
	$content_empty = str_repeat('|', count($parameters) - 1);

	// Define all of the tags here:
	foreach (YASM_tags() as $tag)
	{
		$bbc[] = array(
			'tag' => $tag,
			'type' => 'unparsed_content',
			'parameters' => $parameters,
			'content' => $content_param,
			'validate' => 'YASM_Validate',
			'disallow_children' => $disallowed,
			'block-level' => true,
		);
		$bbc[] = array(
			'tag' => $tag,
			'type' => 'unparsed_content',
			'content' => $content_empty,
			'validate' => 'YASM_Validate',
			'disallow_children' => $disallowed,
			'block-level' => true,
		);
		$bbc[] = array(
			'tag' => $tag,
			'type' => 'unparsed_equals_content',
			'content' => $content_empty,
			'validate' => 'YASM_Validate_Equals',
			'disallow_children' => $disallowed,
			'block-level' => true,
		);
	}
}

function YASM_Buttons(&$buttons)
{
	global $txt;

	foreach (YASM_tags() as $tag)
	{
		$buttons[count($buttons) - 1][] = array(
			'image' => $tag,
			'code' => $tag,
			'description' => $txt[$tag],
			'before' => '[' . $tag . ']',
			'after' => '[/' . $tag . ']',
		);
	}
}

/**********************************************************************************
/* BBCode Validation functions:
**********************************************************************************/
function YASM_Validate_Equals(&$tag, &$data, &$disabled)
{
	$tag['content'] = $data[1] . $tag['content'];
	YASM_Validate($tag, $data[0], $disabled);
}

function YASM_Validate(&$tag, &$data, &$disabled)
{
	global $txt, $user_info, $modSettings, $smcFunc, $context, $scripturl;

	// Are the contents empty?  Then don't show anything to the user:
	if (empty($data))
		return ($tag['content'] = '');

	// Parse the parameters given to us by the poster:
	$name = &$tag['tag'];
	list($text, $quote, $show, $hide, $guests, $expand,, $log_id) = explode('|', $tag['content']);

	// If guests aren't allowed to view, then tell the user to login or register:
	if ((!empty($modSettings['YASM_' . $name . '_no_guests']) || $guests == 'n' || $guests == 'no' || $guests == 'false') && !empty($user_info['is_guest']))
	{
		$search = array('{script_url}', '{tag_name}');
		$replace = array($scripturl, $txt[$name]);
		$message = str_replace($search, $replace, $txt['YASM_strings']['no_guest_html']);
	}
	// Otherwise.....
	else
	{
		// Parse the parameters given to us:
		$quote = $quote;
		$text = empty($text) ? (empty($modSettings['YASM_' . $name . '_text']) ? $txt[$name] : stripslashes($modSettings['YASM_' . $name . '_text'])) : $text;
		$show = empty($show) ? (empty($modSettings['YASM_' . $name . '_show']) ? $txt['YASM_show'] : stripslashes($modSettings['YASM_' . $name . '_show'])) : $show;
		$hide = empty($hide) ? (empty($modSettings['YASM_' . $name . '_hide']) ? $txt['YASM_hide'] : stripslashes($modSettings['YASM_' . $name . '_hide'])) : $hide;
		$expand = empty($expand) ? !empty($modSettings['YASM_' . $name . '_expanded']) : ($expand == 'y' || $expand == 'yes' || $expand == 'true');
		$show_log = $log_id && !empty($modSettings['YASM_' . $name . '_show_log']);

		// Are we allowed to log users who viewed the contents?
		$limit = !empty($modSettings['YASM_' . $name . '_limit_log']) ? $modSettings['YASM_' . $name . '_limit_log'] : 0;
		if (empty($log_id))
			$context['YASM_users'] = array();
		elseif (($context['YASM_users'] = cache_get_data('YASM_viewed_log_' . $log_id, 86400)) == NULL)
			YASM_Get_Members($log_id, $limit);
		$count = isset($context['YASM_users']) ? count($context['YASM_users']) : 0;
		$viewed = sprintf($txt[$limit <= $count ? 'YASM_viewed_by' : 'YASM_last_viewed'], min(max(1, $limit), $count));

		// If expanded and not logged, then log the user and add to the list:
		if ($log_id && $expand && !isset($context['YASM_users'][$user_info['id']]))
		{
			YASM_Add_Member($log_id, $user_info['id'], false);
			if ($show_log)
			{
				// Move user to front of the list, as user is most recent to view:
				YASM_Get_Members($log_id, 0, $user_info['id']);
				$tmp = $context['YASM_users'][$user_info['id']];
				unset($context['YASM_users'][$user_info['id']]);
				$context['YASM_users'] = array_shift($context['YASM_users']);
				array_splice($context['YASM_users'], 0, 0, $tmp);
			}
			$log_id = 0;
		}

		// We need to parse the contents so it is readable for the user:
		$data = parse_bbc($data);
	}

	// Build the string we are going to show the user.  The Easy-To-Read edition! :p
	$style = 'YASM_' . (empty($modSettings['YASM_' . $name . '_style']) ? 'original' : $modSettings['YASM_' . $name . '_style']);
	$tag['content'] =
		'<div class="' . $style . '">' .
			'<div class="YASM_inner' . (empty($message) && empty($modSettings['YASM_' . $name . '_no_border']) ? ' YASM_border' : '') . '">' .
				'<span class="YASM_text">' . (!empty($message) ? $message : $text .
				(!empty($quote) ? '<span class="YASM_quote">'. $quote . '</span> ' : '') . '</span><span class="YASM_colon">:</span> ' .
				'<span class="YASM_links" onClick="YASM_toggle(this, ' . ((int) $log_id) . '); return false;" />' .
					'<a href="#" onClick="return false;"' . ($expand ? ' style="display:none;"' : '') . '>' . $show . '</a>' .
					'<a href="#" onClick="return false;"' . (!$expand ? ' style="display:none;"' : '') . '>' . $hide . '</a>') .
				'</span>' .
			'</div>' . (empty($message) ?
			'<div class="YASM_content quotecontent">' .
				'<div'. ($expand ? '' : ' style="display: none;"') . '>' .
					'$1' . (!empty($count) && $show_log ?
					'<hr class="clear" />' .
					'<span class="YASM_log">' . $viewed . ': <span class="YASM_users">' . implode(', ', $context['YASM_users']) . '</span></span>' : '') .
				'</div>' .
			'</div>' : '') .
		'</div>';

	// Let's free up the context entry for users viewed:
	unset($context['YASM_users']);
}

/**********************************************************************************
/* YASM BBCode admin settings function:
**********************************************************************************/
function YASM_Admin(&$areas)
{
	$areas['config']['areas']['modsettings']['subsections']['YASM'] = array('YASM');
}

function YASM_Modify(&$is_actions)
{
	$is_actions['YASM'] = 'YASM_Settings';
}

function YASM_Settings($return_config = false)
{
	global $txt, $scripturl, $context, $settings, $sc, $modSettings;

	// Now, the settings....
	foreach (YASM_tags() as $tag)
	{
		$config_vars[] = array('title', $tag);
		$config_vars[] = array('select', 'YASM_' . $tag . '_style', $txt['YASM_styles']);
		$config_vars[] = array('text', 'YASM_' . $tag .'_text', 40);
		$config_vars[] = array('text', 'YASM_' . $tag .'_show', 40);
		$config_vars[] = array('text', 'YASM_' . $tag .'_hide', 40);
		$config_vars[] = array('check', 'YASM_' . $tag . '_expanded');
		$config_vars[] = array('check', 'YASM_' . $tag . '_no_guests');
		$config_vars[] = array('check', 'YASM_' . $tag . '_no_border');
		$config_vars[] = array('check', 'YASM_' . $tag . '_show_log');
		$config_vars[] = array('int', 'YASM_' . $tag . '_limit_log');
	}

	if ($return_config)
		return $config_vars;

	$context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=YASM';
	$context['settings_title'] = $txt['YASM_title'];

	// Saving?
	if (isset($_GET['save']))
	{
		checkSession();
		foreach (YASM_tags() as $tag)
		{
			$_POST['YASM_' . $tag . '_text'] = isset($_POST['YASM_' . $tag . '_text']) ? addslashes($_POST['YASM_' . $tag . '_text']) : '';
			$_POST['YASM_' . $tag . '_show'] = isset($_POST['YASM_' . $tag . '_show']) ? addslashes($_POST['YASM_' . $tag . '_show']) : '';
			$_POST['YASM_' . $tag . '_hide'] = isset($_POST['YASM_' . $tag . '_hide']) ? addslashes($_POST['YASM_' . $tag . '_hide']) : '';
		}
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=modsettings;sa=YASM');
	}

	// Prepare the strings to be seen by the user for modification:
	$search = array('{show}', '{hide}', '{tag_name}');
	$replace = array($txt['YASM_show'], $txt['YASM_hide'], '{tag_name}');
	foreach (YASM_tags() as $tag)
	{
		// Build the language strings we need from the generic templates:
		$replace[2] = $txt[$tag];
		foreach ($txt['YASM_strings'] as $name => $text)
			$txt['YASM_' . $tag . '_' . $name] = str_replace($search, $replace, $text);

		// Strip slashes from database text strings:
		if (!empty($modSettings['YASM_' . $tag . '_text']))
			$modSettings['YASM_' . $tag . '_text'] = stripslashes($modSettings['YASM_' . $tag . '_text']);
		if (!empty($modSettings['YASM_' . $tag . '_show']))
			$modSettings['YASM_' . $tag . '_show'] = stripslashes($modSettings['YASM_' . $tag . '_show']);
		if (!empty($modSettings['YASM_' . $tag . '_hide']))
			$modSettings['YASM_' . $tag . '_hide'] = stripslashes($modSettings['YASM_' . $tag . '_hide']);
	}
	prepareDBSettingContext($config_vars);
}

/**********************************************************************************
/* Functions dealing with getting/adding/deleting user viewership logs:
**********************************************************************************/
function YASM_Add_Member($log_id = -1, $id_member = -1, $is_action = true, $msg_id = 0)
{
	global $smcFunc, $user_info, $context;

	if ($is_action)
	{
		// DIE if user is a guest or no log ID provided:
		$log_id = !empty($_GET['id']) ? $_GET['id'] : -1;
		if (!empty($user_info['is_guest']) || $log_id < 1)
			die;

		// DIE if the user is already on the list:
		$id_member = $user_info['id'];
		if (!isset($context['YASM_users']) && ($context['YASM_users'] = cache_get_data('YASM_viewed_log_' . $log_id, 86400)) == NULL)
			YASM_Get_Members($log_id);
		if (in_array($id_member, array_keys($context['YASM_users'])))
			die;
	}

	// Insert user ID and spoiler ID into the table:
	$smcFunc['db_insert']('',
		'{db_prefix}log_viewed_tag',
		array('id_member' => 'int', 'id_spoiler' => 'int', 'time' => 'int', 'id_msg' => 'int'),
		array((int) $id_member, (int) $log_id, time(), (int) $msg_id),
		array()
	);
	cache_put_data('YASM_viewed_log_' . $log_id, NULL, 86400);

	// DIE if we were called as an action:
	if ($is_action)
		die;
}

function YASM_Get_Members($log_id, $limit = 0, $id_member = 0)
{
	global $smcFunc, $scripturl, $user_info, $context;

	// Get list of users having viewed the log, with most recent views first:
	$request = $smcFunc['db_query']('', '
		SELECT
			mem.id_member, mem.real_name, mg.online_color
		FROM {db_prefix}log_viewed_tag AS tag
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = tag.id_member)
			LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = CASE WHEN mem.id_group = {int:reg_user_group} THEN mem.id_post_group ELSE mem.id_group END)
		WHERE tag.id_spoiler = {int:id_spoiler}' . (!empty($id_member) ? '
			AND tag.id_member = {int:id_member}' : '
			AND tag.id_member > {int:id_member}') . '
		ORDER BY tag.time DESC' . (!empty($limit) ? '
		LIMIT {int:limit}' : ''),
		array(
			'id_spoiler' => (int) $log_id,
			'reg_user_group' => 0,
			'id_member' => (int) $id_member,
			'limit' => (int) $limit,
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (empty($row['id_member']))
			continue;
		if (!empty($row['online_color']))
			$context['YASM_users'][$row['id_member']] = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '" style="color: ' . $row['online_color'] . ';">' . $row['real_name'] . '</a>';
		else
			$context['YASM_users'][$row['id_member']] = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>';
	}
	$smcFunc['db_free_result']($request);
	cache_put_data('YASM_viewed_log_' . $log_id, $context['YASM_users'], 86400);
}

/**********************************************************************************
/* Functions dealing with spoiler ID creation & deletion:
**********************************************************************************/
function YASM_Delete_Spoilers($id_member, $spoilers = array())
{
	global $smcFunc, $user_info;
	if (!empty($id_member))
	{
		// Clean up the spoiler ID array, just in case:
		if (!is_array($spoilers))
			$spoilers = array($spoilers);
		foreach ($spoilers as $id => $spoiler)
			$spoilers[$id] = (int) $spoiler;

		// Delete the spoiler logs for the specified user:
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_viewed_tag
			WHERE id_member = {int:id_member}' . (!empty($spoilers) ? '
				AND id_spoiler IN ({array_int:spoilers})' : ''),
			array(
				'id_member' => (int) $id_member,
				'spoilers' => $id_spoiler,
			)
		);
	}
}

function YASM_Insert_Spoiler_ID(&$messages_columns, &$update_parameters, &$msgOptions, &$topicOptions, &$posterOptions, &$messageInts)
{
	global $context, $user_info, $smcFunc, $context;

	// If no body is included, return because we have nothing to do:
	if (empty($messages_columns['body']))
		return;

	// Need to add spoiler IDs for any spoilers requesting logging:
	foreach (YASM_tags() as $tag)
	{
		$pattern = '#\[' . $tag . ' (((?:.*? )?)(log=(y|yes|true))( ?:.*?)?)\](.+?)\[/' . $tag .'\]#i' . ($context['utf8'] ? 'u' : '');
		if (preg_match_all($pattern, $messages_columns['body'], $codes, PREG_PATTERN_ORDER))
		{
			// Get the next spoiler ID from the database:
			$request = $smcFunc['db_query']('', '
				SELECT MAX(id_spoiler)
				FROM {db_prefix}log_viewed_tag',
				array()
			);
			list($id_spoiler) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);
			YASM_Add_Member($id_spoiler += 1, $user_info['id'], false);

			// Replace the spoiler logging request with the new spoiler ID:
			$replacement = str_replace($codes[3], 'log_id=' . $id_spoiler, $codes[0]);
			$messages_columns['body'] = str_replace($codes[0], $replacement, $messages_columns['body']);
		}
	}
}

function YASM_preparse(&$message)
{
}

?>