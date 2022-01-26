<?php
/*
    Main plugin file for 'TopList AddOn für THX/Like' plugin for MyBB 1.8
    Copyright © 2019 Svepu
    Last change: 2022-01-25 - v2.0
*/

if(!defined('IN_MYBB'))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

if(defined('IN_ADMINCP'))
{
    $plugins->add_hook("admin_config_settings_begin",'tyltoplist_settings');
}
else
{
    $plugins->add_hook('global_start','tyltoplist_hooks');
    $plugins->add_hook('build_friendly_wol_location_end', 'tyltoplist_online');
}

function tyltoplist_info() {
    global $mybb, $db, $lang;

    $lang->load("config_tyltoplist");

    $info = array(
        "name"          =>  $db->escape_string($lang->tyltoplist),
        "description"   =>  $db->escape_string($lang->tyltoplist_desc),
        "website"       =>  'https://github.com/SvePu/TYL-TopList',
        "author"        =>  'SvePu',
        "authorsite"    =>  'https://github.com/SvePu',
        "codename"      =>  'tyltoplist',
        "version"       =>  '2.0',
        "compatibility" =>  '18*'
    );

    $info_desc = '';
    $gid_result = $db->simple_select('settinggroups', 'gid', "name = 'tyltoplist'", array('limit' => 1));
    $settings_group = $db->fetch_array($gid_result);
    if(!empty($settings_group['gid']))
    {
        $info_desc .= "<span style=\"font-size: 0.9em;\">(~<a href=\"index.php?module=config-settings&action=change&gid=".$settings_group['gid']."\"> ".$db->escape_string($lang->setting_group_tyltoplist)." </a>~)</span>";
    }

    if($info_desc != '')
    {
        $info['description'] = $info_desc.'<br />'.$info['description'];
    }

    return $info;
}

function tyltoplist_install()
{
    global $db, $mybb, $lang;

    $lang->load("config_tyltoplist");

    if(!isset($mybb->settings['g33k_thankyoulike_enabled']) || !$db->table_exists('g33k_thankyoulike_thankyoulike'))
    {
        flash_message("{$lang->mainplugin_req}", "error");
        admin_redirect("index.php?module=config-plugins");
    }

    if(!$db->field_exists('tyl_pnumtyls', 'posts'))
    {
        flash_message("{$lang->update_mainplugin_req}", "error");
        admin_redirect("index.php?module=config-plugins");
    }

    // Templates
    $templatearray = array(
    'page_view' => '<html>
    <head>
        <title>{$lang->tyltoplist_header} - {$mybb->settings[\'bbname\']}</title>
        {$headerinclude}
    </head>
    <body>
        {$header}
        <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder" width="100%">
            <thead>
                <tr>
                    <td class="thead" colspan="4">
                        <div><strong>{$lang->tyltoplist_header}</strong></div>
                        <div class="smalltext">{$lang->tyltoplist_header_desc}</div>
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="tcat" width="5%" style="text-align:center;"><strong>#</strong></td>
                    <td class="tcat" width="80%"><strong>{$lang->table_header_post}</strong></td>
                    <td class="tcat" width="5%" style="text-align:center;"><strong>{$lang->table_header_number}</strong></td>
                    <td class="tcat" width="10%" style="text-align:right;"><strong>{$lang->table_header_autor}</strong></td>
                </tr>
                {$tlTable}
                <tr>
                    <td class="tfoot" colspan="4">{$tlCopyright}</td>
                </tr>
            </tbody>
        </table>
        {$footer}
    </body>
</html>',
    'row' => '<tr>
    <td class="{$altbg}" style="text-align:center;"><span{$styleclass}>{$i}</span></td>
    <td class="{$altbg}"><span{$styleclass}><a href="{$postlink}"><strong>{$postsubject}</strong></a></span></td>
    <td class="{$altbg}" style="text-align:center;"><span{$styleclass}>{$likes}</span></td>
    <td class="{$altbg}" style="text-align:right;"><span{$styleclass}>{$userlink}</span></td>
</tr>',
    'row_empty' => '<tr>
    <td class="trow1" colspan="4"><span{$styleclass}>{$lang->tyltoplist_no_entries}</span></td>
</tr>',
    'index_view' => '<tr>
    <td style="padding: 0;">
        <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" width="100%">
            <thead>
                <tr>
                    <td class="tcat" colspan="4" style="border-radius: 0;"><span><strong class="smalltext">{$lang->tyltoplist_header}</strong>&nbsp;<span class="smalltext">[{$lang->tyltoplist_header_desc}]</span></span></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="tfoot" width="5%" style="text-align:center;padding: 2px 6px;"><span class="smalltext">#</span></td>
                    <td class="tfoot" width="80%" style="padding: 2px 6px;"><span class="smalltext">{$lang->table_header_post}</span></td>
                    <td class="tfoot" width="5%" style="text-align:center;padding: 2px 6px;"><span class="smalltext">{$lang->table_header_number}</span></td>
                    <td class="tfoot" width="10%" style="text-align:right;padding: 2px 6px;"><span class="smalltext">{$lang->table_header_autor}</span></td>
                </tr>
                {$tlTable}
            </tbody>
        </table>
    </td>
</tr>',
    'stats_view' => '<br/>
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder" width="100%">
    <thead>
        <tr>
            <td class="thead" colspan="4">
                <div><strong>{$lang->tyltoplist_header}</strong></div>
                <div class="smalltext">{$lang->tyltoplist_header_desc}</div>
            </td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="tcat" width="5%" style="text-align:center;">
                <strong>#</strong>
            </td>
            <td class="tcat" width="80%">
                <strong>{$lang->table_header_post}</strong>
            </td>
            <td class="tcat" width="5%" style="text-align:center;">
                <strong>{$lang->table_header_number}</strong>
            </td>
            <td class="tcat" width="10%" style="text-align:right;">
                <strong>{$lang->table_header_autor}</strong>
            </td>
        </tr>
        {$tlTable}
    </tbody>
</table>'
    );

    $group = array(
        'prefix' => $db->escape_string('tyltoplist'),
        'title' => $db->escape_string('TYL-TopList')
    );

    $query = $db->simple_select('templategroups', 'prefix', "prefix='{$group['prefix']}'");

    if($db->fetch_field($query, 'prefix'))
    {
        $db->update_query('templategroups', $group, "prefix='{$group['prefix']}'");
    }
    else
    {
        $db->insert_query('templategroups', $group);
    }

    $query = $db->simple_select('templates', 'tid,title,template', "sid=-2 AND (title='{$group['prefix']}' OR title LIKE '{$group['prefix']}=_%' ESCAPE '=')");

    $templates = $duplicates = array();

    while($row = $db->fetch_array($query))
    {
        $title = $row['title'];
        $row['tid'] = (int)$row['tid'];

        if(isset($templates[$title]))
        {
            $duplicates[] = $row['tid'];
            $templates[$title]['template'] = false;
        }
        else
        {
            $templates[$title] = $row;
        }
    }

    if($duplicates)
    {
        $db->delete_query('templates', 'tid IN ('.implode(",", $duplicates).')');
    }

    foreach($templatearray as $name => $code)
    {
        if(strlen($name))
        {
            $name = "tyltoplist_{$name}";
        }
        else
        {
            $name = "tyltoplist";
        }

        $template = array(
            'title' => $db->escape_string($name),
            'template' => $db->escape_string($code),
            'version' => 1,
            'sid' => -2,
            'dateline' => TIME_NOW
        );

        if(isset($templates[$name]))
        {
            if($templates[$name]['template'] !== $code)
            {
                $db->update_query('templates', array('version' => 0), "title='{$template['title']}'");
                $db->update_query('templates', $template, "tid={$templates[$name]['tid']}");
            }
        }
        else
        {
            $db->insert_query('templates', $template);
        }

        unset($templates[$name]);
    }

    foreach($templates as $name => $row)
    {
        $db->delete_query('templates', "title='{$db->escape_string($name)}'");
    }


    // Settings
    $group = array(
        'name' => 'tyltoplist',
        'title' => $db->escape_string($lang->setting_group_tyltoplist),
        'description' => $db->escape_string($lang->setting_group_tyltoplist_desc),
        'isdefault' => 0
    );

    $query = $db->simple_select('settinggroups', 'gid', "name='tyltoplist'");

    if($gid = (int)$db->fetch_field($query, 'gid'))
    {
        $db->update_query('settinggroups', $group, "gid='{$gid}'");
    }
    else
    {
        $query = $db->simple_select('settinggroups', 'MAX(disporder) AS disporder');
        $disporder = (int)$db->fetch_field($query, 'disporder');

        $group['disporder'] = ++$disporder;

        $gid = (int)$db->insert_query('settinggroups', $group);
    }

    $settings = array(
        'enable' => array(
            'optionscode' => 'yesno',
            'value' => 1
            ),
        'limit' => array(
            'optionscode' => 'numeric \n min=0',
            'value' => 20
            ),
        'usernames' => array(
            'optionscode' => 'yesno',
            'value' => 1
            ),
        'group' => array(
            'optionscode' => 'groupselect',
            'value' => '-1',
            ),
        'fids' => array(
            'optionscode' => 'forumselect',
            'value' => '',
            ),
        'show' => array(
            'optionscode' => 'radio \n 1='.$db->escape_string($lang->setting_tyltoplist_show_1).' \n 2='.$db->escape_string($lang->setting_tyltoplist_show_2).' \n 3='.$db->escape_string($lang->setting_tyltoplist_show_3),
            'value' => 1,
            )
        );

    $disporder = 0;

    foreach($settings as $key => $setting)
    {
        $key = "tyltoplist_{$key}";

        $setting['name'] = $db->escape_string($key);

        $lang_var_title = "setting_{$key}";
        $lang_var_description = "setting_{$key}_desc";

        $setting['title'] = $db->escape_string($lang->{$lang_var_title});
        $setting['description'] = $db->escape_string($lang->{$lang_var_description});
        $setting['disporder'] = $disporder;
        $setting['gid'] = $gid;

        $db->insert_query('settings', $setting);
        ++$disporder;
    }

    rebuild_settings();

    require_once MYBB_ROOT."inc/adminfunctions_templates.php";
    find_replace_templatesets("index_boardstats", '#{\$forumstats}(\r?)\n#', "{\$forumstats}\n{\$tyltoplist}\n");
    find_replace_templatesets("stats", '#{\$footer}(\r?)\n#', "{\$tyltoplist}\n{\$footer}\n");
}

function tyltoplist_is_installed()
{
    global $mybb;
    if(isset($mybb->settings['tyltoplist_enable']))
    {
        return true;
    }
    return false;
}

function tyltoplist_uninstall()
{
    global $db;

    $db->delete_query('templategroups', "prefix='tyltoplist'");
    $db->delete_query('templates', "title='tyltoplist' OR title LIKE 'tyltoplist_%'");

    $result = $db->simple_select('settinggroups', 'gid', "name = 'tyltoplist'", array('limit' => 1));
    $tyl_group = $db->fetch_array($result);

    if(!empty($tyl_group['gid']))
    {
        $db->delete_query('settinggroups', "gid='{$tyl_group['gid']}'");
        $db->delete_query('settings', "gid='{$tyl_group['gid']}'");
        rebuild_settings();
    }

    require_once MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("index_boardstats", '#{\$tyltoplist}(\r?)\n#', "", 0);
    find_replace_templatesets("stats", '#{\$tyltoplist}(\r?)\n#', "", 0);
}

function tyltoplist_activate()
{
    global $db;
    $db->update_query("settings", array("value" => 1), "name='tyltoplist_enable'", 1);
    rebuild_settings();
}

function tyltoplist_deactivate()
{
    global $db;
    $db->update_query("settings", array("value" => 0), "name='tyltoplist_enable'", 1);
    rebuild_settings();
}

function tyltoplist_settings()
{
    global $lang;
    $lang->load('config_tyltoplist');
}

// Delete function momentary not included by default in MyBB.
function tyltoplist_delete()
{
    return array(
        'inc/languages/{lang}/admin/config_tyltoplist.lang.php',
        'inc/languages/{lang}/tyltoplist.lang.php',
        'inc/plugins/tyltoplist.php',
        'tyltoplist.php',
    );
}

function tyltoplist_hooks()
{
    global $plugins, $mybb;

    if($mybb->settings['tyltoplist_enable'] != 1 || !is_member($mybb->settings['tyltoplist_group']) || $mybb->settings['tyltoplist_fids'] == '-1')
    {
        return;
    }

    if(defined('THIS_SCRIPT'))
    {
        global $templatelist;

        if(isset($templatelist))
        {
            $templatelist .= ',';
        }

        switch($mybb->settings['tyltoplist_show'])
        {
            case 1:
                if(THIS_SCRIPT == 'tyltoplist.php')
                {
                    $templatelist .= 'tyltoplist_page_view, tyltoplist_row, tyltoplist_row_empty';
                }
                break;
            case 2:
                if(THIS_SCRIPT == 'index.php')
                {
                    $plugins->add_hook('index_start','tyltoplist_stats');
                    $templatelist .= 'tyltoplist_index_view, tyltoplist_row, tyltoplist_row_empty';
                }
                break;
            case 3:
                if(THIS_SCRIPT == 'stats.php')
                {
                    $plugins->add_hook('stats_start','tyltoplist_stats');
                    $templatelist .= 'tyltoplist_stats_view, tyltoplist_row, tyltoplist_row_empty';
                }
                break;
        }
    }
}

function tyltoplist_stats()
{
    global $mybb, $db, $templates, $theme, $lang, $tyltoplist;

    $lang->load("tyltoplist");
    $tyltoplist = "";
    $tlprefix = $mybb->settings['g33k_thankyoulike_thankslike'] == "thanks" ? $lang->tyltoplist_table_prefix_thanks : $lang->tyltoplist_table_prefix_likes;

    $lang->tyltoplist_header = $db->escape_string($lang->sprintf($lang->tyltoplist_header, (int)$mybb->settings['tyltoplist_limit'], $tlprefix));
    $lang->tyltoplist_header_desc = $db->escape_string($lang->sprintf($lang->tyltoplist_header_desc, $tlprefix));

    $tlTable = tyltoplist_build_rows();

    switch($mybb->settings['tyltoplist_show'])
    {
        case 2:
            eval("\$tyltoplist = \"".$templates->get("tyltoplist_index_view")."\";");
            break;
        case 3:
            eval("\$tyltoplist = \"".$templates->get("tyltoplist_stats_view")."\";");
            break;
    }
}

function tyltoplist_build_rows()
{
    global $mybb, $db, $templates, $lang;

    $limit = (int)$mybb->settings['tyltoplist_limit'];
    if ($mybb->settings['tyltoplist_limit'] < 1)
    {
        $limit = 20;
    }

    $where = "WHERE visible=1 AND tyl_pnumtyls > 0";
    $unviewable = get_unviewable_forums(true);
    if($unviewable)
    {
        $where .= " AND fid NOT IN ($unviewable)";
    }
    $inactive = get_inactive_forums();
    if($inactive)
    {
        $where .= " AND fid NOT IN ($inactive)";
    }

    $onlyusfids = array();
    $group_permissions = forum_permissions();
    foreach($group_permissions as $fid => $forum_permissions)
    {
        if(isset($forum_permissions['canonlyviewownthreads']) && $forum_permissions['canonlyviewownthreads'] == 1)
        {
            $onlyusfids[] = $fid;
        }
    }
    if(!empty($onlyusfids))
    {
        $where .= " AND ((fid IN(".implode(',', $onlyusfids).") AND uid='{$mybb->user['uid']}') OR fid NOT IN(".implode(',', $onlyusfids)."))";
    }

    if(!empty($mybb->settings['tyltoplist_fids']) && $mybb->settings['tyltoplist_fids'] != '-1')
    {
        $where .= " AND fid NOT IN ({$mybb->settings['tyltoplist_fids']})";
    }

    $posts = array();
    $query = $db->query("SELECT pid FROM ".TABLE_PREFIX."posts {$where} ORDER BY tyl_pnumtyls DESC, pid ASC LIMIT 0,{$limit}");
    while($post = $db->fetch_array($query))
    {
        $posts[$post['pid']] = $post;
    }

    if(!empty($posts))
    {
        $i = 1;
        $styleclass = $mybb->settings['tyltoplist_show'] == 2 ? ' class="smalltext"' : '';

        $query = $db->query("
            SELECT p.pid, p.tyl_pnumtyls AS likes, p.subject, p.username, p.uid, p.tid, u.usergroup, u.displaygroup
            FROM ".TABLE_PREFIX."posts p
            INNER JOIN ".TABLE_PREFIX."users u ON (p.uid=u.uid)
            WHERE p.pid IN (".implode(',', array_keys($posts)).")
            ORDER BY likes DESC, p.pid ASC
        ");

        while($results = $db->fetch_array($query))
        {
            $altbg = alt_trow();
            if ($mybb->settings['tyltoplist_usernames'] == 1)
            {
                $userlink = build_profile_link(format_name(htmlspecialchars_uni($results['username']), $results['usergroup'], $results['displaygroup']), $results['uid']);
            }
            else
            {
                $userlink = build_profile_link(htmlspecialchars_uni($results['username']), $results['uid']);
            }

            $postlink = get_post_link($results['pid'], $results['tid'])."#pid".(int)$results['pid'];
            $postsubject = htmlspecialchars_uni($results['subject']);
            $likes = my_number_format((int)$results['likes']);

            eval("\$rows .= \"".$templates->get("tyltoplist_row")."\";");
            ++$i;
        }
    }
    else
    {
        eval("\$rows = \"".$templates->get("tyltoplist_row_empty")."\";");
    }

    return $rows;
}

function tyltoplist_online(&$plugin_array)
{
    global $db, $lang;
    $lang->load("tyltoplist");
    if(my_strpos($plugin_array['user_activity']['location'],'tyltoplist.php'))
    {
        $plugin_array['location_name'] = $lang->sprintf($db->escape_string($lang->tyltoplist_online), '<a href="' . $mybb->settings['bburl'] . '/tyltoplist.php">TYL-Toplist</a>');
    }
}
