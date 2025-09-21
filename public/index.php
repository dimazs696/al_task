<?php

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Image;

$image = new Image();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    header('Content-type: application/json');

    if (isset($_POST['mode'])){

        $mode = strtolower(preg_replace('/[^a-zA-Z_]/', '', $_POST['mode']));

        switch ($mode) {

            /*
             * Генерация картинки.
             */
            case 'generate':{
                #new Image;
                $image->generate();
                echo json_encode([
                    'filename' => $image->file_name,
                    'id' => $image->id,
                    'content' => $image->image_content,
                    'x_true' => $image->x_pos,
                    'y_true' => $image->y_pos,
                ]);
                exit();
            }

            /*
             * Поиск координат.
             */
            case 'find_coordinates':{
                $image->findCoordinates();
                echo json_encode([
                    'x_find' => $image->x_find,
                    'y_find' => $image->y_find,
                    'history' => $image->history,
                    'distance' => $image->distance,
                ]);
                exit();
            }

        }

    }

    exit();

}

//$image->get();
#echo json_encode($image);

include dirname($_SERVER['DOCUMENT_ROOT']).'/html/page.php';
exit();