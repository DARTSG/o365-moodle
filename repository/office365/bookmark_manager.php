<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Bookmark manager for Microsoft 365 repository.
 *
 * This page allows users to manage their folder bookmarks.
 *
 * @package repository_office365
 * @copyright 2024 Microsoft, Inc.
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->libdir . '/tablelib.php');

require_login();

$action = optional_param('action', 'list', PARAM_ALPHA);
$path = optional_param('path', '', PARAM_RAW);
$title = optional_param('title', '', PARAM_TEXT);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$PAGE->set_url('/repository/office365/bookmark_manager.php');
$PAGE->set_title(get_string('bookmarks', 'repository_office365'));
$PAGE->set_heading(get_string('bookmarks', 'repository_office365'));

// Get repository instance.
$repositories = repository::get_instances(['type' => 'office365', 'currentcontext' => $context]);
if (empty($repositories)) {
    throw new moodle_exception('error', 'repository_office365');
}
$repo = reset($repositories);

// Process actions.
if ($action === 'add' && !empty($path) && !empty($title) && confirm_sesskey()) {
    $repo->add_bookmark($path, $title);
    redirect($PAGE->url, get_string('bookmarkadded', 'repository_office365'), null, \core\output\notification::NOTIFY_SUCCESS);
} else if ($action === 'remove' && !empty($path) && confirm_sesskey()) {
    $repo->remove_bookmark($path);
    redirect($PAGE->url, get_string('bookmarkremoved', 'repository_office365'), null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();

// Display bookmarks.
echo $OUTPUT->heading(get_string('bookmarks', 'repository_office365'), 2);
$bookmarks = $repo->get_bookmarks();

if (empty($bookmarks)) {
    echo $OUTPUT->notification(get_string('nobookmarks', 'repository_office365'), 'info');
} else {
    echo html_writer::tag('p', get_string('bookmarkhelp', 'repository_office365'));
    
    $table = new html_table();
    $table->head = [
        get_string('title'),
        get_string('path'),
        get_string('actions'),
    ];
    
    foreach ($bookmarks as $bookmark) {
        $removeurl = new moodle_url($PAGE->url, [
            'action' => 'remove',
            'path' => $bookmark['path'],
            'sesskey' => sesskey(),
        ]);
        
        $removelink = html_writer::link(
            $removeurl,
            get_string('removebookmark', 'repository_office365'),
            ['class' => 'btn btn-sm btn-danger']
        );
        
        $table->data[] = [
            s($bookmark['title']),
            html_writer::tag('code', s($bookmark['path'])),
            $removelink,
        ];
    }
    
    echo html_writer::table($table);
}

// Display recently visited paths.
echo $OUTPUT->heading(get_string('recentfolders', 'repository_office365'), 2);
$recent = get_user_preferences('repository_office365_recent', '', $USER->id);
$recentpaths = empty($recent) ? [] : json_decode($recent, true);

if (empty($recentpaths) || !is_array($recentpaths)) {
    echo $OUTPUT->notification(get_string('norecentfolders', 'repository_office365'), 'info');
} else {
    $table = new html_table();
    $table->head = [
        get_string('folder', 'repository_office365'),
        get_string('path'),
        get_string('lastvisited', 'repository_office365'),
        get_string('actions'),
    ];
    
    foreach ($recentpaths as $recent) {
        // Check if already bookmarked.
        $isbookmarked = false;
        foreach ($bookmarks as $bookmark) {
            if ($bookmark['path'] === $recent['path']) {
                $isbookmarked = true;
                break;
            }
        }
        
        if ($isbookmarked) {
            $actionlink = html_writer::tag('span', get_string('bookmarked', 'repository_office365'), ['class' => 'badge badge-success']);
        } else {
            $addurl = new moodle_url($PAGE->url, [
                'action' => 'add',
                'path' => $recent['path'],
                'title' => $recent['title'],
                'sesskey' => sesskey(),
            ]);
            
            $actionlink = html_writer::link(
                $addurl,
                get_string('addbookmark', 'repository_office365'),
                ['class' => 'btn btn-sm btn-primary']
            );
        }
        
        $table->data[] = [
            s($recent['title']),
            html_writer::tag('code', s($recent['path'])),
            userdate($recent['timestamp'], get_string('strftimedatetime')),
            $actionlink,
        ];
    }
    
    echo html_writer::table($table);
}

// Add bookmark form.
echo $OUTPUT->heading(get_string('addbookmark', 'repository_office365'), 3);
echo html_writer::start_tag('form', ['method' => 'post', 'action' => $PAGE->url]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'add']);

echo html_writer::start_div('form-group');
echo html_writer::label(get_string('name', 'repository_office365'), 'title');
echo html_writer::empty_tag('input', [
    'type' => 'text',
    'name' => 'title',
    'id' => 'title',
    'class' => 'form-control',
    'required' => 'required',
    'placeholder' => 'e.g., My Project Files',
]);
echo html_writer::end_div();

echo html_writer::start_div('form-group');
echo html_writer::label(get_string('path'), 'path');
echo html_writer::empty_tag('input', [
    'type' => 'text',
    'name' => 'path',
    'id' => 'path',
    'class' => 'form-control',
    'required' => 'required',
    'placeholder' => 'e.g., /my/01ABCDEF123456789, /teams/abc-123-def',
]);
echo html_writer::tag('small', get_string('bookmarkpathhelp', 'repository_office365'), ['class' => 'form-text text-muted']);
echo html_writer::end_div();

echo html_writer::tag('button', get_string('addbookmark', 'repository_office365'), [
    'type' => 'submit',
    'class' => 'btn btn-primary',
]);
echo html_writer::end_tag('form');

if (!empty($returnurl)) {
    echo html_writer::tag('div', 
        html_writer::link($returnurl, get_string('back'), ['class' => 'btn btn-secondary mt-3']),
        ['class' => 'mt-3']
    );
}

echo $OUTPUT->footer();
