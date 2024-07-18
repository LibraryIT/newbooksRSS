<?php
require_once('connection.php');
require_once('subjects.php');

//Examples - These pieces of code are put here to reference how to access data from FOLIO.

	//This is how to properly access data from the FOLIO server
	//echo $holdings[0]->items[0]->holdingsRecordId;

	//This code gets the total number of records returned by the API query - NOTE: json_decode true flag must be set.
	//echo intval($items[0]['totalRecords']);
	
	//This is how to properly accesss the title.  I am putting it here as a reference for how to access individual properties from the FOLIO response
	//without setting the true flag of json_decode.
	//var_dump($folioResponse[0]->instances[0]->title);
	
	//This code allows you to dynamically create a variable and then assign it a value.
	/*$words = "example";
	$$var = $words;
	$example = array("Hello", "World", "3");
	print_r($example);*/
	
	//This is how one accesses the properties of the $instance_array objects
	//$instance_array[0][0]->instances[0]->title

//Define variables and initialize arrays

//Initialize array to store response from FOLIO server
$folioResponse = array();

//Create array to hold items
$item_array = array();
$holdings_array = array();
$instance_array = array();

//Create object to hold title, URL, catalog date, and book cover image URL.
class record {

	function set_title($bookTitle){
		$this->title = $bookTitle;
	}
	
	function get_title(){
		return $this->title;
	}
	
	function set_uuid($UUID){
		$this->uuid = $UUID;
	}
	
	function get_uuid(){
		return $this->uuid;
	}
	
	function set_catalog_date($catalogDate){
		$this->catalogDate = $catalogDate;
	}
	
	function get_catalog_date(){
		return $this->addDate;
	}
	
	function set_isbn($ISBN){
		$this->isbn = $ISBN;
	}
	
	function get_isbn(){
		return $this->isbn;
	}
}

//This class contains the code to retrieve item, holdings, and instance records.  
class rssSet {
	
	function get_holdings($callNum, $createDate){
		global $tenant;
		global $token;

		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => '<placeholder>/holdings-storage/holdings?query=(('. $callNum .')%20AND%20metadata.createdDate%20%3E%20%22'.$createDate.'%22)&limit=150',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_HTTPHEADER => array(
			'X-Okapi-Tenant:'.$tenant. '',
			'x-okapi-token:'.$token. ''
		  ),
		));

		$responseH = curl_exec($curl);

		curl_close($curl);

		$holdings = json_decode($responseH, true);
		
		return $holdings;
				
	}
	
	function get_instances($instance_ID){
		$instance_recs = array();
		global $tenant;
		global $token;
		
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => '<placeholder>/inventory/instances?query=id==%22'.$instance_ID.'%22&limit%20=150',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_HTTPHEADER => array(
			'X-Okapi-Tenant:'.$tenant. '',
			'x-okapi-token:'.$token. ''
		  ),
		));

		$responseI = curl_exec($curl);

		curl_close($curl);
		
		array_push($instance_recs, json_decode($responseI));
		
		return $instance_recs;
	}
}

//This function takes an array and a columm as input.  It loads the column into an array with the key of the input array and uses array_multisort to put the latter in order.  
//Change the $direction variable to sort by ascending instead of descending.
//Source: https://stackoverflow.com/questions/14957449/sorting-xml-file-into-ascending-order-using-php 
function array_sort_by_column(&$array, $column, $direction = SORT_DESC) {
    $reference_array = array();
	
    foreach($array as $key => $row) {
		for($i=0; $i < count($row); $i++){
			$reference_array[$key][$i] = $row[$column];
		}
    }

    array_multisort($reference_array, $direction, $array);
}

//Create array to hold books for sorting
$books = array();

//Create array to hold record objects
$rssEntries = array();

//Base URl for Open Library Covers API
$open_lib_covers_url = 'https://www.syndetics.com/hw7.pl?isbn=';
//This prefix specifies the size of the image to be returned and the file extension.
$cover_url_postfix = '/SC.jpg';

//Initialize arrays to store, holdings IDs, instance IDs, and titles
$holdings_id = array();
$instance_id = array();
$instance_title = array ();
$instance_results = array();

//Base URL for VuFind
$vufindURL = '<placeholder>' ;

Try {
	//Here is where the action happens.  The script iterates over the $subjects array and passes each one to a new instance
	//of the rssSet object.  
	foreach($subjects as $key=>$value){
		//create second-level array for each subject area.
		$instance_id[$key] = array();
		
		//create a new instance of rssSet class
		$entry = new rssSet;
		
		//Get holdings records		
		$holdings_array = $entry->get_holdings($value, $API_date);
		
		//Retrieve instance ID
		if($holdings_array != null){
			for($i=0; $i < 11; $i++){
			//Pull out the year of publication from the call number
			$callNumYear = substr($holdings_array['holdingsRecords'][$i]['callNumber'], -4);
			
			//Only push instance IDs if the publication year is greater than a certain value
				if (intval($callNumYear) > date("Y")-2 ){
					array_push($instance_id[$key], $holdings_array['holdingsRecords'][$i]['instanceId']);
				}
			}					
		} else{
				echo "empty" . nl2br("\n");
				echo $API_date . nl2br("\n");
		}
	}

	//Loop through the array of instance IDs and retrieve instance records.  Then save them to a multi-dimensional array.
	foreach($instance_id as $key=>$value){
		$instance_array[$key] = array();
		foreach($value as $key2=>$value2){
			array_push($instance_array[$key], $entry->get_instances($value2));
		}
	}
		
	//Loop through the array of instance records, creating a new sub-array for each subject area (represented by $key).
	foreach($instance_array as $key=>$value){
		$rssEntries[$key] = array();
		
		//This loop doesn't iterate over $value (which is an array).  Rather with each iteration it calls a new instance 
		//of the record class and saves the title, UUID, and catalog date before pushing it to the end of the rssEntries array for the subject area.
		for($i = 0; $i < count($value); $i++){			
			$rssEntry = new record();
			
			$rssEntry->set_title($value[$i][0]->instances[0]->title);
			$rssEntry->set_uuid($value[$i][0]->instances[0]->id);
			$rssEntry->set_catalog_date(substr($value[$i][0]->instances[0]->metadata->createdDate, 0, 10));
					
			//This loop iterates over the identifiers array for each record returned by the out for loop and 
			//if the identifier type ID is either one of two values
			//set the ISBN using the value of the object in the array
			
			for($j=0; $j < count($value[$i][0]->instances[0]->identifiers); $j++){
				//Replace non-numeric characters in the ISBN with an empty character and store the results in a variable.
				$ISBN = preg_replace("/[^0-9]/", '', $value[$i][0]->instances[0]->identifiers[$j]->value);
				
				if ($value[$i][0]->instances[0]->identifiers[$j]->identifierTypeId == 'fcca2643-406a-482a-b760-7a7f8aec640e' or $value[$i][0]->instances[0]->identifiers[$j]->identifierTypeId == '8261054f-be78-422d-bd51-4ed9f33c3422'){
					//Get the character length of $ISBN and only call the set_isbn method if it equals 13 (i.e. ignore 10-digit ISBNs)				
					if (iconv_strlen($ISBN)==13){
						$rssEntry->set_isbn($ISBN);
						//Call the break function to stop execution once a single ISBN is set
						//Since many records have multiple ISBNs, doing so allows the script to ignore them.
						break;
					}																												
				}
			}
			
			array_push($rssEntries[$key], $rssEntry);
		}
		
	}

	//Loop through $rssEntries and set the ISBN to a placeholder value if it is empty.
	//This avoids a broken link icon from coming up in the newbooks feed display.
	foreach($rssEntries as $key=>$value){
		for($i=0; $i < count($value); $i++){
			if($value[$i]->isbn == null){
				$value[$i]->isbn = '9780000000000';
			}
		}
	}


	//Loop through the $rssEntries array and create the XML files for the RSS feed.
	foreach($rssEntries as $key=>$value){

		//Create file name
		$fileName = '/var/www/webroot/ROOT/newbooksRSS/' . $key . '.xml';
		
		//Use DOM to create the XML document
		$doc = new DOMDocument();
		$doc->formatOutput = true;
		
		
		//Create RSS and channel elements
		$root = $doc->createElement('rss');
		$rssVersion  = $doc->createAttribute('version');
		$rssVersion->value = '2.0';
		$root->appendChild($rssVersion);
		
		$doc->appendChild($root);
		
		$channel = $doc->createElement('channel');
		$root->appendChild($channel);
		
		$channelTitle = $doc->createElement('title');
		$channelTitle->nodeValue = 'New books in ' . $key;
		$channel->appendChild($channelTitle);
		
		$channelLink = $doc->createElement('link');
		$channelLink->nodeValue = 'https://lib.skidmore.edu/';
		$channel->appendChild($channelLink);
		
		$channelDescription = $doc->createElement('description');
		$channelDescription->nodeValue = 'New books RSS feeds';
		$channel->appendChild($channelDescription);
		
		//If there are no newly cataloged books $value will be null.  In such cases create a file which says there aren't any new books.
		//Otherwise create the RSS feed XML files.
		if ($value == null){
			$item = $doc->createElement('item');
			$channel->appendChild($item);
			
			$title = $doc->createElement('title');
			$title->nodeValue = 'No new books';
			$item->appendChild($title);
		} else {
			//Loop through the $rssEntries, creating an item element and its children then save the values of each entry.
			for ($i=0; $i < count($value); $i++){
				$item = $doc->createElement('item');
				$channel->appendChild($item);
				
				$guid = $doc->createElement('guid');
				$guid->nodeValue = $vufindURL . $value[$i]->uuid;
				$item->appendChild($guid);
				
				$title = $doc->createElement('title');
				$title->nodeValue = htmlspecialchars($value[$i]->title);
				$item->appendChild($title);
				
				$link = $doc->createElement('link');
				$link->nodeValue = $vufindURL . $value[$i]->uuid;
				$item->appendChild($link);
								
				$description = $doc->createElement('description');
				$description->nodeValue = 'Date Added: '.$value[$i]->catalogDate.PHP_EOL;
				$item->appendChild($description);
				
				$cdata = $doc->createCDATASection('<img src="'. $open_lib_covers_url . $value[$i]->isbn . $cover_url_postfix. '" />'.PHP_EOL);
				$description ->appendChild($cdata);
				
				$pubDate = $doc->createElement('pubDate');
				$pubDate->nodeValue = $value[$i]->catalogDate;
				$item->appendChild($pubDate);
			}
		}
		
		//Create file name
		$doc->save($fileName);
	}
	
	//Load each XML file and pass it to the sorting function
	//This section of the script is needed to ensure the XML file is sorted by date in descending order.
	//There are likely better ways to accomplish this, but I do not know them. - MP 11/09/2022
	foreach($rssEntries as $key=>$value){
		$books = array();
		$fileName = '/var/www/webroot/ROOT/newbooksRSS/' . $key . '.xml';
		
		$file = simplexml_load_file($fileName);
		
		foreach($file->channel->item as $items){
			$books[] = array(
				'id' => (string)$items->guid,
				'title' => (string)$items->title,
				'link' => (string)$items->link,
				'description' => (string)$items->description,
				'pubDate' => strtotime($items->pubDate)		
			);
		}
		
		array_sort_by_column($books, 'pubDate');

		//Use DOM to create the XML document
		$doc = new DOMDocument();
		$doc->formatOutput = true;
		
		
		//Create RSS and channel elements
		$root = $doc->createElement('rss');
		$rssVersion  = $doc->createAttribute('version');
		$rssVersion->value = '2.0';
		$root->appendChild($rssVersion);
		
		$doc->appendChild($root);
		
		$channel = $doc->createElement('channel');
		$root->appendChild($channel);
		
		$channelTitle = $doc->createElement('title');
		$channelTitle->nodeValue = 'New books in ' . $key;
		$channel->appendChild($channelTitle);
		
		$channelLink = $doc->createElement('link');
		$channelLink->nodeValue = 'https://lib.skidmore.edu/';
		$channel->appendChild($channelLink);
		
		$channelDescription = $doc->createElement('description');
		$channelDescription->nodeValue = 'New books RSS feeds';
		$channel->appendChild($channelDescription);
		
		//If there are no newly cataloged books $value will be null.  In such cases create a file which says there aren't any new books.
		//Otherwise create the RSS feed XML files.
		if ($value == null){
			$item = $doc->createElement('item');
			$channel->appendChild($item);
			
			$title = $doc->createElement('title');
			$title->nodeValue = 'No new books';
			$item->appendChild($title);
		} else {
			//Loop through the $rssEntries, creating an item element and its children then save the values of each entry.
			for ($i=0; $i < count($value); $i++){
				$item = $doc->createElement('item');
				$channel->appendChild($item);
				
				$guid = $doc->createElement('guid');
				$guid->nodeValue = $books[$i]['id'];
				$item->appendChild($guid);
				
				$title = $doc->createElement('title');
				$title->nodeValue = htmlspecialchars($books[$i]['title']);
				$item->appendChild($title);
				
				$link = $doc->createElement('link');
				$link->nodeValue = $books[$i]['id'];
				$item->appendChild($link);
								
				$description = $doc->createElement('description');
				$description->nodeValue = $books[$i]['description'].PHP_EOL;
				$item->appendChild($description);

			}
		}
		
		$doc->save($fileName);
		
	}
	
		
}catch (Exception $e){
		echo $e->getMessage(), "\n";
}

?>