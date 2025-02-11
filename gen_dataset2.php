<?php

include __DIR__."/inc.file_generator.php";
include __DIR__."/inc.format.php";

if (http_response_code() !== false) {
	echo "<pre>Please run this script from shell, not from browser.\n";
	echo "This script can't continue.</pre>";
	die("");
}

$dir = 'dataset';
if ( !file_exists( $dir ) || !is_dir( $dir) )
	mkdir($dir);
chdir ($dir);

echo "\n\n\n-------------------------------------------------------------------------\n";
echo "This script will generate dataset which will fail to be backed up by\n";
echo "all recent versions of AOMEI Backupper. It's intended for debugging.\n";
echo "If your backup software isn't able to backup and restore these files\n";
echo "that means the algorithm to create backups is unstable and probably\n";
echo "at least some of your backups are corrupted.\n\n";
echo "Integer width should be 64 bits. Int MAX: ".PHP_INT_MAX."\n";
echo "-------------------------------------------------------------------------\n\n";

$config = [];
$config[] = ['Debian8-Apache-cl1.vmdk', '66299625472', 9281];
$config[] = ['Debian8-Apache-cl1-000001.vmdk', '123336086528', 11098];

$f = new format();
$f->addLine("File", "Size", "Seed");
foreach ($config as $v)
	$f->addLine($v[0], file_generator::toGB($v[1])."GB", $v[2]);
$f->autosize();
echo "{$f}\n";

echo "Ensure there's enough space on the device. Press any key to proceed...\n";
//readline_callback_handler_install('', function() {});
//$keystroke = stream_get_contents(STDIN, 1);
//echo "\n";

foreach ($config as $v) {
	echo "\n\nGenerating {$v[0]}\n";
	$fg = new file_generator($v[2]);
	$fg->createFile($v[0], $v[1]);
}




