<?php
    const ALLOW_CROSS_ORIGIN = true;

    // TODO - Setting DB values for your local test database!!!
	$dbhost = 'localhost';
	$dbuser = 'root';
	$dbpass = '';
    $dbname = 'acts238qb';

	// Here is the common words array to use when making a list
	// of keywords found for Task B.
    const COMMON_WORDS = array(
        "a",
        "an",
        "and",
        "as",
        "be",
        "but",
        "did",
        "for",
        "from",
        "have",
        "he",
        "i",
        "in",
        "it",
        "me",
        "of",
        "on",
        "one",
        "our",
        "shall",
        "she",
        "so",
        "that",
        "the",
        "them",
        "there",
        "these",
        "they",
        "this",
        "thy",
        "to",
        "unto",
        "was",
        "we",
        "were",
        "with",
        "which",
        "ye",
        "yet",
        "you",
        "your"
    );

	// Freelancer note, you are welcome to look at these functions below,
	// and add your own here, but do not change my functions without
	// permission please.
	
	// TODO add any parsing functions here...

    //-----------------------------------------------------------------------------------
    // Meta Data Array
    // Used to track performance statistics passed back to calling app/website
    //-----------------------------------------------------------------------------------
    $dStart = date('m/d/Y h:i:s a', time());
    $tokenData = [];
    $tokenData['received'] = $dStart;
    $metaData = [];
    $metaData['received'] = $dStart;
    $metaStartTime = microtime(true);
    $isHTTPS = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
               (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
    if ($isHTTPS)
    {
        // SSL connection
        $metaData['ssl'] = 'True';
    } else {
        // No SSL
        $metaData['ssl'] = 'False';
    }

    //-----------------------------------------------------------------------------------
	function GetParam($sParameter, $uDefaultValue)
	{
		if (isset($_REQUEST[$sParameter])) 
		{
			return $_REQUEST[$sParameter];
		}
		else
		{
			return $uDefaultValue;
		}
	}

    //-----------------------------------------------------------------------------------
	function AllowedCharsOnly($sInput, $sSpecialChars) {
		// Get allowed characters only
		$sAllowed = preg_replace("/[^a-zA-Z" . $sSpecialChars . "]/", "", $sInput);
		// Trim outside spaces and dupe spaces
		return trim(preg_replace('/\s+/', ' ', $sAllowed));
	}	

    //-----------------------------------------------------------------------------------
    function headerCheckCrossOrigins() {
        if (ALLOW_CROSS_ORIGIN) { header("Access-Control-Allow-Origin: *"); }
        // res.header("Access-Control-Allow-Origin", "*");
        // res.header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept, Access-Control-Allow-Headers, Access-Control-Request-Method, Access-Control-Request-Headers, Authorization");
        // res.header('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, PATCH, OPTIONS');
    }

    //-----------------------------------------------------------------------------------
	function FloatToStr($fInput) {
		return number_format((float)$fInput, 1, '.', '');
	}

    //-----------------------------------------------------------------------------------
	function getRandomString($iLength) { 
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
		$randomString = ''; 
		for ($i = 0; $i < $iLength; $i++) { 
			$index = rand(0, strlen($characters) - 1); 
			$randomString .= $characters[$index]; 
		} 
		return $randomString; 
	} 

    //-----------------------------------------------------------------------------------
    // cleanupHeaderForJSONOutput
    // My battle-tested and proven PHP header cleanup for rest api json!!!
    //-----------------------------------------------------------------------------------
    function cleanupHeaderForJSONOutput()
    {
        // remove any string that could create an invalid JSON
        // such as PHP Notice, Warning, logs...
        ob_clean();

        // this will clean up any previously added headers, to start clean
        header_remove();

        // Set the content type to JSON and charset
        // (charset can be set to something else)
        header("Content-Type: application/json; charset=utf-8");
        headerCheckCrossOrigins();

        // Absolutely no caching allowed!!!
        $ts = gmdate("D, d M Y H:i:s") . " GMT";
        header("Expires: $ts");
        header("Last-Modified: $ts");
        header("Pragma: no-cache");
        header("Cache-Control: no-cache, must-revalidate");
    }

    //-----------------------------------------------------------------------------------
    // returnJsonHttpResponse
    // $success : boolean
    // $data : object or array
    //-----------------------------------------------------------------------------------
    function returnJsonHttpResponse($success, $data, $returnJustRows = false)
    {
        returnJsonHttpResponseExtra($success, $data, $returnJustRows, '', null);
    }

    //-----------------------------------------------------------------------------------
    // returnJsonHttpResponseExtra
    // $success : boolean
    // $data : object or array

    //-----------------------------------------------------------------------------------
    function returnJsonHttpResponseExtra(
               $success, $data, $returnJustRows,
               $extra1Desc, $extra1Data, 
               $extra2Desc = '', $extra2Data = null,
               $extra3Desc = '', $extra3Data = null)
    {
        cleanupHeaderForJSONOutput();

        // Save performance metrics
        global $metaData;
        global $metaStartTime;
        $timeSeconds = microtime(true) - $metaStartTime;
        $metaData['elapsedtime'] = strval(round( $timeSeconds * 1000 )) . ' ms';

        // Set your HTTP response code, 2xx = SUCCESS,
        // anything else will be error, refer to HTTP documentation
        if ($success)
        {
            // Do we have content or not in $data?
            if (count($data) > 0) {
                // We have data to show!
                http_response_code(200);
                if ($returnJustRows) {
                    echo json_encode($data);
                } else {
                    $metaData['responsetype'] = 'Row Data';
                    $metaData['rowcount'] = count($data);
                    $metaData['success'] = true;
                    $theWholeEnchalada =
                        array(
                            'meta' => $metaData,
                            'data' => $data
                        );
                    // Looking at extra data sets, do we include them?
                    // Cannot use array_push with key value pairs
                    if (($extra1Desc !== '') && (!is_null($extra1Data)))
                    { $theWholeEnchalada += [$extra1Desc => $extra1Data]; }
                    if (($extra2Desc !== '') && (!is_null($extra2Data)))
                    { $theWholeEnchalada += [$extra2Desc => $extra2Data]; }
                    if (($extra3Desc !== '') && (!is_null($extra3Data)))
                    { $theWholeEnchalada += [$extra3Desc => $extra3Data]; }
                    echo json_encode($theWholeEnchalada);
                }
            }
            else
            {
                // http_response_code(204);
                // HTTP Specification says 204 must not return anything
                // echo '{"Info": {"Text":"No Content"}}';
                http_response_code(200);
                $metaData['responsetype'] = 'No Data Found';
                $metaData['rowcount'] = 0;
                $metaData['success'] = false;
                $theWholeEnchalada =
                    array(
                        'meta' => $metaData,
                        'data' => $data
                    );
                echo json_encode($theWholeEnchalada);
            }
        }
        else
        {
            $metaData['success'] = false;
            http_response_code(500);
            $theWholeEnchalada = array(
                'meta' => $metaData,
                'message' => 'Unexpected Rest Layer Error'
            );
            echo json_encode($theWholeEnchalada);
            // echo '{"Info": {"Text":"Unexpected Rest Layer Error"}}';
        }

        // making sure nothing is added
        exit();
    }

    //-----------------------------------------------------------------------------------
    // returnJsonHttpStatus
    // $success : boolean
    // $jsonMessage : Json string
    //-----------------------------------------------------------------------------------
    function returnJsonHttpStatus($success, $messageType, $messageText, $rowIDDesc = null, $rowIDValue = null)
    {
        cleanupHeaderForJSONOutput();

        // Save performance metrics
        global $metaData;
        global $metaStartTime;
        $timeSeconds = microtime(true) - $metaStartTime;
        $metaData['elapsedtime'] = strval(round( $timeSeconds * 1000 )) . ' ms';
        $metaData['responsetype'] = 'Message';

        // Set your HTTP response code, 2xx = SUCCESS,
        // anything else will be error, refer to HTTP documentation
        if ($success)
        {
            http_response_code(200);
            $metaData['success'] = true;
        }
        else
        {
            http_response_code(200);
            $metaData['success'] = false;
        }

        $metaData['message'] = $messageText;
        if (!is_null($rowIDDesc) && !is_null($rowIDValue))
        {
            $metaData[$rowIDDesc] = $rowIDValue;
        }

        // Assumes message is already encoded as a Json-compatible string
        // $theWholeEnchalada = array( 'meta' => $metaData, 'message' => '[' . $jsonMessage . ']' );
        // echo json_encode($theWholeEnchalada);
        // echo $jsonMessage;
        $theWholeEnchalada = array( 'meta' => $metaData );
        echo json_encode($theWholeEnchalada);

        // making sure nothing is added
        exit();
    }

    /****************** This is Gerard' s code ************************** */ 
   
    //----------------------------------------------------------------------
    function getRandomVerseId() { 
        return rand(17, 100);
	} 

    //----------------------------------------------------------------------

    function getRandomRefcode($iLength=5) { 
        $characters = '0123456789'; 
		$randomString = ''; 
		for ($i = 0; $i < $iLength; $i++) { 
			$index = rand(0, strlen($characters) - 1); 
			$randomString .= $characters[$index]; 
		} 
		return $randomString; 
	} 

    //----------------------------------------------------------------------

    function getRandomRefpretty() { 
        $hour = rand(0, 23);
        $minute = rand(0, 59);

        $timeString = date('H:i', mktime($hour, $minute));
        return $timeString; // Output: a random t
	} 

    //----------------------------------------------------------------------

    function getRandomVersetext() { 
        $sentence = '';
        $cnt = rand(0, 20);
        for ($i = 0; $i < $cnt; $i++) {
        $randomIndex = rand(0, count(COMMON_WORDS) - 1);
        $sentence .= COMMON_WORDS[$randomIndex] . ' ';
        }

        return $sentence;
	} 

    //----------------------------------------------------------------------

    function getRandomObject() { 
        $obj = new stdClass();
        $obj->verseid = getRandomVerseId();
        $obj->refcode = "0440".getRandomRefcode();
        $obj->refpretty ="Acts " .getRandomRefpretty()."KJV";
        $obj->transcode = "KJV";
        $obj->versetext = getRandomVersetext();
        $obj->uniquestartingphrase = "";
        $obj->uniquewordlist = "";
        $obj->versewordsclean = "";
        $obj->keywordlist = "";

        return $obj;
	} 

    //----------------------------------------------------------------------
    
    function getRandomObjectArray($cnt=5) { 
        $objectArray = array();
	    
		for ($i = 0; $i < $cnt; $i++) {
            $objectArray[]=getRandomObject();
        }
		
        return $objectArray;
	} 
    
    //----------------------------------------------------------------------

    function getRandomizeArrayOrder($arr) {
        $newArr = array();
        $keys = array_keys($arr);
        shuffle($keys);  // Change keys order
        foreach($keys as $key) {
          $newArr[$key] = $arr[$key];
        }
        return $newArr;
      }

?>