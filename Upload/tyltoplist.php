<?php
/*
*   Main page for 'TopList AddOn für THX/Like' plugin for MyBB 1.8
*   Copyright © 2019 Svepu
*   Last change: 2022-01-31 - v2.0
*/

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'tyltoplist.php');

require_once "./global.php";

$lang->load("tyltoplist");

if ($mybb->settings['tyltoplist_enable'] != 1 || $mybb->settings['tyltoplist_fids'] == '-1')
{
    error($lang->tyltoplist_disabled, $lang->tyltoplist);
}

if (!is_member($mybb->settings['tyltoplist_group']))
{
    error_no_permission();
}

switch ($mybb->settings['tyltoplist_show'])
{
    case 1:
        require_once MYBB_ROOT . "inc/plugins/tyltoplist.php";

        $tlprefix = $mybb->settings['g33k_thankyoulike_thankslike'] == "thanks" ? $lang->tyltoplist_table_prefix_thanks : $lang->tyltoplist_table_prefix_likes;

        $content = tyltoplist_build_rows();
        if (!is_array($content))
        {
            error();
        }

        $counter = $mybb->settings['tyltoplist_limit'];
        if ($content['counter'] && in_array($content['counter'], range(1, $counter)))
        {
            $counter = $content['counter'];
        }

        $lang->tyltoplist_header = $db->escape_string($lang->sprintf($lang->tyltoplist_header, (int)$counter, $tlprefix));
        $lang->tyltoplist_header_desc = $db->escape_string($lang->sprintf($lang->tyltoplist_header_desc, $tlprefix));

        $tlCopyright = '<span style="float: right; font-size: 0.75em;">TYL-TopList created by <a href="https://github.com/SvePu" target="_blank">SvePu</a></span>';

        $tlTable = $content['rows'];

        add_breadcrumb($db->escape_string($lang->tyltoplist));
        add_breadcrumb($db->escape_string($lang->sprintf($lang->tyltoplist_header, $mybb->settings['tyltoplist_limit'], $tlprefix)));
        eval("\$tyltoplist = \"" . $templates->get("tyltoplist_page_view") . "\";");
        output_page($tyltoplist);
        break;
    case 2:
        redirect("index.php", $db->escape_string($lang->tyltoplist_redirect_desc_b), $db->escape_string($lang->tyltoplist_redirect_title), true);
        break;
    case 3:
        redirect("stats.php", $db->escape_string($lang->tyltoplist_redirect_desc_f), $db->escape_string($lang->tyltoplist_redirect_title), true);
        break;
}
