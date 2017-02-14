<?php
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if(empty($_POST["movie"])){
            $movieErr = "Please enter the movie";
        }else{
        	$movie = $_POST["movie"];
        }
        if(empty($_POST["actor"])){
        	$actorErr = "Please enter the actor";
        }else{
        	$actor = $_POST["actor"];
        }
        if(empty($_POST["role"])){
        	$roleErr = "Please enter the role";
        }else{
        	$role = $_POST["role"];
        }
    }
?>
<?php
    //connection to database
    include('connect.php');

    $query="SELECT CONCAT(title,' ','(',year,')') AS MovieName, id FROM Movie;";
    $rsmovie = $db->query($query);
    //Basic error handling 
    if(!$rsmovie){
        $errmsg = $db->error;
        print "Query failed: $errmsg <br />";
        exit(1);
    }

    $query1="SELECT CONCAT(first, ' ', last,' ', '(', dob, ')')AS ActorName, id, dob FROM Actor ORDER BY last ASC;";
    $rsactor = $db->query($query1);
    //Basic error handling 
    if(!$rsactor){
        $errmsg = $db->error;
        print "Query failed: $errmsg <br />";
        exit(2);
    }

    //close connection
    $db->close();
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
        color:#FF0000;
    }
    </style>


</head>

<body>

    <!-- Navigation -->
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
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
                            <li><a href="AddMovie.php" >Add Movie</a></li>
                            <li><a href="AddPerson.php" >Add Person</a></li>
                            <li><a href="AddMA.php" >Add Movie/Actor Relation</a></li>
                            <li><a href="AddMD.php">Add Movie/Director Relation</a></li> 
                        </ul>
                    </li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container -->
    </nav>

    <!-- Page Content -->
    <div class="container">
        <div class="col-sm-9 col-md-10  main">
            <h3>Add Movie/Actor Relation</h3><hr>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <p><span class="error">* Required field.</span></p>
                <div class="form-group">
		            <label for="movie">Movie Title:</label>
		            <span class="error">* <?php echo "$movieErr";?></span>
		            <select class="form-control" name="movie">
		                <option value=""> </option>
		                <?php
		                	if($rsmovie->num_rows>0){
		                		while($row = $rsmovie->fetch_assoc()){
            			?>
                			<option value = <?php echo $row["id"] ?>> <?php echo $row["MovieName"] ?></option>
		                <?php		}
		                	}else{
                		?>
                			<option>None</option>
                		<?php
		                	}
                		?>
		            </select>
                </div>

                <div class="form-group">
                	<label for="actor">Actor:</label>
                	<span class="error">* <?php echo "$actorErr";?></span>
                	<select class="form-control" name="actor">
                		<option value=""> </option>
                		<?php
		                	if($rsactor->num_rows>0){
		                		while($row = $rsactor->fetch_assoc()){
            			?>
                			<option value = <?php echo $row["id"] ?>> <?php echo $row["ActorName"] ?></option>
		                <?php		}
		                	}else{
                		?>
                			<option>None</option>
                		<?php
		                	}
		                ?>
	                </select>
                </div>

                <div class="form-group">
	                <label for="role">Role:</label>
	                <span class="error">* <?php echo "$roleErr";?></span>
	                <input type="text" name="role" class="form-control"><br>
	                
                </div>
                	<button type="submit" class="btn btn-default">Submit!</button>
            </form>
            <?php
                if($movie && $actor && $role){
                    //connection to database
                    include('connect.php');

                    $query2="INSERT INTO MovieActor VALUES ('$movie', '$actor', '$role');";
                    $rs = $db->query($query2);
                    if(!$rs){
                        $errmsg = $db->error;
                        print "Query failed: $errmsg <br />";
                        exit(3);
                    }else{
                        echo "<br><b>Succefully Inserted!</b>";
                    }
                    //close connection
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