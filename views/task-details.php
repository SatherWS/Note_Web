<?php
    session_start();
    if (!isset($_SESSION["unq_user"])) {
        header("Location: ../authentication/login.php");
    }
    include("./components/task-editor.php");
    $editor = new TaskEditor();

    // TODO: MOVE BELOW THIS TO CONTROLLERS v
    include("../config/database.php");
    $database = new Database();
    $curs = $database->getConnection();

    if (isset($_GET['task'])) {
        $id = $_GET['task'];
        $sql = "select * from todo_list where id = ?";
        $stmnt = mysqli_prepare($curs, $sql);
        $stmnt -> bind_param("s", $id);
        $stmnt -> execute();
        $results = $stmnt -> get_result();
        $show_editor = true;
        $sql = "select * from sub_tasks where task_id = ?";
        $stmnt = mysqli_prepare($curs, $sql);
        $stmnt -> bind_param("s", $id);
        if ($stmnt -> execute())
            $results2 = $stmnt -> get_result();
    }

    if (isset($_POST['edit'])) {
        // edit main task
        $id = $_POST['edit'];
        $sql = "select *, date(date_created) from todo_list where id = ?";
        $stmnt = mysqli_prepare($curs, $sql);
        $stmnt -> bind_param("s", $id);
        $stmnt -> execute();
        $results = $stmnt -> get_result();
        $data = mysqli_fetch_assoc($results);
        $show_editor = false;
    }

    if (isset($_POST['delete'])) {
        $sql = "delete from sub_tasks where task_id = ?";
        $stmnt = mysqli_prepare($curs, $sql);
        $stmnt-> bind_param("s", $_POST['delete']);
        $stmnt->execute();
        $sql = "delete from todo_list where id = ?";
        $stmnt = mysqli_prepare($curs, $sql);
        $stmnt -> bind_param("s", $_POST['delete']);
        $stmnt -> execute();
        header("Location: ./show-tasks.php");
    }
    if (isset($_POST["delete-sub"])) {
        $sql = "delete from sub_tasks where id = ?";
        $stmnt = mysqli_prepare($curs, $sql);
        $stmnt -> bind_param("s", $_POST["delete-sub"]);
        $stmnt -> execute();
        header("Location: ./task-details.php?task=".$_GET["task"]);
    }
    // TODO: MOVE ALL ABOVE THIS TO CONTROLLERS ^
    // Creator and assignee selector elements
    function team_mates($curs, $team) {
        $selector = "<label>Change Assignee</label><br>";
        $selector .= "<select name='change-assignee' class='spc-n' required>";
        $selector2 = "<label>Change Creator</label><br>";
        $selector2 .= "<select name='change-creator' class='spc-n' required>";

        // TODO: Set value of assignee and creator to current assignee/creator.
        //       Let the values of the other options be each member of the team.

        $sql = "select distinct assignee, creator from todo_list where team_name = ?";
        $stmnt = mysqli_prepare($curs, $sql);
        $stmnt->bind_param("s", $team);
        $stmnt->execute();
        $result = $stmnt->get_result();
        while ($row = mysqli_fetch_assoc($result)) {
            $selector .= "<option value='".$row["assignee"]."'>".$row["assignee"]."</option>";
            $selector2 .= "<option value='".$row["creator"]."'>".$row["creator"]."</option>";
        }
        $selector .= "</select><br><br>";
        $selector2 .= "</select>";
        $selectors = $selector.=$selector2;
        return $selectors;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../favicon.png" >
    <title>Task Details</title>
    <link rel="stylesheet" href="../static/style.css">
    <link rel="stylesheet" href="../static/modal.css">
    <link rel="stylesheet" href="../static/slider.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=PT+Sans&display=swap" rel="stylesheet">
    <script
    src="https://code.jquery.com/jquery-3.5.1.js"
    integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc="
    crossorigin="anonymous"></script>
</head>
<body>
    <?php include("./components/header.php");?>
    <?php include("./components/modals/subtask_modal.php");?>
    <div class="svg-bg sticky">
        <div class="todo-flex btn-spcing">    
            <?php
                if ($show_editor)
                    include("./components/task-headers/edit_task.php");
                else
                    include("./components/task-headers/save_task.php");
            ?>
        </div>
    </div>
    <div class="task-panel">
        <form action="../controllers/edit_entry.php" method="post" class="task-auto" id="editor2">
            <div class="inner-task-panel">
            <?php
                if ($_GET['task'] && mysqli_num_rows($results) > 0) 
                {
                    while($row = mysqli_fetch_assoc($results)) 
                    {
                        // Top task title, description and sub modal trigger
                        echo "<div class='todo-flex r-cols'>";
                        echo "<div><h2>".$row['title']."</h2>";
                        echo "<p>".$row["description"]."</p></div>";
                        echo "<a href='#subModal' id='myBtn'>Add Sub Task</a></div>";

                        // Left detail view table
                        echo "<div class='todo-flex r-cols'>";
                        echo "<div><table>";
                        echo "<tr><td><p><b>Status:</b></p></td>";
                        echo "<td><p>".$row['status']."</p></td>";
                        echo "</tr>";
                        echo "<tr><td><p><b>Deadline:</b></p></td>";
                        echo "<td><p>".$row['deadline']."</p></td>";
                        echo "</tr>";
                        echo "<tr><td><p><b>Start Date:</b></p></td>";
                        echo "<td><p>".$row['date_created']."</p></td>";
                        echo "</tr>";
                        echo "<tr><td><p><b>Importance:</b></p></td>";
                        echo "<td><p>".$row['importance']."</p></td>";
                        echo "</tr>";
                        echo "<tr><td><p><b>Assignee:</b></p></td>";
                        echo "<td><p>".$row['assignee']."</p></td>";
                        echo "</tr>";
                        echo "<tr><td><p><b>Creator:</b></p></td>";
                        echo "<td><p>".$row['creator']."</p></td>";
                        echo "</tr>";
                        echo "</table></div>";

                        // Right email automation form
                        echo "<div class='emailer'>";
                        echo "<label class='container'>";
                        echo "<input type='checkbox' checked='checked' name='remind' value='temp' class='pro-op'>";
                        echo "<span class='checkmark'></span>Send Email Reminders</label>";
                        echo "<br><select name='repeat' class='repeat-input eight-pxp'>";
                        echo "<option type='disabled'>Repitition Pattern</option>";
                        echo "<option>Daily</option>";
                        echo "<option>Weekly</option>";
                        echo "<option>Monthly</option></select>";
                        echo "<br></br>";
                        echo "<input type='time' name='repeat-count' placeholder='Set Reminder Time' class='repeat-input'/>";
                        echo "<br></br>";
                        echo "<input type='number' min='1' max='4' name='repeat-count' placeholder='Enter Repitition Count' class='repeat-input'/>";
                        echo "<br></br>";
                        echo "<input type='submit' name='send-emails' value='Apply Settings' class='add-btn-2'/>";
                        echo "</div></div>";
                    }
                }

                // Task editting view render
                $form = "";
                if (isset($_POST['edit']) && mysqli_num_rows($results) > 0) {
                    $form .= $editor->create_editor($data);
                    //$form .= create_selector($curs, $_SESSION["team"]);
                    $form .= "</div></div>";
                    $form .= $editor->additionals($data);
                    /*
                    if (isset($row2))
                        $form .= $editor->create_editor($row2);
                    */
                    echo $form;
                }
            ?>
            </div>
        </form>
        <div class="inner-task-panel">
        <?php
            // sub tasks component
            if (isset($results2)) 
            {
                while ($row2 = mysqli_fetch_assoc($results2)) 
                {
                    echo "<br><div class='uline'></div><br>";
                    echo "<div class='todo-flex r-cols'>";
                    echo "<h3>Sub Task: ".$row2["title"]."</h3>";
                    echo "<form method='post'>";
                    $id = $row2['id'];
                    echo "<button class='add-btn' type='submit' name='delete-sub' value='$id'><i class='fa fa-close'></i>Delete Sub Task</button>";
                    echo "</form></div>";
                    echo "<p>".$row2["descript"]."</p>";
                    echo "<div class='todo-flex align-initial r-cols'>";
                    echo "<div><p><b>Status:</b> ".$row2['status']."</p>";
                    echo "<p><b>Start Date:</b> ".$row2['date_created']."</p>";
                    echo "<p><b>End Date:</b> ".$row2['deadline']."</p></div>";
                    echo "<div><p><b>Importance:</b> ".$row2['importance']."</p>";
                    echo "<p><b>Assigned To:</b> ".$row2['assignee']."</p>";
                    echo "<p><b>Created By:</b> ".$row2['creator']."</p></div></div>";
                }
            }
        ?>
    </div>
    <script>
        function triggerForm2() {
            document.getElementById("editor2").submit();
        }
        function hideSubRanger() {
            document.getElementById("subranger").style.display = "none";
        }
    </script>
    <script src="../static/main.js"></script>
    <script src="../static/modal.js"></script>
    <!--<script src="../static/slider.js"></script>-->
</body>
</html>
