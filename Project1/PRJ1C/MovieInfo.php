<?php
	//connection to database
	include('connect.php');

	if($_GET["mid"]){
		$mid=$_GET["mid"];

		$query="SELECT * FROM Movie WHERE id=$mid;";
		$rsmovie = $db->query($query);
		//Basic error handling 
		if(!$rsmovie){
			$errmsg = $db->error;
			print "Query failed: $errmsg <br />";
			exit(1);
		}else{
			$rowM = $rsmovie->fetch_assoc();
		}

		$queryDirector = "SELECT CONCAT(first, ' ',last, ' ', '(', dob, ')') AS DirectorName FROM Director, MovieDirector WHERE Director.id = MovieDirector.did AND MovieDirector.mid=$mid;";
		$rsdirector = $db->query($queryDirector);
		//Basic error handling 
		if(!$rsdirector){
			$errmsg = $db->error;
			print "Query failed: $errmsg <br />";
			exit(2);
		}

		$queryGenre = "SELECT genre FROM MovieGenre WHERE mid = $mid;";
		$rsgenre = $db->query($queryGenre);
		//Basic error handling 
		if(!$rsgenre){
			$errmsg = $db->error;
			print "Query failed: $errmsg <br />";
			exit(3);
		}

		$queryA = "SELECT CONCAT(A.first, ' ',A.last) AS ActorName, aid, MA.role FROM Actor A,MovieActor MA WHERE MA.mid = $mid AND A.id = MA.aid;";
		$rsactor = $db->query($queryA);
		//Basic error handling 
		if(!$rsactor){
			$errmsg = $db->error;
			print "Query failed: $errmsg <br />";
			exit(4);
		}

		$queryReview = "SELECT * FROM Review WHERE mid = $mid;";
		$rsreview = $db->query($queryReview);
		//Basic error handling 
		if(!$rsreview){
			$errmsg = $db->error;
			print "Query failed: $errmsg <br />";
			exit(5);
		}

		$queryAvg = "SELECT AVG(rating) AS avgRating FROM Review WHERE mid = $mid;";
		$rsavg = $db->query($queryAvg);
		//Basic error handling 
		if(!$rsavg){
			$errmsg = $db->error;
			print "Query failed: $errmsg <br />";
			exit(6);
		}else{
			$rowAvg = $rsavg->fetch_assoc();
		}
		//close connection
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
            <h3><b> Movie Information Page :</b></h3><hr />
            <h4><b> Movie Information: </b></h4>
            <div> 
            	<p>Title: <span><?php echo $rowM["title"]."(".$rowM["year"].")"; ?></span></p>
            	<p>Producer: <span><?php echo $rowM["company"];?></span></p>
            	<p>MPAA Rating: <span><?php echo $rowM["rating"];?></span></p>
            	<p>Director: <span>
            		<?php 
                    if($rsdirector->num_rows > 0){
                    while($rowDirector = $rsdirector->fetch_assoc()) { ?>
                  <?php echo $rowDirector["DirectorName"]?>
                  <?php } }else { echo "N/A";}?>
            	</span></p>
            	<p>Genre: <span>
            		<?php 
                    if($rsgenre->num_rows > 0){
                    while($rowGenre = $rsgenre->fetch_assoc()) { ?>
	                <?php echo $rowGenre["genre"];?>
	                <?php } }else{ echo "N/A";}?>
            	</span></p>
            </div><hr />
        
	        <h4><b>Actors in this Movie:</b></h4>
	        <div class='table-responsive'> 
	        	<table class='table table-bordered table-condensed table-hover'>
	        		<thead> 
	        		<tr>
	        		<td>Name</td>
	        		<td>Role</td>
	        		</tr>
	        		</thead>
	        		<tbody>
	        		<?php if ($rsactor->num_rows > 0){
	        		while($rowA = $rsactor->fetch_assoc()) { ?>
	        		<tr>
	        		<td><a href="ActorInfo.php?aid=<?php echo $rowA["aid"]?>"> <?php echo $rowA["ActorName"]?></a></td>
	        		<td><?php echo $rowA["role"]?></td>
	        		</tr>
	        		<?php }} ?>
	        		</tbody>
	        	</table>
	        </div><hr />
	        <h4><b>User Review:</b></h4>
	        <div>
	        	<p><?php echo "AvgScore:".$rowAvg["avgRating"]; ?></p>
	        </div>
	        <div class='table-responsive'> 
	        	<table class='table table-bordered table-condensed table-hover'>
	        		<thead> 
	        		<tr>
	        		<td>Name</td><td>Time</td><td>MovieID</td><td>Score</td><td>Comment</td>
	        		</tr>
	        		</thead>
	        		<tbody>
	        		<?php if ($rsreview->num_rows > 0){
	        		while($rowReview = $rsreview->fetch_assoc()) { ?>
	        		<tr>
	        		<td><?php echo $rowReview["name"]?></td>
	        		<td><?php echo $rowReview["time"]?></td>
	        		<td><?php echo $rowReview["mid"]?></td>
	        		<td><?php echo $rowReview["rating"]?></td>
	        		<td><?php echo $rowReview["comment"]?></td>
	        		</tr>
	        		<?php }} ?>
	        		</tbody>
	        	</table>
	        </div>
	        <div><a href="AddComment.php?id=<?php echo $rowM["id"];?>&name=<?php echo $rowM["title"];?>">Add your review</a></div>
	        <hr />

	        <label for="search">Search:</label>
	        <form class="form-group" action="Search.php" method ="GET">
              <input type="text" class="form-control" placeholder="Search" name="keys"><br>
              <input type="submit" value="Search" class="btn btn-default">
          	</form>
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