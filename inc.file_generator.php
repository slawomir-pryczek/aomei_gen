<?php

class file_generator {
	static public $seed = 11098;
	static function random() {
		self::$seed = (self::$seed * 1515245 + 1235) & 0xfffffff;
		return self::$seed;
	}

	static function random_string($length) {
		$s = ord('z')-ord('a');
		$tmp = [];
		for ($i=0; $i<$length; $i++)
			$tmp[] = chr( ord('a') + (self::random() % $s));
		return implode('', $tmp);
	}

	static function random_byte($length) {
		$tmp = [];
		for ($i=0; $i<$length; $i++)
			$tmp[] = chr(self::random() % 255);
		return implode('', $tmp);
	}

	private $rand_string = false;
	private $rand_bytes = false;
	function __construct($seed = false) {
		if ($seed !== false)
			self::$seed = $seed;
		$this->rand_string = self::random_string(10000);
		$this->rand_bytes = self::random_byte(10000);
	}

	function getSomethingRandom() {
		$pos = (self::random()) %9000;
		$len = (self::random()) %100+1;

		if ( (self::random()) %10 >= 3)
			return substr($this->rand_string, $pos, $len);
		return substr($this->rand_bytes, $pos, $len);
	}

	function createFile($file, $size)
	{
		$max_fsize = $size;
		$f = fopen($file, 'w');
		$i = 0;
		while (true) {
			$pos = ftell($f);
			if ($i++ % 1000 == 0) {
				$_gb = self::toGB($pos);
				echo "Progress: {$_gb}GB\n";
			}
			if ($pos > $max_fsize)
				break;

			$t = $this->getSomethingRandom();
			$t_size = strlen($t);
			$towrite = [$t];
			do {
				$t = $this->getSomethingRandom();
				$t_size += strlen($t);
				$towrite[] = $t;
			} while ($t_size < 500000);

			fwrite($f, implode(" ", $towrite));
		}
		ftruncate($f, $max_fsize);
	}

	static function toGB($bytes) {
		$bytes = ($bytes / 1024 / 1024 / 1024) * 100;
		return  intval($bytes) / 100;
	}
}

