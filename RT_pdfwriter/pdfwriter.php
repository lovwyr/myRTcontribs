<?php

require_once('tcpdf/tcpdf.php');
require_once('FirePHPCore/FirePHP.class.php');
addCSS('css/pdf.css');
addJS("js/jquery.media.js");
$tab['row']['RowPDFWriter'] = 'PDF';
$tabhandler['row']['RowPDFWriter'] = 'WriterRowPDF';

function PDFWriter($directory, $filename) {
    $firephp = FirePHP::getInstance(true);
    $firephp->log($directory, 'pdf_dir');
    $firephp->log($filename, 'pdf_file');

    $pdf_files = array_diff(scandir($directory), array('..', '.'));
    $firephp->log($pdf_files, 'pdf_files');

    echo '<div id="wrapper">
            <div id="steuerung"><ol>';
    
    foreach ($pdf_files as $pdf_file) {
        echo '<li>';
        $file = $directory . "/" . $pdf_file;
        echo date("d. M Y H:i:s", filemtime($file));
        echo '</li>';
    }

    echo '</ol>';
    echo '</div>';
    echo '<div id="zweite_spalte">';
    echo '<a class="media" href="' . $filename . '">PDF File</a> ';
    echo '</div>';
    echo '</div>';
    echo '<script type="text/javascript">';
    echo '$(\'a.media\').media({width:1024, height:1024});';
    echo '</script>';
}

function CheckDir($directory) {
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }
}

function WriterRowPDF1() {
    
}

function WriterRowPDF($row_id) {

    $firephp = FirePHP::getInstance(true);
    $options = array('maxObjectDepth' => 5,
        'maxArrayDepth' => 10,
        'maxDepth' => 10,
        'useNativeJsonEncode' => true,
        'includeLineNumbers' => true);

    $firephp->getOptions();
    $firephp->setOptions($options);
    $row = spotEntity('row', $row_id);
    $firephp->log($row, 'row');

    $link = FALSE;
    $spacer = " >> ";
    $loctrail_pdf = getLocationTrail($row['location_id'], $link, $spacer);
    $firephp->log($loctrail_pdf, '$loctrail');

    $directory = './data/' . $row['realm'] . '/' . $row['id'];
    CheckDir($directory);
    $filename = $directory . '/' . date("F_j_Y__H_i") . '.pdf';

    $pageDimension = array('500,300');
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $pageDimension, true, 'UTF-8', false);
    $pdf->SetCreator(get_current_user());
    $pdf->SetAuthor(get_current_user());
    if (empty($loctrail_pdf)) {
        $pdftitel = 'Rack Row: ' . $row['name'];
    } else {
        $pdftitel = 'Rack Row: ' . $row['name'] . " in " . $loctrail_pdf;
    }
    $pdf->SetTitle($pdftitel);
    $pdf->SetSubject('Racktables Export');
    $pdf->SetKeywords($row['realm']);
    $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $pdftitel, '', array(0, 64, 255), array(0, 64, 128));
    $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);


    $rowData = getRowInfo($row_id);
    //$firephp->log($rowData, '$rowData');
    $cellfilter = getCellFilter();
    //$firephp->log($cellfilter, '$cellfilter');
    $rackList = filterCellList(listCells('rack', $row_id), $cellfilter['expression']);
    //$firephp->log($rackList, '$rackList');
    if (!empty($rackList)) {
        foreach ($rackList as $rack) {
            //$rackData = spotEntity('rack', $rack['id']);
            //$firephp->log($rackData, '$rackData' . $rack['id']);
            $pdf->AddPage();
            $pdf->Bookmark('Rack: ' . $rack['name'] . ' Rackview', 0, 0, '', 'B', array(0, 64, 128));
            $pdf->Cell(0, 10, 'Rack: ' . $rack['name'], 0, 1, 'L');
            $rackHtml1 = renderPDFTable($rack['id']);
            $firephp->log($rackHtml1, '$rackHtml1');
            $pdf->writeHTML($rackHtml1, true, false, true, false, '');
            $pdf->lastPage();            
            $pdf->AddPage();
            $pdf->Bookmark('Rack: ' . $rack['name'] . ' Props & ZeroU', 0, 0, '', 'B', array(0, 64, 128));            
            $rackHtml2 = renderPDFTableProps($rack['id']);
            //$firephp->log($rackHtml2, '$rackHtml2');
            $pdf->writeHTML($rackHtml2, true, false, true, false, '');
            $pdf->lastPage();
        }
    } else {
        //$pdf->AddPage();        
    }

    $pdf->setPrintFooter(false);
    $pdf->addTOCPage();
    $pdf->setPrintFooter(true);
    $pdf->SetFont('times', 'B', 16);
    $pdf->MultiCell(0, 0, 'Racks', 0, 'C', 0, 1, '', '', true, 0);
    $pdf->Ln();
    $pdf->SetFont('dejavusans', '', 12);
    $pdf->addTOC(1, 'courier', '.', 'INDEX', 'B', array(128, 0, 0));
    $pdf->endTOCPage();

    $pdf->Output($filename, 'F');
    PDFWriter($directory, $filename);
}

function renderPDFTableProps($rack_id) {
    //$firephp = FirePHP::getInstance(true);
    $rackData = spotEntity('rack', $rack_id);
    $zeroUObjects = getEntityRelatives('children', 'rack', $rack_id);
    //$firephp->log($zeroUObjects, '$zeroUObjects' . $rack_id);
    if (count($zeroUObjects) > 0) {
        $tbl = "<br><table width='75%' class=rack border=0 cellspacing=0 cellpadding=1>\n";
        $tbl = $tbl . "<tr><th>Zero-U:</th></tr>\n";
        foreach ($zeroUObjects as $zeroUObject) {
            
            $tbl = $tbl. "<tr><td>";
            $tbl = $tbl. getOutputOf('printObjectDetailsForRenderRack',$zeroUObject['entity_id']);
            $tbl = $tbl. "</td></tr>\n";
        }
        $tbl = $tbl. "</table>\n";
    }
    //$firephp->log($tbl, '$tbl' . $rack_id);
    return $tbl;    
}

function renderPDFTable($rack_id) {
    $firephp = FirePHP::getInstance(true);
    $rackData = spotEntity('rack', $rack_id);
    ob_start();
    amplifyCell($rackData);
    markAllSpans($rackData);
    ob_end_clean();
    $tbl = <<<EOD
<style>
table {
    text-align: center;
    font-family: serif;
    font-size: 11pt;
}
            
table th {
    border:#000000 solid 1px;
}
            
table tr {
}

table tr td {
    border:#000000 solid 1px;
}

em {
    font-size: 4pt;
}
            
tr td{ 
    white-space:nowrap; 
}
            
div.free {
    
}

div.used {
    background-color: #8fbfff;
}   
            
</style>
            
    <table cellspacing="0" cellpadding="1" nobr="true">
    <tr>
        <th border="0" width="5%">U</th>
        <th width="40%">Front</th>
	<th width="15%">Interior</th>
        <th width="40%">Back</th>
    </tr>         
EOD;
    for ($i = $rackData['height']; $i > 0; $i--) {
        $tbl = $tbl . '<tr>';
        $tbl = $tbl . "<td>" . inverseRackUnit($i, $rackData) . "</td>";
        
        for ($locidx = 0; $locidx < 3; $locidx++) {
            if (isset($rackData[$i][$locidx]['skipped'])) {
                continue;
            }
            $state = $rackData[$i][$locidx]['state'];
            $firephp->log($state, '$state - ' . $rack_id . " - " . $i);
            $tbl = $tbl . "<td";
            //if (isset($rackData[$i][$locidx]['hl'])) {
            //    $tbl = $tbl . $rackData[$i][$locidx]['hl'];
            //}
            //$tbl = $tbl . "'";
            if (isset($rackData[$i][$locidx]['colspan'])) {
                $tbl = $tbl . ' colspan="' . $rackData[$i][$locidx]['colspan'] . '"';
            }
            if (isset($rackData[$i][$locidx]['rowspan'])) {
                $tbl = $tbl . ' rowspan="' . $rackData[$i][$locidx]['rowspan'] . '"';
            }
            $tbl = $tbl . ">";
            
            switch ($state) {
               case 'A':
                    $tbl = $tbl . 'A';
                    break;
                case 'T':                    
                    $o = spotEntity('object', $rackData[$i][$locidx]['object_id']);
                    $firephp->log($o, '$o - ' . $rackData[$i][$locidx]['object_id']);
                    $tbl = $tbl . '<div class="used">'.$o['name'].'</div>';
                    break;
                case 'F':
                    $tbl = $tbl . '';
                    break;
                case 'U':
                    $tbl = $tbl . 'P';
                    break;
                default:
                    $tbl = $tbl . 'N';
                    break; 
            }                                    
            $tbl = $tbl . '</td>';
        }
        
        //$tbl = $tbl . "<td></td>";
        //$tbl = $tbl . "<td></td>";
        //$tbl = $tbl . "<td></td>";
        $tbl = $tbl . "</tr>";
    }

    $tbl = $tbl . <<<EOD
        </table> 
EOD;
    return $tbl;
}

function renderReducedRackPDF($rack_id, $hl_obj_id = 0) {
    $rackData = spotEntity('rack', $rack_id);
    amplifyCell($rackData);
    markAllSpans($rackData);
    if ($hl_obj_id > 0)
        highlightObject($rackData, $hl_obj_id);
    // markupObjectProblems ($rackData); // Function removed in 0.20.5
    //echo "<center><table border=0><tr valign=middle>";
    //echo '<td><h2>' . mkA ($rackData['name'], 'rack', $rackData['id']) . '</h2></td>';
    //echo "</h2></td></tr></table>\n";
    echo "<table  border=1 cellspacing=0 cellpadding=1>\n";
    echo "<tr><th width='10%'>&nbsp;</th><th width='20%'>Front</th>";
    echo "<th width='50%'>Interior</th><th width='20%'>Back</th></tr>\n";
    for ($i = $rackData['height']; $i > 0; $i--) {
        echo "<tr><td>" . inverseRackUnit($i, $rackData) . "</td>";
        for ($locidx = 0; $locidx < 3; $locidx++) {
            if (isset($rackData[$i][$locidx]['skipped']))
                continue;
            $state = $rackData[$i][$locidx]['state'];
            echo "<td class='atom state_${state}";
            if (isset($rackData[$i][$locidx]['hl']))
                echo $rackData[$i][$locidx]['hl'];
            echo "'";
            if (isset($rackData[$i][$locidx]['colspan']))
                echo ' colspan=' . $rackData[$i][$locidx]['colspan'];
            if (isset($rackData[$i][$locidx]['rowspan']))
                echo ' rowspan=' . $rackData[$i][$locidx]['rowspan'];
            echo ">";

            switch ($state) {
                case 'T':
                    printObjectDetailsForRenderRack($rackData[$i][$locidx]['object_id']);
                    // TODO set background color based on the tag
                    $o = spotEntity('object', $rackData[$i][$locidx]['object_id']);
                    while (list ($key, $val) = each($o['etags'])) {
                        echo "<div style='font: 8px Verdana,sans-serif; text-decoration:none; color=black'>";
                        echo $val['tag'];
                        echo "</div>";
                        break;
                    }
                    break;
                case 'A':
                    echo '<div title="This rackspace does not exist">&nbsp;</div>';
                    break;
                case 'F':
                    echo '<div title="Free rackspace">&nbsp;</div>';
                    break;
                case 'U':
                    echo '<div title="Problematic rackspace, you CAN\'T mount here">&nbsp;</div>';
                    break;
                default:
                    echo '<div title="No data">&nbsp;</div>';
                    break;
            }
            echo '</td>';
        }
        echo "</tr>\n";
    }
    echo "</table>\n";
    // Get a list of all of objects Zero-U mounted to this rack
    $zeroUObjects = getEntityRelatives('children', 'rack', $rack_id);
    if (count($zeroUObjects) > 0) {
        echo "<br><table width='75%' class=rack border=0 cellspacing=0 cellpadding=1>\n";
        echo "<tr><th>Zero-U:</th></tr>\n";
        foreach ($zeroUObjects as $zeroUObject) {
            $state = ($zeroUObject['entity_id'] == $hl_obj_id) ? 'Th' : 'T';
            echo "<tr><td class='atom state_${state}'>";
            printObjectDetailsForRenderRack($zeroUObject['entity_id']);
            echo "</td></tr>\n";
        }
        echo "</table>\n";
    }
    echo "</center>\n";
}
