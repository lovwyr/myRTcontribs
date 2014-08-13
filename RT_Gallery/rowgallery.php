<?php

addCSS('css/custom1.css');
require_once('FirePHPCore/FirePHP.class.php');

$tab['row']['ImageGalleryView'] = 'Gallery';
$trigger['row']['ImageGalleryView'] = 'ImageGalleryViewTrigger';
$tabhandler['row']['ImageGalleryView'] = 'ShowRowGallery';

function ImageGalleryViewTrigger() {
    ob_start();
    
    return 1;
//    $firephp = FirePHP::getInstance(true);
//    $allok = FALSE;
//    $row = spotEntity('row', $row_id);
//    $firephp->log($row, 'row');
//    $files = getFilesOfEntity($row['realm'], $row['id']);
//    foreach ($files as $filerun) {
//        switch ($filerun['type']) {
//            case 'image/jpeg':
//            case 'image/png':
//            case 'image/gif':
//                $allok = TRUE;
//                break;
//            default:
//                break;
//        }
//        if ($allok) {
//            return 1;
//        } else {
//            return '';
//        }
//    }
}

// display the import page.
function ShowRowGallery($row_id) {
    echo '<script type="text/javascript" src="/racktable/extensions/jquery/jquery-latest.js"></script>';
    echo '<script type="text/javascript" src="/racktable/extensions/galleria/galleria-1.3.6.min.js"></script>';
    ob_start();
    $firephp = FirePHP::getInstance(true);

    $firephp->log($image, 'images');

    $row = spotEntity('row', $row_id);
    $files = getFilesOfEntity($row['realm'], $row['id']);

    $firephp->log($row, 'row');
    $firephp->log($files, 'files');
    echo '<div class="galleria">';
    //echo '<div class="album" data-jgallery-album-title="Album 1">';
    foreach ($files as $filerun) {

        switch ($filerun['type']) {
            case 'image/jpeg':
            case 'image/png':
            case 'image/gif':
                $file = getFile($filerun['id']);
                $image = imagecreatefromstring($file['contents']);
                $width = imagesx($image);
                $height = imagesy($image);
                $firephp->log($file, 'file ');
                echo "<img src='?module=download&file_id=${file['id']}&asattach=no'>";
                //echo "<img src='?module=image&img=preview&file_id=${file['id']}' />";
                //echo '</a>';
                //echo '</br>';
                break;
            default:
                break;
        }
    }
    echo '</div>';
    echo '<script>';
    echo 'Galleria.loadTheme(\'/racktable/extensions/galleria/themes/classic/galleria.classic.min.js\');';
    echo 'Galleria.run(\'.galleria\');';
    echo '</script>';
    //echo '</div>';
    //echo '<script type="text/javascript" src="/racktable/extensions/rowgallery/RowGalleryConfig.js"></script>';
    $assetnr = $object['asset_no'];
    $firephp->log($assetnr, 'asset');
}

?>