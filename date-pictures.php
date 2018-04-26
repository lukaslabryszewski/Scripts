#!/usr/local/opt/php@7.1/bin/php
<?php
//print "$argc arguments were passed. In order: \n";
//
//for ($i = 0; $i <= $argc -1; ++$i) {
//    print "$i: $argv[$i]\n";
//}

$path = ".";
//$dh = opendir($path);
//$i = 1;
//while (($file = readdir($dh)) !== false) {
//    if (!in_array($file, [".", "..", "index.php", ".htaccess", ".DS_Store", ".idea", "error_log", "cgi-bin"])) {
//        echo "$path/$file\n";
//        $i++;
//    }
//}
//closedir($dh);


$Directory = new RecursiveDirectoryIterator($path);
$Iterator = new RecursiveIteratorIterator($Directory);
//$Regex = new RegexIterator($Iterator, '/^.+\.jpg/i', RecursiveRegexIterator::GET_MATCH);

$count = 0;
$successCount = 0;
$errorCount = 0;
$successFiles = [];
$errorFiles = [];
$deletedFiles = [];
foreach ($Iterator as $file) {
    /* @var \SplFileInfo $file */
    if (!$file->isFile()) {
        continue;
    }

    if (!in_array(strtolower($file->getExtension()), ['jpg', 'gif', ''])) {
        continue;
    }

    //echo "EXT: " . $file->getExtension() . PHP_EOL;
    echo "Processing File: " . $file->getPathname() . PHP_EOL;
    //echo "Real Path: " . $file->getRealPath() . PHP_EOL;

    $cmd = "/usr/local/bin/exiftool '-filemodifydate<datetimeoriginal' '-filecreatedate<datetimeoriginal' '" . $file->getRealPath() . "'";
    //echo "CMD: $cmd" . PHP_EOL;

    $output = shell_exec($cmd);
    //echo $output;

    if (strstr($output, '1 image files updated')) {
        $successCount++;
    } elseif (strstr($output, '0 image files updated')) {
        $errorCount++;

        // Delete Invalid hidden Files
        if(substr($file->getFilename(), 0, 1) == '.' && $file->getSize() < 6000) {
            $deletedFiles[] = $file;
            echo "Deleting file (".$file->getSize()." B) " . $file->getFilename() . PHP_EOL;
            unlink($file->getRealPath());
        } else {
            $errorFiles[] = $file;
        }
    }

    //echo PHP_EOL;

    $count++;
//    if ($count >= 10) {
//        break;
//    }
}
echo PHP_EOL;
echo "-----------------------------------------" . PHP_EOL;
echo "Completed Processing $count files." . PHP_EOL;
echo "$successCount Files Processed Successfuly" . PHP_EOL;

if (count($errorFiles)) {
    echo PHP_EOL;
    echo "$errorCount Files Failed:" . PHP_EOL;

    foreach ($errorFiles as $file) {
        echo $file->getRealPath() . PHP_EOL;
    }

}

if (count($deletedFiles)) {
    echo PHP_EOL;
    echo count($deletedFiles) . " Files were Deleted:" . PHP_EOL;

    foreach ($deletedFiles as $file) {
        echo $file->getPathname() . PHP_EOL;
    }
}

echo PHP_EOL . "Bye." . PHP_EOL;

//
//
//ARGV.each do |file|
//	if file.include? '.jpg'
//		puts file
//		# puts "exiftool '-filemodifydate<datetimeoriginal' '" + file + "'"
//		system('logger Fixer: Processing ' + file)
//		result = system("/usr/local/bin/exiftool '-filemodifydate<datetimeoriginal' '-filecreatedate<datetimeoriginal' '" + file + "'")
//		if result
//        system('logger Fixer Done!')
//		else
//            system('logger Fixer Error: ' + result)
//		end
//	end
//end