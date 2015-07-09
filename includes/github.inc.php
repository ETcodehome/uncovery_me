<?php

global $GITHUB;
$GITHUB['owner'] = 'uncovery';
$GITHUB['repo'] = 'uncovery_me'; 
$GITHUB['page'] = 'http://uncovery.me/server-features/development-status/';

// https://github.com/KnpLabs/php-github-api

function umc_github_client_connect($owner, $repo) {
    require_once '/home/includes/composer/vendor/autoload.php';
    $cache_dir = "/tmp/github-$repo-$owner-cache";
    $token_file = __DIR__ . "/github-$repo-$owner.token";
    $client = new \Github\Client(
            new \Github\HttpClient\CachedHttpClient(array('cache_dir' => $cache_dir))
    );

    $token = file_get_contents($token_file);

    $client->authenticate($token, \Github\Client::AUTH_HTTP_TOKEN);
    return $client;
}

function umc_github_issue_body($issues, $comments) {
    $table_body = '';
    foreach ($issues as $issue) {
        if (!isset($issue['pull_request'])) {
            $table_body .= "<tr class='popover' data-popover-width=700 data-placement='bottom' data-animation='pop' data-issueid='{$issue['number']}'>\n"
                . "    <td class='dt-center'>{$issue['number']}</td>\n"
                . "    <td>{$issue['title']}</td>\n"
                . "    <td class='popover-content'>\n" . umc_github_issue_details($issue, $comments) . '</td>' . "\n"
                . "    <td>";
            foreach ($issue['labels'] as $label) {
                $table_body .= "<span style='background-color: #{$label['color']}'>&nbsp;{$label['name']}&nbsp;</span> ";
            }
            $updated = trim(substr($issue['updated_at'], 0, 10));
            $table_body .= "</td>\n    <td>$updated</td>\n</tr>\n";
        }
    }
    return $table_body;
}

function umc_github_commit_body($commits) {
    $table_body = '';
    foreach ($commits as $commit) {
        $updated = substr($commit['commit']['committer']['date'], 0, 10);
        $table_body .= "    <tr>
        <td>$updated</td>
        <td>{$commit['author']['login']}</td>
        <td>{$commit['commit']['message']}</td>
    </tr>\n";
    }
    return $table_body;
}

function umc_github_link() {
    global $GITHUB;
    $repo = $GITHUB['repo'];
    $owner = $GITHUB['owner'];

    $client = umc_github_client_connect($owner, $repo);

    $out = '';

    $paginator  = new Github\ResultPager($client);

    $api = $client->api('issue');
    $parameters = array($owner, $repo, array('state' => 'open'));
    $open_issues = $paginator->fetchAll($api, 'all', $parameters);

    $api = $client->api('issue');
    $parameters = array($owner, $repo, array('state' => 'closed'));
    $closed_issues = $paginator->fetchAll($api, 'all', $parameters);

    $api = $client->api('issue');
    $parameters = array($owner, $repo, 'comments');
    $comments = $paginator->fetchAll($api, 'show', $parameters);

    $commits = $client->api('repo')->commits()->all($owner, $repo, array('sha' => 'master', 'per_page' => 100));

    $out .= '<script type="text/javascript" src="/admin/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript">
        jQuery(document).ready(function() {jQuery("#shoptable_open").dataTable( {"autoWidth": false, "order": [[ 4 ]], "paging": false, "ordering": true, "info": false});;} );
        jQuery(document).ready(function() {jQuery("#shoptable_closed").dataTable( {"autoWidth": false, "order": [[ 4 ]], "paging": false, "ordering": true, "info": false});;} );
        jQuery(document).ready(function() {jQuery("#shoptable_commits").dataTable( {"autoWidth": false, "order": [[ 0 ]], "paging": false, "ordering": true, "info": false});;} );
    </script>
';

    $tab1 = "            <table class='unc_datatables' id='shoptable_open'>
                <thead>
                    <tr><th>#</th><th>Title</th><th style='display:none;'>hidden data</th><th>Labels</th><th>Updated</th></tr>
                </thead>
                <tbody>" . umc_github_issue_body($open_issues, $comments) . "</tbody>
            </table>";
    $tab2 = "            <table class='unc_datatables' id='shoptable_closed'>
                <thead>
                    <tr><th>#</th><th>Title</th><th style='display:none;'>hidden data</th><th>Labels</th><th>Updated</th></tr>
                </thead>
                <tbody>" . umc_github_issue_body($closed_issues, $comments) . "</tbody>
            </table>";
    $tab3 = "            <table class='unc_datatables' id='shoptable_commits'>
                <thead>
                    <tr><th>Date</th><th>User</th><th>Message</th></tr>
                </thead>
                <tbody>" . umc_github_commit_body($commits) . "</tbody>
             </table>";

    $out .= umc_jquery_tabs(array('Open Issues'=>$tab1, 'Closed Issues'=>$tab2, 'Commits'=>$tab3));

    return $out;
}

function umc_github_issue_details($issue, $comments) {
    $labels = '';
    foreach ($issue['labels'] as $label) {
        $labels .= " <span style='background-color: #{$label['color']}'>&nbsp;{$label['name']}&nbsp;</span> ";
    }
    $created = substr($issue['created_at'], 0, 10);
    $updated = substr($issue['updated_at'], 0, 10);
    $body = htmlentities(nl2br($issue['body']));

    $comments_html = '';
    if ($issue['comments'] > 0) {
        $comments_html = "<tr>\n    <td colspan=5 style='text-align:center'><strong>Comments</strong></td>\n        </tr>\n";
        foreach ($comments as $comment) {
            // Skip this comment if it's not related to our issue
            if ($comment['issue_url'] != $issue['url']) {
                continue;
            }
            $comment_body = htmlentities(nl2br($comment['body']));
            $updated = substr($comment['updated_at'], 0, 10);
            $comments_html .= "        <tr>\n"
                . "            <td colspan='2' class='dt-right'><strong>{$comment['user']['login']} @ $updated:</strong></td>\n"
                . "            <td colspan=3>$comment_body</td>\n"
                . "        </tr>\n";
        }
    }

    $out = "    <table class='dataTable'>
        <tr>
            <td><strong>ID:</strong> {$issue['number']}</td>
            <td colspan=4><strong>Title:</strong> {$issue['title']}</td>
        </tr>
        <tr>
            <td><strong>Labels:</strong></td><td>$labels</td>
            <td><strong>Created by:</strong> {$issue['user']['login']}</td>
            <td><strong>Created at:</strong> $created</td>
            <td><strong>Updated at:</strong> $updated</td>
        </tr>
        <tr>
            <td><strong>Description:</strong></td>
            <td colspan=4>$body</td>
        </tr>
        $comments_html
    </table>\n";

    return $out;
}

function umc_github_wordpress_update() {
    require_once('/home/minecraft/public_html/wp-load.php'); 
    
    global $GITHUB;
    $repo = $GITHUB['repo'];
    $owner = $GITHUB['owner'];
    $page = $GITHUB['page'];
    
    $client = umc_github_client_connect($owner, $repo);
    
    $today_obj = new DateTime(NULL);
    $today_obj->modify('-1 day');
    // $date_new->setTimezone(new DateTimeZone('Asia/Hong_Kong'));    
    $today_str = $today_obj->format('Y-m-d\T00:00:00\Z');
    
    $issue_arr = array(
    );
    
    $issues = $client->api('issue')->all($owner, $repo, array('state' => 'all', 'since' => $today_str));
    if (count($issues) == 0) {
        return;
    }
    foreach ($issues as $issue) {
        if (!isset($issue['pull_request'])) {
            $labels = '';
            $issue_opened_date = substr($issue['created_at'], 0, 10);
            $issue_updated_date = substr($issue['updated_at'], 0, 10);
            foreach ($issue['labels'] as $label) {
                $labels .= " <span style='background-color: #{$label['color']}'>&nbsp;{$label['name']}&nbsp;</span> ";
            }            
            $text = "Issue No. {$issue['number']}, <a href=\"$page?action=issue_detail&amp;id={$issue['number']}\">{$issue['title']}</a> ($labels)";
            if ($issue['state'] == 'open') {
                if ($issue_opened_date == $issue_updated_date) {
                    $issue_arr['opened'][$issue['number']] = $text;
                } else {
                    $issue_arr['updated'][$issue['number']] = $text;
                }
            } else {
                $issue_arr['closed'][$issue['number']] = $text;
            }
        }
    }    

    $out = "This is a daily update on the status of the work done behind the scenes. You can see the complete status <a href=\"$page\">here</a>.\n<ul>\n"; 
    foreach ($issue_arr as $section => $lines) {
        $section_str = ucwords($section);
        $out .= "    <li><strong>Issues $section_str:</strong>\n";
        $out .= "        <ul>\n"; 
        foreach ($lines as $line) {
            $out .= "            <li>$line</li>\n";
        }
        $out .= "        </ul>\n"; 
        $out .= "    </li>\n";
    }
    $out .= "</ul>\n";
    
    $post = array(
        'comment_status' => 'open', // 'closed' means no comments.
        'ping_status' => 'closed', // 'closed' means pingbacks or trackbacks turned off
        'post_author' => 1, //The user ID number of the author.
        'post_content' => $out, //The full text of the post.
        'post_status' => 'publish', //Set the status of the new post.
        'post_title' => "Today's development updates", //The title of your post.
        'post_type' => 'post' //You may want to insert a regular post, page, link, a menu item or some custom post type
    );
    wp_insert_post($post); 
}
