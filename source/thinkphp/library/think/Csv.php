<?php
namespace think;

class Csv
{
    /**
     * 导出csv文件
     * @param array $list  数据数组
     * @param array $title 标题头数组
     * @param array $fileName 文件名称
     */
    public function put_csv($list,$title,$fileName)
    {
        header('Content-Type: application/vnd.ms-excel');//设置内容类型为Excel
        header('Content-Disposition: attachment;filename='.$fileName );//下载文件
        header('Cache-Control: max-age=0');//表示当访问此网页后的0秒内再次访问不会去服务器
        $file = fopen('php://output',"a");//打开文件或者 URL,  php://output 是一个只写的数据流， 允许你以 print 和 echo 一样的方式 写入到输出缓冲区,  a:写入方式打开，将文件指针指向文件末尾。如果文件不存在则尝试创建之。
        $limit = 1000;
        $calc = 0;
        foreach ($title as $v){
            $tit[] = iconv('UTF-8', 'GB2312//IGNORE',$v);//转码
        }
        fputcsv($file,$tit);//将行格式化为 CSV 并写入一个打开的文件中。(标题)
        foreach ($list as $v){
            $calc++;
            //-------核心！！！清空缓存，将缓存上的数据写入到文件--------
            if($limit == $calc){
                ob_flush();//将本来存在输出缓存中的内容取出来，调用ob_flush()之后缓冲区内容将被丢弃。
                flush();   //待输出的内容立即发送。   具体查看：https://www.jb51.net/article/37822.htm
                $calc = 0;
            }//-------核心--------
            foreach($v as $t){
                $tarr[] = iconv('UTF-8', 'GB2312//IGNORE',$t);
            }
            fputcsv($file,$tarr);//将行格式化为 CSV 并写入一个打开的文件中。（内容）
            unset($tarr);//销毁指定的变量
        }
        unset($list);//销毁指定的变量
        fclose($file);//关闭打开的文件
        exit();
    }
}