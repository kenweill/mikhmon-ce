<?php
/*****************************
 *
 * RouterOS PHP API class v1.6
 * Author: Denis Basta
 * Contributors:
 *    Nick Barnes
 *    Ben Menking (ben [at] infotechsc [dot] com)
 *    Jeremy Jefferson (http://jeremyj.com)
 *    Cristian Deluxe (djcristiandeluxe [at] gmail [dot] com)
 *    Mikhail Moskalev (mmv.rus [at] gmail [dot] com)
 *
 * http://www.mikrotik.com
 * http://wiki.mikrotik.com/wiki/API_PHP_class
 *
 ******************************/

class RouterosAPI
{
    public $debug     = false; //  Show debug information
    public $connected = false; //  Connection state
    public $port      = 8728;  //  Port to connect to (default 8729 for ssl)
    public $ssl       = false; //  Connect using SSL (must enable api-ssl in IP/Services)
    public $timeout   = 3;     //  Connection attempt timeout and data read timeout
    public $attempts  = 5;     //  Connection attempt count
    public $delay     = 3;     //  Delay between connection attempts in seconds

    public $socket;            //  Variable for storing socket resource
    public $error_no;          //  Variable for storing connection error number, if any
    public $error_str;         //  Variable for storing connection error text, if any

    /* Check, can be var used in foreach  */
    public function isIterable($var)
    {
        return $var !== null
                && (is_array($var)
                || $var instanceof Traversable
                || $var instanceof Iterator
                || $var instanceof IteratorAggregate
                );
    }

    /**
     * Print text for debug purposes
     *
     * @param string      $text       Text to print
     *
     * @return void
     */
    public function debug($text)
    {
        if ($this->debug) {
            echo $text . "\n";
        }
    }


    /**
     *
     *
     * @param string        $length
     *
     * @return void
     */
    public function encodeLength($length)
    {
        if ($length < 0x80) {
            $length = chr($length);
        } elseif ($length < 0x4000) {
            $length |= 0x8000;
            $length = chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
        } elseif ($length < 0x200000) {
            $length |= 0xC00000;
            $length = chr(($length >> 16) & 0xFF) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
        } elseif ($length < 0x10000000) {
            $length |= 0xE0000000;
            $length = chr(($length >> 24) & 0xFF) . chr(($length >> 16) & 0xFF) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
        } elseif ($length >= 0x10000000) {
            $length = chr(0xF0) . chr(($length >> 24) & 0xFF) . chr(($length >> 16) & 0xFF) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
        }

        return $length;
    }


    /**
     * Login to RouterOS
     *
     * @param string      $ip         Hostname (IP or domain) of the RouterOS server
     * @param string      $login      The RouterOS username
     * @param string      $password   The RouterOS password
     *
     * @return boolean                If we are connected or not
     */
    public function connect($ip, $login, $password)
    {
        for ($ATTEMPT = 1; $ATTEMPT <= $this->attempts; $ATTEMPT++) {
            $this->connected = false;
            $PROTOCOL = ($this->ssl ? 'ssl://' : '' );
            $context = stream_context_create(array('ssl' => array('ciphers' => 'ADH:ALL', 'verify_peer' => false, 'verify_peer_name' => false)));
            $this->debug('Connection attempt #' . $ATTEMPT . ' to ' . $PROTOCOL . $ip . ':' . $this->port . '...');
            $this->socket = @stream_socket_client($PROTOCOL . $ip.':'. $this->port, $this->error_no, $this->error_str, $this->timeout, STREAM_CLIENT_CONNECT,$context);
            if ($this->socket) {
                stream_set_timeout($this->socket, $this->timeout);
                $this->write('/login', false);
                $this->write('=name=' . $login, false);
                $this->write('=password=' . $password);
                $RESPONSE = $this->read(false);
                if (isset($RESPONSE[0])) {
                    if ($RESPONSE[0] == '!done') {
                        if (!isset($RESPONSE[1])) {
                            // Login method post-v6.43
                            $this->connected = true;
                            break;
                        } else {
                            // Login method pre-v6.43
                            $MATCHES = array();
                            if (preg_match_all('/[^=]+/i', $RESPONSE[1], $MATCHES)) {
                                if ($MATCHES[0][0] == 'ret' && strlen($MATCHES[0][1]) == 32) {
                                    $this->write('/login', false);
                                    $this->write('=name=' . $login, false);
                                    $this->write('=response=00' . md5(chr(0) . $password . pack('H*', $MATCHES[0][1])));
                                    $RESPONSE = $this->read(false);
                                    if (isset($RESPONSE[0]) && $RESPONSE[0] == '!done') {
                                        $this->connected = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
                fclose($this->socket);
            }
            sleep($this->delay);
        }

        if ($this->connected) {
            $this->debug('Connected...');
        } else {
            $this->debug('Error...');
        }
        return $this->connected;
    }


    /**
     * Disconnect from RouterOS
     *
     * @return void
     */
    public function disconnect()
    {
        // let's make sure this socket is still valid.  it may have been closed by something else
        // PHP 8: stream_socket_client returns an object, not a resource
        if ($this->socket !== null && (is_resource($this->socket) || is_object($this->socket))) {
            fclose($this->socket);
            $this->socket = null;
        }
        $this->connected = false;
        $this->debug('Disconnected...');
    }


    /**
     * Parse response from Router OS
     *
     * @param array       $response   Response data
     *
     * @return array                  Array with parsed data
     */
    public function parseResponse($response)
    {
        if (is_array($response)) {
            $PARSED      = array();
            $CURRENT     = null;
            $singlevalue = null;
            foreach ($response as $x) {
                if (in_array($x, array('!fatal','!re','!trap'))) {
                    if ($x == '!re') {
                        $CURRENT =& $PARSED[];
                    } else {
                        $CURRENT =& $PARSED[$x][];
                    }
                } elseif ($x != '!done') {
                    $MATCHES = array();
                    if (preg_match_all('/[^=]+/i', $x, $MATCHES)) {
                        if ($MATCHES[0][0] == 'ret') {
                            $singlevalue = $MATCHES[0][1];
                        }
                        $CURRENT[$MATCHES[0][0]] = (isset($MATCHES[0][1]) ? $MATCHES[0][1] : '');
                    }
                }
            }

            if (empty($PARSED) && !is_null($singlevalue)) {
                $PARSED = $singlevalue;
            }

            return $PARSED;
        } else {
            return array();
        }
    }


    /**
     * Parse response from Router OS
     *
     * @param array       $response   Response data
     *
     * @return array                  Array with parsed data
     */
    public function parseResponse4Smarty($response)
    {
        if (is_array($response)) {
            $PARSED      = array();
            $CURRENT     = null;
            $singlevalue = null;
            foreach ($response as $x) {
                if (in_array($x, array('!fatal','!re','!trap'))) {
                    if ($x == '!re') {
                        $CURRENT =& $PARSED[];
                    } else {
                        $CURRENT =& $PARSED[$x][];
                    }
                } elseif ($x != '!done') {
                    $MATCHES = array();
                    if (preg_match_all('/[^=]+/i', $x, $MATCHES)) {
                        if ($MATCHES[0][0] == 'ret') {
                            $singlevalue = $MATCHES[0][1];
                        }
                        $CURRENT[$MATCHES[0][0]] = (isset($MATCHES[0][1]) ? $MATCHES[0][1] : '');
                    }
                }
            }
            foreach ($PARSED as $key => $value) {
                $PARSED[$key] = $this->arrayChangeKeyName($value);
            }
            return $PARSED;
            if (empty($PARSED) && !is_null($singlevalue)) {
                $PARSED = $singlevalue;
            }
        } else {
            return array();
        }
    }


    /**
     * Change "-" and "/" from array key to "_"
     *
     * @param array       $array      Input array
     *
     * @return array                  Array with changed key names
     */
    public function arrayChangeKeyName(&$array)
    {
        if (is_array($array)) {
            foreach ($array as $k => $v) {
                $tmp = str_replace("-", "_", $k);
                $tmp = str_replace("/", "_", $tmp);
                if ($tmp) {
                    $array_new[$tmp] = $v;
                } else {
                    $array_new[$k] = $v;
                }
            }
            return $array_new;
        } else {
            return $array;
        }
    }


    /**
     * Read data from Router OS
     *
     * @param boolean     $parse      Parse the data? default: true
     *
     * @return array                  Array with parsed or unparsed data
     */
    public function read($parse = true)
    {
        $RESPONSE     = array();
        $receiveddone = false;
        while (true) {
            // Read the first byte of input which gives us some or all of the length
            // of the remaining reply.
            $BYTE   = ord(fread($this->socket, 1));
            $LENGTH = 0;
            // If the first bit is set then we need to remove the first four bits, shift left 8
            // and then read another byte in.
            // We repeat this for the second and third bits.
            // If the fourth bit is set, we need to remove anything left in the first byte
            // and then read in yet another byte.
            if ($BYTE & 128) {
                if (($BYTE & 192) == 128) {
                    $LENGTH = (($BYTE & 63) << 8) + ord(fread($this->socket, 1));
                } else {
                    if (($BYTE & 224) == 192) {
                        $LENGTH = (($BYTE & 31) << 8) + ord(fread($this->socket, 1));
                        $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                    } else {
                        if (($BYTE & 240) == 224) {
                            $LENGTH = (($BYTE & 15) << 8) + ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                        } else {
                            $LENGTH = ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                        }
                    }
                }
            } else {
                $LENGTH = $BYTE;
            }

            $_ = "";

            // If we have got more characters to read, read them in.
            if ($LENGTH > 0) {
                $_      = "";
                $retlen = 0;
                while ($retlen < $LENGTH) {
                    $toread = $LENGTH - $retlen;
                    $_ .= fread($this->socket, $toread);
                    $retlen = strlen($_);
                }
                $RESPONSE[] = $_;
                $this->debug('>>> [' . $retlen . '/' . $LENGTH . '] bytes read.');
            }

            // If we get a !done, make a note of it.
            if ($_ == "!done") {
                $receiveddone = true;
            }

            $STATUS = stream_get_meta_data($this->socket);
            if ($LENGTH > 0) {
                $this->debug('>>> [' . $LENGTH . ', ' . $STATUS['unread_bytes'] . ']' . $_);
            }

            if ((!$this->connected && !$STATUS['unread_bytes']) || ($this->connected && !$STATUS['unread_bytes'] && $receiveddone)) {
                break;
            }
        }

        if ($parse) {
            $RESPONSE = $this->parseResponse($RESPONSE);
        }

        return $RESPONSE;
    }


    /**
     * Write (send) data to Router OS
     *
     * @param string      $command    A string with the command to send
     * @param mixed       $param2     If we set an integer, the command will send this data as a "tag"
     *                                If we set it to boolean true, the funcion will send the comand and finish
     *                                If we set it to boolean false, the funcion will send the comand and wait for next command
     *                                Default: true
     *
     * @return boolean                Return false if no command especified
     */
    public function write($command, $param2 = true)
    {
        if ($command) {
            $data = explode("\n", $command);
            foreach ($data as $com) {
                $com = trim($com);
                fwrite($this->socket, $this->encodeLength(strlen($com)) . $com);
                $this->debug('<<< [' . strlen($com) . '] ' . $com);
            }

            if (gettype($param2) == 'integer') {
                fwrite($this->socket, $this->encodeLength(strlen('.tag=' . $param2)) . '.tag=' . $param2 . chr(0));
                $this->debug('<<< [' . strlen('.tag=' . $param2) . '] .tag=' . $param2);
            } elseif (gettype($param2) == 'boolean') {
                fwrite($this->socket, ($param2 ? chr(0) : ''));
            }

            return true;
        } else {
            return false;
        }
    }


    /**
     * Write (send) data to Router OS
     *
     * @param string      $com        A string with the command to send
     * @param array       $arr        An array with arguments or queries
     *
     * @return array                  Array with parsed
     */
    public function comm($com, $arr = array())
    {
        $count = count($arr);
        $this->write($com, !$arr);
        $i = 0;
        if ($this->isIterable($arr)) {
            foreach ($arr as $k => $v) {
                switch ($k[0]) {
                    case "?":
                        $el = "$k=$v";
                        break;
                    case "~":
                        $el = "$k~$v";
                        break;
                    default:
                        $el = "=$k=$v";
                        break;
                }

                $last = ($i++ == $count - 1);
                $this->write($el, $last);
            }
        }

        return $this->read();
    }

    /**
     * Standard destructor
     *
     * @return void
     */
    public function __destruct()
    {
        $this->disconnect();
    }
}

// encrypt decript

function encrypt($string, $key=128) {
	$result = '';
	for($i=0, $k= strlen($string); $i<$k; $i++) {
		$char = substr($string, $i, 1);
		$keychar = substr($key, ($i % strlen($key))-1, 1);
		$char = chr(ord($char)+ord($keychar));
		$result .= $char;
	}
	return base64_encode($result);
}
function decrypt($string, $key=128) {
	$result = '';
	$string = base64_decode($string);
	for($i=0, $k=strlen($string); $i< $k ; $i++) {
		$char = substr($string, $i, 1);
		$keychar = substr($key, ($i % strlen($key))-1, 1);
		$char = chr(ord($char)-ord($keychar));
		$result .= $char;
	}
	return $result;
}

// Reformat date time MikroTik
// by Laksamadi Guko

function formatInterval($dtm){
$val_convert = $dtm;
$new_format = str_replace("s", "", str_replace("m", "m ", str_replace("h", "h ", str_replace("d", "d ", str_replace("w", "w ", $val_convert)))));
return $new_format;
}

function formatDTM($dtm){
if(substr($dtm, 1,1) == "d" || substr($dtm, 2,1) == "d"){
    $day = explode("d",$dtm)[0]."d";
    $day = str_replace("d", "d ", str_replace("w", "w ", $day));
    $dtm = explode("d",$dtm)[1];
}elseif(substr($dtm, 1,1) == "w" && substr($dtm, 3,1) == "d" || substr($dtm, 2,1) == "w" && substr($dtm, 4,1) == "d"){
    $day = explode("d",$dtm)[0]."d";
    $day = str_replace("d", "d ", str_replace("w", "w ", $day));
    $dtm = explode("d",$dtm)[1];
}elseif (substr($dtm, 1,1) == "w" || substr($dtm, 2,1) == "w" ) {
    $day = explode("w",$dtm)[0]."w";
    $day = str_replace("d", "d ", str_replace("w", "w ", $day));
    $dtm = explode("w",$dtm)[1];
}

// secs
if(strlen($dtm) == "2" && substr($dtm, -1) == "s"){
    $format = $day." 00:00:0".substr($dtm, 0,-1);
}elseif(strlen($dtm) == "3" && substr($dtm, -1) == "s"){
    $format = $day." 00:00:".substr($dtm, 0,-1);
//minutes
}elseif(strlen($dtm) == "2" && substr($dtm, -1) == "m"){
    $format = $day." 00:0".substr($dtm, 0,-1).":00";
}elseif(strlen($dtm) == "3" && substr($dtm, -1) == "m"){
    $format = $day." 00:".substr($dtm, 0,-1).":00";
//hours
}elseif(strlen($dtm) == "2" && substr($dtm, -1) == "h"){
    $format = $day." 0".substr($dtm, 0,-1).":00:00";
}elseif(strlen($dtm) == "3" && substr($dtm, -1) == "h"){
    $format = $day." ".substr($dtm, 0,-1).":00:00";
 
//minutes -secs
}elseif(strlen($dtm) == "4" && substr($dtm, -1) == "s" && substr($dtm,1,-2) == "m"){
    $format = $day." "."00:0".substr($dtm, 0,1).":0".substr($dtm, 2,-1);
}elseif(strlen($dtm) == "5" && substr($dtm, -1) == "s" && substr($dtm,1,-3) == "m"){
    $format = $day." "."00:0".substr($dtm, 0,1).":".substr($dtm, 2,-1);
}elseif(strlen($dtm) == "5" && substr($dtm, -1) == "s" && substr($dtm,2,-2) == "m"){
    $format = $day." "."00:".substr($dtm, 0,2).":0".substr($dtm, 3,-1);
}elseif(strlen($dtm) == "6" && substr($dtm, -1) == "s" && substr($dtm,2,-3) == "m"){
    $format = $day." "."00:".substr($dtm, 0,2).":".substr($dtm, 3,-1);

//hours -secs
}elseif(strlen($dtm) == "4" && substr($dtm, -1) == "s" && substr($dtm,1,-2) == "h"){
    $format = $day." 0".substr($dtm, 0,1).":00:0".substr($dtm, 2,-1);
}elseif(strlen($dtm) == "5" && substr($dtm, -1) == "s" && substr($dtm,1,-3) == "h"){
    $format = $day." 0".substr($dtm, 0,1).":00:".substr($dtm, 2,-1);
}elseif(strlen($dtm) == "5" && substr($dtm, -1) == "s" && substr($dtm,2,-2) == "h"){
    $format = $day." ".substr($dtm, 0,2).":00:0".substr($dtm, 3,-1);
}elseif(strlen($dtm) == "6" && substr($dtm, -1) == "s" && substr($dtm,2,-3) == "h"){
    $format = $day." ".substr($dtm, 0,2).":00:".substr($dtm, 3,-1);

//hours -secs
}elseif(strlen($dtm) == "4" && substr($dtm, -1) == "m" && substr($dtm,1,-2) == "h"){
    $format = $day." 0".substr($dtm, 0,1).":0".substr($dtm, 2,-1).":00";
}elseif(strlen($dtm) == "5" && substr($dtm, -1) == "m" && substr($dtm,1,-3) == "h"){
    $format = $day." 0".substr($dtm, 0,1).":".substr($dtm, 2,-1).":00";
}elseif(strlen($dtm) == "5" && substr($dtm, -1) == "m" && substr($dtm,2,-2) == "h"){
    $format = $day." ".substr($dtm, 0,2).":0".substr($dtm, 3,-1).":00";
}elseif(strlen($dtm) == "6" && substr($dtm, -1) == "m" && substr($dtm,2,-3) == "h"){
    $format = $day." ".substr($dtm, 0,2).":".substr($dtm, 3,-1).":00";

//hours minutes secs
}elseif(strlen($dtm) == "6" && substr($dtm, -1) == "s" && substr($dtm,3,-2) == "m" && substr($dtm,1,-4) == "h"){
    $format = $day." 0".substr($dtm, 0,1).":0".substr($dtm, 2,-3).":0".substr($dtm, 4,-1);
}elseif(strlen($dtm) == "7" && substr($dtm, -1) == "s" && substr($dtm,3,-3) == "m" && substr($dtm,1,-5) == "h"){
    $format = $day." 0".substr($dtm, 0,1).":0".substr($dtm, 2,-4).":".substr($dtm, 4,-1);
}elseif(strlen($dtm) == "7" && substr($dtm, -1) == "s" && substr($dtm,4,-2) == "m" && substr($dtm,1,-5) == "h"){
    $format = $day." 0".substr($dtm, 0,1).":".substr($dtm, 2,-3).":0".substr($dtm, 5,-1);
}elseif(strlen($dtm) == "8" && substr($dtm, -1) == "s" && substr($dtm,4,-3) == "m" && substr($dtm,1,-6) == "h"){
    $format = $day." 0".substr($dtm, 0,1).":".substr($dtm, 2,-4).":".substr($dtm, 5,-1);
}elseif(strlen($dtm) == "7" && substr($dtm, -1) == "s" && substr($dtm,4,-2) == "m" && substr($dtm,2,-4) == "h"){
    $format = $day." ".substr($dtm, 0,2).":0".substr($dtm, 3,-3).":0".substr($dtm, 5,-1);
}elseif(strlen($dtm) == "8" && substr($dtm, -1) == "s" && substr($dtm,4,-3) == "m" && substr($dtm,2,-5) == "h"){
    $format = $day." ".substr($dtm, 0,2).":0".substr($dtm, 3,-4).":".substr($dtm, 5,-1);
}elseif(strlen($dtm) == "8" && substr($dtm, -1) == "s" && substr($dtm,5,-2) == "m" && substr($dtm,2,-5) == "h"){
    $format = $day." ".substr($dtm, 0,2).":".substr($dtm, 3,-3).":0".substr($dtm, 6,-1);
}elseif(strlen($dtm) == "9" && substr($dtm, -1) == "s" && substr($dtm,5,-3) == "m" && substr($dtm,2,-6) == "h"){
    $format = $day." ".substr($dtm, 0,2).":".substr($dtm, 3,-4).":".substr($dtm, 6,-1);

}else{
    $format = $dtm;
}
return $format;
}


function randN($length) {
	$chars = "23456789";
	$charArray = str_split($chars);
	$charCount = strlen($chars);
	$result = "";
	for($i=1;$i<=$length;$i++)
	{
		$randChar = rand(0,$charCount-1);
		$result .= $charArray[$randChar];
	}
	return $result;
}

function randUC($length) {
	$chars = "ABCDEFGHJKLMNPRSTUVWXYZ";
	$charArray = str_split($chars);
	$charCount = strlen($chars);
	$result = "";
	for($i=1;$i<=$length;$i++)
	{
		$randChar = rand(0,$charCount-1);
		$result .= $charArray[$randChar];
	}
	return $result;
}
function randLC($length) {
	$chars = "abcdefghijkmnprstuvwxyz";
	$charArray = str_split($chars);
	$charCount = strlen($chars);
	$result = "";
	for($i=1;$i<=$length;$i++)
	{
		$randChar = rand(0,$charCount-1);
		$result .= $charArray[$randChar];
	}
	return $result;
}

function randULC($length) {
	$chars = "ABCDEFGHJKLMNPRSTUVWXYZabcdefghijkmnprstuvwxyz";
	$charArray = str_split($chars);
	$charCount = strlen($chars);
	$result = "";
	for($i=1;$i<=$length;$i++)
	{
		$randChar = rand(0,$charCount-1);
		$result .= $charArray[$randChar];
	}
	return $result;
}

function randNLC($length) {
	$chars = "23456789abcdefghijkmnprstuvwxyz";
	$charArray = str_split($chars);
	$charCount = strlen($chars);
	$result = "";
	for($i=1;$i<=$length;$i++)
	{
		$randChar = rand(0,$charCount-1);
		$result .= $charArray[$randChar];
	}
	return $result;
}

function randNUC($length) {
	$chars = "23456789ABCDEFGHJKLMNPRSTUVWXYZ";
	$charArray = str_split($chars);
	$charCount = strlen($chars);
	$result = "";
	for($i=1;$i<=$length;$i++)
	{
		$randChar = rand(0,$charCount-1);
		$result .= $charArray[$randChar];
	}
	return $result;
}

function randNULC($length) {
	$chars = "23456789ABCDEFGHJKLMNPRSTUVWXYZabcdefghijkmnprstuvwxyz";
	$charArray = str_split($chars);
	$charCount = strlen($chars);
	$result = "";
	for($i=1;$i<=$length;$i++)
	{
		$randChar = rand(0,$charCount-1);
		$result .= $charArray[$randChar];
	}
	return $result;
}


// ============================================================
// MikhMon ROS7 Fork - Helper Functions
// PHP 8.x + RouterOS 7.x Compatibility
// ============================================================

/**
 * Detect RouterOS major version from version string
 * Returns 6 or 7 (integer)
 */
function mikhmon_ros_version($API) {
    $res = $API->comm("/system/resource/print");
    if (isset($res[0]['version'])) {
        $ver = $res[0]['version'];
        $major = (int)explode(".", $ver)[0];
        return $major;
    }
    return 6; // default to ROS6 behavior if unknown
}

/**
 * Parse RouterOS date string - handles both ROS6 and ROS7 formats
 * ROS6: "apr/08/2026" 
 * ROS7: "2026-04-08"
 * Returns: ['year'=>2026, 'month'=>4, 'day'=>8]
 */
function mikhmon_parse_date($datestr) {
    $datestr = trim($datestr);
    
    // ROS7 format: YYYY-MM-DD
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $datestr, $m)) {
        return ['year' => (int)$m[1], 'month' => (int)$m[2], 'day' => (int)$m[3]];
    }
    
    // ROS6 format: mon/dd/yyyy (e.g. apr/08/2026)
    $months = ['jan'=>1,'feb'=>2,'mar'=>3,'apr'=>4,'may'=>5,'jun'=>6,
               'jul'=>7,'aug'=>8,'sep'=>9,'oct'=>10,'nov'=>11,'dec'=>12];
    if (preg_match('/^([a-z]{3})\/(\d{2})\/(\d{4})$/i', $datestr, $m)) {
        $mon = strtolower($m[1]);
        return ['year' => (int)$m[3], 'month' => $months[$mon] ?? 1, 'day' => (int)$m[2]];
    }
    
    // Fallback
    return ['year' => (int)date('Y'), 'month' => (int)date('n'), 'day' => (int)date('j')];
}

/**
 * Parse RouterOS time string - handles both ROS6 and ROS7 formats
 * ROS6 duration: "3h", "2d3h15m30s", "1d10m"
 * ROS7 duration: "3:00:00", "2d 03:15:30"
 * Clock time: "14:30:00" (same in both)
 * Returns: seconds (integer)
 */
function mikhmon_duration_to_seconds($duration) {
    $duration = trim($duration);
    $total = 0;

    // ROS7 format with days prefix: "2d 03:15:30"
    if (preg_match('/^(\d+)d\s+(\d{2}):(\d{2}):(\d{2})$/', $duration, $m)) {
        return ((int)$m[1] * 86400) + ((int)$m[2] * 3600) + ((int)$m[3] * 60) + (int)$m[4];
    }

    // Pure HH:MM:SS format (ROS7 time or short duration)
    if (preg_match('/^(\d{2}):(\d{2}):(\d{2})$/', $duration, $m)) {
        return ((int)$m[1] * 3600) + ((int)$m[2] * 60) + (int)$m[3];
    }

    // ROS6 format: combinations of w, d, h, m, s
    if (preg_match('/[wdhms]/', $duration)) {
        if (preg_match('/(\d+)w/', $duration, $m)) $total += (int)$m[1] * 604800;
        if (preg_match('/(\d+)d/', $duration, $m)) $total += (int)$m[1] * 86400;
        if (preg_match('/(\d+)h/', $duration, $m)) $total += (int)$m[1] * 3600;
        if (preg_match('/(\d+)m/', $duration, $m)) $total += (int)$m[1] * 60;
        if (preg_match('/(\d+)s/', $duration, $m)) $total += (int)$m[1];
        return $total;
    }

    return 0;
}

/**
 * Format seconds into human-readable duration
 * e.g. 10800 => "3h", 90000 => "1d 01:00:00"
 */
function mikhmon_seconds_to_duration($seconds) {
    $seconds = (int)$seconds;
    if ($seconds <= 0) return "0s";
    $d = floor($seconds / 86400);
    $h = floor(($seconds % 86400) / 3600);
    $m = floor(($seconds % 3600) / 60);
    $s = $seconds % 60;
    if ($d > 0) {
        return sprintf("%dd %02d:%02d:%02d", $d, $h, $m, $s);
    }
    return sprintf("%02d:%02d:%02d", $h, $m, $s);
}

/**
 * Format RouterOS uptime/duration for display - ROS6 & ROS7 compatible
 * Converts both formats to a clean readable string
 */
function mikhmon_format_uptime($uptime) {
    $secs = mikhmon_duration_to_seconds($uptime);
    return mikhmon_seconds_to_duration($secs);
}

/**
 * Get ROS7-compatible on-login script for hotspot profile
 * This replaces the old scheduler-based approach that broke in ROS7
 * Works with both ROS6 and ROS7
 */
function mikhmon_build_onlogin($expmode, $price, $validity, $sprice, $lock, $name) {
    $lockscript = '';
    if ($lock == "Enable") {
        $lockscript = '; [:local mac $"mac-address"; /ip hotspot user set mac-address=$mac [find where name=$user]]';
    }

    $recordscript = '; :local mac $"mac-address"; :local time [/system clock get time ]; /system script add name="$date-|-$time-|-$user-|-' . $price . '-|-$address-|-$mac-|-' . $validity . '-|-' . $name . '-|-$comment" owner="$month$year" source=$date comment=mikhmon';

    // Base put command for MikhMon to read profile info
    $put = ':put (",' . $expmode . ',' . $price . ',' . $validity . ',' . $sprice . ',,' . $lock . ',")';

    if ($expmode == "0") {
        return ':put (",,' . $price . ',,,noexp,' . $lock . ',")' . $lockscript;
    }

    // ROS7-compatible expiry script using scheduler trick
    // Works on both ROS6 and ROS7
    $expscript = '{' .
        ':local date [ /system clock get date ];' .
        ':local year [ :pick $date 0 4 ];' .
        ':local month [ :pick $date 5 7 ];' .
        ':local comment [ /ip hotspot user get [/ip hotspot user find where name="$user"] comment];' .
        ':local ucode [:pic $comment 0 2];' .
        ':if ($ucode = "vc" or $ucode = "up" or $comment = "") do={' .
        '/sys sch add name="$user" disable=no start-date=$date interval="' . $validity . '";' .
        ':delay 2s;' .
        ':local exp [ /sys sch get [ /sys sch find where name="$user" ] next-run];' .
        ':local getxp [len $exp];' .
        ':if ($getxp = 19) do={' .
            '/ip hotspot user set comment=$exp [find where name="$user"];' .
        '};' .
        ':if ($getxp = 15) do={' .
            ':local d [:pic $exp 0 6];' .
            ':local t [:pic $exp 7 16];' .
            ':local s ("/");' .
            ':local exp ("$d$s$year $t");' .
            '/ip hotspot user set comment=$exp [find where name="$user"];' .
        '};' .
        ':if ($getxp = 8) do={' .
            '/ip hotspot user set comment="$date $exp" [find where name="$user"];' .
        '};' .
        ':if ($getxp > 15 and $getxp != 19) do={' .
            '/ip hotspot user set comment=$exp [find where name="$user"];' .
        '};' .
        '/sys sch remove [find where name="$user"]';

    $onlogin = $put . '; ' . $expscript;

    if ($expmode == "rem") {
        return $onlogin . $lockscript . '}}';
    } elseif ($expmode == "ntf") {
        return $onlogin . $lockscript . '}}';
    } elseif ($expmode == "remc") {
        return $onlogin . $recordscript . $lockscript . '}}';
    } elseif ($expmode == "ntfc") {
        return $onlogin . $recordscript . $lockscript . '}}';
    }

    return $onlogin . $lockscript . '}}';
}

/**
 * Build background scheduler script for profile monitoring
 * ROS6 & ROS7 compatible - checks both date formats
 */
function mikhmon_build_bgservice($name, $mode) {
    return ':local dateint do={' .
        ':local montharray ("jan","feb","mar","apr","may","jun","jul","aug","sep","oct","nov","dec");' .
        ':local dd; :local mm; :local yy;' .
        // ROS7 format YYYY-MM-DD
        ':if ([:len $d] = 10 and [:pick $d 4] = "-") do={' .
            ':set yy [:tonum [:pick $d 0 4]];' .
            ':set mm [:tonum [:pick $d 5 7]];' .
            ':set dd [:tonum [:pick $d 8 10]];' .
        '} else={' .
        // ROS6 format mon/dd/yyyy
            ':set dd [:tonum [:pick $d 4 6]];' .
            ':set yy [:tonum [:pick $d 7 11]];' .
            ':local month [:pick $d 0 3];' .
            ':local monthint ([:find $montharray $month]);' .
            ':set mm ($monthint + 1);' .
        '};' .
        ':if ([len $mm] = 1) do={:return [:tonum ("$yy" . "0" . "$mm" . "$dd")];} else={:return [:tonum ("$yy$mm$dd")];}' .
    '}; ' .
    ':local timeint do={' .
        ':local hours [:pick $t 0 2];' .
        ':local minutes [:pick $t 3 5];' .
        ':return ($hours * 60 + $minutes);' .
    '}; ' .
    ':local date [/system clock get date]; ' .
    ':local time [/system clock get time]; ' .
    ':local today [$dateint d=$date]; ' .
    ':local curtime [$timeint t=$time]; ' .
    ':foreach i in [/ip hotspot user find where profile="' . $name . '"] do={' .
        ':local comment [/ip hotspot user get $i comment];' .
        ':local uname [/ip hotspot user get $i name];' .
        ':local gettime "";' .
        // Handle both ROS6 comment format (mon/dd/yyyy HH:MM:SS) and ROS7 (YYYY-MM-DD HH:MM:SS)
        ':if ([:len $comment] >= 19) do={:set gettime [:pick $comment 11 19];};' .
        ':local isvalid false;' .
        // ROS7 format check: YYYY-MM-DD
        ':if ([:pick $comment 4] = "-" and [:pick $comment 7] = "-") do={:set isvalid true;};' .
        // ROS6 format check: mon/dd/yyyy  
        ':if ([:pick $comment 3] = "/" and [:pick $comment 6] = "/") do={:set isvalid true;};' .
        ':if ($isvalid) do={' .
            ':local expd [$dateint d=$comment];' .
            ':local expt [$timeint t=$gettime];' .
            ':if (($expd < $today) or ($expd = $today and $expt < $curtime)) do={' .
                '[/ip hotspot user ' . $mode . ' $i];' .
                '[/ip hotspot active remove [find where user=$uname]];' .
            '};' .
        '};' .
    '}';
}


?>
