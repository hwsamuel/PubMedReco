/* 
 * Chat simulator engine
 * @author Hamman Samuel hwsamuel@ualberta.ca
 */

const ThreadFields = {TIMESTAMP: 0, USER: 1, TEXT: 2, KEYWORDS: 3};

/*
 * Constructor 
 */
function ChatSimulator(threads)
{
	this.threadPtr = 0;
	this.threads = threads;
	this.nextThread = nextThread;
	this.getThreadNum = getThreadNum;
}

/*
 * Loads the next thread into the interface
 * @return object - Next thread row
 */
function nextThread()
{
	if (this.threadPtr >= this.threads.length)
	{
		return null;
	}

	var itm = {};
	itm[ThreadFields.TIMESTAMP] = this.threads[this.threadPtr][0];
	itm[ThreadFields.USER] = this.threads[this.threadPtr][1];	
	itm[ThreadFields.TEXT] = this.threads[this.threadPtr][2];
	itm[ThreadFields.KEYWORDS] = this.threads[this.threadPtr][3];
	this.threadPtr += 1;
	return itm; 
}

/*
 * Get current value of thread pointer
 * @return int 
 */
function getThreadNum()
{
	return this.threadPtr;
}