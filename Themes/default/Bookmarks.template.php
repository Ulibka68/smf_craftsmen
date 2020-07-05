<?php
function template_main()
{
	global $context, $settings, $scripturl, $txt;

	// Show the good or bad news, if any.
	if (isset($context['success']))
		echo '
			<div class="windowbg" id="profile_success">
				', $context['success'], '
			</div>';
	elseif (isset($context['failure']))
		echo '
			<div class="errorbox">
				<h3>', $txt['error_occured'], '</h3>
				<p>', $context['failure'], '</p>
			</div>';

	// Let's get the show moving.
	echo '
			<div class="cat_bar">
				<h3 class="catbg">', $txt['bookmark_list'], '</h3>
			</div>';

	// Show the bookmarks, if any.
	if (!empty($context['bookmarks']))
	{
		echo '
			<form action="', $scripturl, '?action=bookmarks;sa=delete" method="post">
				<table border="0" width="100%" cellspacing="1" cellpadding="5" class="table_grid">
					<thead>
						<tr class="catbg">
							<th class="first_th"></th>
							<th>', $txt['subject'], '</th>
							<th width="11%">', $txt['started_by'], '</th>
							<th width="4%">', $txt['replies'], '</th>
							<th width="4%">', $txt['views'], '</th>
							<th width="22%">', $txt['latest_post'], '</th>
							<th class="last_th" width="3%" align="center">
								<input type="checkbox" class="check" onclick="invertAll(this, this.form);" />
							</th>
						</tr>';

		foreach ($context['bookmarks'] as $topic)
		{
			// Show the topic's subject
			echo '
					<tr>
						<td class="windowbg2" width="4%" align="center">
							<img src="', $topic['first_post']['icon_url'], '" alt="" />
						</td>
						<td class="windowbg" valign="middle">
							', $topic['first_post']['link'];
				
			// Any new replies?
			if ($topic['new'])
				echo ' <a href="', $topic['new_href'], '"><img src="', $settings['images_url'], '/', $context['user']['language'], '/new.gif" alt="', $txt['new'], '" /></a>';

			// Show the board the topic was posted in, as well as a link to the profile of the topic starter	
			echo '<br />
							<span class="smalltext"><i>', $txt['in'], ' ', $topic['board']['link'], '</i></span>
						</td>
						<td class="windowbg2" valign="middle">', $topic['first_post']['member']['link'], '</td>
						<td class="windowbg" valign="middle" align="center">', $topic['replies'], '</td>
						<td class="windowbg" valign="middle" align="center">', $topic['views'], '</td>
						<td class="windowbg2" valign="middle">
							<a href="', $topic['last_post']['href'], '"><img src="', $settings['images_url'], '/icons/last_post.gif" alt="', $txt['last_post'], '" title="', $txt['last_post'], '" style="float: right;" /></a>
							<span class="smalltext">
								', $topic['last_post']['time'], '<br />
								', $txt['by'], ' ', $topic['last_post']['member']['link'], '
							</span>
						</td>
						<td class="windowbg2" valign="middle" align="center"><input type="checkbox" name="remove_bookmarks[]" value="', $topic['id'], '" class="check" /></td>
					</tr>';
		}

		echo '
				</table>
				<div class="padding righttext">
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input class="button_submit" type="submit" name="send" value="', $txt['bookmark_delete'], '" />
				</div>
			</form>';
	}
	// Show a message saying there aren't any bookmarks yet
	else
	{
		echo '
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="content">', $txt['bookmark_list_empty'], '</div>
				<span class="botslice"><span></span></span>
			</div>';
	}
}
?>