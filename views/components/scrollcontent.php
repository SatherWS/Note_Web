<?php
    class Scroll {

        function scroll_content($curs) {
            $sql = "select *, date_format(deadline, '%M %D %Y') as d from todo_list order by deadline desc";
            $result = mysqli_query($curs, $sql);
            $html = "";

            while($row = mysqli_fetch_assoc($result)) {
                $id = $row["id"];
                $html .= "<div class='scroll-item' onclick='getTask($id)'>";
                $html .= "<h3>".$row["title"]."</h3>";
                $html .= "<h4>Deadline is ".$row["d"].", ".$row["time_due"]."</h4>";
                $html .= "<h4>Created on ".$row["date_created"]."</h4>";
                $html .= "<p>Importance: ".$row["importance"]."</p>";
                $html .= "<p>".$row["description"]."</p></div>";
            }
            echo $html;
        }
    }
?>
