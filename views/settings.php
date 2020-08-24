<?php
include_once("../config/database.php");
//include("../models/settings.php");

session_start();
if (!isset($_SESSION["unq_user"]))
    header("Location: ../authentication/login.php");

$db = new Database();
$curs = $db -> getConnection();
if (isset($_SESSION["team"])) {
    $sql = "select email from members where team_name = ?";
    $stmnt = mysqli_prepare($curs, $sql);
    $stmnt -> bind_param("s", $_SESSION["team"]);
    $stmnt -> execute();
    $results = $stmnt -> get_result();
}
/*
$sql2 = "select * from invites where team_name = ?";
$stmnt2 = mysqli_prepare($curs, $sql2);
$stmnt2->bind_param("s", $_SESSION["team"]);
$stmnt2->execute();
$results2 = $stmnt2->get_result();
*/
if (isset($_SESSION["team"])) {
    $sql3 = "select * from invites where receiver = ? or sender = ? order by date_created desc";
    $stmnt3 = mysqli_prepare($curs, $sql3);
    $stmnt3->bind_param("ss", $_SESSION["unq_user"], $_SESSION["unq_user"]);
    $stmnt3->execute();
    $results3 = $stmnt3->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swoop.Team | User Settings</title>
    <link rel="stylesheet" href="../static/style.css">
    <link rel="stylesheet" href="../static/modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=PT+Sans&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <link rel="shortcut icon" href="../favicon.png" >
</head>
<body class="todo-bg-test">
    <?php include("./components/header.php");?>
    <?php include("./components/modal.php");?>
    <div class="todo-bg-test">
        <div class="svg-bg">
            <div class="todo-flex">
                <p class="welcome"><?php echo $_SESSION["team"];?></p>
                <p class="welcome"><?php echo $_SESSION["unq_user"];?></p>
            </div>
        </div>
        <div class="dash-grid r-cols">
            <?php include("./components/sidebar.php");?>
            <div class="settings-space">
                <div class="settings-panel">
                    <div class="settings-flex r-cols">
                        <div>
                            <h2>User Information</h2>
                            <?php 
                                // for invite teammate form
                                if (isset($_GET["error"])) {
                                    echo "<div><p>".$_GET["error"]."</p></div>";
                                }
                                echo "<p>Username: ".$_SESSION["user"]."</p>";
                                echo "<p>Email: ".$_SESSION["unq_user"]."</p>";
                                echo "<p>Team: ".$_SESSION["team"]."</p>";
                            ?>
                        </div>
                        <div>
                            <?php
                                echo "<h2>Members of ".$_SESSION['team']."</h2>";
                                while ($row = mysqli_fetch_assoc($results)) {
                                    echo "<p>".$row["email"]."</p>";
                                }
                            ?>
                        </div>
                    </div>
                    <div class="add-worker">
                        <form method="post" action="../controllers/auth_user.php">
                            <h3>Invite a new user</h3>
                            <div class="todo-flex r-cols">
                                <input type="text" name="user_email" placeholder="search member by email address" class="spc-n mr2rem" required>
                                <input type="submit" name="invite_user" value="Invite User" id="form-control2" class="settings-btn">
                            </div>
                        </form>
                        <?php echo "<h4>".$_GET["msg"]."</h4>";?>
                    </div>
                    <div class="invites">
                        <h2>Invite History</h2>
                        <form action="../controllers/auth_user.php" method="post">
                            <table class="data journal-tab">
                            <?php
                            if (mysqli_num_rows($results3) > 0) {
                                include ("./components/settings-table.php");
                                while ($row = mysqli_fetch_assoc($results3)) {
                                    $id = $row["team_name"];
                                    if ($row["status"] != "pending") {
                                        echo "<tr><td></td>";
                                    }
                                    else if ($_SESSION["unq_user"] == $row["receiver"]) {
                                        echo "<tr><td><button class='accept-btn' type='submit' name='accept' value='$id'>Accept</button>";
                                        echo "<button class='deny-btn' type='submit' name='deny' value='$id'>Deny</button></td>";
                                    }
                                    else {
                                        echo "<tr><td></td>";
                                    }
                                    echo "<td>".$row["team_name"]."</td>";
                                    echo "<td>".$row["receiver"]."</td>";
                                    echo "<td>".$row["sender"]."</td>";
                                    echo "<td>".$row["status"]."</td>";
                                    echo "<td>".$row["date_created"]."</td>";
                                }
                            }
                            else {
                                echo "<h4>No invites have beed processed yet...</h4>";
                            }
                            ?>
                            </table>
                        </form>
                    </div>
                    <!-- WIP 
                    <h2>Danger Zone</h2>-->
                </div>
            </div>
        </div>
    </div>
    <script src="../static/main.js"></script>
    <script src="../static/modal.js"></script>
</body>
</html>