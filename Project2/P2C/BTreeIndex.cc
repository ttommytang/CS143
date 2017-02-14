/*
 * Copyright (C) 2008 by The Regents of the University of California
 * Redistribution of this file is permitted under the terms of the GNU
 * Public License (GPL).
 *
 * @author Junghoo "John" Cho <cho AT cs.ucla.edu>
 * @date 3/24/2008
 */
 
#include "BTreeIndex.h"
#include "BTreeNode.h"

using namespace std;

/*
 * BTreeIndex constructor
 */
BTreeIndex::BTreeIndex()
{
    rootPid = -1;
    treeHeight = 0;
}

/*
 * Open the index file in read or write mode.
 * Under 'w' mode, the index file should be created if it does not exist.
 * @param indexname[IN] the name of the index file
 * @param mode[IN] 'r' for read, 'w' for write
 * @return error code. 0 if no error
 */
RC BTreeIndex::open(const string& indexname, char mode)
{
    RC rc;
    char buffer[PageFile::PAGE_SIZE];

    if(mode == 'r'){
        // Return error code if failed to open the PageFile.
        if((rc = pf.open(indexname, mode)) < 0){
            return rc;
        }
        // Read PageFile into buffer, and copy the rootPid and treeHeight into corresponding variable.
        if((rc = pf.read(0, buffer)) < 0){
                return rc;
        }
        memcpy(&rootPid, buffer, sizeof(PageId));
        memcpy(&treeHeight, buffer+sizeof(PageId), sizeof(int));
    }else{
        // Write Mode:
        if(pf.endPid() == 0){
            // If Disk is empty, then initialize the rootPid to -1 and treeHeight to 0, write the buffer into the first
            // PageFile - Pid = 0;
            rootPid = -1;
            treeHeight = 0;
            memcpy(buffer, &rootPid, sizeof(PageId));
            memcpy(buffer+sizeof(PageId), &treeHeight, sizeof(int));
            if((rc = pf.write(0, buffer)) < 0){
                return rc;
            }
        }else{
            // Else if the disk is not empty, read the first PageFile into the buffer and also the rootPid and the treeHeight.
            if((rc = pf.open(indexname, mode)) < 0){
                return rc;
            }
            if((rc = pf.read(0, buffer)) < 0){
                return rc;
            }
            memcpy(&rootPid, buffer, sizeof(PageId));
            memcpy(&treeHeight, buffer+sizeof(PageId), sizeof(int));
        }
    }
    return 0;
}

/*
 * Close the index file.
 * @return error code. 0 if no error
 */
RC BTreeIndex::close()
{
    RC rc;
    char buffer[PageFile::PAGE_SIZE];

    // In avoid of the loss of member variables after closing the PageFile, write it into the disk.
    memcpy(buffer, &rootPid, sizeof(PageId));
    memcpy(buffer+sizeof(PageId), &treeHeight, sizeof(int));
    if((rc = pf.write(0, buffer)) < 0){
        return rc;
    }

    // Close the PageFile.
    if((rc = pf.close()) < 0){
        return rc;
    }
    return 0;
}

/*
 * Insert (key, RecordId) pair to the index.
 * @param key[IN] the key for the value inserted into the index
 * @param rid[IN] the RecordId for the record being inserted into the index
 * @return error code. 0 if no error
 */
RC BTreeIndex::insert(int key, const RecordId& rid)
{
    RC rc;
    // Initialize a root node with given <key, rid> if empty tree.
    if(treeHeight == 0){
        BTLeafNode root;
        if( (rc = root.insert(key, rid)) < 0)
            return rc;
        // Index tree takes up from Page 1, Page 0 for rootPid and treeHeight.
        rootPid = pf.endPid();
        if(rootPid == 0){
            rootPid = rootPid + 1;
        }
        if( (rc = root.write(rootPid, pf)) < 0)
            return rc;
        treeHeight++;
    }else{
        int upKey = -1;
        PageId upPid = -1;
        int height = 1;
        if((rc = insertRec(key, rid, rootPid, upKey, upPid, height)) < 0){
            return rc;
        }
    }
    return 0;
}

/*
 * Recursive helper function for the insert function. Carry the key value
 * and Rid to insert along the recursion, and also height variable to distinguish
 * whether its LeafNode level or NonLeafNode level, also need a flag to record whether
 * we need to update the parent node, and the midkey to insert into the parent node.
 * @param key[IN] key value to insert
 * @param rid[IN] rid to insert
 * @param curPid[IN/OUT] current node to do the insert
 * @param upkey[IN/OUT] Key value to insert to the parent non-leaf node(if overflow)
 * @param upPid[IN/OUT] PageId to insert to the parent non-leaf node(if overflow)
 * @param height[IN/OUT] recursive mark to tell apart the LeafNode/NonLeafNode
 */
RC BTreeIndex::insertRec(int key, const RecordId& rid, PageId& curPid, int& upKey, PageId& upPid, int height)
{
    RC rc;

    // Reset the update value.
    upKey = -1;
    upPid = -1;

    // If currently at the leaf-node level:
    if(height == treeHeight){
        // Initialize a new leafNode and read the current PageId into the buffer.
        BTLeafNode leafNode;
        if((rc = leafNode.read(curPid, pf)) < 0)
            return rc;

        if((rc = leafNode.insert(key, rid)) == 0) {
        // No overflow happended in the Leaf-Node insert, then write the PageFile and work done!
            if( (rc = leafNode.write(curPid, pf)) < 0)
                return rc;
            return 0;
        }else{
            // Overflow case: Need to insert and split half with a new sibling node.
            // Initialize a sibling leaf node.
            BTLeafNode siblingNode;
            // Call the API to do the job and return the sibling key to update.
            if((rc = leafNode.insertAndSplit(key, rid, siblingNode, upKey)) < 0){
                return rc;
            }
            // Set the new sibling node Pid to upPid(need to be inserted into the parent node).
            upPid = pf.endPid();
            // Re-link the leaf nodes, return any possible error code.
            if( (rc = siblingNode.setNextNodePtr(leafNode.getNextNodePtr())) < 0 ||
                    (rc = leafNode.setNextNodePtr(upPid)) < 0 ||
                    (rc = siblingNode.write(upPid, pf)) <0 ||
                    (rc = leafNode.write(curPid, pf)) < 0)
                return rc;
            // Overflow in the single-node tree case, initialize a new root node and return.
            if(height == 1){
                BTNonLeafNode newRoot;
                if( (rc = newRoot.initializeRoot(curPid, upKey, upPid)) < 0)
                    return rc;

                rootPid = pf.endPid();
                if((rc = newRoot.write(rootPid, pf)) <0)
                    return rc;

                treeHeight++;
                return 0;
            }
        }
    }else{
        // Non-Leaf node level:
        BTNonLeafNode nonleafNode;
        if((rc = nonleafNode.read(curPid, pf)) < 0)
            return rc;
        PageId childPid;
        // Call the locate API to find the right child point to trace down.
        if( (rc = nonleafNode.locateChildPtr(key, childPid)) < 0)
            return rc;

        // Reset the key/pid value.
        int childSibKey = -1;
        PageId childSibPid = -1;

        // Recursively call the function.
        if((rc = insertRec(key, rid, childPid, childSibKey, childSibPid, height+1)) < 0){
            return rc;
        }
        //overflow case: send the median key to parent
        if(childSibKey != -1){
            rc = nonleafNode.insert(childSibKey, childSibPid);
            //overflow case
            if(rc == 0){
                //non overflow case, write the pf then return 0.
                if( (rc = nonleafNode.write(curPid, pf)) < 0)
                    return rc;
                return 0;
            }else{
                // Overflow again after the insertion:
                BTNonLeafNode nonSibling;
                if((rc = nonleafNode.insertAndSplit(childSibKey, childSibPid, nonSibling, upKey)) < 0){
                    return rc;
                }

                // Write the updated and new node into pf.
                upPid = pf.endPid();
                if( (rc = nonSibling.write(upPid, pf)) < 0
                        || ( rc = nonleafNode.write(curPid, pf)) < 0)
                    return rc;

                // If currently at the root node, need to initialize a new root node and update the height.
                if(height == 1){
                    BTNonLeafNode newRoot;
                    if( ( rc = newRoot.initializeRoot(curPid, upKey ,upPid) ) < 0 )
                        return rc;

                    rootPid = pf.endPid();
                    if( (rc = newRoot.write(rootPid, pf)) < 0)
                        return rc;

                    treeHeight++;
                    return 0;
                }
            }
        } else {
            // No overflow in the current Non-Leaf level, stop and return 0.
            return 0;
        }

    }

}

/*
 * Run the standard B+Tree key search algorithm and identify the
 * leaf node where searchKey may exist. If an index entry with
 * searchKey exists in the leaf node, set IndexCursor to its location
 * (i.e., IndexCursor.pid = PageId of the leaf node, and
 * IndexCursor.eid = the searchKey index entry number.) and return 0.
 * If not, set IndexCursor.pid = PageId of the leaf node and
 * IndexCursor.eid = the index entry immediately after the largest
 * index key that is smaller than searchKey, and return the error
 * code RC_NO_SUCH_RECORD.
 * Using the returned "IndexCursor", you will have to call readForward()
 * to retrieve the actual (key, rid) pair from the index.
 * @param key[IN] the key to find
 * @param cursor[OUT] the cursor pointing to the index entry with
 *                    searchKey or immediately behind the largest key
 *                    smaller than searchKey.
 * @return 0 if searchKey is found. Othewise an error code
 */
RC BTreeIndex::locate(int searchKey, IndexCursor& cursor)
{
    return locateRec(searchKey, cursor, 1, rootPid);
}

/*
 * Recursive helper function for locate function. If locate at the leaf node
 * level currently, then call the LeafNode.locate function with the current
 * pageId to locate the entry number, else call the NonLeafNode.locateChildPtr
 * function to locate the PageId of the child node that direct to our target key.
 * @param searchKey[IN] the key to find
 * @param cursor[OUT] the cursor pointing to the index entry with
 *                    searchKey or immediately behind the largest key
 *                    smaller than searchKey.
 * @param height[IN/OUT] the current level to distinguish whether Leaf or
 *                       NonLeaf.
 * @param curPid[IN/OUT] current PageFile to read/search and also the next PageFile
 *                       to read and search.
 */
RC BTreeIndex::locateRec(int searchKey, IndexCursor& cursor, int height, PageId curPid)
{
    RC rc;
    // If current level is LeafNode, then call the locate function in LeafNode class
    // and write the PageId, EntryId into the cursor, return the 0/Error as the locate function.
    if(height == treeHeight){
        // Initialize the LeafNode to store the content to do the search.
        BTLeafNode leafNode = BTLeafNode();
        leafNode.read(curPid, pf);
        cursor.pid = curPid;
        if((rc = leafNode.locate(searchKey, cursor.eid)) < 0){
            return rc;
        }
    }else{
        // If current level is Non-leaf Node then initialize a Non-leaf node to store the content
        // then call the locateChildPtr function in the NonLeafNode class to locate the Child ptr,
        // then recursively call the function again.
        BTNonLeafNode nonleafNode = BTNonLeafNode();
        nonleafNode.read(curPid, pf);
        PageId childPid;
        if((rc = nonleafNode.locateChildPtr(searchKey, childPid)) < 0){
            return rc;
        }
        // Recursion, height + 1 and change PageId to the next child pointer.
        return locateRec(searchKey, cursor, height+1, childPid);
    }
}
/*
 * Read the (key, rid) pair at the location specified by the index cursor,
 * and move foward the cursor to the next entry.
 * @param cursor[IN/OUT] the cursor pointing to an leaf-node index entry in the b+tree
 * @param key[OUT] the key stored at the index cursor location.
 * @param rid[OUT] the RecordId stored at the index cursor location.
 * @return error code. 0 if no error
 */
RC BTreeIndex::readForward(IndexCursor& cursor, int& key, RecordId& rid)
{
    RC rc;
    BTLeafNode leafNode;

    // Return Error Code if the cursor has invalid PageId or EntryId.
    if(cursor.pid < 0 || cursor.pid >= pf.endPid() || cursor.eid < 0){
        return RC_INVALID_CURSOR;
    }

    // Read the PageFile into the buffer.
    if((rc = leafNode.read(cursor.pid, pf)) < 0){
        return rc;
    }

    // Read the entry into the <key, Rid> pair.
    if((rc = leafNode.readEntry(cursor.eid, key, rid)) < 0){
        return rc;
    }

    // Move the eid to the next entry index after reading, locate to the
    // sibling node and reset the entry id if necessary.
    cursor.eid = cursor.eid +1;
    if(cursor.eid > leafNode.getKeyCount()){
        cursor.pid = leafNode.getNextNodePtr();
        cursor.eid = 1;
    }
    return 0;
}
