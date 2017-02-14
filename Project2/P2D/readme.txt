Member: Hangjie Shi (104591649)
E-mail: hangjie@ucla.edu

Member: Yingchao Tang (404592020)
E-mail: yctang19325@ucla.edu

- We use one grace day for this part of project.

- This is the final part of project II, we modified the sqlEngine.h and sqlEngine.cc file.

- After running the test cases given, all functions we modified meet the requirement.


- Some specs to mention:
+ In order to simplify our code, we specifically implement the condition check function and make it part of the sqlEngine`s class, so we modify the sqlEngine.h file in this part only;
+ We use the first page to store the pid of root node and also the tree-height, so we need one more page read for test case 2, 4 and so on, but the final counts still meet the requirement.
+ For "SELECT COUNT(*)" query without condition check, we can get much less page reads than the requirement. Cause we just applied the original code if there is no need to use the indexing.
+ Some further thinking about improving the efficiency: we can modify the locate function in BTreeNode.cc for both leaf and non-leaf node. Use the binary search to locate the rid or pid due to that all the keys in the node of index tree should be sorted.
