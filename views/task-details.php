<?php
    session_start();
    if (!isset($_SESSION["unq_user"])) 
        header("Location: ../authentication/login.php");
    

    // TODO: MOVE BELOW THIS TO CONTROLLERS v
    include("../config/database.php");
    include("./components/task-editor.php");

    $editor = new TaskEditor();
    $database = new Database();
    $curs = $database->getConnection();


    if (isset($_GET['task'])) 
    {
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

    if (isset($_POST['edit'])) 
    {
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

    if (isset($_POST['delete'])) 
    {
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
    if (isset($_POST["delete-sub"])) 
    {
        $sql = "delete from sub_tasks where id = ?";
        $stmnt = mysqli_prepare($curs, $sql);
        $stmnt -> bind_param("s", $_POST["delete-sub"]);
        $stmnt -> execute();
        header("Location: ./task-details.php?task=".$_GET["task"]);
    }
    // TODO: MOVE ALL ABOVE THIS TO CONTROLLERS ^
    // Creator and assignee selector elements
    function team_mates($curs, $team) 
    {
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

        // WIP: display all team members in select inputs
        while ($row = mysqli_fetch_assoc($result)) 
        {
            $selector .= "<option value='".$row["assignee"]."'>".$row["assignee"]."</option>";
            $selector2 .= "<option value='".$row["creator"]."'>".$row["creator"]."</option>";
        }
        $selector .= "</select><br><br>";
        $selector2 .= "</select>";
        $selectors = $selector.=$selector2;
        return $selectors;
    }

    $assignee_address = "";
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
    <div class="svg-bg">
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
        <!-- Task details UI-->
            <div class="inner-task-panel">
            <?php
            if ($_GET['task'] && mysqli_num_rows($results) > 0) 
            {
                while($row = mysqli_fetch_assoc($results)) 
                {
                    $assignee_address = $row["assignee"];
                    // Top task title, description and sub modal trigger
                    echo "<h2>".$row['title']."</h2>";
                    echo "<p>Task Admin: <span class='usrname'>".$row["creator"]."</span></p>";
                    echo "<p>Assigned to: <span class='usrname'>".$row["assignee"]."</span></p>";
                    echo "<p>".$row["description"]."</p><br>";
                    echo "<a href='#subModal' id='myBtn' class='add-btn-2'>Add a subtask</a>";
                    echo "<br><br><div class='uline'></div>";

                    // task detail view table
                    echo "<div class='todo-flex r-cols'>";
                    echo "<div>";
                    echo "<pre><p><b>Status: &#9; </b>".$row['status']."</p></pre>";
                    echo "<pre><p><b>Deadline: &#9; </b>".$row['deadline']."</p></pre>";
                    echo "<pre><p><b>Date Created: &#9; </b>".$row['date_created']."</p></pre>";
                    echo "<pre><p><b>Importance: &#9; </b>".$row['importance']."</p></pre>";
                    echo "</div>";

                    // email reminder form
                    echo "<form action='../controllers/email_interface.php' method='post'>";
                    echo "<div class='emailer'>";
                    echo "<label class='container'>";
                    echo "<input type='checkbox' name='remind' value='temp' class='pro-op'>";
                    echo "<span class='checkmark'></span>Remind everyone</label>";
                    echo "<input type='date' name='remind-date' class='repeat-input' required>";
                    echo "<br></br>";
                    echo "<input type='time' name='remind-time' placeholder='Set Reminder Time' class='repeat-input' required/>";
                    echo "<input type='hidden' name='assignee' value=$assignee_address>";
                    echo "<input type='hidden' name='taskid' value=$id>";
                    echo "<div class='text-center'>";
                    echo "<br><button type='submit' name='send-email' class='add-btn-2'>Set email reminder</button>";
                    echo "</div>";
                    echo "</div>";
                    echo "</form>";

                    echo "</div>";
                }
            }

            // Task editing view render
            $form = "";
            if (isset($_POST['edit']) && mysqli_num_rows($results) > 0) {
                $form .= "<form action='../controllers/edit_entry.php' method='post' class='task-auto' id='editor2'>";
                $form .= $editor->additionals($data);
                $form .= $editor->create_editor($data);
                $form .= "</div></div>";
                $form .= "</form>";
                echo $form;
            }
            ?>
            </div>
        
        <div class="inner-task-panel">
        <?php
        // sub tasks component
        if (isset($results2)) 
        {
            while ($row2 = mysqli_fetch_assoc($results2)) 
            {
                echo "<div class='uline'></div><br>";
                echo "<div class='todo-flex r-cols'>";
                echo "<h3>Sub Task: ".$row2["title"]."</h3>";
                echo "<form method='post'>";
                $id = $row2['id'];
                echo "<button class='add-btn-2' type='submit' name='delete-sub' value='$id'><i class='fa fa-close'></i>Delete Sub Task</button>";
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
</body>
</html>
