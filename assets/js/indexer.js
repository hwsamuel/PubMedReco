/*
 * Add words to an index that exists on current page till refreshed/reloaded
 * @author Hamman Samuel hwsamuel@ualberta.ca
 */

const IndexFields = {WORD: 1, WORDFREQ: 2, POSITION: 3, UPVOTE: 4, DOWNVOTE: 5, USERS: 6, WEIGHT: 7};

/*
 * Constructor
 */
function Indexer()
{
	this.keywordsIndex = new Array();
	
	this.isEmpty = isEmpty;
	this.getIndex = getIndex;
	this.find = find;
	this.addToIndex = addToIndex;
	this.updateIndex = updateIndex;
	this.updateWeights = updateWeights;
	
	this.getWord = getWord;
	this.getFrequency = getFrequency;
	this.getPosition = getPosition;
	this.getWeight = getWeight;
	this.getUpVotes = getUpVotes;
	this.getDownVotes = getDownVotes;
	this.getUsers = getUsers;
	
	this.voteUp = voteUp;
	this.voteDown = voteDown;
}

/*
 * Check if string is empty
 * @param string str - String to test
 * @return boolean
 */
function isEmpty(str)
{
	str = jQuery.trim(str);
	if (str == '' || str == null)
	{
		return true;
	}
	else
	{
		return false;
	}
}

/*
 * Get the index
 * @return array - The index
 */
function getIndex()
{
	return this.keywordsIndex;
}

/*
 * Checks if word is in index
 * @param string word - Word to look up
 * @return int - The index of the word in the array   
 */
function find(word)
{
	for (var i = 0; i < this.keywordsIndex.length; i++) 
	{
	    if (this.keywordsIndex[i][IndexFields.WORD] == word)
    	{
	    	return i;
    	}
	}
	return -1;
}

/*
 * Adds a word to the index
 * @param string word - The word to add
 * @param int position - Current position
 * @param string user - User associated with word
 * @param float weight - Keyword weight 
 */
function addToIndex(word, position, usr, weight)
{
	var itm = {};
	
	itm[IndexFields.WORD] = word;
	itm[IndexFields.WORDFREQ] = 1;
	itm[IndexFields.POSITION] = position;
	itm[IndexFields.UPVOTE] = 0;
	itm[IndexFields.DOWNVOTE] = 0;
	itm[IndexFields.USERS] = [usr];
	itm[IndexFields.WEIGHT] = weight;
	this.keywordsIndex.push(itm);
}

/*
 * Updates index for existing keyword (weight updates ignored)
 * @param int i - Reference to an item from the words index
 * @param int position - Current position
*/
function updateIndex(i, position, usr)
{
	this.keywordsIndex[i][IndexFields.WORDFREQ] += 1;
	this.keywordsIndex[i][IndexFields.POSITION] = position;

	if(this.keywordsIndex[i][IndexFields.USERS].indexOf(usr) == -1)
	{
		this.keywordsIndex[i][IndexFields.USERS].push(usr);
	}
}

/*
 * Cast up-vote
 * @param int i - Reference to an item from the words index
 */
function voteUp(i)
{
	this.keywordsIndex[i][IndexFields.UPVOTE] += 1;
}

/*
 * Cast down-vote
 * @param int i - Reference to an item from the words index
 */
function voteDown(i)
{
	this.keywordsIndex[i][IndexFields.DOWNVOTE] += 1;
}

/*
 * Get word at specified index
 * @param int i - Index reference
 * @return string
 */
function getWord(i)
{
	return this.keywordsIndex[i][IndexFields.WORD];
}

/*
 * Get frequency of word at specified index
 * @param int i - Index reference
 * @return int
 */
function getFrequency(i)
{
	return parseInt(this.keywordsIndex[i][IndexFields.WORDFREQ]);
}

/*
 * Get position of word at specified index
 * @param int i - Index reference
 * @return int
 */
function getPosition(i)
{
	return parseInt(this.keywordsIndex[i][IndexFields.POSITION]);
}

/*
 * Get weight of word at specified index
 * @param int i - Index reference
 * @return int
 */
function getWeight(i)
{
	return parseFloat(this.keywordsIndex[i][IndexFields.WEIGHT]).toFixed(2);
}

/*
 * Get number of upvotes of word at specified index
 * @param int i - Index reference
 * @return int
 */
function getUpVotes(i)
{
	return parseInt(this.keywordsIndex[i][IndexFields.UPVOTE]);
}

/*
 * Get number of downvotes of word at specified index
 * @param int i - Index reference
 * @return int
 */
function getDownVotes(i)
{
	return parseInt(this.keywordsIndex[i][IndexFields.DOWNVOTE]);
}

/*
 * Get users associated with specified index word
 * @param int i - Index reference
 * @return array
 */
function getUsers(i)
{
	return this.keywordsIndex[i][IndexFields.USERS];
}
