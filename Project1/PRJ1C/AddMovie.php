<?php
	if($_SERVER["REQUEST_METHOD"] == "POST"){

		if(empty($_POST['title'])){
			$titleErr = "Please enter the title of the movie";
		}else{
			$title = $_POST['title'];
		}

		if(empty($_POST['year'])){
			$year = "NULL";
		}else{
			$year = $_POST['year'];
		}

		if(empty($_POST['rating'])){
			$rating = "NULL";
		}else{
			$rating = $_POST['rating'];
		}

		if(empty($_POST['company'])){
			$genre = "NULL";
		}else{
			$genre = $_POST['company'];
		}

		if(empty($_POST['genre'])){
			$genreErr = "Please enter the genre of the movie";
		}else{
			$genre = $_POST['genre'];
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
                            <li><a href="AddMD.php" >Add Movie/Director Relation</a></li> 
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
            <h3>Add new Movie</h3><hr>
            <p><span class="error">* Required field.</span></p>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <div class="form-group">
                  <label for="title">Title:</label>
                  <span class="error">* <?php echo "$titleErr";?></span>
                  <input type="text" class="form-control" placeholder="Text input" name="title">
                </div>
                <div class="form-group">
                  <label for="company">Company</label>
                  <input type="text" class="form-control" placeholder="Text input" name="company">
                </div>
                <div class="form-group">
                  <label for="year">Year</label>
                  <input type="text" class="form-control" placeholder="Text input" name="year">
                </div>
                <div class="form-group">
                    <label for="rating">MPAA Rating</label>
                    <select   class="form-control" name="rating">
                        <option value="G">G</option>
                        <option value="NC-17">NC-17</option>
                        <option value="PG">PG</option>
                        <option value="PG-13">PG-13</option>
                        <option value="R">R</option>
                        <option value="surrendere">surrendere</option>
                    </select>
                </div>
                <div class="form-group">
                    <label >Genre:</label>
                    <span class="error">* <?php echo "$genreErr";?></span>
                    <input type="checkbox" name="genre[]" value="Action">Action</input>
                    <input type="checkbox" name="genre[]" value="Adult">Adult</input>
                    <input type="checkbox" name="genre[]" value="Adventure">Adventure</input>
                    <input type="checkbox" name="genre[]" value="Animation">Animation</input>
                    <input type="checkbox" name="genre[]" value="Comedy">Comedy</input>
                    <input type="checkbox" name="genre[]" value="Crime">Crime</input>
                    <input type="checkbox" name="genre[]" value="Documentary">Documentary</input>
                    <input type="checkbox" name="genre[]" value="Drama">Drama</input>
                    <input type="checkbox" name="genre[]" value="Family">Family</input>
                    <input type="checkbox" name="genre[]" value="Fantasy">Fantasy</input>
                    <input type="checkbox" name="genre[]" value="Horror">Horror</input>
                    <input type="checkbox" name="genre[]" value="Musical">Musical</input>
                    <input type="checkbox" name="genre[]" value="Mystery">Mystery</input>
                    <input type="checkbox" name="genre[]" value="Romance">Romance</input>
                    <input type="checkbox" name="genre[]" value="Sci-Fi">Sci-Fi</input>
                    <input type="checkbox" name="genre[]" value="Short">Short</input>
                    <input type="checkbox" name="genre[]" value="Thriller">Thriller</input>
                    <input type="checkbox" name="genre[]" value="War">War</input>
                    <input type="checkbox" name="genre[]" value="Western">Western</input>
                </div>
                <button type="submit" class="btn btn-default">Submit!</button>
            </form>
        
            <?php
                if($title && $genre){
                    //connection to database
                    include('connect.php');

                    $query="SELECT id FROM MaxMovieID;";
                    $rs = $db->query($query);
                    //Basic error handling 
                    if(!$rs){
                        $errmsg = $db->error;
                        print "Query failed: $errmsg <br />";
                        exit(1);
                    }else{
                        $row = $rs->fetch_assoc();
                        $newMax = $row["id"];
                        $newid = $newMax + 1;

                        $queryID = "UPDATE MaxMovieID SET id = id+1;";
                        $rsID = $db->query($queryID);

                        if(!$rsID){
                            $errmsg = $db->error;
                            print "Query failed: $errmsg <br />";
                            exit(2);
                        }else{
                            $queryNew = "INSERT INTO Movie VALUES($newid,'$title',$year,'$rating','$company');";
                            foreach ($genre as $key => $value) {
                                $queryNew .= "INSERT INTO MovieGenre VALUES($newid, '$value');";
                            }
                            $rsMovie = $db->multi_query($queryNew);
                            if(!$rsMovie){
                                $errmsg = $db->error;
                                print "Query failed: $errmsg <br />";
                                exit(3);
                            }else{
                                echo "<br><b>Succefully Inserted!</b>";
                            }
                        }   
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