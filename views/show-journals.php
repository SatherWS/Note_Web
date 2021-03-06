<?php
    session_start();

    include_once ("../config/database.php");
    $database = new Database();
    $curs = $database->getConnection();
    
    if (isset($_GET["project"])) {
        $sql = "select id, subject, creator, team_name, substring(message,1, 55) as preview, date_format(date_created, '%m/%d/%Y') as dt from journal where team_name = ? order by date_created desc";
        $stmnt = mysqli_prepare($curs, $sql);
        $stmnt -> bind_param("s", $_GET["project"]);
        $stmnt -> execute();
        $result = $stmnt -> get_result();
        $total = mysqli_num_rows($result);
    }
    else {
        $sql = "select id, subject, creator, team_name, substring(message,1, 55) as preview, date_format(date_created, '%m/%d/%Y') as dt from journal where team_name = ? order by date_created desc";
        $stmnt = mysqli_prepare($curs, $sql);
        $stmnt -> bind_param("s", $_SESSION["team"]);
        $stmnt -> execute();
        $result = $stmnt -> get_result();
        $total = mysqli_num_rows($result);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/style.css">
    <link rel="stylesheet" href="../static/modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=PT+Sans&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="../favicon.png" >
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" 
    integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <title>Swoop | Posts</title>
</head>
<body class="log-bg">
    <?php include("./components/header.php");?>
    <?php include("./components/modals/modal.php");?>
    <div class="svg-bg">
        <div class="todo-flex">
        <?php
            if (isset($_GET["project"]))
                echo "<p class='welcome'>". $_GET["project"]. "</p>";
            
            else
                echo "<p class='welcome'>". $_SESSION["team"]. "</p>";
            
            if (isset($_SESSION["unq_user"]))
                echo "<p class='welcome'>". $_SESSION["unq_user"]. "</p>";
            else
                echo "<p class='welcome'>Guest</p>";
        ?>
        </div>
    </div>
    <div class="dash-grid r-cols" id="main">
        <div class="log-container">
            <div class="todo-flex">
                <div class="review">
                    <h4 id="logs-title"><?php echo $total;?> Articles</h4>
                </div>
                <div class="add-btn">
                    <h4>
                        <a href="./create-journal.php">
                            <span>Add Entry</span>
                            <i class="fa fa-plus-circle"></i>
                        </a>
                    </h4>
                </div>
            </div>
            <form id="notes" action="./journal-details.php" method="post">
                <table class="data journal-tab">
                    <tr class="tbl-head">
                        <th>SUBJECT</th>
                        <th>PREVIEW</th>
                        <th>CREATOR</th>
                        <th>DATE CREATED</th>
                    </tr>
                    <?php
                        while($row = mysqli_fetch_assoc($result)) {
                            $id = $row['id'];
                            echo "<tr onclick='myFunction($id)' name='btn-submit' value='".$row["id"]."'><td>".$row["subject"]."</td>";
                            echo "<td>".strip_tags($row["preview"], '<br><b><i>'). "...</td>";
                            echo "<td>".$row["creator"]."</td>";
                            echo "<td>".$row["dt"]."</td></tr>";
                        }
                    ?>
                </table>
            </form>
        </div>
        <?php include("./components/sidebar.php");?>
    </div>
    <script>
    function myFunction(id) {
        window.location='./journal-details.php?journal='+id;
    }
    </script>
    <script src="../static/main.js"></script>
    <script src="../static/modal.js"></script>
</body>
</html>
