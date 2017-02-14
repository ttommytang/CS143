<?php
/*
 * Pass the input value into the corresponding variables
 * and build a error msg if the value is not valid;
*/
/**
 * Created by PhpStorm.
 * User: TommysMac
 * Date: 10/23/16
 * Time: 2:53 PM
 */
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $Err_input = "Please specify the ";
    if(empty($_POST["firstName"]) || empty($_POST["lastName"])) {
        $Err_input_name = $Err_input."name!";
    }else{
        $firstName = $_POST["firstName"];
        $lastName = $_POST["lastName"];
    }

    if(empty($_POST["identity"])) {
        $Err_input_iden = $Err_input."identity!";
    }else{
        $identity = $_POST["identity"];
    }
    if(empty($_POST["gender"])) {
        $Err_input_gender = $Err_input."gender!";
    }else{
        $gender = $_POST["gender"];
    }
    if(empty($_POST["birthyear"]) || empty($_POST["birthday"]) || empty($_POST["birthmonth"])) {
        $Err_input_dob = $Err_input."date of birth!";
        //$dob = "";
    } else {
        $birthyear = $_POST["birthyear"];
        $birthmonth = $_POST["birthmonth"];
        $birthday = $_POST["birthday"];
        $dob = $birthyear.$birthmonth.$birthday;
    }
    if(empty($_POST["deathyear"]) || empty($_POST["deathday"]) || empty($_POST["deathmonth"])) {
        $dod = "NULL";
    } else {
        $deathyear = $_POST["deathyear"];
        $deathmonth = $_POST["deathmonth"];
        $deathday = $_POST["deathday"];
        $dod = $deathyear.$deathmonth.$deathday;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>CS143 DataBase Query System</title>

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        body {
            padding-top: 70px;
        }
        footer {
            margin: 50px 0;
            width:960px;
            height:200px;
        }
        .error{
            color:#FF0000
        }
    </style>
</head>

<body>

    <!-- Navigation -->
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse"
                        data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.php">Home</a>
            </div>
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li>
                        <a href="Search.php">Search</a>
                    </li>
                    <li>
                        <a href="MovieInfo.php">MovieInfo</a>
                    </li>
                    <li>
                        <a href="ActorInfo.php">ActorInfo</a>
                    </li>
                    <li>
                        <a href="AddComment.php">Comment</a>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Add Info<span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="AddMovie.php">Add Movie</a></li>
                            <li><a href="AddPerson.php">Add Person</a></li>
                            <li><a href="AddMA.php">Add Movie/Actor Relation</a></li>
                            <li><a href="AddMD.php">Add Movie/Director Relation</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container -->
    </nav>
    <!-- Page content -->
    <div class="container">
        <div class="col-sm-9 col-md-10 main">
            <h3>Add new Actor/Director</h3><hr>
            <p><span class="error">* Required fields.</span></p>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <div class="form-group">
                    <label for="firstName">First Name:</label>
                    <span class="error">* <?php echo "$Err_input_name"; ?></span>
                    <input type="text" class="form-control" placeholder="First Name" name="firstName" maxlength=20>
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name:</label>
                    <span class="error">* <?php echo "$Err_input_name"; ?></span>
                    <input type="text" class="form-control" placeholder="Last Name" name="lastName" maxlength=20>
                </div>
                <div class="form-group">
                    <label for="identity">Identity:</label>
                    <span class="error">* <?php echo "$Err_input_iden"; ?></span>
                    <input type="checkbox" name="identity[]" value="Actor">Actor</input>
                    <input type="checkbox" name="identity[]" value="Director">Director</input>
                </div>
                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <span class="error">* <?php echo "$Err_input_gender"; ?></span>
                    <select class="form-control" name="gender" style="width: 300px">
                        <option value="Female">Female</option>
                        <option value="Male">Male</option>
                    </select>
                </div>
                <div class = "form-group">
                    <label for="dob">Date of birth:</label>
                    <span class="error">* <?php echo "$Err_input_dob"; ?></span>
                    <input type="text" class="form-control" style="text-align: start; width: 70px"
                    placeholder="yyyy" maxlength=4 name="birthyear">
                    <select class="form-control" name="birthmonth" style="width: 250px">
                        <option value="01" selected="selected">Jan</option>
                        <option value="02">Feb</option>
                        <option value="03">Mar</option>
                        <option value="04">Apr</option>
                        <option value="05">May</option>
                        <option value="06">Jun</option>
                        <option value="07">Jul</option>
                        <option value="08">Aug</option>
                        <option value="09">Sep</option>
                        <option value="10">Oct</option>
                        <option value="11">Nov</option>
                        <option value="12">Dec</option>
                    </select>
                    <input type="text" class="form-control" style="text-align: start; width: 50px"
                           placeholder="dd" maxlength=2 name="birthday">
                </div>
                <div class = "form-group">
                    <label for="dod">Date of death:</label>
                    <input type="text" class="form-control" style="text-align: start; width: 70px"
                           placeholder="yyyy" maxlength=4 name="deathyear">
                    <select class="form-control" name="deathmonth" style="width: 250px">
                        <option value="01" selected="selected">Jan</option>
                        <option value="02">Feb</option>
                        <option value="03">Mar</option>
                        <option value="04">Apr</option>
                        <option value="05">May</option>
                        <option value="06">Jun</option>
                        <option value="07">Jul</option>
                        <option value="08">Aug</option>
                        <option value="09">Sep</option>
                        <option value="10">Oct</option>
                        <option value="11">Nov</option>
                        <option value="12">Dec</option>
                    </select>
                    <input type="text" class="form-control" style="text-align: start; width: 50px"
                           placeholder="dd" maxlength=2 name="deathday">
                    (Leave blank if not applicable)
                </div>
                <button type="submit" class="btn btn-default">Submit</button>
            </form>
            <?php

                if($identity && $firstName && $lastName && $gender && $dob && $dod) {
                    include('connect.php');
                    $query = "SELECT id FROM MaxPersonID;";

                    $rs = $db->query($query);
                    if(!$rs) {
                        $Err_msg_max = $db->error;
                        echo("Query failed to fetch max ID: $Err_msg_max<br>");
                        exit(1);
                    } else {
                        $row = $rs->fetch_assoc();
                        $newID = $row["id"] + 1;

                        $query = "UPDATE MaxPersonID SET id = id+1;";
                        $rsID = $db->query($query);
                        if(!$rsID) {
                            $Err_msg_upd = $db->error;
                            echo("Failed to update Max ID: $Err_msg_upd<br>");
                            exit(2);
                        } else {
                            if(in_array('Actor', $identity)) {
                                $queryAdd = "INSERT INTO Actor VALUES($newID, '$lastName', '$firstName', '$gender', $dob, $dod);";
                                $rsAdd = $db->query($queryAdd);
                                if(!$rsAdd) {
                                    $Err_msg_add = $db->error;
                                    echo("Failed to insert the actor information: $Err_msg_add<br>");
                                    exit(3);
                                } else {
                                    echo("</br><b>Successfully inserted the actor/actress information!</b>");
                                }
                            }
                            if(in_array('Director', $identity)) {
                                $queryAdd = "INSERT INTO Director VALUES($newID, '$lastName', '$firstName', $dob, $dod);";
                                $rsAdd = $db->query($queryAdd);
                                if(!$rsAdd) {
                                    $Err_msg_add = $db->error;
                                    echo("Failed to insert the director information: $Err_msg_add<br>");
                                    exit(3);
                                } else {
                                    echo("</br><b>Successfully inserted the director information!</b>");
                                }
                            }

                        }
                    }
                    $rs->free();
                    $db->close();
                }
                ?>
        </div>
    </div>
    <!-- /.container -->

        <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <p>Copyright &copy; CS143 2016</p>
                </div>
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container -->
    </footer>

    

    <!-- jQuery Version 1.11.1 -->
    <script src="js/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>
</body>
</html>




