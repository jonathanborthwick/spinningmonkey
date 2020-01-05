<?PHP namespace brog;
	class BPUtil{
		private $debug = false;
		
        public function __construct($debug)
		{
            $this->debug = $debug;
		}
		
		function startsWith($haystack, $needle)
		{
			$length = strlen($needle);
			return (substr($haystack, 0, $length) === $needle);
		}
		
		function endsWith($haystack, $needle)
		{
			$length = strlen($needle);
			if ($length == 0) {
				return true;
			}
		
			return (substr($haystack, -$length) === $needle);
		}
		
        function echoNl($text){
            if($this->debug){
                //echo("\n\r----------------- DEBUG IS TRUE HERE--------------\n\r");
                echo($text . "\n\r");
            }
        }
	}
?>