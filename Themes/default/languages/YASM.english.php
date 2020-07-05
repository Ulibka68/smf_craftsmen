<?php
/**********************************************************************************
* YASM.english.php
***********************************************************************************
* This mod is licensed under the 2-clause BSD License, which can be found here:
*	http://opensource.org/licenses/BSD-2-Clause
***********************************************************************************
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
**********************************************************************************/

// General use strings:
$txt['spoiler'] = 'Spoiler';
$txt['changelog'] = 'Changelog';
$txt['offtopic'] = 'Off-Topic';
$txt['YASM_show'] = 'Show';
$txt['YASM_hide'] = 'Hide';
$txt['YASM_viewed_by'] = 'Viewed by';
$txt['YASM_last_viewed'] = 'Latest %d members viewed';

// Style names using the following format:
//   CSS Class Name (in CSS file) => CSS Style Name (visible to user)
$txt['YASM_styles'] = array(
	'original' => 'Original',
	'wellwisher' => 'WellWisher',
);

// Admin strings:
$txt['YASM_title'] = 'Yet Another Spoiler Mod';
$txt['YASM_default'] = '<div class="smalltext"><strong>NOTE:</strong> Does not affect tags that specify this text!</div>';
$txt['YASM_strings'] = array(
	'style' => '{tag_name} style to use:',
	'show' => 'Replace button text &quot;{show}&quot; with:' . $txt['YASM_default'],
	'hide' => 'Replace button text &quot;{hide}&quot; with:' . $txt['YASM_default'],
	'text' => 'Replace &quot;{tag_name}&quot; header with:' . $txt['YASM_default'],
	'expanded' => 'Default to expanded status globally?<div class="smalltext"><strong>NOTE:</strong>  Does not affect tags that specify expanded status!</div>',
	'no_guests' => 'Disable {tag_name} for guests globally?<div class="smalltext"><strong>NOTE:</strong>  Does not affect tags that specify guest status!</div>',
	'no_border' => 'Disable line beneath {tag_name} header?',
	'show_log' => 'Show which members viewed the {tag_name}?',
	'limit_log' => 'Number of members to show that viewed the {tag_name}?<div class="smalltext"><strong>NOTE:</strong>  Set to <strong>0</strong> to show everybody!</div>',
	'no_guest_html' => '[ You must <a href="{scripturl}?action=login">login</a> or <a href="{scripturl}?action=register">register</a> to view this {tag_name}! ]',
);

?>