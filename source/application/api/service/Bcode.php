<?php

namespace app\api\service;


class Bcode
{

    public function __construct()
    {

        require VENDOR_PATH.'/bcode/BCGFontFile.php';
        require VENDOR_PATH.'/bcode/BCGDrawing.php';
        require VENDOR_PATH.'/bcode/BCGcode39.barcode.php';
    }


    /**
     * 生成商品规格条形码
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-27
     * Time: 21:22
     */
    public function createCode($order_sn){
        //颜色条形码
        $color_black = new \BCGColor(0, 0, 0);
        $color_white = new \BCGColor(255, 255, 255);
        $drawException = null;
        try
        {
            $code = new \BCGcode39();
            $code->setScale(2);
            $code->setThickness(30); // 条形码的厚度
            $code->setForegroundColor($color_black); // 条形码颜色
            $code->setBackgroundColor($color_white); // 空白间隙颜色
            // $code->setFont($font); //
            $code->parse($order_sn); // 条形码需要的数据内容
        }
        catch(\Exception $exception)
        {
            $drawException = $exception;
        }
        //根据以上条件绘制条形码
        $drawing = new \BCGDrawing('', $color_white);
        if($drawException) {
            $drawing->drawException($drawException);
        }else{
            $drawing->setBarcode($code);
            $drawing->draw();
        }
        // 生成PNG格式的图片
//        header('Content-Type: image/png');
//         header('Content-Disposition:attachment; filename="1.png"'); //自动下载
        $drawing->finish(\BCGDrawing::IMG_FORMAT_PNG);
    }

}