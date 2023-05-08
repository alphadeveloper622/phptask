<?php
    require_once('verses_config.php');
	$dblink = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
	if ($dblink->connect_errno) {
		printf("Failed to connect to database");
		exit();
	}

	$iFetchCount = intval(GetParam('fetchcount', 5));
	if ($iFetchCount < 0) 	{ $iFetchCount = 0; }
	if ($iFetchCount > 10) 	{ $iFetchCount = 10; }
	if ($iFetchCount > 0)
	{ $sFetchLimit = " LIMIT " . $iFetchCount;	}
	else
	{ $sFetchLimit = ""; }
	$metaData['fetchlimit'] = $iFetchCount;

	$iMinPhraseWordCount = intval(GetParam('minphrasewordcount', 3));
	if ($iMinPhraseWordCount < 1) 	{ $iMinPhraseWordCount = 1; }
	if ($iMinPhraseWordCount > 6) 	{ $iMinPhraseWordCount = 6; }
	$metaData['minphrasewordcount'] = $iMinPhraseWordCount;
	$metaData['commonwordlist'] = implode(',', COMMON_WORDS);

	$sql = 
		"SELECT " . 
		"BV.verseid, BV.refcode, BV.refpretty, BV.transcode, BV.versetext, " .
		"'' AS uniquestartingphrase, " .
		"'' AS uniquewordlist, " .
		"'' AS versewordsclean, " .
		"'' AS keywordlist " .
		"FROM bibleverses BV " .
		"ORDER BY RAND()" . $sFetchLimit;

	// Fetch rows from test table
	$result = $dblink->query($sql);
	if (!$result) { echo "Database Query Error: " . $dblink -> error; } else {
		$dbdata = array();
		while ( $row = $result->fetch_assoc())  {
			// $dbdata[]=$row;
			$dbdata[]=array_map("utf8_encode", $row);
		}

		// Freelancer Project Tasks - Now that we have multiple records
		// and an integer value for $iMinPhraseWordCount
		//
		// A - Calculate the list in original word order 
		//     without punctuation in versetext for each row.
		//     Store that result in versewordsclean for each record.
		//
		// B - Make a list of keywords found in each verse 
		//     with no duplicates or punctuation.
		//     Store that result in keywordlist for each record.
		//
		// C - Find any words in this verse text that are unique 
		//     from all these selected verses
		//     and store that list as comma-delimited 
		//     in uniquewordlist with no spaces.
		//
		// D - Find the unique starting phrase for each verse 
		//     and store that in uniquestartingphrase.
		//     So how many words in do you have to go for 
		//     these verse to be different then the others here.
		//     And if less than $iMinPhraseWordCount, 
		//     then go at least that far in in the answer.
		//
		// Note apostrophes, conjuctions and possessives
		// count as a single word in each case.
		// So "it's" is a single word. 
		// "Paul's" is 1 word and so is "wasn't".
		//
		// We want to return an array of records identical 
		// to how the example above returns $dbdata[] as JSON.
		// If you have to create a new functional array to do this, 
		// that is fine.

		returnJsonHttpResponse(true, $dbdata);
	}
	$dblink = nil;
	
?>