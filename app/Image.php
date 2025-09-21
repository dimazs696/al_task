<?php

namespace App;

use System\Connect;
use Exception;

class Image
{
    public $x_pos;
    public $y_pos;
    public $file_name;
    public $text;
    public $image_content;
    public $id;

    public $x_find;
    public $y_find;
    public $history;
    public $distance;

    public function __construct()
    {
        $this->id = null;
        $this->x_pos = null;
        $this->y_pos = null;
        $this->file_name = null;
        $this->text = null;
        $this->image_content = null;
        $this->history = [];

        $this->get();
    }

    /**
     * Получение изображения.
     *
     * @return void
     */
    public function get(): void
    {
        $connect = new Connect();

        $query = $connect->query("select * from images order by id desc limit 1",[],'');
        $row = $connect->fetch($query);

        if (!$row) return;

        $this->id = $row['id'];
        $this->x_pos = $row['x'];
        $this->y_pos = $row['y'];
        $this->text = $row['text'];
        $this->file_name = $row['filename'];

        if (!file_exists(dirname($_SERVER['DOCUMENT_ROOT']).'/storage/'.$this->file_name.'.png')) return;

        $this->image_content = 'data:image/png;base64,'.base64_encode(file_get_contents(dirname($_SERVER['DOCUMENT_ROOT']).'/storage/'.$this->file_name.'.png'));
        $this->loadHistory();

        return;
    }

    /**
     * Генерация изображения;
     *
     * @return bool|void
     * @throws \Random\RandomException
     */
    public function generate()
    {
        $this->x_pos = rand(20,800);
        $this->y_pos = rand(20,976);

        $image_name = md5(random_bytes(128));

        $this->text = (function (): string
        {
            $symbols = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $t = '';
            for ($i = 0; $i != 10; $i++) $t .= $symbols[rand(0,strlen($symbols)-1)];
            return $t;
        })();

        $this->file_name = dirname($_SERVER['DOCUMENT_ROOT']).'/storage/'.$image_name.'.png';

        try {

            $image = imagecreate(1000,1000);

            imagecolorallocate($image, 255, 255, 255);
            $text_color = imagecolorallocate($image, 0, 0, 0);
            imagettftext($image, 24, 0, $this->x_pos, $this->y_pos, $text_color, dirname($_SERVER['DOCUMENT_ROOT']).'/public/assets/fonts/Onest-Regular.ttf', $this->text);
            imagepng($image,$this->file_name);

            imagedestroy($image);

            if (file_exists($this->file_name)){

                $connect = new Connect();
                $result = $connect->query("insert into images (filename, x, y, text) values (?,?,?,?)",[
                    $image_name,
                    $this->x_pos,
                    $this->y_pos,
                    $this->text
                ],'siis');
                $connect->close();

                if ($result) return $this->get();
            }

            return;

        } catch (Exception $e){

            echo json_encode(['error' => $e->getMessage()]);
            exit();
        }
    }

    /**
     * Найти координаты.
     *
     * @return void
     */
    public function findCoordinates()
    {
        if (is_null($this->id)) return;

        $image = imagecreatefrompng(dirname($_SERVER['DOCUMENT_ROOT']).'/storage/'.$this->file_name.'.png');
        if (!$image) return;

        $this->x_find = null;
        $this->y_find = null;

        $width = imagesx($image);
        $height = imagesy($image);

        for ($y = 0; $y < $height; $y++) {
            if (!is_null($this->x_find) && !is_null($this->y_find)) break;
            for ($x = 0; $x < $width; $x++) {

                $rgb = imagecolorsforindex($image, imagecolorat($image, $x, $y));

                if ($rgb['red'] < 250) {

                    $this->x_find = $x;
                    $this->y_find = $y;
                    break;
                }
            }
        }

        $this->distance = sqrt(pow($this->x_find - $this->x_pos, 2) + pow($this->y_find - $this->y_pos, 2));

        if (is_null($this->x_find)) return;

        $connect = new Connect();
        $connect->query("insert into image_coordinates (image_id, x, y, distance) values (?,?,?,?)",[
            $this->id,
            $this->x_find,
            $this->y_find,
            $this->distance
        ],'iiii');
        $connect->close();

        return $this->loadHistory();
    }

    /**
     * Загрузка истории.
     *
     * @param int $page
     * @param int $limit
     * @return void
     */
    public function loadHistory(int $page = 1, int $limit = 10)
    {
        $this->history = [];
        if (is_null($this->id)) $this->get();

        $offset = ($page - 1) * $limit;

        $connect = new Connect();
        ///$query = $connect->query("select * from image_coordinates where image_id = ? order by id desc limit ? offset ?",[$this->id,$limit,$offset],'iii');
        $query = $connect->query("select * from image_coordinates where image_id = ? order by id desc",[$this->id],'i');
        $rows = $connect->fetchArray($query);

        foreach ($rows as $row) $this->history[] = $row;

        return;
    }
}