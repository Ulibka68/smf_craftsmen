<?php

/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines http://www.simplemachines.org
 * @copyright 2011 Simple Machines
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/*	!!!

*/



// Get the latest posts of a forum.
function getLastPosts($latestPostOptions)
{
	global $scripturl, $txt, $user_info, $modSettings, $smcFunc, $context;

    $request = $smcFunc['db_query']('',
        'call get_last_topics_post ( {int:likely_max_msg})',
        array(
            'likely_max_msg' =>  $latestPostOptions['number_posts'],
        )
    );


	$posts = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		// Censor the subject and post for the preview ;).
		censorText($row['subject']);
		censorText($row['body']);

		$row['body'] = strip_tags(strtr(parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']), array('<br />' => '&#10;')));
		if ($smcFunc['strlen']($row['body']) > 128)
			$row['body'] = $smcFunc['substr']($row['body'], 0, 128) . '...';

		// Build the array.
		$posts[] = array(
			'board' => array(
				'id' => $row['id_board'],
				'name' => $row['board_name'],
				'href' => $scripturl . '?board=' . $row['id_board'] . '.0',
				'link' => '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['board_name'] . '</a>'
			),
			'topic' => $row['id_topic'],
			'poster' => array(
				'id' => $row['id_member'],
				'name' => $row['poster_name'],
				'href' => empty($row['id_member']) ? '' : $scripturl . '?action=profile;u=' . $row['id_member'],
				'link' => empty($row['id_member']) ? $row['poster_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['poster_name'] . '</a>'
			),
			'subject' => $row['subject'],
			'short_subject' => shorten_subject($row['subject'], 24),
			'preview' => $row['body'],
			'time' => timeformat($row['poster_time']),
			'timestamp' => forum_time(true, $row['poster_time']),
			'raw_timestamp' => $row['poster_time'],
			'href' => $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . ';topicseen#msg' . $row['id_msg'],
			'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . ';topicseen#msg' . $row['id_msg'] . '" rel="nofollow">' . $row['subject'] . '</a>'
		);
	}
    $smcFunc['db_free_result']($request);

	global $db_connection;
    mysqli_next_result($db_connection);


	return $posts;
}

// Callback-function for the cache for getLastPosts().
function cache_getLastPosts($latestPostOptions)
{
	return array(
		'data' => getLastPosts($latestPostOptions),
		'expires' => time() + 60,
		'post_retri_eval' => '
			foreach ($cache_block[\'data\'] as $k => $post)
			{
				$cache_block[\'data\'][$k][\'time\'] = timeformat($post[\'raw_timestamp\']);
				$cache_block[\'data\'][$k][\'timestamp\'] = forum_time(true, $post[\'raw_timestamp\']);
			}',
	);
}

?>