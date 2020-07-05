<?php
/**********************************************************************************
* Subs-BBCode-Instagram.php
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

function BBCode_Instagram(&$bbc)
{
	// Format: [instagram width=x height=x frameborder=x]{instagram ID}[/instagram]
	$bbc[] = array(
		'tag' => 'instagram',
		'type' => 'unparsed_content',
		'parameters' => array(
			'width' => array('match' => '(\d+[\%|px|])'),
			'height' => array('optional' => true, 'match' => '(\d+[\%|px|])'),
			'frameborder' => array('optional' => true, 'match' => '(\d+)'),
			'captioned' => array('optional' => true, 'match' => '(y|n|yes|no)'),
		),
		'validate' => 'BBCode_Instagram_Validate',
		'content' => '{width}|{height}|{frameborder}|{captioned}',
		'disabled_content' => '$1',
	);

	// Format: [instagram width=x height=x frameborder=x]{instagram ID}[/instagram]
	$bbc[] = array(
		'tag' => 'instagram',
		'type' => 'unparsed_content',
		'parameters' => array(
			'height' => array('optional' => true, 'match' => '(\d+[\%|px|])'),
			'frameborder' => array('optional' => true, 'match' => '(\d+)'),
			'captioned' => array('optional' => true, 'match' => '(y|n|yes|no)'),
		),
		'validate' => 'BBCode_Instagram_Validate',
		'content' => '0|{height}|{frameborder}|{captioned}',
		'disabled_content' => '$1',
	);

	// Format: [instagram]{instagram ID}[/instagram]
	$bbc[] = array(
		'tag' => 'instagram',
		'type' => 'unparsed_content',
		'validate' => 'BBCode_Instagram_Validate',
		'content' => '0|0|0|0',
		'disabled_content' => '$1',
	);
}

function BBCode_Instagram_Button(&$buttons)
{
	$buttons[count($buttons) - 1][] = array(
		'image' => 'instagram',
		'code' => 'instagram',
		'description' => 'Instagram',
		'before' => '[instagram]',
		'after' => '[/instagram]',
	);
}

function BBCode_Instagram_Validate(&$tag, &$data, &$disabled)
{
	global $txt, $modSettings;
	
	// Set up for a run through the bbcode:
	list($width, $height, $frameborder, $captioned) = explode('|', $tag['content']);
	$tag['content'] = $txt['instagram_no_post_id'];
	if (empty($data))
		return;
	$data = strtr(trim($data), array('<br />' => ''));

	// Is this a Tumblr post URL?  If not, abort:
	if (strlen($data) > 11)
	{
		if (strpos($data, 'http://') !== 0 && strpos($data, 'https://') !== 0)
			$data = 'http://' . $data;
		if (!preg_match('#(http|https):\/\/(|(.+?).)instagram.com\/p\/([A-Za-z0-9_\-]+)#i', $data, $parts))
			return;
		$data = $parts[4] . (isset($parts[5]) ? $parts[5] : '');
	}
	
	// Build the Instagram html code:
	if (empty($width) && !empty($modSettings['instagram_default_width']))
		$width = $modSettings['instagram_default_width'];
	if (empty($height) && !empty($modSettings['instagram_default_height']))
		$height = $modSettings['instagram_default_height'];
	if (!empty($width) && strpos($width, '%') === false)
		$width .= 'px';
	if (!empty($height) && strpos($height, '%') === false)
		$height .= 'px';
	$captioned = empty($captioned) || $captioned == 'y' || $captioned == 'yes';
	$style = 'style="' . (empty($width) ? '' : 'max-width: ' . $width . '; ') . (empty($height) ? '' : 'max-height: ' . $height . ';');
	$tag['content'] = '<div ' . $style . '">' .
		'<div class="instagram-wrapper" ' . str_replace('max-height', 'padding-bottom', $style) . '">' .
			'<iframe src="https://instagram.com/p/' . $data .'/embed' . ($captioned ? '/captioned/' : '') . '" scrolling="no" ' . (!empty($frameborder) ? ' frameborder="' . $frameborder . '" ' : '') . $style . '"></iframe>' .
		'</div></div>';

	// Add the Instagram URL if admin says so:
	if (!empty($modSettings['instagram_include_link']))
		$tag['content'] .= '<br /><a href="https://instagram.com/p/' . $data . '">https://instagram.com/p/' . $data . '</a>';
}

function BBCode_Instagram_LoadTheme()
{
	global $context, $settings;
	$context['html_headers'] .= '
	<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/instagram.css" />';
	$context['allowed_html_tags'][] = '<iframe>';
}

function BBCode_Instagram_Settings(&$config_vars)
{
	$config_vars[] = array('int', 'instagram_default_width');
	$config_vars[] = array('int', 'instagram_default_height');
	$config_vars[] = array('check', 'instagram_include_link');
}

function BBCode_Instagram_Embed(&$message, &$smileys, &$cache_id, &$parse_tags)
{
	if ($message === false)
		return;
	$replace = (strpos($cache_id, 'sig') !== false ? '[url]$0[/url]' : '[instagram]$0[/instagram]');
	$pattern = '~(?<=[\s>\.(;\'"]|^)(https?\:\/\/)(?:www\.)?instagram.com\/p\/?[A-Za-z0-9_\-]+(\/embed|\/embed\/captioned|)\/\?taken\-by=[A-Za-z0-9_\-]+\??[/\w\-_\~%@\?;=#}\\\\]?~';
	$message = preg_replace($pattern, $replace, $message);
	$pattern = '~(?<=[\s>\.(;\'"]|^)(https?\:\/\/)(?:www\.)?instagram.com\/p\/[A-Za-z0-9_\-]+(\/embed\/|\/embed\/captioned\/|)\??[/\w\-_\~%@\?;=#}\\\\]?~';
	$message = preg_replace($pattern, $replace, $message);
	if (strpos($cache_id, 'sig') !== false)
		$message = preg_replace('#\[instagram.*\](.*)\[\/instagram\]#i', '[url]$1[/url]', $message);
}

?>