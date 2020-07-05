<?php
if (!defined('SMF'))
	die('Hacking attempt...');

function Bookmarks()
{
	global $txt, $context, $user_info, $scripturl;

	// Load the template
	loadTemplate('Bookmarks');

	// Kick users that don't have permission to make bookmarks.
	isAllowedTo('make_bookmarks');

	// Set the page title
	$context['page_title'] = $txt['bookmarks'];

	// Add it to the linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=bookmarks',
		'name' => $txt['bookmarks'],
		);

	// Determine whether we should run a subaction.
	$context['sub_action'] = isset($_GET['sa']) ? $_GET['sa'] : '';

	switch ($context['sub_action'])
	{
		// Add a new bookmark
		case 'add':
			checkSession('get');
			$success = !empty($_GET['topic']) ? addBookmark((int) $_GET['topic'], $user_info['id']) : false;
			if ($success)
				$context['success'] = $txt['bookmark_add_success'];
	        else
				$context['failure'] = $txt['bookmark_add_failed'];
			break;
		
		// Delete a bookmark
		case 'delete':
			checkSession('post');
			$num_deleted = !empty($_POST['remove_bookmarks']) ? deleteBookmarks($_POST['remove_bookmarks'], $user_info['id']) : 0;
			if ($num_deleted !== false)
				$context['success'] = sprintf($txt['bookmark_delete_success'], $num_deleted);
	        else
				$context['failure'] = $txt['bookmark_delete_failure'];
			break;
	}

	// Load this user's bookmarks
	$context['bookmarks'] = getBookmarks($user_info['id']);
}

// Gathers a list of all of this user's bookmarks.
function getBookmarks($id_member)
{
	global $smcFunc, $settings, $scripturl, $modSettings, $user_info, $txt;

	$request = $smcFunc['db_query']('substring', '
		SELECT
			t.id_topic, t.num_replies, t.locked, t.num_views, t.id_board, b.name AS board_name,
			IFNULL(lt.id_msg, IFNULL(lmr.id_msg, -1)) + 1 AS new_from,
			t.id_last_msg, ml.poster_time AS last_poster_time,
			ml.id_msg_modified, ml.subject AS last_subject, ml.icon AS last_icon,
			ml.poster_name AS last_member_name, ml.id_member AS last_id_member,
			IFNULL(meml.real_name, ml.poster_name) AS last_display_name, t.id_first_msg,
			mf.poster_time AS first_poster_time, mf.subject AS first_subject, mf.icon AS first_icon,
			mf.poster_name AS first_member_name, mf.id_member AS first_id_member,
			IFNULL(memf.real_name, mf.poster_name) AS first_display_name
		FROM {db_prefix}bookmarks AS bm
			INNER JOIN {db_prefix}topics AS t ON (bm.id_topic = t.id_topic)
			INNER JOIN {db_prefix}boards AS b ON (t.id_board = b.id_board)
			INNER JOIN {db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)
			INNER JOIN {db_prefix}messages AS mf ON (mf.id_msg = t.id_first_msg)
			LEFT JOIN {db_prefix}members AS meml ON (meml.id_member = ml.id_member)
			LEFT JOIN {db_prefix}members AS memf ON (memf.id_member = mf.id_member)
			LEFT JOIN {db_prefix}log_topics AS lt ON (lt.id_topic = t.id_topic AND lt.id_member = {int:current_member})
			LEFT JOIN {db_prefix}log_mark_read AS lmr ON (lmr.id_board = t.id_board AND lmr.id_member = {int:current_member})
		WHERE
			bm.id_member = {int:current_member}' . (!$modSettings['postmod_active'] || allowedTo('approve_posts') ? '' : '
			AND (t.approved = {int:is_approved} OR t.id_member_started = {int:current_member})') . '
			AND {query_see_board}
		ORDER BY t.id_last_msg DESC',
		array(
			'current_member' => $id_member,
			'is_approved' => 1,
			)
		);

	$bookmarks = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		censorText($row['subject']);

		$bookmarks[] = array(
			'id' => $row['id_topic'],
			'board' => array(
				'id' => $row['id_board'],
				'name' => $row['board_name'],
				'href' => $scripturl . '?board=' . $row['id_board'] . '.0',
				'link' => '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['board_name'] . '</a>'
			),
			'first_post' => array(
				'id' => $row['id_first_msg'],
				'member' => array(
					'username' => $row['first_member_name'],
					'name' => $row['first_display_name'],
					'id' => $row['first_id_member'],
					'href' => !empty($row['first_id_member']) ? $scripturl . '?action=profile;u=' . $row['first_id_member'] : '',
					'link' => !empty($row['first_id_member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['first_id_member'] . '" title="' . $txt['profile_of'] . ' ' . $row['first_display_name'] . '">' . $row['first_display_name'] . '</a>' : $row['first_display_name']
				),
				'time' => timeformat($row['first_poster_time']),
				'timestamp' => forum_time(true, $row['first_poster_time']),
				'subject' => $row['first_subject'],
				'icon' => $row['first_icon'],
				'icon_url' => $settings['images_url'] . '/post/' . $row['first_icon'] . '.gif',
				'href' => $scripturl . '?topic=' . $row['id_topic'] . '.0',
				'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0">' . $row['first_subject'] . '</a>'
			),
			'last_post' => array(
				'id' => $row['id_last_msg'],
				'member' => array(
					'username' => $row['last_member_name'],
					'name' => $row['last_display_name'],
					'id' => $row['last_id_member'],
					'href' => !empty($row['last_id_member']) ? $scripturl . '?action=profile;u=' . $row['last_id_member'] : '',
					'link' => !empty($row['last_id_member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['last_id_member'] . '">' . $row['last_display_name'] . '</a>' : $row['last_display_name']
				),
				'time' => timeformat($row['last_poster_time']),
				'timestamp' => forum_time(true, $row['last_poster_time']),
				'subject' => $row['last_subject'],
				'icon' => $row['last_icon'],
				'icon_url' => $settings['images_url'] . '/post/' . $row['last_icon'] . '.gif',
				'href' => $scripturl . '?topic=' . $row['id_topic'] . ($user_info['is_guest'] ? ('.' . (!empty($options['view_newest_first']) ? 0 : ((int) (($row['num_replies']) / $context['pageindex_multiplier'])) * $context['pageindex_multiplier']) . '#msg' . $row['id_last_msg']) : (($row['num_replies'] == 0 ? '.0' : '.msg' . $row['id_last_msg']) . '#new')),
				'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . ($user_info['is_guest'] ? ('.' . (!empty($options['view_newest_first']) ? 0 : ((int) (($row['num_replies']) / $context['pageindex_multiplier'])) * $context['pageindex_multiplier']) . '#msg' . $row['id_last_msg']) : (($row['num_replies'] == 0 ? '.0' : '.msg' . $row['id_last_msg']) . '#new')) . '" ' . ($row['num_replies'] == 0 ? '' : 'rel="nofollow"') . '>' . $row['last_subject'] . '</a>'
			),
			'icon' => $row['first_icon'],
			'icon_url' => $settings['theme_url'] . '/post/' . $row['first_icon'] . '.gif',
			'subject' => $row['first_subject'],
			'new' => $row['new_from'] <= $row['id_msg_modified'],
			'new_from' => $row['new_from'],
			'newtime' => $row['new_from'],
			'new_href' => $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['new_from'] . '#new',
			'replies' => $row['num_replies'],
			'views' => $row['num_views'],
		);
	}
	$smcFunc['db_free_result']($request);

	return $bookmarks;
}

// Adds a bookmark for a certain topic for a certain user.
function addBookmark($id_topic, $id_member)
{
	global $smcFunc;

	$result = $smcFunc['db_insert']('replace', '{db_prefix}bookmarks',
		array(
			'id_member' => 'int',
			'id_topic' => 'int',
			),
		array(
			'id_member' => $id_member,
			'id_topic' => $id_topic,
			),
		array()
	);

	return $smcFunc['db_affected_rows']($result) != 0;
}

// Delete bookmarks for a certain user.
function deleteBookmarks($topic_ids, $id_member)
{
	global $smcFunc;

	// Make sure we have valid id's here.
	foreach ($topic_ids as $index => $id)
		$topic_ids[$index] = (int) $id;

	$result = $smcFunc['db_query']('', "
		DELETE FROM {db_prefix}bookmarks
		WHERE
			id_topic IN({array_int:topics}) AND
			id_member = {int:id_member}",
		array(
			'id_member' => $id_member,
			'topics' => $topic_ids,
		)
	);

	// Return the amount of deleted bookmarks, unless an error occured.
	return $result ? $smcFunc['db_affected_rows']() : false;
}

?>