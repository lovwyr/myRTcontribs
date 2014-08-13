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

    $files1 = array_diff(scandir($directory), array('..', '.'));
    $firephp->log($files1, 'pdf_files');

    echo '<div id="wrapper">
            <div id="steuerung"><ul>';

    foreach ($files1 as $fvalue) {
        echo '<li>';
        echo $fvalue;
        echo '</li>';
    }


    echo '</ul></div>
            <div id="zweite_spalte">';
    echo '<a class="media" href="' . $filename . '">PDF File</a> ';
    echo '</div>
            </div>';

    echo '<script type="text/javascript">';
    echo '$(\'a.media\').media({width:1024, height:1024});';
    echo '</script>';
}

function CheckDir($directory) {
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }
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
    $loctrail = getLocationTrail($row['location_id'], $link, $spacer);    
    $firephp->log($loctrail, '$loctrail');
    
    $directory = './data/' . $row['realm'] . '/' . $row['id'];
    CheckDir($directory);
    $filename = $directory . '/' . date("F_j_Y__H_i") . '.pdf';

    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(get_current_user());
    $pdf->SetAuthor(get_current_user());
    if(empty($loctrail))
    {
        $pdftitel = 'Rack Row: ' . $row['name'];
    }
    else
    {
        $pdftitel = 'Rack Row: ' . $row['name'] . " in " . $loctrail;
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
    $firephp->log($rowData, '$rowData');
    $cellfilter = getCellFilter();
    $firephp->log($cellfilter, '$cellfilter');
    $rackList = filterCellList(listCells('rack', $row_id), $cellfilter['expression']);
    $firephp->log($rackList, '$rackList');
    if (!empty($rackList)) {
        foreach ($rackList as $rack) {
            $rackData = spotEntity('rack', $rack['id']);
            $firephp->log($rackData, '$rackData' . $rack['id']);
            $pdf->AddPage();
            $pdf->Bookmark('Rack: ' . $rack['name'], 0, 0, '', 'B', array(0, 64, 128));
            $pdf->Cell(0, 10, 'Rack: ' . $rack['name'], 0, 1, 'L');
            $index_link = $pdf->AddLink();
            $pdf->SetLink($index_link, 0, '*1');
            $pdf->Cell(0, 10, 'Link to INDEX', 0, 1, 'R', false, $index_link);
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
?>

