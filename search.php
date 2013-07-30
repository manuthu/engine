<?php
$connection_params = array(      
    "host"     => "localhost",   
    "username" => "root",        
    "password" => "mike",        
    "database" => "search"       
);                               

if (isset($_GET['search'])) {
    //load data
    $search = strip_tags($_GET['search']);
    $search_term = trim($search);

    //TODO: Get hits
    $data = get_data();
    $results = array_filter($data, function($val) use ($search_term) {
        $exists = strripos($val['name'], $search_term);
        if ($exists === FALSE) {
            return false;
        } else {
            return true;
        }
    });
    //TODO: Get the updates from db.
    $results = get_sorted_results($results);

    //TODO: Record the number of times a file's been searched.
    //TODO: Display the results of the search.;
    echo 'Search for: ' . $name . '<br/>Hits: ' . count($results);
    foreach ($results as $profile) {
        echo '</br/>Name: <a href="profile.php?id=' . $profile['id'] . '">' . $profile['name'] . '</a>';
        echo '</br/> Age:' . $profile['age'];
        echo '</br/> Gender:' . $profile['gender'];
        echo '</br/> Phone: ' . $profile['phone'];

        echo '<ul>';
        echo 'Friends ...';
        foreach ($profile['friends'] as $friend) {
            echo '<li>' . $friend['name'] . '</li>';
        }
        echo '</ul>';
    }

    return;
    foreach ($data as $profile) {
        echo '</br/>' . $profile['name'];
        echo '<ul>';
        foreach ($profile['friends'] as $friend) {
            echo '<li>' . $friend['name'] . '</li>';
        }
        echo '</ul>';
    }
}

function get_sorted_results($profiles){
    global $connection_params;                 
    $username = $connection_params['username'];
    $password = $connection_params['password'];
    $database = $connection_params['database'];
    $host = $connection_params['host'];        

    //xxx: Lazy load the data from database
    $sql = "Select Keyword, Hits from KeyHits ";
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $keywords = $stmt->fetchAll();

    //xxx: Rank the chunkified keywords.
    $new_profiles = array();
    foreach($profiles as $profile){
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
            // Rank 
            $rank += $hit;
        }
        //xxx: Associate the rank with the profile
        $new_ar = array(
            $rank => $prifile
        );

        var_dump($new_ar);
        echo('<hr/>'); 
        array_push($new_profiles, $new_ar);      
    }

    var_dump($new_profiles);
    echo('<hr/>');
    return $profiles;
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
