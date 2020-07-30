<?php
    // Date: January 30, 2020
    // Last Edit Author: Jessica Thompson
    // Purpose: Index/home page to show all rooms
    
    SESSION_START();
    require_once('lib/config.php');
    require_once('lib/utilities.php');
    include('lib/verifyLoggedin.php');
    //If user clicks logout, they are logged out, and page is reset
    if (isset($_GET['message']) && $_GET['message'] == "logout") {
        $userName = $_GET['userName'];
        session_unset();
        session_destroy();
        //Username sent in the get to display a logout message
        header("Location: index.php?userName=$userName");
    }
    
    
    // if mysqli_connect_errno() is set, we did not successfully connect. Here we deal with the error.
    if (mysqli_connect_errno()) {
        echo 'Error: Could not connect to database.  Please try again later.</body></html>';
        exit;
    }
    
    //------------------------------------LOGIN QUERY START------------------------------------
    //If username is sent via post, user is attempting to login,
    if (isset($_POST['userName'])) {
        //testing and escaping username and password to be used in query
        $userName = $mysqli->real_escape_string($_POST['userName']);
        $password = $mysqli->real_escape_string($_POST['password']);
        $password = sha1($_POST['password']);
        
        //Query to check if valid login
        $query = "Select * From internal_users WHERE username like '" . $userName . "' AND encryptPass = '" . $password . "'";
        
        if ($userResult = $mysqli->query($query)) {
            //if $result returns a row, user submitted valid login data
            if ($userResult->num_rows == 1) {
                $userData = $userResult->fetch_all(MYSQLI_ASSOC);
                $thisUser = null;
                foreach ($userData as $data) {
                    $counter = 0;
                    foreach ($data as $k => $v) {
                        $thisUser[$counter] = $v;
                        $counter++;
                    }
                }
                $_SESSION['access'] = $thisUser[0];
                $_SESSION['empNum'] = $thisUser[1];
                $_SESSION['userName'] = $thisUser[2];
                
                $_SESSION['loggedIn'] = true;
            } else {
                $_SESSION['loggedIn'] = false;
                header("Location: index.php?message=invalidLogin");
            }
        }
    }
    //------------------------------------LOGIN QUERY END------------------------------------
?>
<!doctype html>
<html>
<head>
    <title>Easiest Hotel Registry</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/stylesheet.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
</head>
<body>
    <div id="carouselBG">
        <?php
            
            
            // ------------------------------------ROOM QUERY START------------------------------------
            if (isset($_GET["allRooms"])) {
                $allRooms = $_GET["allRooms"];
            } else {
                $allRooms = "no";
            }
            
            $query = "SELECT roomNumber as 'Room', description as 'Description', type as 'Type', rate as 'Rate', status as 'Status' FROM room";
            
            if ($allRooms != "yes") {
                $query = "SELECT roomNumber as 'Room', description as 'Description', type as 'Type', rate as 'Rate', status as 'Status' FROM room where status LIKE 'available'";
            }
            
            
            //if user selects a new sorting option, it will be held in session after testing for SQL injection
            //If user selects a new heading to sort by, it will check to see if it matches the current sort selection.
            if (isset($_GET['sortRooms'])) {
                if ($_SESSION['sortRooms'] != $_GET['sortRooms']) {
                    $_SESSION['sortRooms'] = test_input($_GET['sortRooms']);
                    $_SESSION['order'] = "ASC";
                } else {
                    //Allowing toggle between ascending and descending
                    if ($_SESSION['order'] == "ASC") {
                        $_SESSION['order'] = "DESC";
                    } else {
                        $_SESSION['order'] = "ASC";
                    }
                }
            }
            
            
            //If sortRooms and order aren't set, they will be defaulted to roomNumber and ASC before querying the database
            if (!isset($_SESSION['sortRooms'])) {
                $_SESSION['sortRooms'] = "Room";
                $_SESSION['order'] = "ASC";
            }
            
            
            //adding the sortRooms to the query
            $query .= " ORDER BY " . $_SESSION['sortRooms'] . " " . $_SESSION['order'];
            
            // Here we use our $mysqli object to run the query() method. We pass it our query from above.
            $result = $mysqli->query($query);
            //------------------------------------ROOM QUERY END------------------------------------
            
            
            include_once("include/navbar.php");
            include_once("include/messages.php");
            
            
            //--------------------------------CAROUSEL----------------------------------------------
        
        
        ?>

        <div id="myCarousel" class="carousel slide" data-ride="carousel">
            <!-- Indicators -->
            <ol class="carousel-indicators">
                <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
                <li data-target="#myCarousel" data-slide-to="1"></li>
                <li data-target="#myCarousel" data-slide-to="2"></li>
                <li data-target="#myCarousel" data-slide-to="3"></li>
            </ol>

            <!-- Wrapper for slides -->
            <div class="carousel-inner">
                <div class="item active">
                    <img src="images/room_preview_4.jpg" alt="Room Preview">
                </div>

                <div class="item">
                    <img src="images/room_preview_3.jpg" alt="Room Preview">
                </div>

                <div class="item">
                    <img src="images/room_preview_2.jpg" alt="Room Preview">
                </div>

                <div class="item">
                    <img src="images/room_preview_1.jpg" alt="Room Preview">
                </div>
            </div>

            <!-- Left and right controls -->
            <a class="left carousel-control" href="#myCarousel" data-slide="prev">
                <span class="glyphicon glyphicon-chevron-left"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="right carousel-control" href="#myCarousel" data-slide="next">
                <span class="glyphicon glyphicon-chevron-right"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
    </div>
    <div id="roomArea">
        <?php
            //--------------------------------------------------------------------------------------
            
            //TODO logic here can be changed to show available rooms - or all rooms listed by availability
            echo "<h2 class='rooms'>Available Rooms</h2>";
            if (isset($allRooms)) {
                if ($allRooms == "yes") {
                    echo "<a href='index.php?allRooms=no' title='Show Available Rooms' data-toggle='tooltip'>Click to see less rooms</a>";
                } else {
                    echo "<a href='index.php?allRooms=yes' title='Show all Rooms' data-toggle='tooltip'>Click to see all Rooms</a>";
                }
            }
            //Checking to see if there was 1 or more rows returned from the query
            if ($result->num_rows >= 1) {
                $rooms = $result->fetch_all(MYSQLI_ASSOC);
                
                
                echo "<table class='table table-bordered'><tr>";
                
                
                //retrieves table data as key/value pairs and displays the keys (table headers)
                foreach ($rooms[0] as $k => $v) {
                    echo "<th><a href='index.php?sortRooms=$k'>$k</a></th>";
                    
                }
                if ($_SESSION['loggedIn']) {
                    echo "<th>Edit</th>";
                    echo "<th>Book</th>";
                }
                echo "</tr>";
                
                foreach ($rooms as $room) {
                    $roomNumber = null;
                    $v = null;
                    echo "<tr>";
                    foreach ($room as $k => $v) {
                        
                        echo "<td>" . $v . "</td>";
                        //saving the roomNumber in a temp variable to place it in the GET
                        if ($k == "Room") {
                            $roomNumber = $v;
                        }
                    }
                    if ($_SESSION['loggedIn']) {
                        echo "<td><a href='admin/updateRoom.php?roomNumber=$roomNumber' title='Edit Room Details' data-toggle='tooltip'><span class='glyphicon glyphicon-pencil glyphicon-align'></span></a> </td>";
                        if ($v == "available") {
                            echo "<td><a href='admin/updateRegistration.php?roomNumber=$roomNumber' title='Book Room' data-toggle='tooltip'><span class='glyphicon glyphicon-bed glyphicon-align'></span></a> </td>";
                        } else {
                            echo "<td><a title='Room Already Booked' data-toggle='tooltip'><span class='glyphicon glyphicon-bed glyphicon-align'></span></a> </td>";
                            
                        }
                    }
                    
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                // if no results
                header("Location: index.php?message=noEntries");
            }
            
            // frees up memory on our server
            $result->free();
            // disconnect our connection to the DB
            $mysqli->close();
        ?>
    </div>
</body>
</html>
