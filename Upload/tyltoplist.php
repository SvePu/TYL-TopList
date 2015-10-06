<?php
/*
*	Main page for 'TopList AddOn für THX/Like' plugin for MyBB 1.8
*	Copyright © 2015 Svepu
*	Last change: 2015-10-06 - v 1.9.3
*/

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'tyltoplist.php');

$templatelist = "tyltoplist_view,tyltoplist_disabled";

require_once "./global.php";

if (is_member($mybb->settings['tyltoplist_groupselect']) OR ($mybb->settings['tyltoplist_groupselect'] == "-1"))
{				
	if($mybb->settings['tyltoplist_show_where'] == 1)
	{		
		$lang->load("tyltoplist");
		
		if ($settings['g33k_thankyoulike_thankslike'] == "thanks")
		{
			$tlprefix = $db->escape_string($lang->tyltoplist_table_prefix_thanks);
		} 
		else
		{
			$tlprefix = $db->escape_string($lang->tyltoplist_table_prefix_likes);
		}
		
		if ($settings['tyltoplist_limit'] < 1)
		{
			$settings['tyltoplist_limit'] = 20;
		}
		
		if ($settings['tyltoplist_enable'] == 1 && $settings['tyltoplist_fidsout'] != -1)
		{
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
			while ($data = $db->fetch_array($tul))
			{
				$tyltoplist_username = htmlspecialchars_uni($data['username']);
				if ($settings['tyltoplist_styled_usernames'] == 1)
				{
					$tyltoplist_userlink = build_profile_link(format_name($tyltoplist_username, $data['usergroup'], $data['displaygroup']), $data['uid']);
				}
				else
				{
					$tyltoplist_userlink = build_profile_link($tyltoplist_username, $data['uid']);
				}
				$trow = alt_trow();
				$tlTable = $tlTable . '<tr><td class="' . $trow . '" valign="middle" align="center">' . $iPlace . '</td><td class="' . $trow . '" valign="middle"><a href="' . $mybb->settings['bburl'] . '/showthread.php?tid='.$data['tid'].'&amp;pid='.$data['pid'].'#pid'.$data['pid'] . '">' . htmlspecialchars_uni($tlparser->parse_message($data['subject'], $tlparser_options)) . '</a></td><td class="' . $trow . '" valign="middle" align="center">' . $data['likes'] . '</td><td class="' . $trow . '" valign="middle" align="right">' . $tyltoplist_userlink . '</td></tr>';
				$iPlace++;
			}						
			add_breadcrumb($db->escape_string($lang->tyltoplist_header).' '.$settings['tyltoplist_limit'].' '.$tlprefix);
			eval("\$tyltoplist = \"".$templates->get("tyltoplist_view")."\";");
		} 
		else
		{						
			add_breadcrumb($db->escape_string($lang->tyltoplist_header).' '.$settings['tyltoplist_limit'].' '.$tlprefix.' - Info');
			eval("\$tyltoplist = \"".$templates->get("tyltoplist_disabled")."\";");		
		}
		output_page($tyltoplist);					
		exit();
	} 
	else if ($mybb->settings['tyltoplist_show_where'] == 2)
	{
		global $lang;
		$lang->load("tyltoplist");
		redirect("index.php", $db->escape_string($lang->tyltoplist_redirect_desc_b), $db->escape_string($lang->tyltoplist_redirect_title), $force_redirect=true);					
	}
	else
	{
		global $lang;
		$lang->load("tyltoplist");
		redirect("stats.php", $db->escape_string($lang->tyltoplist_redirect_desc_f), $db->escape_string($lang->tyltoplist_redirect_title), $force_redirect=true);					
	}
}
else
{
	error_no_permission();
}
?>