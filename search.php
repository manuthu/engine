<?php
$connection_params = array(      
	"host"     => "localhost",   
	"username" => "root",        
	"password" => "mike",        
	"database" => "search"       
);                               

if (isset($_GET['search'])) {
	//XXX: load data
	$search = strip_tags($_GET['search']);
	$search_term = trim($search);
	$data = get_data();
	$results = array_filter($data, function($val) use ($search_term) {
		$exists = strripos($val['name'], $search_term);
		if ($exists === FALSE) {
			return false;
		} else {
			return true;
		}
	});

	//XXX: Get a sorted array with the various ranks.
	$sorted_results = get_sorted_results($results);

	foreach ($sorted_results as $result){
		$id = $result['id'];
		$profile_data = array_filter($results, function($val) use ($id) { 
			return $val['id'] == $id;                                  
		});
		//var_dump($profile_data);
		// Display the data.
		foreach( $profile_data as $profile){
			//TODO: Load a template??
			echo '<div id="search">';
			echo '</br/>Name: <a href="profile.php?id=' . $profile['id'] . '">' . $profile['name'] . '</a>';
			echo '</br/> Age:' . $profile['age'];
			echo '</br/> Phone: ' . $profile['phone'];
			echo '<div/>';
		}
	}
}

function get_sorted_results($profiles){
	global $connection_params;                 
	$username = $connection_params['username'];
	$password = $connection_params['password'];
	$database = $connection_params['database'];
	$host = $connection_params['host'];        

	//TODO: Clean this db access...
	$sql = "Select Keyword, Hits from KeyHits ";
	$pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$keywords = $stmt->fetchAll();

	//xxx: Rank the chunkified keywords.
	$new_profiles = array();
	$sort_array = array();
	foreach($profiles as $profile){
		//1. Rank by both the names
		$name_chunk = explode(' ', $profile['name']);
		$rank = 0;
		foreach($name_chunk as $name){
			$hit = 0;
			$result = array_filter($keywords, function($val) use ($name) {    
				if ($val['Keyword'] === $name) {
					return true;
				} else { 
					return false;
				}
			});
			foreach($result as $val){
				$hit = $val['Hits'];
			}
			// Increase the rank 
			$rank += $hit;
		}
		//TODO: 2. Rank by other profile details.
		$sort_array[] = array('id' => $profile['id'], 'rank' => $rank);
	}

	// Sort the array by ranks descending
	$ranks = array();
	foreach($sort_array as &$value){
		$ranks[] = &$value['rank'];
	}
	array_multisort($ranks, SORT_DESC, $sort_array);
	return $sort_array;
}

function get_data() {
	$string = file_get_contents("data/people.json");
	$json_a = json_decode($string, true);
	return $json_a['result'];
}

function array_results($val) {
	$name = 'o';
	$exists = strripos($val['name'], $name);
	if ($exists === FALSE) {
		return false;
	} else {
		return true;
	}
}

?>
