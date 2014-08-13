<?php

addCSS("css/imdicteditor.css");

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$page['editDict']['title'] = 'Dictionary Editor';
$page['editDict']['parent'] = 'config';
$tab['editDict']['default'] = 'Edit Dictionary';
$tabhandler['editDict']['default'] = 'edit_dict';
$tab['editDict']['viewDict'] = 'View  Dictionary';
$tabhandler['editDict']['viewDict'] = 'view_dict';

function edit_dict() {
    
}

function view_dict() {
    echo "<div id='dicteditor_cont'>";
    echo "<div id='dicteditor_c1'>";
    echo "</div>";
    echo "<div id='dicteditor_c2'>";
    echo '<ul>';
    foreach (getChapterList() as $chapter_no => $chapter)
        echo '<li>' . mkA($chapter['name'], 'chapter', $chapter_no) . " (${chapter['wordc']} records)</li>";
    echo '</ul>';
    echo "</div>";
    echo "</div>";
}
