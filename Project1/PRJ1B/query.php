<!DOCTYPE html>
<html>
<body>

<h2 style="text-align:center;">WEB SQL</h2>
<p style="text-align: center;">Type SQL query in the following box:<br>
    Example: SELECT * FROM Actor WHERE id=10;<br>
    *Massive results might take longer time to show :)<br><br></p>

<form action="./query.php" method="get" style="text-align: center;">
    <textarea name="query" rows="8" cols="60"></textarea><br>
    <input type="submit" value="Submit" />
</form>

<?php
/**
 * Created by PhpStorm.
 * User: TommysMac
 * Date: 10/18/16
 * Time: 5:37 PM
 */
$query = $_GET["query"];
if($query != "") {
    $db = new mysqli('localhost','cs143','','CS143');
    if($db -> connect_errno > 0) {
        die('Unable to connect to database [' . $db->connect_errno.']');
    } else {
        echo 'DBMS connection succeed!<br>';
        $sanitized_query = $db->real_escape_string($query);
        $query_todo = sprintf($query, $sanitized_query);
        $rs = $db->query($query);
        echo 'Your input Query: '.$query_todo.'<br>';

        if($rs) {
            echo 'Query succeed!<br>';

            $cols = $rs->field_count;
            $rows = $rs->num_rows;
            echo 'We found '.$rows.' tuples for you!<br>';

            if($rows != 0) {
                echo "<table border='1.5'>";
                echo "<tr>";
                $fields = $rs->fetch_fields();
                foreach ($fields as $field) {
                    echo '<th>'.$field->name.'</th>';
                }
                echo "</tr>";

                while($row = $rs->fetch_assoc()) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        if($value) {
                            echo '<td>'.$value.'</td>';
                        } else {
                            echo '<td>'.'N/A'.'</td>';
                        }
                    }
                    echo "</tr>";
                }
                echo "</table>";
                echo 'Print Done!<br>';
            }

        } else {
            $error_msg = $db->error;
            print "QUERY FAILED: $error_msg<br />";
            exit(1);
        }
        $rs->free();
    }
}

?>

</body>
</html>
