<?php

class format
{
	private static function getLen($str) {
		return mb_strlen(html_entity_decode(strip_tags($str)));
	}

	function autosize($margin = 1)
	{
		$lines = $this->lines;
		if (count($lines) == 0)
			return;

		// normalize and get column sizes
		$this->normalize();
		$col_sizes = [];
		foreach ($lines[0] as $v) {
			$col_sizes[] = 1;
		}
		foreach ($lines as $line)
		{
			foreach ($line as $kk=>$vv) {
				$col_sizes[$kk] = max($col_sizes[$kk] ?? 1, self::getLen($vv));
			}
		}

		$margin_str = str_repeat(" ", $margin);

		// apply formatting
		$out = [];
		foreach ($lines as $line_no=>$v)
		{
			foreach ($v as $col_no=>$data) {
				$data = self::ensureChars($data, $col_sizes[$col_no]).$margin_str;
				$out[$line_no][$col_no] = $data;
			}
		}
		$this->lines = $out;
	}

	static function ensureChars($text, $size)
	{
		$missing = $size - self::getLen($text);
		if ($missing > 0)
			$text = $text . str_repeat(" ", $missing);
		return $text;
	}

	public $lines = [];
	private $is_normalized = true;
	function __construct()
	{
	}

	private function normalize()
	{
		if ($this->is_normalized)
			return;
		$columns = 0;
		foreach ($this->lines as $v)
			$columns = max($columns, count($v));
		foreach ($this->lines as $k=>$v)
		{
			if (!is_array($v))
				unset($this->lines[$k]);
			if (count($v) == $columns)
				continue;
			while(count($v) < $columns) {
				$v[] = '';
			}
			$this->lines[$k] = $v;
		}
		$this->is_normalized = true;
	}

	function addLine(...$columns)
	{
		if (count($this->lines) > 1)
			$this->is_normalized = $this->is_normalized && count($this->lines[0]) == count($columns);
		$this->lines[] = $columns;
	}

	function __toString()
	{
		$tmp = [];
		foreach ($this->lines as $line)
		{
			$_t = [];
			foreach ($line as $v) {
				$_t[] = $v;
			}
			$tmp[] = implode("", $_t);
		}
		return implode("\n", $tmp);
	}
}