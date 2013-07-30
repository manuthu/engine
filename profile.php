<?php

$connection_params = array(                  
    "host"     => "localhost",              
    "username" => "root",                   
    "password" => "mike",                   
    "database" => "search"                     
);                                          

if (isset($_GET['id'])) {

    $p_id = strip_tags($_GET['id']);
    $id = trim($p_id);
    $data = get_data("data/people.json");

    // 1. Get the profile data
    $profile_data = array_filter($data, function($val) use ($id) {
        return $val['id'] == $id;
    });

    // 2. Rank and dispay
    if (count($profile_data) < 1) {
        echo '<p> No data <p>';
    } else {
        // Split the user name data
        foreach ($profile_data as $profile){
            $chunks = explode(' ', $profile['name']);
            foreach($chunks as $keyword){
                //Rank the keyword.
                rank_keyword($keyword);
            }
        } 
        display_profile($profile_data);
    }
}

function get_data($json) {
    $string = file_get_contents($json);
    $json_a = json_decode($string, true);
    return $json_a['result'];
}

function rank_keyword($keyword){
    // 1. Select key from db
    $sql = "SELECT Keyword, Hits FROM KeyHits WHERE Keyword=:keyword";
    $params = array(
        ':keyword' => $keyword,
    );
    $stmt = _execute($sql, $params);
    $results = $stmt->fetchAll();

    // 2. Create a new entry or rank exists.
    if(count($results) == 0){
        // Add as new record
        $sql = "INSERT INTO KeyHits (Keyword, Hits)
            VALUES(:keyword, :hits) ";
        $params = array(
            ':keyword' => $keyword,
            ':hits' => 1
        );
    }else{
        // Update hits
        $hits = $results[0]['Hits'];
        $sql = "UPDATE KeyHits SET Hits=:hits WHERE Keyword=:keyword";
        $params = array(
            ':hits' => $hits+1,
            ':keyword' => $keyword
        );
    }
    $stmt = _execute($sql, $params);
}

function _execute($sql, $params){
    global $connection_params;
    $username = $connection_params['username'];                                   
    $password = $connection_params['password'];                                   
    $database = $connection_params['database'];                                   
    $host = $connection_params['host'];                                           

    try                                                                           
    {                                                                             
        $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);            
        $stmt = $pdo->prepare($sql);                                              
        $stmt->execute($params);                                                  
        $pdo = null;                                                              
        return $stmt;
    }                                                                             

    catch (PDOException $error)                                                   
    {                                                                             
        throw $error;
    }        

}

function display_profile($profile_data) {
    echo '
        <form action="search.php" method="GET" id="search">
        <div id="search-box">
        <input type="text" name="search"/>
        <input type="submit" value="Search"/>
        </div>
        </form>
        ';
    foreach ($profile_data as $profile) {
        echo '<p>Name:' . $profile['name'] . '</p>';
        echo '<p> Age:' . $profile['age'] . '</p>';
        echo '<p> Gender:' . $profile['gender'] . '</p>';
        echo '<p> Company: ' . $profile['company'] . '</p>';
        echo '<p> Phone: ' . $profile['phone'] . '</p>';
        echo '<p> Email: ' . $profile['email'] . '</p>';
        echo '<p> Address: ' . $profile['address'] . '</p>';
        echo '<p> About: ' . $profile['about'] . '</p>';
        echo '<p> Registered: ' . $profile['registered'] . '</p>';

        echo '<ul>';
        echo 'Friends ...';
        foreach ($profile['friends'] as $friend) {
            echo '<li>' . $friend['name'] . '</li>';
        }
        echo '</ul>';

        echo '<ul>';
        echo 'TAGS ...';
        foreach ($profile['tags'] as $tag) {
            echo '<li>' . $tag . '</li>';
        }
        echo '</ul>';
    }
}

?>
