<?php
	//connection to database
	include('connect.php');
	
	if($_GET["aid"]){
		$aid=$_GET["aid"];
		$query="SELECT * FROM Actor WHERE id=$aid;";
		$rsactor = $db->query($query);
		//Basic error handling 
		if(!$rsactor){
			$errmsg = $db->error;
			print "Query failed: $errmsg <br />";
			exit(1);
		}else{
			$rowA = $rsactor->fetch_assoc();
		}
		

		$queryM = "SELECT CONCAT(M.title,' ','(',M.year,')') AS MovieName, mid, MA.role FROM Movie M, MovieActor MA WHERE MA.mid=M.id AND MA.aid=$aid;";
		$rsmovie = $db->query($queryM);
		//Basic error handling 
		if(!$rsmovie){
			$errmsg = $db->error;
			print "Query failed: $errmsg <br />";
			exit(2);
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
            <h3><b> Actor Information Page :</b></h3><hr />
            <h4><b> Actor Information: </b></h4>
            <div class='table-responsive'> 
            	<table class='table table-bordered table-condensed table-hover'>
	            	<thead> 
	            		<tr>
	            		<td>Name</td>
	            		<td>Sex</td>
	            		<td>Date of Birth</td>
	            		<td>Date of Death</td>
	            		</tr>
	            	</thead>
	            	<tbody>
		            	<tr>
			            	<td><?php echo $rowA["first"]." ".$rowA["last"];?></td>
			            	<td><?php echo $rowA["sex"];?></td>
			            	<td><?php echo $rowA["dob"];?></td>
			            	<td><?php echo $rowA["dod"] ? $rowA["dod"]:"Still Alive";?></td>
		            	</tr>
	            	</tbody>
            	</table>
            </div>
            	<hr /> 
        
	        <h4><b>Actor's Movies and Role:</b></h4>
	        <div class='table-responsive'> 
	        	<table class='table table-bordered table-condensed table-hover'>
	        		<thead> 
	        		<tr>
	        		<td>Role</td>
	        		<td>Movie Title</td>
	        		</tr>
	        		</thead>
	        		<tbody>
	        		<?php if ($rsmovie->num_rows > 0){
	        		while($rowM = $rsmovie->fetch_assoc()) { ?>
	        		<tr>
	        		<td><?php echo $rowM["role"]?></td>
	        		<td><a href="MovieInfo.php?mid=<?php echo $rowM["mid"]?>"> <?php echo $rowM["MovieName"]?></a></td>
	        		</tr>
	        		<?php }} ?>
	        		</tbody>
	        	</table>
	        </div><hr />
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