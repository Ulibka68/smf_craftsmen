<?php
/**********************************************************************************
* Subs-BBCode-YouTube.php
***********************************************************************************
* This mod is licensed under the 2-clause BSD License, which can be found here:
*	http://opensource.org/licenses/BSD-2-Clause
***********************************************************************************
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
**********************************************************************************/

// require_once("logfile.php");

if (!defined('SMF')) 
	die('Hacking attempt...');

//=================================================================================
// BBCode Hook functions
//=================================================================================
function BBCode_YouTube_Settings(&$config_vars)
{
	$config_vars[] = array('int', 'youtube_default_width');
	$config_vars[] = array('int', 'youtube_default_height');
	$config_vars[] = array('check', 'youtube_no_embed');
	$config_vars[] = array('check', 'youtube_disable_autoembed');
}

function BBCode_YouTube_LoadTheme()
{
	global $context, $settings, $sourcedir;
	
	// Add necessary headers and stuff for the forum:
	// _log($context);
	// echo $context;
	$context['html_headers'] .= '
	<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/BBCode-YouTube2.css" />';
	$context['allowed_html_tags'][] = '<iframe>';
//	$context['insert_after_template'] = '<div>insert_after_template</div>';
	
	// If ADK Portal is installed and we're using main page(s), add the integration hook:
	if (file_exists($sourcedir . '/AdkPortal/AdkPortal.php') && empty($_GET['action']))
		add_integration_function('integrate_buffer', 'BBCode_YouTube_Buffer', false);
}

function BBCode_YouTube_parameters()
{
	return array(
		'width' => array('optional' => true, 'match' => '(\d+)', 'validate' => 'BBCode_YouTube_width'),
		'height' => array('optional' => true, 'match' => '(\d+)', 'validate' => 'BBCode_YouTube_height'),
		'autoplay' => array('optional' => true, 'match' => '(1|yes|on|true)', 'validate' => 'BBCode_YouTube_autoplay'),
		'color' => array('optional' => true, 'match' => '(red|white)', 'validate' => 'BBCode_YouTube_color'),
		'theme' => array('optional' => true, 'match' => '(light|dark)', 'validate' => 'BBCode_YouTube_theme'),
		'loop' => array('optional' => true, 'match' => '(1|yes|on|true)', 'validate' => 'BBCode_YouTube_loop'),
		'start' => array('optional' => true, 'match' => '(\d+|\d+\:\d+)', 'validate' => 'BBCode_YouTube_start'),
		'end' => array('optional' => true, 'match' => '(\d+|\d+\:\d+)', 'validate' => 'BBCode_YouTube_end'),
		'privacy' => array('optional' => true, 'match' => '(1|yes|on|true)', 'validate' => 'BBCode_YouTube_privacy'),
		'controls' => array('optional' => true, 'match' => '(0|no|off|false|hide)', 'validate' => 'BBCode_YouTube_controls'),
		'showinfo' => array('optional' => true, 'match' => '(0|no|off|false|hide)', 'validate' => 'BBCode_YouTube_showinfo'),
	);
}

function BBCode_YouTube(&$bbc)
{
	// Since all the bbcodes use the same parameters, let's define them ONCE!
	$params = BBCode_YouTube_parameters();

	// Syntax: [youtube {params}]{URL}[/youtube]
	$bbc[] = array(
		'tag' => 'youtube',
		'type' => 'unparsed_content',
		'content' => '$1',
		'parameters' => $params,
		'validate' => 'BBCode_YouTube_URL',
		'disabled_content' => '$1',
	);

	// Syntax: [youtube]{URL}[/youtube]
	$bbc[] = array(
		'tag' => 'youtube',
		'type' => 'unparsed_content',
		'content' => '$1',
		'validate' => 'BBCode_YouTube_URL',
		'disabled_content' => '$1',
	);

	// Syntax: [yt {params}]{URL}[/yt]
	$bbc[] = array(
		'tag' => 'yt',
		'type' => 'unparsed_content',
		'content' => '$1',
		'parameters' => $params,
		'validate' => 'BBCode_YouTube_URL',
		'disabled_content' => '$1',
	);

	// Syntax: [yt]{URL}[/yt]
	$bbc[] = array(
		'tag' => 'yt',
		'type' => 'unparsed_content',
		'content' => '$1',
		'validate' => 'BBCode_YouTube_URL',
		'disabled_content' => '$1',
	);

	// Syntax: [yt_user {params}]{user name}[/yt_user]
	$bbc[] = array(
		'tag' => 'yt_user',
		'type' => 'unparsed_content',
		'content' => '$1',
		'parameters' => $params,
		'validate' => 'BBCode_YouTube_User',
		'disabled_content' => '$1',
	);

	// Syntax: [yt_user]{user name}[/yt_user]
	$bbc[] = array(
		'tag' => 'yt_user',
		'type' => 'unparsed_content',
		'content' => '$1',
		'validate' => 'BBCode_YouTube_User',
		'disabled_content' => '$1',
	);

	// Syntax: [yt_search {params}]{search spec}[/yt_search]
	$bbc[] = array(
		'tag' => 'yt_search',
		'type' => 'unparsed_content',
		'content' => '$1',
		'parameters' => $params,
		'validate' => 'BBCode_YouTube_Search',
		'disabled_content' => '$1',
	);

	// Syntax: [yt_search]{search spec}[/yt_search]
	$bbc[] = array(
		'tag' => 'yt_search',
		'type' => 'unparsed_content',
		'content' => '$1',
		'validate' => 'BBCode_YouTube_Search',
		'disabled_content' => '$1',
	);

    //	Gayrat
    // Syntax: [smg id=22]
    $bbc[] = [
        'tag' => 'smg',
        'type' => 'closed',
//        'content' => '<img src="{id}">',
        'content' => '{id}',
        'parameters' => ['id' => ['validate' => 'findSmgSrc']]
    ];

}

//	Gayrat
function findSmgSrc($id_parm)
{
//    [smg id=783 type=preview align=left width=80 caption="Увеличить"]
//    если на вход попал текст, то $id =0
    $id = intval($id_parm) ; // будет 783

    global $smcFunc;

    /*
    $query = "
    SELECT m.id_media,
       m.id_file,
       m.id_preview,
       f.file_name_full img_fname,
       coalesce(pt.file_name_full,'') img_preview_fname
    FROM smf_aeva_media AS m
             inner JOIN smf_aeva_files_scan AS f ON (f.fk_wt_id_file = m.id_file)
             LEFT JOIN smf_aeva_files_scan AS pt ON (pt.fk_wt_id_file = m.id_preview)
    where
        m.id_media=" . $id;
    */

    $query = "
    SELECT id_media,
       coalesce(img_fname, '') img_fname,
       coalesce(img_fname_preview,'') img_preview_fname
    FROM  smf_aeva_media_jino 
    where
        id_media=" . $id;


    $results = $smcFunc['db_query']('',$query);

    $file_name_full = '';
    $file_name_preview = '';
    while ($row = $smcFunc['db_fetch_assoc']($results))
    {
        $file_name_full = $row['img_fname'];
        $file_name_preview = $row['img_preview_fname'];
    }

    $header_aeva = 'https://woodtoolsimg.ru/mgal_data/albums/';
    $a = '';
    if ($file_name_full != '') {
        $file_name_full =$header_aeva . preg_replace('/%2F/', '/', urlencode($file_name_full));

//        if ( $file_name_preview != '') {
//            $file_name_preview = $header_aeva . preg_replace('/%2F/', '/', urlencode($file_name_preview));
//            $a = '<a href="' . $file_name_full . '" target="_blank">';
//            $a .= '<img src="' . $file_name_preview . '">';
//            $a .= '</a>';
//        } else {
//            $a .= '<img src="' . $file_name_full . '">';
//        }

        if ( $file_name_preview != '') {
            $file_name_preview = $header_aeva . preg_replace('/%2F/', '/', urlencode($file_name_preview));

            $a .= '<img class="aeva_file" src="' . $file_name_preview . '" data-nexturl="' . $file_name_full . '" onclick="aeva_media_img_swap_handle(this);">';

        } else {
            $a .= '<img  class="aeva_file" src="' . $file_name_full . '">';
        }
    }

    return $a;
}



function BBCode_YouTube_Button(&$buttons)
{
	global $txt;

//	*******************************************
//    PHOTOS
//	*******************************************

	$buttons[count($buttons) - 1][] = array(
		'image' => 'photos',
		'code' => 'img',
		'description' => 'Загрузить фото',
		'before' => '',
		'after' => '',
	);
	$buttons[count($buttons) - 1][] = array(
		'image' => 'youtube',
		'code' => 'youtube',
		'description' => $txt['youtube'],
		'before' => '[youtube]',
		'after' => '[/youtube]',
	);
/*    $buttons[count($buttons) - 1][] = array(
        'image' => 'spoiler',
        'code' => 'spoiler',
        'description' => 'Спойлер',
        'before' => '[spoiler]',
        'after' => '[/spoiler]',
    );*/
    /*$buttons[count($buttons) - 1][] = array(
        'image' => 'yt_search',
        'code' => 'yt_search',
        'description' => $txt['yt_search'],
        'before' => '[yt_search]',
        'after' => '[/yt_search]',
    );
    $buttons[count($buttons) - 1][] = array(
        'image' => 'yt_user',
        'code' => 'yt_user',
        'description' => $txt['yt_user'],
        'before' => '[yt_user]',
        'after' => '[/yt_user]',
    );*/
}

//=================================================================================
// Parameter validation functions
//=================================================================================
function BBCode_YouTube_height($height)
{
	global $context;
	$context['bbc_youtube']['height'] = max(0, (int) $height);
}

function BBCode_YouTube_width($width)
{
	global $context;
	$context['bbc_youtube']['width'] = max(0, (int) $width);
}

function BBCode_YouTube_autoplay($autoplay)
{
	global $context;
	$context['bbc_youtube']['autoplay'] = 1;
}

function BBCode_YouTube_color($color)
{
	global $context;
	$context['bbc_youtube']['color'] = $color;
}

function BBCode_YouTube_theme($theme)
{
	global $context;
	$context['bbc_youtube']['theme'] = $theme;
}

function BBCode_YouTube_loop($loop)
{
	global $context;
	$context['bbc_youtube']['loop'] = 1;
}

function BBCode_YouTube_start($start)
{
	global $context;
	$minutes = 0;
	if (strpos($start, ':'))
	{
		list($minutes, $start) = explode(':', $start);
		$start = max(0, min(59, $start));
	}
	$context['bbc_youtube']['start'] = ($minutes * 60) + $start;
}

function BBCode_YouTube_end($end)
{
	global $context;
	$minutes = 0;
	if (strpos($end, ':'))
	{
		list($minutes, $end) = explode(':', $end);
		$end = max(0, min(59, $end));
	}
	$context['bbc_youtube']['end'] = ($minutes * 60) + $end;
}

function BBCode_YouTube_privacy($privacy)
{
	global $context;
	$context['bbc_youtube']['privacy'] = 1;
}

function BBCode_YouTube_controls($controls)
{
	global $context;
	$context['bbc_youtube']['controls'] = 1;
}

function BBCode_YouTube_showinfo($showinfo)
{
	global $context;
	$context['bbc_youtube']['showinfo'] = 1;
}

//=================================================================================
// YouTube and YT BBCode validation functions:
//=================================================================================
function BBCode_YouTube_URL(&$tag, &$data, &$disabled)
{
	global $txt, $context, $modSettings;

	// Do some preperation to avoid some issues within the code:
	$data = str_replace('&amp;', '&', strip_tags($data));

	// Start building the YouTube URL that we are going to show to the user:
	if (strpos($data, 'youtube-nocookie.com') !== false)
		$context['bbc_youtube']['privacy'] = 1;
	$server = 'https://www.youtube' . (isset($context['bbc_youtube']['privacy']) ? '-nocookie' : '') . '.com';

	// Figure out if what's been passed is a YouTube video URL or ID:
	if (($len = strlen($data)) == 11)
		$data = $server . '/embed/' . ($url = $data);
	elseif ($len == 18)
		$data = $server . '/embed?listType=playlist&list=' . ($url = $data);
	elseif ($len == 34 && substr($data, 0, 2) == 'PL')
		$data = $server . '/embed?listType=playlist&list=' . ($url = $data);
	else
		$data = $server . '/' . ($url = BBCode_YouTube_Parse($data));

	// If the URL variable is empty, return link invalid to user....
	if (empty($url))
		$data = $txt['youtube_link_invalid'];
	// If the YouTube bbcode is disabled, create a simple link to the video:
	elseif (isset($disabled['youtube']))
		$data = '<a href=' . $data . '>' . $data . '</a>';
	// Otherwise, build the YouTube HTML string that we're going to use:
	else
		BBCode_YouTube_Link($data);

	// Remove the validation variables from the context array:
	unset($context['bbc_youtube']);
}

function BBCode_YouTube_User(&$tag, &$data, &$disabled)
{
	global $txt, $context, $modSettings;

	// Create the actual URL we are going to be using:
	$server = 'https://www.youtube' . (isset($context['bbc_youtube']['privacy']) ? '-nocookie' : '') . '.com';
	$data = $server . '/embed?listType=user_uploads&list=' . str_replace(" ", "+", strip_tags($data));

	// If the YouTube bbcode is disabled, create a simple link to the video:
	if (isset($disabled['youtube']))
		$data = '<a href=' . $data . '>' . $data . '</a>';
	// Otherwise, build the YouTube HTML string that we're going to use:
	else
		BBCode_YouTube_Link($data);

	// Remove the validation variables from the context array:
	unset($context['bbc_youtube']);
}

function BBCode_YouTube_Search(&$tag, &$data, &$disabled)
{
	global $txt, $context, $modSettings;

	// Create the actual URL we are going to be using:
	$server = 'https://www.youtube' . (isset($context['bbc_youtube']['privacy']) ? '-nocookie' : '') . '.com';
	$data = $server . '/embed?listType=search&list=' . str_replace(" ", "+", strip_tags($data));

	// If the YouTube bbcode is disabled, create a simple link to the video:
	if (isset($disabled['youtube']))
		$data = '<a href=' . $data . '>' . $data . '</a>';
	// Otherwise, build the YouTube HTML string that we're going to use:
	else
		BBCode_YouTube_Link($data);

	// Remove the validation variables from the context array:
	unset($context['bbc_youtube']);
}

//=================================================================================
// YouTube and YT BBCode link creation function:
//=================================================================================
function BBCode_YouTube_Link(&$data)
{
	global $context, $modSettings;
	
	// If either width or height is not defined, then pull the default values:
	$width = (isset($context['bbc_youtube']['width']) ? $context['bbc_youtube']['width'] : (!empty($modSettings['youtube_default_width']) ? $modSettings['youtube_default_width'] : 0));
	$height = (isset($context['bbc_youtube']['height']) ? $context['bbc_youtube']['height'] : (!empty($modSettings['youtube_default_height']) ? $modSettings['youtube_default_height'] : 0));

	// Adjust the video width & height to match a 16:9 ratio:
	if (!empty($width) && !empty($height) && ($width / $height) != 0.5625)
		$height = floor($width * 0.5625);

	// Process the rest of the parameters we've saved in the validation functions:
	if (isset($context['bbc_youtube']['autoplay']))
		$data = $data . (strpos($data, '?') !== false ? '&' : '?') . 'autoplay=1';
	if (isset($context['bbc_youtube']['color']))
		$data = $data . (strpos($data, '?') !== false ? '&' : '?') . 'color=' . $context['bbc_youtube']['color'];
	if (isset($context['bbc_youtube']['theme']))
		$data = $data . (strpos($data, '?') !== false ? '&' : '?') . 'theme=' . $context['bbc_youtube']['theme'];
	if (isset($context['bbc_youtube']['start']))
		$data = $data . (strpos($data, '?') !== false ? '&' : '?') . 'start=' . $context['bbc_youtube']['start'];
	if (isset($context['bbc_youtube']['end']))
		$data = $data . (strpos($data, '?') !== false ? '&' : '?') . 'end=' . $context['bbc_youtube']['end'];
	if (isset($context['bbc_youtube']['loop']))
		$data = $data . (strpos($data, '?') !== false ? '&' : '?') . 'loop=1';
	if (isset($context['bbc_youtube']['controls']))
		$data = $data . (strpos($data, '?') !== false ? '&' : '?') . 'controls=0';
	if (isset($context['bbc_youtube']['showinfo']))
		$data = $data . (strpos($data, '?') !== false ? '&' : '?') . 'showinfo=0';

	// Build the HTML string that we are going to display to the user:
	$data = 
		'<div style="' . (!empty($width) ? 'max-width: ' . $width . 'px;' : '') . (!empty($height) ? ' max-height: ' . $height . 'px;' : '') . '">' .
			'<div class="yt-wrapper">' .
				'<iframe type="text/html" src="' . $data . '" allowfullscreen frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>' .
			'</div>' . 
		'</div>';
}

//=================================================================================
// Regular Expression function for URL validation:
//=================================================================================
function BBCode_YouTube_Parse($url)
{
	if (preg_match('#(\?list=|\&amp;list=|/|/e/|/embed/|/p/)(PL[\w-]{32}|[\w-]{18})#i', $url, $matches))
	{
		parse_str(parse_url(str_replace('&amp;', '&', $url), PHP_URL_QUERY), $out);
		return ('embed?listType=playlist&list=' . $matches[2] . (isset($out['v']) ? '&v=' . $out['v'] : '') . (isset($out['index']) ? '&index=' . $out['index'] : ''));
	}
	preg_match_all('#(\?v=|\&anp;v=|/|/v/|/e/|/embed/)([\w-]{11})#i', $url, $matches);
	return (isset($matches[2][count($matches[2]) - 1]) ? 'embed/' . $matches[2][count($matches[2]) - 1] : false);
}

//=================================================================================
// Function to fix parameter order of the YouTube bbcode:
//=================================================================================
function BBCode_YouTube_Params(&$message, $pos, &$parameters)
{
	$match = substr($message, $pos - 1);
	$match = substr($match, 0, strpos($match, ']'));
	$params = explode(' ', $match);
	unset($params[0]);
	$order = array();
	$replace_str = $old = '';
	foreach ($params as $param)
	{
		if (strpos($param, '=') === false && isset($order[$old]))
			$order[$old] .= ' ' . $param;
		else
			$order[$old = substr($param, 0, strpos($param, '='))] = substr($param, strpos($param, '=') + 1);
	}
	foreach ($parameters as $key => $ignore)
	{
		$replace_str .= (isset($order[$key]) ? ' ' . $key . '=' . $order[$key] : '');
		unset($order[$key]);
	}
	$message = str_replace($match, $replace_str, $message);
	return count($order) == 0;
}

//=================================================================================
// Function to auto-embed YouTube URLs:
//=================================================================================
function BBCode_YouTube_Embed(&$message, &$smileys, &$cache_id, &$parse_tags)
{
	global $options, $context, $modSettings;
	
	// Added to allow the user the option to not embed a YouTube video
	if (!empty($options['youtube_no_embed']))
		$modSettings['disabledBBC'] = (!empty($modSettings['disabledBBC']) ? $modSettings['disabledBBC'] . ',' : '') . 'youtube,yt,yt_search,yt_user';

	// Is message empty, OR autoembedding links disabled?  If so, return to caller:
	if ($message === false || !empty($context['bbc_youtube']['ignore']) || !empty($modSettings['youtube_disable_autoembed']))
		return;
	$replace = (strpos($cache_id, 'sig') !== false ? '[url]$0[/url]' : '[youtube]$0[/youtube]');
	$pattern = '~(?<=[\s>\.(;\'"]|^)(?:https?\:\/\/)(?:www\.)?(youtube\.com|youtube-nocookie\.com|youtu\.be)/?(?:/[\w\-_\~%\.@!,\?&;=#(){}+:\'\\\\]*)*(\?list=|\&amp;list=|/e/|/embed/|/p/)(PL[\w-]{32}|[\w-]{18})\??[/\w\-_\~%@\?;=#}\\\\]?~';
	$message = preg_replace($pattern, $replace, $message);
	$pattern = '~(?<=[\s>\.(;\'"]|^)(?:https?\:\/\/)(?:www\.)?(youtube\.com|youtube-nocookie\.com|youtu\.be)/?(?:/[\w\-_\~%\.@!,\?&;=#(){}+:\'\\\\]*)*(\?v=|\&amp;v=|/v/|/e/|/embed/|)([\w-]{11})(?:\&amp;list=(PL[\w-]{32}|[\w-]{18}))?\??[/\w\-_\~%@\?;=#}\\\\]?~';
	$message = preg_replace($pattern, $replace, $message);
	if (strpos($cache_id, 'sig') !== false && empty($modSettings['youtube_sig_embed']))
		$message = preg_replace('#\[youtube.*\](.*)\[\/youtube\]#i', '[url]$1[/url]', $message);
}

//=================================================================================
// Function to remove unnecessary class addition from YouTube videos:
//=================================================================================
function BBCode_YouTube_Buffer($buffer)
{
	return str_replace('class="resize_auto_new youtube-player"', 'class="youtube-player"', $buffer);
}

?>