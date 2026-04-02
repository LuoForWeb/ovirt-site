<?php

if (!function_exists('getfiles')) {
    // 获取指定目录下的所有 PHP 文件，排除指定目录
    function getfiles($directory, $excludeDirectory) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator(
                new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
                function ($current, $key, $iterator) use ($excludeDirectory) {
                    $currentPath = $current->getRealPath();
                    foreach ($excludeDirectory as $excludeDir) {
                        if (strpos($currentPath, $excludeDir) !== false) {
                            return false;
                        }
                    }
                    return true;
                }
            ),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        $phpFiles = [];
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $phpFiles[] = $file->getPathname();
            }
        }
        return $phpFiles;
    }
}

if (!function_exists('deleteFolderContents')) {
	function deleteFolderContents($folderPath) {
		$files = glob($folderPath . '/*');

		foreach ($files as $file) {
			if (is_file($file)) {
				unlink($file);
			} elseif (is_dir($file)) {
				deleteFolderContents($file);
				rmdir($file);
			}
		}
	}
}

if (!function_exists('opcodeMain')) {
    // 主程序入口
    function opcodeMain($vendor, $baseUrl, $ignore) {
        $files = getfiles($baseUrl, $ignore);

        deleteFolderContents('/usr/share/nginx/web_code');
        opcache_reset();

        $new_files = [];
        $num = 0;

        $chunkSize = 500; // 每次处理的文件数量
        $chunks = array_chunk($files, $chunkSize);

        foreach ($chunks as $chunk) {
            foreach ($chunk as $file) {
                try {
                    if (opcache_compile_file($file)) {
                        echo $file . "\n";
                        $num++;
                        $new_files[] = $file;
                    } else {
                        echo $file . '：预编译失败' . "\n";
                    }
                } catch (Exception $e) {
                    echo "Error compiling file: " . $file . "\n";
                    echo "Error message: " . $e->getMessage() . "\n";
                    exit(1);
                }
            }
            // 可以在每个批次后执行一些清理操作，或者等待一段时间以释放资源
            gc_collect_cycles(); // 收集循环引用并释放内存
            sleep(3);
        }

        echo 'Total opcode PHP Files: '.count($files). ', succes Files:' . $num  . "\n\n\n\n";

        $num = 0;
        foreach ($new_files as $file) {
            file_put_contents($file, '');
            echo "$file\n";
            $num ++;

        }

        echo 'Total clear PHP Files: '.count($new_files). ', succes Files:' . $num  . "\n\n\n\n";

        echo 'success';
        exit(0);
    }
}

error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('memory_limit', '512M');
$vendor = $argv[1] ?? 'vinchin'; //厂商

$baseUrl = getcwd();

$ignore = [
	$baseUrl . '/api/xphp/libs/phpqrcode',
	$baseUrl . '/api/xphp/libs/phpseclib',
	$baseUrl . '/api/xphp/libs/wechat-php-sdk',
	$baseUrl . '/apis/xphp/libs/phpseclib',
	$baseUrl . '/css',
	$baseUrl . '/download',
	$baseUrl . '/email',
	$baseUrl . '/help',
	$baseUrl . '/img',
	$baseUrl . '/scripts',
	$baseUrl . '/tmp',
	$baseUrl . '/tools',
	$baseUrl . '/web_ng/api/data',
	$baseUrl . '/web_ng/api/vendor',
];

$content = file_get_contents($baseUrl . '/api/index.php');
if (empty($content)) {
	echo 'again create!';
    exit(1);
}

opcodeMain($vendor, $baseUrl, $ignore);
