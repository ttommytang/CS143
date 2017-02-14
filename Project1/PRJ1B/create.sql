DROP TABLE IF EXISTS MovieGenre;
DROP TABLE IF EXISTS MovieDirector;
DROP TABLE IF EXISTS MovieActor;
DROP TABLE IF EXISTS Review;
DROP TABLE IF EXISTS MaxPersonID;
DROP TABLE IF EXISTS MaxMovieID;
DROP TABLE IF EXISTS Movie;
DROP TABLE IF EXISTS Actor;
DROP TABLE IF EXISTS Director;



create table Movie(
	id int not null, 
	# Every movie should have id.
	title varchar(100) not null,
	#  Every movie should have a title.
	year int, 
	rating varchar(10), 
	company varchar(50),
	primary key(id),
	# Id should be unique for every movie.
	CHECK(year <= 2016),
	# Movie should be no later than 2016.
	# Movie id should at least be a positive number.
	CHECK(id >= 0)
	)ENGINE = INNODB;

create table Actor(
	id int not null, 
	last varchar(20) not null, 
	first varchar(20) not null, 
	sex varchar(6) not null, 
	dob date not null, 
	# Every actor should at least have id, name, sex and dob;
	dod date,
	primary key(id),
	# actor id should be unique.
	CHECK(id>=0),
	# id should be a positive number at least;
	CHECK(dob < date_format(curdate(),'%Y%m%d'))
	)ENGINE = INNODB;

create table Director(
	id int NOT NULL, 
	last varchar(20) NOT NULL, 
	first varchar(20)NOT NULL,
	# Every director should at least have id and name and DOB; 
	dob date NOT NULL, 
	dod date,
	primary key(id),
	# id should be unique for every director;
	CHECK(dob < date_format(curdate(),'%Y%m%d'))
	)ENGINE=INNODB;

create table MovieGenre(
	mid int, 
	genre varchar(20) not NULL,
	# Gnere should not be empty if movie listed in this table;
	FOREIGN KEY (mid) REFERENCES Movie(id)
	ON DELETE CASCADE
	ON UPDATE CASCADE
	# The movie id in this table should also appear in the movie table to be valid;
	)ENGINE=INNODB;

create table MovieDirector(
	mid int, 
	did int not NULL,
	# movie id and director id should appear in complete pair;
	FOREIGN KEY (mid) REFERENCES Movie(id)
	ON DELETE CASCADE
	ON UPDATE SET NULL,
	# MOVIE ID here should also appear in movie table to be valid
	FOREIGN KEY (did) REFERENCES Director(id)
	ON DELETE CASCADE
	ON UPDATE CASCADE
	)ENGINE=INNODB;

create table MovieActor(
	mid int, 
	aid int not null, 
	role varchar(50) not null,
	# role and actor id cannot be empty once its recorded.
	FOREIGN KEY (mid) REFERENCES Movie(id)
	ON DELETE CASCADE
	ON UPDATE CASCADE,
	# MOVIE ID here should also appear in movie table to be valid
	# Actor id here should at first appear in the actor table to be valid;
	FOREIGN KEY (aid) REFERENCES Actor(id)
	ON DELETE CASCADE
	ON UPDATE CASCADE
	)ENGINE=INNODB;

create table Review(
	name varchar(20), 
	time timestamp, 
	mid int not NULL, 
	rating int, 
	comment varchar(500),
	FOREIGN KEY (mid) REFERENCES Movie(id)
	ON DELETE CASCADE
	ON UPDATE CASCADE
	# MOVIE ID here should also appear in movie table to be valid
	)ENGINE=INNODB;

create table MaxPersonID(
	id int NOT NULL
	)ENGINE=INNODB;

create table MaxMovieID(
	id int NOT NULL
	)ENGINE=INNODB;