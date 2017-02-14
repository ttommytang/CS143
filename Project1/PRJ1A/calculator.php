<!DOCTYPE html>
<html>
<body>

<h2 style="text-align:center;">WEB CALCULATOR</h2>
<p style="text-align:center;">Ver 1.0 10/1/2016 by Tommy Tang<br>
    Please type the expression in the following box.<br>
    Example: 3 + -1 * 1.25<br>
    * Parentheses not supported in this version.<br><br></p>

<form action="./calculator.php" method="get" style="text-align:center;">
    Expression:  <input type = "text" name = "expression"><br>
    <input type = "submit"><br>
</form>
<?php
/**
 * Created by PhpStorm.
 * User: TommysMac
 * Date: 10/3/16
 * Time: 3:51 PM
 */
$valid = "/^((([0-9]+)|([0-9]+\.[0-9]+))[\+\-\*\/])*(([0-9]+)|([0-9]+\.[0-9]+))$/";
$valid1 = "/\/([0]|[0]\.[0]+)[\+\-\*\/]/";
$valid2 = "/\/([0]|[0]\.[0]+)$/";

$expression = $_GET["expression"];
$expression = preg_replace('/\s+/','',$expression);
if($expression != "") {
    if (preg_match($valid, $expression) == 1) {
        if(preg_match($valid1,$expression) || preg_match($valid2,$expression)) {
            echo "<p style='text-align:center'>".$expression." is invalid!";
        } else {
            eval("\$ans = $expression ;");
            echo "<p style='text-align:center'>".$expression . " = " . $ans;
        }

    } else {
        //echo "Not OK<br>";
        $expression2 = preg_replace('/\+\-/', '-', $expression);
        $expression2 = preg_replace('/\-\-/', '+', $expression2);
        if (preg_match($valid, $expression2) == 1) {
            eval("\$ans = $expression2 ;");
            echo "<p style='text-align:center'>".$expression . " = " . $ans;
        } else {
            echo "<p style='text-align:center'>".$expression." is invalid!";
        }
    }
}


?>

</body>
</html>

