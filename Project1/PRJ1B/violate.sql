############### Primary key violation ################
insert into Movie values(10,'KONG',2002,'R','FOX');
# Movie id is the primary key must be unique, this command will 
# generate error cause duplicated entry '10' for id;

insert into Actor values(10, 'Pete','Brad','Male','1961-02-02','');
# This command as well will violate the unique actor id constrain;

############## Referential constrain violation ############
insert into MovieGenre values(4800, 'Horror');
# This command will violate the referential constrain on MovieGenre table
# Cause there cannot be movie number 4800 in the Movie table, this command

update MovieGenre SET mid = 4800 WHERE mid = 4555;;
# Same manner;

insert into MovieDirector values(70000,2);
update MovieDirector SET mid = 70000 where did = 1000;
# These two commands will violate the referential constrain on the 
# MovieDirector table, cause there cannot be any movie numbered 70000

insert into MovieActor values(100,69999,'Leading');
update MovieActor SET mid = 70000 where mid = 100;
# These two commands will violate the referential constrain on MovieActor 
# table cause there is no #70000 in MOvie table nor #69999 in Actor table;

insert into Review values('Tommy','2015-01-01','69001','5','kkk');

################# CHECK violation ##################
update Movie SET year = 2020 where id = 20;
insert into Movie values(-3,'KONG',2014,'R','Universal');
# These two commands will violate the non-negative id and year < 2016 constrains on Movie;

insert into Actor values(2,'Lively','Blake','A','1983-03-08','');
insert into Actor values(2,'Lively','Blake','A','2034-03-08','');
# These two commands will violate the check constrain on Actor table, invalid 
# sex and dob;

insert into Director values(2,'Lively','Blake','2034-03-08','');
# Invalid sex field will violate the check constrain in Director table;

################ Not null constrain ##############
insert into Movie values(3,'',2000,'R','FOX');
# Movie title cannot be null due to the constrain
insert into Actor values(3,'','Johnson','Male','1993-01-25','');
# Name cannot be null
insert into Actor values(3,'Carey','Johnson','','1993-01-25','');
# Sex cannot be null
insert into Actor values(3,'Carey','Johnson','Male','','');
# DOB cannot be null
insert into Review values('Jack','','','','Nice');
# Movie id cannot be null in Review


