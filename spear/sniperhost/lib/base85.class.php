<?PHP

#error_reporting(E_ALL);

// Examples:
// $str = base85::encode("Hello world!");
// $str = base85::decode(":e4D*;K$&\Er");

class base85 
{

	public static function decode($str) {
		$str = preg_replace("/ \t\r\n\f/","",$str);
		$str = preg_replace("/z/","!!!!!",$str);
		$str = preg_replace("/y/","+<VdL/",$str);

		// Pad the end of the string so it's a multiple of 5
		$padding = 5 - (strlen($str) % 5);
		if (strlen($str) % 5 === 0) {
			$padding = 0;
		}
		$str .= str_repeat('u',$padding);

		$num = 0;
		$ret = '';

		// Foreach 5 chars, convert it to an integer
		while ($chunk = substr($str, $num * 5, 5)) {
			$tmp = 0;

			foreach (unpack('C*',$chunk) as $item) {
				$tmp *= 85;
				$tmp += $item - 33;
			}

			// Convert the integer in to a string
			$ret .= pack('N', $tmp);

			$num++;
		}

		// Remove any padding we had to add
		$ret = substr($ret,0,strlen($ret) - $padding);

		return $ret;
	}

	public static function encode($str) {
		$ret   = '';
		$debug = 0;

		$padding = 4 - (strlen($str) % 4);
		if (strlen($str) % 4 === 0) {
			$padding = 0;
		}

		if ($debug) {
			printf("Length: %d = Padding: %s<br /><br />\n",strlen($str),$padding);
		}

		// If we don't have a four byte chunk, append \0s
		$str .= str_repeat("\0", $padding);

		foreach (unpack('N*',$str) as $chunk) {
			// If there is an all zero chunk, it has a shortcut of 'z'
			if ($chunk == "\0") {
				$ret .= "z";
				continue;
			}

			// Four spaces has a shortcut of 'y'
			if ($chunk == 538976288) {
				$ret .= "y";
				continue;
			}

			if ($debug) {
				var_dump($chunk); print "<br />\n";
			}

			// Convert the integer into 5 "quintet" chunks
			for ($a = 0; $a < 5; $a++) {
				$b	= intval($chunk / (pow(85,4 - $a)));
				$ret .= chr($b + 33);

				if ($debug) {
					printf("%03d = %s <br />\n",$b,chr($b+33));
				}

				$chunk -= $b * pow(85,4 - $a);
			}
		}

		// If we added some null bytes, we remove them from the final string
		if ($padding) {
			$ret = preg_replace("/z$/",'!!!!!',$ret);
			$ret = substr($ret,0,strlen($ret) - $padding);
		}

		return $ret;
	}

} // End of base85 class
