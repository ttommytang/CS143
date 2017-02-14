#include "BTreeNode.h"

using namespace std;
/*
 * Constructor for the BTLeafNode class, set buffer all zero.
 */
BTLeafNode::BTLeafNode()
{
    memset(buffer, 0, PageFile::PAGE_SIZE);
}
/*
 * Read the content of the node from the page pid in the PageFile pf.
 * @param pid[IN] the PageId to read
 * @param pf[IN] PageFile to read from
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::read(PageId pid, const PageFile& pf)
{
    RC rc;
    if((rc = pf.read(pid,buffer)) < 0){
        return rc;
    }
    return 0;
}
    
/*
 * Write the content of the node to the page pid in the PageFile pf.
 * @param pid[IN] the PageId to write to
 * @param pf[IN] PageFile to write to
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::write(PageId pid, PageFile& pf)
{
    RC rc;
    if((rc = pf.write(pid,buffer)) < 0){
        return rc;
    }
    return 0;
}

/*
 * Return the number of keys stored in the node.
 * @return the number of keys in the node
 */
int BTLeafNode::getKeyCount()
{
    int KeyCount = 0;
    memcpy(&KeyCount, buffer, sizeof(KeyCount));
    return KeyCount;
}

/*
 * Insert a (key, rid) pair to the node.
 * @param key[IN] the key to insert
 * @param rid[IN] the RecordId to insert
 * @return 0 if successful. Return an error code if the node is full.
 */
RC BTLeafNode::insert(int key, const RecordId& rid)
{
    int KeyCount = getKeyCount();
    if(KeyCount >= MAX_KEYS_LEAF){
        return RC_NODE_FULL;
    }
    /* Node still has space, and put key[in] into the position before
    the min key[node] larger than key[in]. Such as 10, 14, 20; put 10 before 14 */
    int curKey;
    int i;
    for(i=0; i<KeyCount; i++){
        memcpy(&curKey, buffer+sizeof(KeyCount)+i*LEAF_ENTRY_SIZE+sizeof(RecordId), sizeof(int));
        if(curKey > key){
            break;
        }
    }
    char *curBuffer = buffer;
    curBuffer = curBuffer + i*LEAF_ENTRY_SIZE + sizeof(KeyCount);
    //Move the latter part of the entry and insert the new key&rid
    memmove(curBuffer + LEAF_ENTRY_SIZE, curBuffer, (KeyCount-i)*LEAF_ENTRY_SIZE);
    memcpy(curBuffer, &rid, sizeof(RecordId));
    memcpy(curBuffer+sizeof(RecordId), &key, sizeof(int));

    //renew the KeyCount at the start
    KeyCount++;

    memcpy(buffer, &KeyCount, sizeof(KeyCount));
    return 0;
}

/*
 * Insert the (key, rid) pair to the node
 * and split the node half and half with sibling.
 * The first key of the sibling node is returned in siblingKey.
 * @param key[IN] the key to insert.
 * @param rid[IN] the RecordId to insert.
 * @param sibling[IN] the sibling node to split with. This node MUST be EMPTY when this function is called.
 * @param siblingKey[OUT] the first key in the sibling node after split.
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::insertAndSplit(int key, const RecordId& rid,
                              BTLeafNode& sibling, int& siblingKey)
{
    //When the node is full, and added with another entry, then it needs to be split.
    int KeyCount = getKeyCount();
    //Get the size of 2 halves
    int FirstHalf = (KeyCount + 1) / 2;
    int SecondHalf = KeyCount - FirstHalf;
    //copy the SecondHalf to the sibling node, and set the corresponding position in buffer to 0;
    memcpy(sibling.buffer + sizeof(int), buffer + sizeof(int) + FirstHalf * (LEAF_ENTRY_SIZE), SecondHalf * LEAF_ENTRY_SIZE);
    memset(buffer + sizeof(int) + FirstHalf * (LEAF_ENTRY_SIZE), 0, SecondHalf * LEAF_ENTRY_SIZE);

    //get the first tempkey at the start of the sibling node
    int tempkey;
    memcpy(&tempkey, sibling.buffer + sizeof(int) + sizeof(RecordId), sizeof(int));
    if(key < tempkey){
        insert(key, rid);
        memcpy(sibling.buffer, &SecondHalf, sizeof(int));
    }else{
        sibling.insert(key, rid);
        int siblingCount = SecondHalf + 1;
        memcpy(sibling.buffer, &siblingCount, sizeof(int));
    }

    //get the siblingKey, the first key in the new sibling node
    memcpy(&siblingKey, sibling.buffer + sizeof(int) + sizeof(RecordId), sizeof(int));

    return 0;
}

/**
 * If searchKey exists in the node, set eid to the index entry
 * with searchKey and return 0. If not, set eid to the index entry
 * immediately after the largest index key that is smaller than searchKey,
 * and return the error code RC_NO_SUCH_RECORD.
 * Remember that keys inside a B+tree node are always kept sorted.
 * @param searchKey[IN] the key to search for.
 * @param eid[OUT] the index entry number with searchKey or immediately
                   behind the largest key smaller than searchKey.
 * @return 0 if searchKey is found. Otherwise return an error code.
 */
RC BTLeafNode::locate(int searchKey, int& eid)
{
    int KeyCount = getKeyCount();
    int curKey;
    for(int i=0; i < KeyCount; i++){
        //KeyCount || Key, RecordId ||...||PageId
        memcpy(&curKey, buffer+sizeof(KeyCount)+i*LEAF_ENTRY_SIZE+sizeof(RecordId), sizeof(int));
        if(searchKey == curKey){
            eid = i+1;
            return 0;
        }else if(curKey > searchKey){
            eid = i+1;
            return RC_NO_SUCH_RECORD;
        }
    }
    eid = KeyCount + 1;
    return RC_NO_SUCH_RECORD;
}

/*
 * Read the (key, rid) pair from the eid entry.
 * @param eid[IN] the entry number to read the (key, rid) pair from
 * @param key[OUT] the key from the entry
 * @param rid[OUT] the RecordId from the entry
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::readEntry(int eid, int &key, RecordId &rid)
{
    int KeyCount=getKeyCount();
    if(eid < 0 || eid > KeyCount){
        return -1111;
        //-1111 Means the eid is invalid;
    }
    int plus = (eid-1)*LEAF_ENTRY_SIZE;
    memcpy(&rid, buffer+sizeof(int)+plus, sizeof(RecordId));
    memcpy(&key, buffer+sizeof(int)+sizeof(RecordId)+plus, sizeof(int));

    return 0;
}

/*
 * Return the pid of the next slibling node.
 * @return the PageId of the next sibling node 
 */
PageId BTLeafNode::getNextNodePtr()
{
    PageId nextNodePid;
    memcpy(&nextNodePid, buffer+PageFile::PAGE_SIZE-sizeof(PageId), sizeof(PageId));
    return nextNodePid;
}

/*
 * Set the pid of the next slibling node.
 * @param pid[IN] the PageId of the next sibling node 
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::setNextNodePtr(PageId pid)
{
    if(pid<0){
        return RC_INVALID_PID;
    }
    memcpy(buffer+PageFile::PAGE_SIZE-sizeof(PageId), &pid, sizeof(PageId));
    return 0;
}

/*------------------------------------------------------------------------------*/
/*
 * For constructor of the class, simply set all content in buffer zero.
 */
BTNonLeafNode::BTNonLeafNode()
{
    memset(buffer, 0, PageFile::PAGE_SIZE);
}
/*
 * Read the content of the node from the page pid in the PageFile pf.
 * @param pid[IN] the PageId to read
 * @param pf[IN] PageFile to read from
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::read(PageId pid, const PageFile& pf)
{
    RC rc;
    // Return error code if read failed.
    if((rc = pf.read(pid, buffer)) < 0){
        return rc;
    }
    return 0;
}
    
/*
 * Write the content of the node to the page pid in the PageFile pf.
 * @param pid[IN] the PageId to write to
 * @param pf[IN] PageFile to write to
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::write(PageId pid, PageFile& pf)
{
    RC rc;
    if((rc = pf.write(pid, buffer)) < 0){
        return rc;
    }
    return 0;
}

/*
 * Return the number of keys stored in the node.
 * @return the number of keys in the node
 */
int BTNonLeafNode::getKeyCount()
{
    int keyCount = 0;

    if(this != NULL) {
        memcpy(&keyCount, buffer, sizeof(keyCount));
    }
    return keyCount;
}

/*
 * Insert a (key, pid) pair to the node.
 * @param key[IN] the key to insert
 * @param pid[IN] the PageId to insert
 * @return 0 if successful. Return an error code if the node is full.
 */
RC BTNonLeafNode::insert(int key, PageId pid)
{
    RC rc;
    int keyCount = getKeyCount();
    // Return error code if the current node is already full.
    if(keyCount >= NONLEAF_ENTRY_NUM) {
        return RC_NODE_FULL;
    }

    int eid = 0;
    // If all key values in the node are smaller than the key to insert, then append the key and pid to the end.
    if((rc = locate(key, eid)) < 0) {
        memcpy(buffer + sizeof(int) + keyCount * NONLEAF_ENTRY_SIZE + sizeof(PageId), &key, sizeof(key));
        memcpy(buffer + sizeof(int) + keyCount * NONLEAF_ENTRY_SIZE + sizeof(PageId) + sizeof(key), &pid, sizeof(PageId));
    } else {
        // else, shift all the entries following the eid(including the key of located entry), then insert the pair.
        int shift = sizeof(keyCount) + (eid-1) * NONLEAF_ENTRY_SIZE + sizeof(PageId);
//        for(int i = sizeof(keyCount) + keyCount * NONLEAF_ENTRY_SIZE + sizeof(PageId) - 1; i >= shift; i--) {
//            buffer[i + NONLEAF_ENTRY_SIZE] = buffer[i];
//        }
        memmove(buffer + shift + NONLEAF_ENTRY_SIZE, buffer + shift, (keyCount - eid + 1) * NONLEAF_ENTRY_SIZE);
        // Write the pair into the buffer.
        memcpy(buffer + shift, &key, sizeof(key));
        memcpy(buffer + shift + sizeof(key), &pid, sizeof(PageId));
    }

    keyCount++;
    memcpy(buffer, &keyCount, sizeof(int));
    return 0;

}

/*
 * Insert the (key, pid) pair to the node
 * and split the node half and half with sibling.
 * The middle key after the split is returned in midKey.
 * @param key[IN] the key to insert
 * @param pid[IN] the PageId to insert
 * @param sibling[IN] the sibling node to split with. This node MUST be empty when this function is called.
 * @param midKey[OUT] the key in the middle after the split. This key should be inserted to the parent node.
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::insertAndSplit(int key, PageId pid, BTNonLeafNode& sibling, int& midKey)
{
    RC rc;
    // Partition should be the entry number point to the
    int keyCount = getKeyCount();
    int partition = (keyCount + 1)/2 - 1;

    // Copy the middle key value into midkey.
    memcpy(&midKey, buffer + sizeof(int) + partition * NONLEAF_ENTRY_SIZE + sizeof(PageId), sizeof(int));

    PageId iterPid;
    int iterKey;
    PageId sibPid;
    memcpy(&sibPid, buffer + sizeof(int) + (partition + 1) * NONLEAF_ENTRY_SIZE, sizeof(PageId));

    // Copy all the last half entries into the sibling node.
    for(int i = partition+1; i < keyCount; i ++) {
        // Copy the <key, pid> entries into <iterKey, iterPid>, for the first pair need to initialize the siblig root!
        memcpy(&iterKey, buffer + sizeof(int) + i * NONLEAF_ENTRY_SIZE + sizeof(PageId), sizeof(int));
        memcpy(&iterPid, buffer + sizeof(int) + (i+1) * NONLEAF_ENTRY_SIZE, sizeof(PageId));

        if(i == partition + 1 && sibling.getKeyCount() <= 0) {
            sibling.initializeRoot(sibPid, iterKey, iterPid);
        } else if((rc = sibling.insert(iterKey, iterPid)) < 0) {
            return rc;
        }
    }
    // Clear the second half of the original node.
    for(int i = sizeof(int) + partition * NONLEAF_ENTRY_SIZE + sizeof(PageId); i < (keyCount + 1) * NONLEAF_ENTRY_SIZE; i ++)
    {
        buffer[i] = '\0';
    }

    // Reset the key count.
    memcpy(buffer, &partition, sizeof(int));

    // Insert the new key into the original/sibling node, depend on its key value.
    if(key < midKey) {
        if((rc = this->insert(key, pid)) < 0)
            return rc;
    } else {
        if((rc = sibling.insert(key, pid)) < 0)
            return rc;
    }

    return 0;
}

/*
    * Set eid to the index(start from 1) entry immediately after the largest index key that is smaller than searchKey.
    * Remember that keys inside a B+tree node are always kept sorted.
    * @param searchKey[IN] the key to search for.
    * @param eid[OUT] the index entry number with searchKey or immediately
                      behind the largest key smaller than searchKey.
    * @return 0 if the located index is not at the tail of the node. If not, RC_NO_SEARCH_RECORD.
    */
RC BTNonLeafNode::locate(int searchKey, int& eid)
{
    int keyCount = this->getKeyCount();
    int curKey;
    int cursor = 0;

    // Read the very first key value into the curKey.
    memcpy(&curKey, buffer + sizeof(int) + sizeof(PageId) + cursor * NONLEAF_ENTRY_SIZE, sizeof(int));

    // Locate cursor right after the entry that has largest smaller key value.
    while(curKey < searchKey && cursor < keyCount) {
        cursor++;
        memcpy(&curKey, buffer + sizeof(int) + sizeof(PageId) + cursor * NONLEAF_ENTRY_SIZE, sizeof(int));
    }
    eid = cursor + 1;
    if(cursor == keyCount)
        return RC_NO_SUCH_RECORD;
    else
        return 0;
}

/*
 * Given the searchKey, find the child-node pointer to follow and
 * output it in pid.
 * @param searchKey[IN] the searchKey that is being looked up.
 * @param pid[OUT] the pointer to the child node to follow.
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::locateChildPtr(int searchKey, PageId& pid)
{
    // Initialize the keyCount to iterate and the pid as zero.
    int keyCount = this->getKeyCount();
    int curKey;
    pid = 0;
    // Traverse through the Non-Leaf node until we find the first larger or equal key.
    // Return the pid after the equal key or before the first larger key.
    for(int i = 0; i < keyCount; i++) {
        memcpy(&curKey, buffer + sizeof(keyCount) + i * NONLEAF_ENTRY_SIZE + sizeof(PageId), sizeof(int));
        // Copy the i-th key value into the curKey variable through memcpy API.
        if(searchKey == curKey) {
            memcpy(&pid, buffer + sizeof(keyCount) + (i+1) * NONLEAF_ENTRY_SIZE, sizeof(PageId));
            return 0;
        }
        if(searchKey < curKey) {
            memcpy(&pid, buffer + sizeof(keyCount) + i * NONLEAF_ENTRY_SIZE, sizeof(PageId));
            return 0;
        }
    }
    // If the searchKey is larger than all keys in the node.
    memcpy(&pid, buffer + sizeof(keyCount) + keyCount * NONLEAF_ENTRY_SIZE, sizeof(PageId));
    return 0;
}

/*
 * Initialize the root node with (pid1, key, pid2).
 * @param pid1[IN] the first PageId to insert
 * @param key[IN] the key that should be inserted between the two PageIds
 * @param pid2[IN] the PageId to insert behind the key
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::initializeRoot(PageId pid1, int key, PageId pid2)
{
    if(pid1 < 0 || pid2 < 0) {
        return RC_INVALID_PID;
    }
    // Initialize the key count to 1 and insert the first entry with the tail pid into the node.
    int keyCount = 1;
    memcpy(buffer, &keyCount, sizeof(keyCount));
    memcpy(buffer + sizeof(keyCount), &pid1, sizeof(PageId));
    memcpy(buffer + sizeof(keyCount) + sizeof(PageId), &key, sizeof(int));
    memcpy(buffer + NONLEAF_ENTRY_SIZE, &pid2, sizeof(PageId));
    return 0;
}
