## Give me the names of all the actors in the movie 'Die Another Day'. 
## Please also make sure actor names are in this format:  <firstname> <lastname>
SELECT CONCAT(first,' ',last)
FROM Movie M,MovieActor MA,Actor A
WHERE M.title='Die Another Day' AND M.id=MA.mid AND MA.aid=A.id;

## Give me the count of all the actors who acted in multiple movies.
SELECT COUNT(DISTINCT aid)
FROM MovieActor
GROUP BY aid
HAVING COUNT(*) > 1

## Give the number of people who are both actor and diretor
SELECT COUNT(*)
FROM Actor A,Diretor D
WHERE A.id = D.id