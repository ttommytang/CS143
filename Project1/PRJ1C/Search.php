<?php
/**
 * Created by PhpStorm.
 * User: TommysMac
 * Date: 10/25/16
 * Time: 3:24 PM
 */

if(!$_GET["keys"]) {
    $Err_key = "Please enter the key words!";
    $Err_flag = 1;
} else {
    $keys = $_GET["keys"];
    $keys = explode(' ', $keys);
    $Err_flag = 0;
}


?>

<?php
if($Err_flag == "0"){  
    $countKeys = count($keys);

    $queryMovie = "SELECT * FROM Movie WHERE ";
    for($i = 0; $i < $countKeys; $i++) {
        $queryMovie = $queryMovie."title LIKE '%".$keys[$i]."%'";
        if($i != $countKeys - 1) {
            $queryMovie = $queryMovie." AND ";
        }else {
            $queryMovie = $queryMovie.";";
        }
    }

    $queryActor = "SELECT * FROM (SELECT id, CONCAT(first,' ',last) AS Aname, sex, dob, dod FROM Actor) actor WHERE ";
    for($i = 0; $i < $countKeys; $i++) {
        $queryActor = $queryActor."actor.Aname LIKE '%".$keys[$i]."%'";
        if($i != $countKeys - 1) {
            $queryActor = $queryActor." AND ";
        }else {
            $queryActor = $queryActor.";";
        }
    }

    // Build three query to fetch tuples from Actor, Director and Movie tables,
    // While in Actor and Director search by name, in Movie search by title;

    include('connect.php');
    $rsMovie = $db->query($queryMovie);
    if(!$rsMovie) {
        $Err_msg = $db->error;
        echo "Error in SQL: $Err_msg";
        exit(1);
    }
    $rsActor = $db->query($queryActor);
    if(!$rsActor) {
        $Err_msg = $db->error;
        echo "Error in SQL: $Err_msg";
        exit(2);
    }
    $db->close();

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

    <title>Search</title>

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
<!--Page content-->
<div class="container">
    <div class="col-sm-9 col-md-10 main">
        <h3>Search</h3><hr>
        <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <div class="form-group">
                <input type="text" class="form-control" placeholder="Input Movies/Actors " name="keys"
                       value="<?php echo $_GET["keys"] ?>" ><br>
                <input type="submit" value="Search" class="btn btn-default"><hr>
                <h4><b>Matching Actor:</b></h4>
                <div class='table-responsive'>
                    <table class='table table-bordered table-condensed table-hover'>
                    <thead> 
                        <tr>
                        <td>Name</td>
                        <td>Date of Birth</td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($rsActor->num_rows > 0){
                        while($rowA = $rsActor->fetch_assoc()) { ?>
                        <tr>
                        <td><a href="ActorInfo.php?aid=<?php echo $rowA["id"]?>"> <?php echo $rowA["Aname"]?></a></td>
                        <td><?php echo $rowA["dob"]?></td>
                        </tr>
                        <?php }} ?>
                        </tbody>
                    </table>    
                </div><hr>

                <h4><b>Matching Movie:</b></h4>
                <div class='table-responsive'>
                    <table class='table table-bordered table-condensed table-hover'>
                    <thead> 
                        <tr>
                        <td>Title</td>
                        <td>Year</td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($rsMovie->num_rows > 0){
                        while($rowM = $rsMovie->fetch_assoc()) { ?>
                        <tr>
                        <td><a href="MovieInfo.php?mid=<?php echo $rowM["id"]?>"> <?php echo $rowM["title"]?></a></td>
                        <td><?php echo $rowM["year"]?></td>
                        </tr>
                        <?php }} ?>
                        </tbody>
                    </table>    
                </div>
            </div>
        </form>
    </div>   
</div>

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

</html>
