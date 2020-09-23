<?php
include 'db.php';
$conn = OpenCon();
echo "Connected Successfully! <br>";
$bounceActive = 0;
$bounce;

    if(isset($_POST['newBounce'])){
        
        $sql = "SELECT uid, timestamp, caught, bouncer, bouncie FROM bounces ORDER BY uid DESC LIMIT 1";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $time = strtotime($row["timestamp"]);
                $curtime = time();
                $timeremaining = $curtime-$time;

                if(($timeremaining) > 60 || $row["caught"]==1) {                    
                    echo "You threw a new bounce! <br>";
                    $sql = "INSERT INTO bounces (bouncer) VALUES ('" . addslashes($_REQUEST['bouncer']) . "')";
                    
                    $sql2 = "
                        INSERT INTO 
                            bouncers (name, throws)
                        VALUES 
                            ('" . addslashes($_REQUEST['bouncer']) . "', bouncers.throws + 1)
                        ON DUPLICATE KEY UPDATE throws = bouncers.throws + 1";
                    
                    if (mysqli_query($conn, $sql) && mysqli_query($conn, $sql2)) {
                        echo "New bounce thrown successfully! <br>";
                        $bounceActive = 1;
                    } else {
                        echo "Oh no! You fumbled the ball whilst throwing it it!: " . $sql . "<br>" . $conn->error . "<br>";
                    }
                } else {
                    echo "Hold on! You haven't got the ball anymore, it's already in the air! <br>";
                }
            }
        }
                
    } else if(isset($_POST['catch'])){ 
        echo "You caught the bounce! <br>";
        
        $sql = "
        UPDATE 
            bounces
        SET 
            bounces.caught=1, 
            bounces.bouncie='" . addslashes($_REQUEST['bouncie']) . "'
        ORDER BY uid DESC 
        LIMIT 1";
                
        $sql2 = "
        INSERT INTO 
            bouncers (name, catches, score)
        VALUES 
            ('" . addslashes($_REQUEST['bouncie']) . "', bouncers.catches + 1, bouncers.score + 1)
        ON DUPLICATE KEY UPDATE catches = bouncers.catches + 1, score = bouncers.score + 1";
        
        if (mysqli_query($conn, $sql) && mysqli_query($conn, $sql2)) {
            echo "You now have the ball! <br>";
            $bounceActive = 0;
            
            header('location: index.php');
        } else {
            echo "Oh no! You fumbled the ball whilst catching it!: " . mysqli_error($conn) . "<br>";
        }
    }

    echo "Checking for fresh bounces... <br>";

    $sql = "SELECT uid, timestamp, caught, bouncer, bouncie FROM bounces ORDER BY uid DESC LIMIT 1";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            echo "Bounce trace found... <br>";
            $bounce = $row;

            $time = strtotime($row["timestamp"]);
            $curtime = time();
            $timeremaining = $curtime-$time;

            if(($timeremaining) < 60 && $row["caught"]==0) {     //60 seconds
                $bounceActive = 1;
                echo "&nbsp; uid: Bounce " . $row["uid"]. " - Timestamp: " . $row["timestamp"]. " – Caught: " . $row["caught"]. " - thrown by: " . $row["bouncer"] . " <br>";
                echo "&nbsp; Incoming Bounce! <br>";
                $bounce["timeRemaining"] = (60 - $timeremaining);
                echo "&nbsp; Time Remaining " . $bounce["timeRemaining"] . " seconds! <br>";
            } else {
                echo "&nbsp; uid: Bounce " . $row["uid"]. " - Timestamp: " . $row["timestamp"]. " – Caught: " . $row["caught"]. " - thrown by: " . $row["bouncer"] . " <br>";
                echo "&nbsp; Bounce has already ended... <br>";
                echo "&nbsp; Bounce Age: " . $timeremaining . " seconds...<br>";
                
                if ($bounce["caught"] == 1) {
                    echo "&nbsp; The bounce WAS caught! <br>";
                    echo "&nbsp; Caught by: " . $row["bouncie"] . " <br>";
                } else {
                    echo "&nbsp; The bounce was NOT caught! <br>";
                }
                echo "The ball is available to be claimed.";
            }

        }
    } else {
        echo "No bounces in the whole universe? Something has gone wrong";
    }

?>

<html>
    <head>
        <title>Bounce</title>
        <style>
            body, html {
                padding: 0;
                margin: 0;
                background-color: #000;
                color: lime;
                text-align: left;
                font-family: "Courier New";
                font-size: 14px;
                overflow: hidden;
            }
            #container {
                text-align: center;
                color: #fff;
                font-family: "Candara";
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                z-index: 999;
            }
            td {
                padding: 1px 15px;
            }
            table tr td {
                color: yellow;
            }
            table tr:first-child td {
                font-weight: 900;
            }
            form input {
                font-size: 3em;
                text-align: center;
                margin: 20px;
            }
            form input#bouncebutton {
                font-size: 10rem;
            }
            #confirmCatch {
                display: none;
            }
            #catch {
                background-color: red;
                color: white;
                font-size: 1rem;
                border-radius: 50%;
                padding: 10px;
                transition: all 0.2s ease-in-out;
                background: url(ball.png);
                background-size: cover;
                background-position: center;
                color: white;
                border: solid rgba(0,0,0,0.2) 2px;
                background-repeat: no-repeat;
            } 
            .y:active, .x:active, #catch:active {
              -webkit-animation-play-state:paused;
              -moz-animation-play-state:paused;
              -o-animation-play-state:paused;
              animation-play-state:paused;
              cursor: pointer;
            }
            .catchContainer, #catch {
                width: 100px;
                height: 100px;
            }
            .catchContainerContainer {
                position: absolute;
                top:0;
                bottom: 0;
                right: 0;
                left: 0;
            }
            #catch:hover {
              -webkit-animation-play-state:paused;
              -moz-animation-play-state:paused;
              -o-animation-play-state:paused;
              animation-play-state:paused;
              cursor: pointer;
            }
            .y:hover, .x:hover { cursor: pointer; }
            input#catch:active { transform: scale(0.8); }
            .x { animation: x 13s linear infinite alternate; }
            .y { animation: y 7s linear infinite alternate; }
            #catch { animation: rotate 12s linear infinite alternate; }
            @keyframes x { 100% { transform: translateX( calc(100vw - 100px) ); } }
            @keyframes y { 100% { transform: translateY( calc(100vh - 100px) ); } }
            @keyframes rotate { 100% {  transform: rotateZ(360deg); }}
        </style>
    </head>
    <body>
        <div id="container">
            <h1>Welcome to Digital Bounce</h1>

        <?php if($bounceActive === 0) { ?>
            <h2>There is currently no Bounce active</h2>
            <h3>Start a Bounce</h3>
            <form action="#" method="post">
                <input id="bouncer" type="text" name="bouncer" required placeholder="What's your name?"/><br>
                <input id="bouncebutton" type="submit" name="newBounce" value="BOUNCE!"/>
            </form>
        </div>
        
        <?php } else { ?>
            <h2>BOUNCE!</h2>
            <h3 style="font-size: 10em;" id="time"><?php echo $bounce["timeRemaining"]; ?></h3>
            
            <div id="confirmCatch">
                <h2>You caught it!</h2>
                <form name="catchForm" action="#" onsubmit="return validateForm()" method="post">
                    <input id="bouncie" type="text" name="bouncie" required placeholder="What's your name?"/><br>
                    <input id="confirmCatchButton" type="submit" name="catch" value="I caught it!"/>
                </form>
            </div>
        </div>
            <div class="catchContainerContainer">
                    <div class="catchContainer x">
                        <div class="y">
                            <input id="catch" type="submit" name="catchBall" onClick="catchTheBall()" value=""/>
                        </div>
                    </div>
            </div>

            <script>
                // Update the count down every 1 second
                var x = setInterval(function() {
                    var time =  document.getElementById("time").innerHTML;


                  // Output the result in an element with id="demo"
                  document.getElementById("time").innerHTML = time - 1;

                  if (time < 45) { document.getElementById("time").style.color = "yellow"; }
                  if (time < 30) { document.getElementById("time").style.color = "orange"; }
                  if (time < 15) { document.getElementById("time").style.color = "red"; } 
                  if (time < 1) { 
                    clearInterval(x); 
                    document.getElementById("time").innerHTML = "FAIL";
                    document.getElementById("catch").style.display = "none";
                    document.getElementById("confirmCatch").style.display = "block";
                  }
                }, 1000);
                
                function validateForm() {
                  var x = document.forms["catchForm"]["bouncie"].value;
                  if (x == '<?php echo "" . $bounce["bouncer"] . ""; ?>') {
                    document.getElementById("confirmCatch").style.display = "none";
                    document.getElementById("catch").style.display = "block";
                    document.getElementById("time").innerHTML = "FUMBLED!";
                    document.getElementById("time").style.color = "red";
                    alert("You can't catch your own throw! Send a bounce and let a friend know they need to ctach it!");
                    window.location = '/bounce/index.php';
                    return false;
                  }
                }
                
                function catchTheBall() {
                    document.getElementById("confirmCatch").style.display = "block";
                    document.getElementById("catch").style.display = "none";
                    document.getElementById("time").innerHTML = "Success! <span style='font-size: 0.6em;'>Quick don't drop it!</span>";
                    document.getElementById("time").style.color = "green";
                    clearInterval(x); 
                }
            </script>
        <?php } ?> 
    
    <div id="leaderboard" style="position: absolute; text-align: right; bottom:0; right: 0; padding-bottom: 10px; color: yellow;">

        <?php
            $sql = "SELECT name, throws, catches, score FROM bouncers ORDER BY score DESC LIMIT 10";
            $result = mysqli_query($conn, $sql);
                            
            echo "
            <table>
                <span style='width: 100%; display: block; text-align: center;'>Leaderboard</span>
                <tr>
                    <td>Name</TD>
                    <td>Throws</td>
                    <td>Catches</td>
                    <td>Score</td>
                </tr>";

            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    echo "
                        <tr>
                            <td>" . $row["name"] . "</td>
                            <td>" . $row["throws"] . "</td>
                            <td>" . $row["catches"] . "</td>
                            <td>" . $row["score"] . "<td>
                        </tr>";
                }
            }
        
            echo "
            </table>";

            CloseCon($conn);
        ?>
    </div>

    </body>
    </html>
    
    