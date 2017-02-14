/**
 * Copyright (C) 2008 by The Regents of the University of California
 * Redistribution of this file is permitted under the terms of the GNU
 * Public License (GPL).
 *
 * @author Junghoo "John" Cho <cho AT cs.ucla.edu>
 * @date 3/24/2008
 */

#include <cstdio>
#include <cstring>
#include <cstdlib>
#include <iostream>
#include <fstream>
#include "Bruinbase.h"
#include "SqlEngine.h"
#include <limits.h>
#include "BTreeIndex.h"

using namespace std;

// external functions and variables for load file and sql command parsing 
extern FILE* sqlin;
int sqlparse(void);


RC SqlEngine::run(FILE* commandline)
{
  fprintf(stdout, "Bruinbase> ");

  // set the command line input and start parsing user input
  sqlin = commandline;
  sqlparse();  // sqlparse() is defined in SqlParser.tab.c generated from
               // SqlParser.y by bison (bison is GNU equivalent of yacc)

  return 0;
}

RC SqlEngine::select(int attr, const string& table, const vector<SelCond>& cond)
{
  RecordFile rf;   // RecordFile containing the table
  RecordId   rid;  // record cursor for table scanning

  RC     rc;
  int    key;     
  string value;
  int    count;
  int    diff;

  BTreeIndex treeIndex; // The tree for index
  IndexCursor cursor;  // The cursor for navigation
  vector<SelCond> checkCond; 
  //use minKey & maxKey to help get the range in using index tree
  int minKey = INT_MIN;
  int maxKey = INT_MAX;
  bool indexExist = false; //Mark whether index.idx file can be successfully open 
  bool useIndex = false; //Mark whether to use index
  bool hasValCond = false; //Mark whether value conditions exist

  // open the table file
  if ((rc = rf.open(table + ".tbl", 'r')) < 0) {
    fprintf(stderr, "Error: table %s does not exist\n", table.c_str());
    return rc;
  }
  rid.pid = rid.sid = 0;
  count = 0;

  if(treeIndex.open(table + ".idx", 'r') == 0){
    indexExist = true;
  }

  for(int i = 0; i < cond.size(); i++){
    //Case: condition in key
    if(cond[i].attr == 1){
      int condValue = atoi(cond[i].value);
      switch(cond[i].comp){
        case SelCond::EQ:
          if(condValue < minKey || condValue > maxKey){
            fprintf(stderr, "Error in where condition! \n");
            goto exit_select;
          }
          minKey = condValue;
          maxKey = condValue;
          break;
        case SelCond::NE:
          checkCond.push_back(cond[i]);
          break;
        case SelCond::LT:
          if(condValue-1 < maxKey){
            maxKey = condValue - 1;
          }
          break;
        case SelCond::LE:
          if(condValue < maxKey){
            maxKey = condValue;
          }
          break;
        case SelCond::GT:
          if(condValue+1 > minKey){
            minKey = condValue + 1;
          }
          break;
        case SelCond::GE:
          if(condValue > minKey){
            minKey = condValue;
          }
          break;
      }
    }else{
    //Case: condition in Value
    hasValCond = true;
    checkCond.push_back(cond[i]);
    }
  }//for ends

  if(minKey != INT_MIN || maxKey != INT_MAX ){
    useIndex = true;
  }

  if(indexExist && useIndex){
    treeIndex.locate(minKey, cursor);
    while(treeIndex.readForward(cursor, key, rid) == 0){
      //make sure it's in the range
      if(key > maxKey) break;
      //Condition on value OR attr on value/*
      if(hasValCond == true || attr == 2 || attr == 3){
        if((rc = rf.read(rid, key, value)) < 0){
          fprintf(stderr, "Error: 1 while reading a tuple from table %s\n", table.c_str());
          goto exit_select;
        }
      }
      if(checkCondition(checkCond, key, value)){
        count++;
        // print the tuple 
        switch (attr) {
        case 1:  // SELECT key
          fprintf(stdout, "%d\n", key);
          break;
        case 2:  // SELECT value
          fprintf(stdout, "%s\n", value.c_str());
          break;
        case 3:  // SELECT *
          fprintf(stdout, "%d '%s'\n", key, value.c_str());
          break;
        }
      }
    }//while ends

  }else{
    //index doesn't exist OR don't need use index
    //Just use the original code
    while (rid < rf.endRid()) {
      // read the tuple
      if(hasValCond || (attr != 4)){
        if ((rc = rf.read(rid, key, value)) < 0) {
          fprintf(stderr, "Error: 2 while reading a tuple from table %s\n", table.c_str());
          goto exit_select;
        }
      }

      // check the conditions on the tuple
      for (unsigned i = 0; i < cond.size(); i++) {
        // compute the difference between the tuple value and the condition value
        switch (cond[i].attr) {
        case 1:
    diff = key - atoi(cond[i].value);
    break;
        case 2:
    diff = strcmp(value.c_str(), cond[i].value);
    break;
        }

        // skip the tuple if any condition is not met
        switch (cond[i].comp) {
        case SelCond::EQ:
    if (diff != 0) goto next_tuple;
    break;
        case SelCond::NE:
    if (diff == 0) goto next_tuple;
    break;
        case SelCond::GT:
    if (diff <= 0) goto next_tuple;
    break;
        case SelCond::LT:
    if (diff >= 0) goto next_tuple;
    break;
        case SelCond::GE:
    if (diff < 0) goto next_tuple;
    break;
        case SelCond::LE:
    if (diff > 0) goto next_tuple;
    break;
        }
      }

      // the condition is met for the tuple. 
      // increase matching tuple counter
      count++;

      // print the tuple 
      switch (attr) {
      case 1:  // SELECT key
        fprintf(stdout, "%d\n", key);
        break;
      case 2:  // SELECT value
        fprintf(stdout, "%s\n", value.c_str());
        break;
      case 3:  // SELECT *
        fprintf(stdout, "%d '%s'\n", key, value.c_str());
        break;
      }

      // move to the next tuple
      next_tuple:
      ++rid;
    }//while ends
  }

  // print matching tuple count if "select count(*)"
  if (attr == 4) {
    fprintf(stdout, "%d\n", count);
  }
  rc = 0;

  // close the table file and return
  exit_select:
  rf.close();
  return rc;
}

bool SqlEngine::checkCondition(const vector<SelCond>& cond, int key, string value)
{
  int diff;

  for (unsigned i = 0; i < cond.size(); i++) {
      // compute the difference between the tuple value and the condition value
      switch (cond[i].attr) {
      case 1:
  diff = key - atoi(cond[i].value);
  break;
      case 2:
  diff = strcmp(value.c_str(), cond[i].value);
  break;
      }

      // skip the tuple if any condition is not met
      switch (cond[i].comp) {
      case SelCond::EQ:
  if (diff != 0) return false;
  break;
      case SelCond::NE:
  if (diff == 0) return false;
  break;
      case SelCond::GT:
  if (diff <= 0) return false;
  break;
      case SelCond::LT:
  if (diff >= 0) return false;
  break;
      case SelCond::GE:
  if (diff < 0) return false;
  break;
      case SelCond::LE:
  if (diff > 0) return false;
  break;
      }
  }
    return true;
}


RC SqlEngine::load(const string& table, const string& loadfile, bool index)
{
  /* your code here */
  RecordFile rf; // RecordFile containing the table
  RecordId   rid; 
  RC     rc;
  string line;
  int key;
  string value;

  ifstream infile(loadfile.c_str());
  if(!infile.is_open()){
    rc = -1;
    fprintf(stderr, "Error: Open loadfile %s failed \n",loadfile.c_str());
    return rc;
  }

  if((rc = rf.open(table + ".tbl", 'w')) < 0){
    fprintf(stderr, "Error: Create table %s failed \n", table.c_str());
    return rc;
  }
  if(index){
    BTreeIndex treeIndex;
    if((rc = treeIndex.open(table + ".idx", 'w')) < 0){
      infile.close();
      rf.close();
      fprintf(stderr, "Error: Create table index %s failed \n", table.c_str());
      return rc;
    }
    while(getline(infile,line)){
      parseLoadLine(line, key, value);
      if((rc = rf.append(key, value, rid)) < 0){
        fprintf(stderr, "Error: Append key %i failed \n", key);
        return rc;
      }
      if((rc = treeIndex.insert(key, rid)) < 0){
        fprintf(stderr, "Error: Insert key %i to tree failed \n", key);
        return rc;
      }
    }
    infile.close();
    rf.close();
    if((rc = treeIndex.close()) < 0){
      return rc;
    }
    return 0;
  }else{
    while(getline(infile,line)){
      parseLoadLine(line, key, value);
      if((rc = rf.append(key, value, rid)) < 0){
        fprintf(stderr, "Error: Append key %i failed \n", key);
        return rc;
      }
    }
    infile.close();
    rf.close();
    return 0;
  }

}

RC SqlEngine::parseLoadLine(const string& line, int& key, string& value)
{
    const char *s;
    char        c;
    string::size_type loc;
    
    // ignore beginning white spaces
    c = *(s = line.c_str());
    while (c == ' ' || c == '\t') { c = *++s; }

    // get the integer key value
    key = atoi(s);

    // look for comma
    s = strchr(s, ',');
    if (s == NULL) { return RC_INVALID_FILE_FORMAT; }

    // ignore white spaces
    do { c = *++s; } while (c == ' ' || c == '\t');
    
    // if there is nothing left, set the value to empty string
    if (c == 0) { 
        value.erase();
        return 0;
    }

    // is the value field delimited by ' or "?
    if (c == '\'' || c == '"') {
        s++;
    } else {
        c = '\n';
    }

    // get the value string
    value.assign(s);
    loc = value.find(c, 0);
    if (loc != string::npos) { value.erase(loc); }

    return 0;
}
