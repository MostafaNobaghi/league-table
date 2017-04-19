<?php
echo redirect_to();
$db;
//// CREATE DATABASE FILE AND CONNECT TO EXISTING DATABASE
function connect_to_db($dbName){
    global $db;
    if(!$db){
        $db=new SQLite3($dbName);
    }
    create_tables();
}

connect_to_db('league.database');


/////////////////////////////////////// CREATE TABLES FOR NEW DATABASE
function create_tables(){
    global $db;
    
    $db->query("
        CREATE TABLE IF NOT EXISTS team_data ( 
        id INTEGER PRIMARY KEY AUTOINCREMENT , 
        team_name TEXT NOT NULL , 
        `Games` INT UNSIGNED NOT NULL DEFAULT '0' , 
        `Win` INT UNSIGNED NOT NULL DEFAULT '0' , 
        `Loose` INT UNSIGNED NOT NULL DEFAULT '0' , 
        `Draw` INT UNSIGNED NOT NULL DEFAULT '0' , 
        `Points` INT NOT NULL DEFAULT '0' ,
        `GA` INT NOT NULL DEFAULT '0' , 
        `GF` INT NOT NULL DEFAULT '0' , 
        `GD` INT NOT NULL DEFAULT '0'
        
        )  
       
    ");
    
    $db -> query("
        CREATE TABLE IF NOT EXISTS league_data (
        set_matchs_status INTEGER UNSIGNED NOT NULL DEFAULT 0,
        priod INTEGER NOT NULL DEFAULT '0'
        )
    ");
    
    $db -> query("
        INSERT INTO league_data
        (set_matchs_status, priod)
        VALUES(
        0, 0
        )
    ");
    
    $db -> query("
       CREATE TABLE IF NOT EXISTS matchs_data(
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        match_id TEXT NOT NULL,
        home_team_name TEXT NOT NULL,
        away_team_name TEXT NOT NULL,
        home_team_goals INTEGER NOT NULL,
        away_team_goals INTEGER NOT NULL,
        is_match_finish INTEGER NOT NULL DEFAULT 0
       )
    ");
}



///////////////////////////////////////  ADD NEW PLAYER (TEAM) TO DATABASE, TEAM DATA TABLE
function add_team($team_name){
    global $db;
    $db->query("
        INSERT INTO team_data 
        (team_name) VALUES
        ('$team_name');
    ");
    
    $url = redirect_to();
   header("Location: $url");
   die();
}


function get_league_data(){
    global $db;
    $result = $db -> query("
        SELECT *
        FROM league_data
    ");
    $row = $result -> fetchArray(SQLITE3_ASSOC);
    return $row;
}


///////////////////////////////////////  CHECK IF ADDING PLAYER IS FINNISH,(  USE LATER TO DONT LET ADD NEW TEAM  (HIDE THE FORM)   )
function get_set_match_status(){
    $league_data = get_league_data();
    $bool = $league_data['set_matchs_status'];
    if($bool==0){
        return FALSE;
    } else {
        return TRUE;
    }
}


function team_count(){
    global $db;
    $result = $db -> query("
       SELECT count(id)
       FROM team_data
    ");
    $r = $result -> fetchArray(SQLITE3_ASSOC);
    return $r;
}


///////////////////////////////////////   CHANG THE STATUS AFTER FINISHING ADDS TEAM, 
function change_set_match_status(){
    global $db;
    $db -> query("
        UPDATE league_data
        SET set_matchs_status=1
        WHERE set_matchs_status=0
    ");
    $url = redirect_to();
    header("Location: $url");
    die();
    
}


/////////////////////////////////////// 
function get_teams_list(){
    global $db;
    $result = $db->query("
        SELECT team_name
        FROM team_data
        ");
    $rows=[];
    
    while ($row = $result -> fetchArray(SQLITE3_ASSOC)){
        $rows[] = $row;
    }
    return $rows;
}

function set_matchs(){
    $teams_list = get_teams_list();
    //$match_data = get_matchs($match_id);
    echo '<div class="matchs">'
    . '<h4>Put results and click "match" button to add result to database </h4>';
    
    $x=1;
    for($i=0; $i<count($teams_list)-1; $i++){
        for($j=$i+1; $j<count($teams_list); $j++){
            $match_id= 'match'.$x++;
            $match_data = get_matchs($match_id);
            
            
            if(isset($match_data[0])){
                $home_goals = $match_data[0]['home_team_goals'];
                $away_goals = $match_data[0]['away_team_goals'];
            } else {
                $home_goals = '';
                $away_goals = '';
            }
            
            $disable = '';
            if(isset($match_data[0])){
                if($match_data[0]['is_match_finish']==1){ $disable = 'disabled';}
            }
            
            ?>
<form  name="<?php echo $match_id; ?>">
    
    <input class="hide" name="home_team_name" id="home_team_name" value="<?php echo $teams_list[$i]['team_name']; ?>">
    <input class="hide" name="away_team_name" id="away_team_name" value="<?php echo $teams_list[$j]['team_name']; ?>">
            
    <label for="home_goals"><?php echo $teams_list[$i]['team_name']; ?> </label>
    <input type="text" name="home_goals" id="home_goals" value="<?php if($home_goals){echo $home_goals;} ?>" <?php echo $disable; ?>>
    
    <input type="text" name="away_goals" id="away_goals" value="<?php if($away_goals){echo $away_goals;} ?>" <?php echo $disable; ?> >
    <label for="away_goals"><?php echo $teams_list[$j]['team_name']; ?> </label>
    
    <input name="match_id" value="<?php echo $match_id; ?>" type="submit" <?php echo $disable; ?> >
    <br>
</form>
<br>
            <?php 
             
            
            //echo $teams_list[$i]['team_name'] .' --xxx-- '.$teams_list[$j]['team_name'];
        }
    }
    echo '</div>';
}

function add_to_match_table(){
    $match_id = $_GET['match_id'];
    $home_team_name = $_GET['home_team_name'];
    $away_team_name = $_GET['away_team_name'];
    $home_goals = $_GET['home_goals'];
    $away_goals = $_GET['away_goals'];

    if($home_goals > $away_goals){
        $home_result = 'Win';
        $away_result = 'Loose';
    } elseif ($home_goals < $away_goals) {
        $home_result = 'Loose';
        $away_result = 'Win';
    } else {
        $home_result = 'Draw';
        $away_result = 'Draw';
    }
    
    
    global $db;
    $db -> query("
        INSERT INTO matchs_data
        (match_id, home_team_name, away_team_name, home_team_goals, away_team_goals, is_match_finish) VALUES
        ('$match_id', '$home_team_name', '$away_team_name', '$home_goals', '$away_goals', '1')
    ");
    
    //////////////UPDATE GF COL FOR HOME TEAM
    $db -> query("
        UPDATE team_data
        SET GF = (GF + '$home_goals'), GA = (GA + '$away_goals'), '$home_result' = ($home_result + 1)
        WHERE team_name = '$home_team_name'
    ");
    //////////////UPDATE GF COL FOR AWAY TEAM
    $db -> query("
       
        UPDATE team_data
        SET GF = (GF + '$away_goals'), GA = (GA + '$home_goals'), '$away_result' = (`$away_result` + 1)
        WHERE team_name = '$away_team_name'
    ");
    
    //$home_points = calculate_point($home_team_name)
    $url = redirect_to();
    header("Location: $url");

    
}

function calculate_point($team_name){
    global $db;
    $result=$db->query("
        SELECT win, Draw
        FROM team_data
         WHERE team_name = '$team_name'
        
        ");
   $row=$result-> fetchArray(SQLITE3_ASSOC);
    
   $points = ($row['Win']*3)+($row['Draw']);
   
   $db -> query("
       UPDATE team_data
       SET Points = '$points'
       WHERE team_name = '$team_name'
       ");
    
    return $points;
    
}


function calculate_gd($team_name){
    global $db;
    $goals=$db->query("
        SELECT GF, GA
        FROM team_data
         WHERE team_name = '$team_name'
    ");
   $row=$goals-> fetchArray(SQLITE3_ASSOC);
   
   $gd = ($row['GF'])-($row['GA']);
   
   $db -> query("
       UPDATE team_data
       SET GD = '$gd'
       WHERE team_name = '$team_name'
       ");
   
    return $gd;
}



function table_render(){
    global $db;
    $result = $db->query("
        SELECT *
        FROM team_data
        ORDER BY
        Points DESC, 
        GD DESC;
        ");
    $rows=[];
    $counter=0;
    echo '<div class="table"><table>'; ?>
<tr><th colspan="10">LEAGUE TABLE </th></tr>
<tr>
    <th>#</th>
    <th>Team Name</th>
    <th>Games</th>
    <th>Win</th>
    <th>Loose</th>
    <th>Draw</th>
    <th>Points</th>
    <th>GF</th>
    <th>GA</th>
    <th>Goal Diffrent</th>
<?php
    while ($row = $result -> fetchArray(SQLITE3_ASSOC)){
        $counter++; ?>
        <tr>
            <td><?php echo $counter; ?></td>
            <td><?php echo $row['team_name']; ?></td>
            <td><?php echo  $row['Win']+$row['Loose']+$row['Draw']; ?></td>
            <td><?php echo $row['Win']; ?></td>
            <td><?php echo $row['Loose']; ?></td>
            <td><?php echo $row['Draw']; ?></td>
            <td class="points"><?php echo calculate_point($row['team_name']); ?></td>
            <td><?php echo $row['GF']; ?></td>
            <td><?php echo $row['GA']; ?></td>
            <td class="gd"><?php echo calculate_gd($row['team_name']); ?></td>
        </tr>
        
        <?php
    }
    echo '</table></div>';
}

function show_all(){
    global $db;
    $result = $db->query("
        SELECT *
        FROM team_data
        ");
    $rows = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)){
        $rows[]=$row;
    }
    echo '<pre>';
    var_dump($rows);
    
    
    echo '</pre>';

}
function show_matchs(){
    global $db;
    $result = $db->query("
        SELECT *
        FROM matchs_data
        ");
    $rows = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)){
        $rows[]=$row;
    }
    echo '<pre> MATCH DATA';
    var_dump($rows);
    
    
    echo '</pre>';
    

}

function get_matchs($match_id){
    global $db;
    $result = $db->query("
        SELECT *
        FROM matchs_data
        WHERE match_id = '$match_id'
        ");
    $rows = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)){
        $rows[]=$row;
    }
    return $rows;
    

}



function show_st(){
    global $db;
    $result = $db->query("
        SELECT *
        FROM league_data
        ");
    $rows = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)){
        $rows[]=$row;
    }
    echo '<pre>';
    var_dump($rows);
    
    
    echo '</pre>';

}

function redirect_to(){
    $prococol = $_SERVER["REQUEST_SCHEME"];
    $host = $_SERVER['HTTP_HOST'];
    $addr =$_SERVER['PHP_SELF'];
    $url = $prococol."://".$host.$addr;
    return $url;
}









   // echo '<br>abc';
//$abc=team_count();

//var_dump($abc);
//echo '<br>abc';
//$abc=team_count();

//var_dump($abc);
//echo '<br>';
//show_st();

    //$xz = get_league_data();
    //echo 'league_data: ';
   // var_dump($xz);
   // echo '<br>';
    //$teams_list = get_teams_list();
    //var_dump($teams_list);
    //show_matchs();
//show_all();


