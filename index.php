<!DOCTYPE html>
<?php
require_once 'funcs.php';

if(isset($_GET['team'])){
    $teamName=$_GET['team'];
    add_team($teamName);
}

if(isset($_GET['set-matchs'])){
    change_set_match_status();
}

if(isset($_GET['match_id'])){

    add_to_match_table();
}
?>

<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
        <link rel="stylesheet" type="text/css" href="../league-sqlite_2/styles/style.css">
    </head>
    <body>
        <?php
            table_render();
            if(!get_set_match_status()):
        ?>
        
        <div id="add-team-form">
            <h3>To add a player to game, enter a team name and click "ADD" button: </h3>
            <form method="get">
                
                <!--<input value="" name="league" type="text" placeholder="league name"><br>-->
                <input name="team" type="text" placeholder="team name">
                <input value="ADD" name="add" type="submit">
            </form>
            <form>
                
                <input id="set-matchs" value="set-matchs" name="set-matchs" type="submit" >
            </form>
        </div>
        <?php else: ?>
        <div class="matchs-table"><?php set_matchs(); ?></div>
        
        <?php    endif; ?>
            
            
        
        <script type="text/javascript" >
            
        </script>
    </body>
</html>
