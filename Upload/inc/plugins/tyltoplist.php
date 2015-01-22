<?php
/*
	Main plugin file for 'TopList AddOn für THX/Like' plugin for MyBB 1.8
	Copyright © 2015 Svepu
	Last change: 2015-01-22 - v 1.9.1
*/

if(!defined('IN_MYBB')) {
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

if(my_strpos($_SERVER['PHP_SELF'], 'stats.php'))
{
	global $templatelist;
	if(isset($templatelist)){$templatelist .= ',';}
	$templatelist .= 'tyltoplist_stats_view';
}

$plugins->add_hook('stats_start','tyltoplist_stats');
$plugins->add_hook('build_friendly_wol_location_end', 'tyltoplist_online');

function tyltoplist_info() {
	global $plugins_cache, $mybb, $db, $lang;
	
	$lang->load("tyltoplist");
	
    $info = array(
		"name" 			=> $db->escape_string($lang->plugin_name),
		"description" 	=> $db->escape_string($lang->plugin_desc),
		"website"		=> 'https://github.com/SvePu/TYL-TopList',
		"author"		=> 'SvePu',
		"authorsite"	=> 'https://github.com/SvePu',
		"codename"	=>	'tyltoplist',
		"version"		=> '1.9.1',
		"compatibility"	=> '18*'
	);
	
	$info_desc = '';
	$gid_result = $db->simple_select('settinggroups', 'gid', "name = 'tyltoplist_settings'", array('limit' => 1));
	$settings_group = $db->fetch_array($gid_result);
	if(!empty($settings_group['gid']))
	{
		$info_desc .= "<span style=\"font-size: 0.9em;\">(~<a href=\"index.php?module=config-settings&action=change&gid=".$settings_group['gid']."\"> ".$db->escape_string($lang->tyltoplist_settings_title)." </a>~)</span>";
	}
    
    if(is_array($plugins_cache) && is_array($plugins_cache['active']) && $plugins_cache['active']['tyltoplist'])
    {
		$info_desc .= '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float: right;" target="_blank" />
<input type="hidden" name="cmd" value="_s-xclick" />
<input type="hidden" name="hosted_button_id" value="VGQ4ZDT8M7WS2" />
<input type="image" src="https://www.paypalobjects.com/webstatic/en_US/btn/btn_donate_pp_142x27.png" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" />
<img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1" />
</form>';
	}
	
	if($info_desc != '')
	{
		$info['description'] = $info_desc.'<br />'.$info['description'];
	}
    
    return $info;
}

function tyltoplist_activate() {
	global $db, $mybb, $lang;
	
	$lang->load("tyltoplist");
	
	if($mybb->settings['g33k_thankyoulike_enabled'] != "1"){
		flash_message("{$lang->mainplugin_req}", "error");
		admin_redirect("index.php?module=config-plugins");
	}
	
	$templateset = array(
	    "prefix" => "tyltoplist",
	    "title" => "TYL-TopList",
    );
	$db->insert_query("templategroups", $templateset);

	$templatearray = array(
        "title" => "tyltoplist_view",
        "template" => "<html><head><title>{\$lang->tyltoplist_header} {\$mybb->settings[\'tyltoplist_limit\']} {\$tlprefix} - {\$mybb->settings[\'bbname\']}</title>{\$headerinclude}</head><body>{\$header}<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" width=\"100%\"><thead><tr><td class=\"thead\" colspan=\"4\"><div><strong>{\$lang->tyltoplist_header} {\$mybb->settings[\'tyltoplist_limit\']} {\$tlprefix}</strong><br /><div class=\"smalltext\">{\$lang->tyltoplist_desc} {\$tlprefix}</div></div></td></tr></thead><tbody><tr><td class=\"tcat\" width=\"5%\" style=\"text-align:center;\"><strong>{\$lang->table_header_place}</strong></td><td class=\"tcat\" width=\"80%\"><strong>{\$lang->table_header_post}</strong></td><td class=\"tcat\" width=\"5%\" style=\"text-align:center;\"><strong>{\$tlprefix}</strong></td><td class=\"tcat\" width=\"10%\" style=\"text-align:right;\"><strong>{\$lang->table_header_autor}</strong></td></tr>{\$tlTable}<tr><td class=\"tfoot\" colspan=\"4\">{\$tlCopyright}</td></tr></tbody></table>{\$footer}</body></html>",
				"sid" => -2
	);
	$db->insert_query("templates", $templatearray);
	
	$templatearray = array(
        "title" => "tyltoplist_disabled",
        "template" => "<html><head><title>{\$lang->tyltoplist_header} {\$mybb->settings[\'tyltoplist_limit\']} {\$tlprefix} - {\$mybb->settings[\'bbname\']}</title>{\$headerinclude}</head><body>{\$header}<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" width=\"100%\"><thead><tr><td class=\"thead\"><div><strong>{\$lang->tyltoplist_header} {\$mybb->settings[\'tyltoplist_limit\']} {\$tlprefix} - Info</strong></div></td></tr></thead><tbody><tr></tr><td class=\"trow1\"><div style=\"padding: 15px 5px;\">{\$lang->tyltoplist_disabled}</div></td><tr><td class=\"tfoot\"></td></tr></tbody></table>{\$footer}</body></html>",
				"sid" => -2
	);
	$db->insert_query("templates", $templatearray);
	
	$templatearray = array(
        "title" => "tyltoplist_stats_view",
        "template" => "<br/><table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" width=\"100%\"><thead><tr><td class=\"thead\" colspan=\"4\"><div><strong>{\$lang->tyltoplist_header} {\$mybb->settings[\'tyltoplist_limit\']} {\$tlprefix}</strong><br /><div class=\"smalltext\">{\$lang->tyltoplist_desc} {\$tlprefix}</div></div></td></tr></thead><tbody><tr><td class=\"tcat\" width=\"5%\" style=\"text-align:center;\"><strong>{\$lang->table_header_place}</strong></td><td class=\"tcat\" width=\"80%\"><strong>{\$lang->table_header_post}</strong></td><td class=\"tcat\" width=\"5%\" style=\"text-align:center;\"><strong>{\$tlprefix}</strong></td><td class=\"tcat\" width=\"10%\" style=\"text-align:right;\"><strong>{\$lang->table_header_autor}</strong></td></tr>{\$tlTable}</tbody></table>",
				"sid" => -2
	);
	$db->insert_query("templates", $templatearray);
	
	$query_add = $db->simple_select("settinggroups", "COUNT(*) as rows");
	$rows = $db->fetch_field($query_add, "rows");
    $tyltoplist_group = array(
		"name" 			=>	"tyltoplist_settings",
		"title" 		=>	$db->escape_string($lang->tyltoplist_settings_title),
		"description" 	=>	$db->escape_string($lang->tyltoplist_settings_title_desc),
		"disporder"		=> 	$rows+1,
		"isdefault" 	=>  0
	);
    $db->insert_query("settinggroups", $tyltoplist_group);
	$gid = $db->insert_id();
	
	$tyltoplist_1 = array(
        'sid'           => 'NULL',
        'name'			=> 'tyltoplist_enable',
        'title'			=> $db->escape_string($lang->tyltoplist_enable_title),
        'description'  	=> $db->escape_string($lang->tyltoplist_enable_title_desc),
        'optionscode'  	=> 'yesno',
        'value'        	=> '1',
        'disporder'		=> 1,
        "gid" 			=> (int)$gid
    );
	$db->insert_query('settings', $tyltoplist_1);
	
	
    $tyltoplist_2 = array(
		"name"			=> "tyltoplist_limit",
		"title"			=> $db->escape_string($lang->tyltoplist_limit_title),
		"description" 	=> $db->escape_string($lang->tyltoplist_limit_title_desc),
        'optionscode'  	=> 'numeric',
        'value'        	=> '20',
		"disporder"		=> "2",
		"gid" 			=> (int)$gid
	);
	$db->insert_query("settings", $tyltoplist_2);
	
	$tyltoplist_3 = array(
        'sid'           => 'NULL',
        'name'			=> 'tyltoplist_styled_usernames',
        'title'			=> $db->escape_string($lang->tyltoplist_styled_usernames_title),
        'description'  	=> $db->escape_string($lang->tyltoplist_styled_usernames_title_desc),
        'optionscode'  	=> 'yesno',
        'value'        	=> '0',
        'disporder'		=> "3",
        "gid" 			=> (int)$gid
    );
	$db->insert_query('settings', $tyltoplist_3);
	
	$tyltoplist_4 = array(
		"name"			=> "tyltoplist_groupselect",
		"title"			=> $db->escape_string($lang->tyltoplist_groupselect_title),
		"description" 	=> $db->escape_string($lang->tyltoplist_groupselect_title_desc),
        'optionscode'  	=> 'groupselect',
        'value'        	=> '-1',
		"disporder"		=> "4",
		"gid" 			=> (int)$gid
	);
	$db->insert_query("settings", $tyltoplist_4);
	
	$tyltoplist_5 = array(
		"name"			=> "tyltoplist_fidsout",
		"title"			=> $db->escape_string($lang->tyltoplist_fidsout_title),
		"description" 	=> $db->escape_string($lang->tyltoplist_fidsout_title_desc),
        'optionscode'  	=> 'forumselect',
        'value'        	=> '',
		"disporder"		=> "5",
		"gid" 			=> (int)$gid
	);
	$db->insert_query("settings", $tyltoplist_5);
	
	$tyltoplist_6 = array(
		"name"			=> "tyltoplist_show_in_stats",
		"title"			=> $db->escape_string($lang->tyltoplist_show_in_stats_title),
		"description" 	=> $db->escape_string($lang->tyltoplist_show_in_stats_title_desc),
        'optionscode'  	=> 'yesno',
        'value'        	=> '0',
		"disporder"		=> "6",
		"gid" 			=> (int)$gid
	);
	$db->insert_query("settings", $tyltoplist_6);
	rebuild_settings();
	
	require_once MYBB_ROOT."inc/adminfunctions_templates.php";
    find_replace_templatesets("stats", '#{\$footer}(\r?)\n#', "{\$tyltoplist_stats}\n{\$footer}\n");
}

function tyltoplist_deactivate() {
	global $db;
	
	$templatearray = array(
        "tyltoplist_view",
		"tyltoplist_disabled",
		"tyltoplist_stats_view"
    );
	$deltemplates = implode("','", $templatearray);
	$db->delete_query("templates", "title in ('{$deltemplates}')");	
	$db->delete_query("templategroups", "prefix in ('tyltoplist')");
	
	$result = $db->simple_select('settinggroups', 'gid', "name = 'tyltoplist_settings'", array('limit' => 1));
	$tyl_group = $db->fetch_array($result);
	
	if(!empty($tyl_group['gid']))
	{
		$db->delete_query('settinggroups', "gid='{$tyl_group['gid']}'");
		$db->delete_query('settings', "gid='{$tyl_group['gid']}'");
		rebuild_settings();
	}
	
	require_once MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("stats", '#{\$tyltoplist_stats}(\r?)\n#', "", 0);
}

function tyltoplist_stats()
{
	global $mybb;
	
	if ($mybb->settings['tyltoplist_show_in_stats'] == 1){
	
		if(is_member($mybb->settings['tyltoplist_groupselect']) OR ($mybb->settings['tyltoplist_groupselect'] == "-1")){
			global $settings, $db,$templates,$theme,$lang,$parser,$tyltoplist_stats;
			
			$lang->load("tyltoplist");
			
			if ($settings['g33k_thankyoulike_thankslike'] == "thanks"){
				$tlprefix = $db->escape_string($lang->tyltoplist_table_prefix_thanks);
			} else {
				$tlprefix = $db->escape_string($lang->tyltoplist_table_prefix_likes);
			}
			
			if ($settings['tyltoplist_limit'] < 1){
				$settings['tyltoplist_limit'] = 20;
			}
			
			if ($mybb->settings['tyltoplist_enable'] == 1 && $settings['tyltoplist_fidsout'] != -1){		
				$tlTable = "";
				$tyltoplist_unviewwhere = "";
				$tyltoplist_unviewable = get_unviewable_forums();
				if($tyltoplist_unviewable)
				{
					$tyltoplist_unviewwhere = "AND fid NOT IN ({$tyltoplist_unviewable})";
				}
				$tyltoplist_fidsoutlist = "";
				if(!empty($settings['tyltoplist_fidsout']))
				{
					$tyltoplist_fidsoutlist = "AND p.fid NOT IN ({$settings['tyltoplist_fidsout']})";
				}						
				$tul = $db->query("SELECT l.pid, count( * ) AS likes, p.subject, p.username, p.uid, p.fid, p.tid, u.usergroup, u.displaygroup
									FROM ".TABLE_PREFIX."g33k_thankyoulike_thankyoulike l
									LEFT JOIN ".TABLE_PREFIX."users u ON (l.puid=u.uid)
									LEFT JOIN ".TABLE_PREFIX."posts p ON (l.pid=p.pid)
									WHERE visible='1' {$tyltoplist_unviewwhere} {$tyltoplist_fidsoutlist}
									GROUP BY l.pid
									ORDER BY likes DESC, l.pid ASC
									LIMIT 0,{$settings['tyltoplist_limit']}");				
				$maxplace = $tul->num_rows;
				$iPlace = 1;
				
				while ($data = $db->fetch_array($tul)) {
					$tyltoplist_username = htmlspecialchars_uni($data['username']);
					if ($settings['tyltoplist_styled_usernames'] == 1){
						$tyltoplist_userlink = build_profile_link(format_name($tyltoplist_username, $data['usergroup'], $data['displaygroup']), $data['uid']);
					} else {
						$tyltoplist_userlink = build_profile_link($tyltoplist_username, $data['uid']);
					}
					$tlTable = $tlTable . '<tr><td class="trow1" valign="middle" align="center">' . $iPlace . '</td><td class="trow1" valign="middle"><a href="' . $mybb->settings['bburl'] . '/showthread.php?tid='.$data['tid'].'&amp;pid='.$data['pid'].'#pid'.$data['pid'] . '"><strong>' . htmlspecialchars_uni($parser->parse_badwords($data['subject'])) . '</strong></a></td><td class="trow1" valign="middle" align="center">' . $data['likes'] . '</td><td class="trow1" valign="middle" align="right">' . $tyltoplist_userlink . '</td></tr>';
					$iPlace++;
				}			
				eval("\$tyltoplist_stats = \"".$templates->get("tyltoplist_stats_view")."\";");
				return $tyltoplist_stats;
			}		
		}
	}
}

function tyltoplist_online(&$plugin_array)
{
	global $db, $lang;
	$lang->load("tyltoplist");
	if(my_strpos($plugin_array['user_activity']['location'],'tyltoplist.php')){
		$plugin_array['location_name'] = $lang->sprintf($db->escape_string($lang->tyltoplist_online), '<a href="' . $mybb->settings['bburl'] . '/tyltoplist.php">TYL-Toplist</a>');
	}
}
?>
