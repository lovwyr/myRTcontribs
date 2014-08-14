<?php

addCSS("css/imdicteditor.css");
addJS("js/jquery.tablesorter.min.js");
addJS("js/editor.js");
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
    echo '<table id="myTable" class="tablesorter">';
    echo '<thead><tr><th>Chapter Name</th><th>Records</th></tr></thead>';
    echo '<tbody>';
    foreach (getChapterList() as $chapter_no => $chapter) {
        echo '<tr>';
        echo '<td class="dicteditor_td1">';
        echo '<a>';
        echo '<div class="test" onclick="populateED(\'#dicteditor_c1\', \'help\', \'open\')">' . $chapter['name'] . '</div>';        
        //echo '<div class="test" onclick="TestEditor(\'test\')">' . $chapter['name'] . '</div>';
        echo '</a>';     
        echo '</td>';
        echo '<td class="dicteditor_td2">' . $chapter['wordc'] . '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
    echo "</div>";
    echo "</div>";

    echo '<script type="text/javascript">';
    echo '$("#myTable").tablesorter();';
    echo '</script>';
}
