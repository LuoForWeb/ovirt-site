<?php
/*********************************************************************************
 *  扩展类库-文件操作类
***********************************************************************************/
class File {
    //创建目录
    function forcemkdir($path){
        if(!file_exists($path)){
            file::forcemkdir(dirname($path));
            mkdir($path,0777);
        }
    }
    //检测文件是否存在
    function iswriteable($file){
        $writeable=0;
        if(is_dir($file)){
            $dir=$file;
            if($fp=@fopen("$dir/test.txt",'w')){
                @fclose($fp);
                @unlink("$dir/test.txt");
                $writeable=1;
            }
        }else{
            if($fp=@fopen($file,'a+')){
                @fclose($fp);
                $writeable=1;
            }
        }
        return $writeable;
    }
    //删除当前目录下的文件或目录
    function cleardir($dir,$forceclear=false) {
        if(!is_dir($dir)){
            return;
        }
        $directory=dir($dir);
        while($entry=$directory->read()){
            $filename=$dir.'/'.$entry;
            if(is_file($filename)){
                @unlink($filename);
            }elseif(is_dir($filename)&$forceclear&$entry!='.'&$entry!='..'){
                chmod($filename,0777);
                file::cleardir($filename,$forceclear);
                rmdir($filename);
            }
        }
        $directory->close();
    }
    //删除当前目录及目录下的文件
    function removedir($dir){
        if (is_dir($dir) && !is_link($dir)){
            if ($dh=opendir($dir)){
                while (($sf= readdir($dh))!== false){
                    if('.'==$sf || '..'==$sf){
                        continue;
                    }
                    file::removedir($dir.'/'.$sf);
                }
                closedir($dh);
            }
            return rmdir($dir);
        }
        return @unlink($dir);
    }
    //复制文件
    function copydir($srcdir, $dstdir) {
        if(!is_dir($dstdir)) mkdir($dstdir);
        if($curdir = opendir($srcdir)) {
            while($file = readdir($curdir)) {
                if($file != '.' && $file != '..') {
                    $srcfile = $srcdir . '/' . $file;
                    $dstfile = $dstdir . '/' . $file;
                    if(is_file($srcfile)) {
                        copy($srcfile, $dstfile);
                    }
                    else if(is_dir($srcfile)) {
                        file::copydir($srcfile, $dstfile);
                    }
                }
            }
            closedir($curdir);
        }
    }
    //读取文件
    function readfromfile($filename) {
        if ($fp=@fopen($filename,'rb')) {
            if(PHP_VERSION >='4.3.0' && function_exists('file_get_contents')){
                return file_get_contents($filename);
            }else{
                flock($fp,LOCK_EX);
                $data=fread($fp,filesize($filename));
                flock($fp,LOCK_UN);
                fclose($fp);
                return $data;
            }
        }else{
            return '';
        }
    }
	//写入文件
    function writetofile($filename,$data){
        if($fp=@fopen($filename,'wb')){
            if (PHP_VERSION >='4.3.0' && function_exists('file_put_contents')) {
                return @file_put_contents($filename,$data);
            }else{
                flock($fp, LOCK_EX);
                $bytes=fwrite($fp, $data);
                flock($fp,LOCK_UN);
                fclose($fp);
                return $bytes;
            }
        }else{
            return false;
        }
    }
}	
?>