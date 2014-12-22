<?php
/*
	Main plugin file for 'TopList AddOn für THX/Like' plugin for MyBB 1.8
	Copyright © 2014 Svepu
	Last change: 2014-12-22
*/

if(!defined('IN_MYBB')) {
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

if(my_strpos($_SERVER['PHP_SELF'], 'misc.php'))
{
	global $templatelist;
	if(isset($templatelist)){$templatelist .= ',';}
	$templatelist .= 'tyltoplist_view,tyltoplist_disabled';
}
if(my_strpos($_SERVER['PHP_SELF'], 'stats.php'))
{
	global $templatelist;
	if(isset($templatelist)){$templatelist .= ',';}
	$templatelist .= 'tyltoplist_stats_view';
}

$plugins->add_hook('misc_start','tyltoplist');
$plugins->add_hook('stats_start','tyltoplist_stats');
$plugins->add_hook('build_friendly_wol_location_end', 'tyltoplist_online');

function tyltoplist_info() {
	global $lang, $db;
	
	$lang->load("tyltoplist");

	return array(
		"name" 			=> $db->escape_string($lang->plugin_name),
		"description" 	=> $db->escape_string($lang->plugin_desc),
		"website"		=> 'https://github.com/SvePu/TYL-TopList',
		"author"		=> 'SvePu',
		"authorsite"	=> 'https://github.com/SvePu',
		"version"		=> '1.8',
		"compatibility"	=> '18*'
	);
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
        "template" => "<html><head><title>{\$lang->tyltoplist_header} {\$mybb->settings[\'tyltoplist_limit\']} {\$tlprefix} - {\$mybb->settings[\'bbname\']}</title>{\$headerinclude}</head><body>{\$header}<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" with=\"100%\"><thead><tr><td class=\"thead\" colspan=\"4\"><div><strong>{\$lang->tyltoplist_header} {\$mybb->settings[\'tyltoplist_limit\']} {\$tlprefix}</strong><br /><div class=\"smalltext\">{\$lang->tyltoplist_desc} {\$tlprefix}</div></div></td></tr></thead><tbody><tr><td class=\"tcat\" width=\"5%\" style=\"text-align:center;\"><strong>{\$lang->table_header_place}</strong></td><td class=\"tcat\" width=\"80%\"><strong>{\$lang->table_header_post}</strong></td><td class=\"tcat\" width=\"5%\" style=\"text-align:center;\"><strong>{\$tlprefix}</strong></td><td class=\"tcat\" width=\"10%\" style=\"text-align:right;\"><strong>{\$lang->table_header_autor}</strong></td></tr>{\$tlTable}<tr><td class=\"tfoot\" colspan=\"4\">{\$tlCopyright}</td></tr></tbody></table>{\$footer}</body></html>",
				"sid" => -2
	);
	$db->insert_query("templates", $templatearray);
	
	$templatearray = array(
        "title" => "tyltoplist_disabled",
        "template" => "<html><head><title>{\$lang->tyltoplist_header} {\$mybb->settings[\'tyltoplist_limit\']} {\$tlprefix} - {\$mybb->settings[\'bbname\']}</title>{\$headerinclude}</head><body>{\$header}<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" with=\"100%\"><thead><tr><td class=\"thead\"><div><strong>{\$lang->tyltoplist_header} {\$mybb->settings[\'tyltoplist_limit\']} {\$tlprefix} - Info</strong></div></td></tr></thead><tbody><tr></tr><td class=\"trow1\"><div style=\"padding: 15px 5px;\">{\$lang->tyltoplist_disabled}</div></td><tr><td class=\"tfoot\"></td></tr></tbody></table>{\$footer}</body></html>",
				"sid" => -2
	);
	$db->insert_query("templates", $templatearray);
	
	$templatearray = array(
        "title" => "tyltoplist_stats_view",
        "template" => "<br/><table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" with=\"100%\"><thead><tr><td class=\"thead\" colspan=\"4\"><div><strong>{\$lang->tyltoplist_header} {\$mybb->settings[\'tyltoplist_limit\']} {\$tlprefix}</strong><br /><div class=\"smalltext\">{\$lang->tyltoplist_desc} {\$tlprefix}</div></div></td></tr></thead><tbody><tr><td class=\"tcat\" width=\"5%\" style=\"text-align:center;\"><strong>{\$lang->table_header_place}</strong></td><td class=\"tcat\" width=\"80%\"><strong>{\$lang->table_header_post}</strong></td><td class=\"tcat\" width=\"5%\" style=\"text-align:center;\"><strong>{\$tlprefix}</strong></td><td class=\"tcat\" width=\"10%\" style=\"text-align:right;\"><strong>{\$lang->table_header_autor}</strong></td></tr>{\$tlTable}</tbody></table>",
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
        'optionscode'  	=> 'text',
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
	$db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name='tyltoplist_settings'");
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='tyltoplist_enable'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='tyltoplist_limit'");
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='tyltoplist_styled_usernames'");
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='tyltoplist_groupselect'");
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='tyltoplist_fidsout'");
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='tyltoplist_show_in_stats'");
	rebuild_settings();
	
	require_once MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("stats", '#{\$tyltoplist_stats}(\r?)\n#', "", 0);
}

function tyltoplist()
{
	global $mybb;
	
		if(isset($mybb->input['action']) && ($mybb->input['action'] == "tyltoplist"))
		{
			if (is_member($mybb->settings['tyltoplist_groupselect']) OR ($mybb->settings['tyltoplist_groupselect'] == "-1")){
				
				if($mybb->settings['tyltoplist_show_in_stats'] != 1){
					global $settings, $db, $templates, $theme, $headerinclude, $header, $footer, $lang;
					
					$lang->load("tyltoplist");
					
					if ($settings['g33k_thankyoulike_thankslike'] == "thanks"){
						$tlprefix = $lang->tyltoplist_table_prefix_thanks;
					} else {
						$tlprefix = $lang->tyltoplist_table_prefix_likes;
					}
					
					if ($settings['tyltoplist_limit'] < 1){
						$settings['tyltoplist_limit'] = 20;
					}
					
					if ($settings['tyltoplist_enable'] == 1 && $settings['tyltoplist_fidsout'] != -1){
						require_once MYBB_ROOT."inc/class_parser.php";
						$tlparser = new postParser();
						$tlparser_options = array("filter_badwords" => 1);
						$tlTable = "";
						$tlCopyright = '<span style="float: right; font-size: 0.75em;">TYL-TopList created by <a href="https://github.com/SvePu" target="_blank">SvePu</a></span>';
						$tyltoplist_unviewwhere = "";
						$tyltoplist_unviewable = get_unviewable_forums();
						if($tyltoplist_unviewable)
						{
							$tyltoplist_unviewwhere = "AND p.fid NOT IN ({$tyltoplist_unviewable})";
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
							$username = htmlspecialchars_uni($data['username']);
							if ($settings['tyltoplist_styled_usernames'] == 1){
								$userlink = build_profile_link(format_name($username, $data['usergroup'], $data['displaygroup']), $data['uid']);
							} else {
								$userlink = build_profile_link($username, $data['uid']);
							}
							$trow = alt_trow();
							$tlTable = $tlTable . '<tr><td class="' . $trow . '" valign="middle" align="center">' . $iPlace . '</td><td class="' . $trow . '" valign="middle"><a href="' . $mybb->settings['bburl'] . '/showthread.php?tid='.$data['tid'].'&amp;pid='.$data['pid'].'#pid'.$data['pid'] . '">' . htmlspecialchars_uni($tlparser->parse_message($data['subject'], $tlparser_options)) . '</a></td><td class="' . $trow . '" valign="middle" align="center">' . $data['likes'] . '</td><td class="' . $trow . '" valign="middle" align="right">' . $userlink . '</td></tr>';
							$iPlace++;
						}						
						add_breadcrumb($lang->tyltoplist_header.' '.$settings['tyltoplist_limit'].' '.$tlprefix);
						eval("\$tyltoplist = \"".$templates->get("tyltoplist_view")."\";");
					} else {						
						add_breadcrumb($lang->tyltoplist_header.' '.$settings['tyltoplist_limit'].' '.$tlprefix.' - Info');
						eval("\$tyltoplist = \"".$templates->get("tyltoplist_disabled")."\";");
					
					}
					output_page($tyltoplist);					
					exit();
				} else {
					global $lang;
					$lang->load("tyltoplist");
					redirect("stats.php", $lang->tyltoplist_redirect_desc, $lang->tyltoplist_redirect_title, $force_redirect=true);					
				}
			} else {
				error_no_permission();
			}
		}
}

function tyltoplist_stats()
{
	global $mybb;
	
	if ($mybb->settings['tyltoplist_show_in_stats'] == 1){
	
		if(is_member($mybb->settings['tyltoplist_groupselect']) OR ($mybb->settings['tyltoplist_groupselect'] == "-1")){
			global $settings, $db,$templates,$theme,$lang,$parser,$tyltoplist_stats;
			
			$lang->load("tyltoplist");
			
			if ($settings['g33k_thankyoulike_thankslike'] == "thanks"){
				$tlprefix = $lang->tyltoplist_table_prefix_thanks;
			} else {
				$tlprefix = $lang->tyltoplist_table_prefix_likes;
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
						$tyltoplist_userlink = build_profile_link($username, $data['uid']);
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
	global $lang;
	$lang->load("tyltoplist");
	if(my_strpos($plugin_array['user_activity']['location'],'misc.php?action=tyltoplist')){
		$plugin_array['location_name'] = $lang->tyltoplist_online;
	}
}
?>