/*
 * Main call point
 * @author Hamman Samuel hwsamuel@ualberta.ca 
 */

const Methods = {WORDFREQ: 1, TIMEWINDOW: 2, GRADFORGET: 3};

const LOADING = 'Loading...';
const WEIGHTSTARTBOUND = '(';
const WEIGHTENDBOUND = ')';

var lastTopWords = null;

var cht =  new ChatSimulator(_smartyThreads);
var idx = new Indexer();
var ajx = new AJAX(AJAXMethod.POST, AJAXReturn.JSON);

var recsIndex = new Array();
var recsNew = 0;
var recsRepeat = 0;
var autorun = null;

/*
 * Main event hook
 */
$(function()
{
	captionThreshold();
	
	$('#autorun').click(function(e){
		if ($('#autorun').text() == 'Autorun')
		{
			$('#autorun').text('Pause/Stop');
			autorun = setInterval('loadThread()', 250);
		}
		else
		{
			clearInterval(autorun);
			$('#autorun').text('Autorun');
		}
	});
	
	$('#method').change(function(e)
	{
		captionThreshold();
	});
	
	$('#threshValue').keypress(function (e)
	{
		if (e.which != 46 && e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) 
		{
	        $("#errmsg").html("Number Only").show().fadeOut(2000);
	        return false;
		}
	});

	$('body').keyup(function(e) // Watch page for key press release
	{
		if (e.keyCode != 32) // Only activate on space bar pressed
		{
			return false;
		}

		loadThread();
	});	
});

/*
 * Updates threshold label with caption matching method
 */
function captionThreshold()
{
	switch (getMethods())
	{
		case Methods.WORDFREQ:
			$('#threshLabel').text('Keywords count');
			break;
		case Methods.TIMEWINDOW:
			$('#threshLabel').text('Window size');
			break;
		case Methods.GRADFORGET:
			$('#threshLabel').text('Weight threshold');
			break;
	}
}

/*
 * Update index weights 
 * @param array keywords - Keywords in current thread
 */
function updateWeights(keywords)
{
	keywords = keywords.toString();
	var weightThresh = parseFloat(getThresholdValue());
	for (var i = 0; i < idx.getIndex().length; i++) 
	{
		var word = idx.getWord(i);
		var weight = parseFloat(idx.getWeight(i));
		var found = keywords.indexOf(word);
		if (found != -1)
		{
			var subkeys = keywords.substring(found);
			var weightStart = subkeys.indexOf(WEIGHTSTARTBOUND);
			var weightEnd = subkeys.indexOf(WEIGHTENDBOUND);
			var initWeight = parseFloat(subkeys.substring(weightStart + 1, weightEnd));
			weight += initWeight * weightThresh;
		}
		else
		{
			weight -= weight * weightThresh;
		}				
		idx.keywordsIndex[i][IndexFields.WEIGHT] = weight;
	}
}

/*
 * Load next thread into window
 */
function loadThread()
{
	if (isProcessing() == true)
	{
		return false;
	}

	var data = cht.nextThread();
	if (data == null)
	{
		return;
	}
	updateWeights(data[ThreadFields.KEYWORDS]);
	out = "<span class='label label-default'>" + data[ThreadFields.TIMESTAMP] + "</span>";
	out += "<span class='label label-default'>" + data[ThreadFields.USER] + "</span>";
	out += "<span>" + data[ThreadFields.TEXT] + "</span>";
	
	out += "<span class='pull-right small'>";

	for(var i in data[ThreadFields.KEYWORDS])
	{
		var keyword = data[ThreadFields.KEYWORDS][i];
		var weightPart = keyword.indexOf(WEIGHTSTARTBOUND); // Remove weight from printout
		var printKeyword = weightPart == -1 ? keyword : keyword.substring(0, weightPart - 1);
		var weight = keyword.substring(weightPart + 1, keyword.length - 1);
		out += "<span class='label label-success'>" + printKeyword + "</span>";

		loadIndex(printKeyword, data[ThreadFields.USER], weight);
	}
	out += "</span>";
	$('#chat-area').append($("<p>"+ out +"</p>"));
	$('#chat-area').scrollTop(document.getElementById('chat-area').scrollHeight);
	reprintIndex(getTopKeywords());
	
	if (data[ThreadFields.KEYWORDS].length > 0)
	{
		getRecommendations();
	}
}

/*
 * Adds or updates keywords in index
 * @param string keyword - Keyword to incorporate
 * @param string usr - User
 * @param float weight - Weight
 */
function loadIndex(keyword, usr, weight)
{
	if (idx.isEmpty(keyword) == true)
	{
		return;
	}

	var usrView = $('#userView').val();
	if (usrView != "" && usrView != usr)
	{
		return;
	}
	
	var threadNum = cht.getThreadNum();
	var inIndex = idx.find(keyword);
	if (inIndex == -1)
	{
		idx.addToIndex(keyword, threadNum, usr, weight);
	}
	else
	{
		idx.updateIndex(inIndex, threadNum, usr);
	}
}

/*
 * Return top keywords to send to PubMed
 * @return array
 */
function getTopKeywords()
{
	var topWords = null;
	switch (getMethods())
	{
		case Methods.WORDFREQ:
			topWords = getFrequentKeywords();
			break;
		case Methods.TIMEWINDOW:
			topWords = getLatestKeywords();
			break;
		case Methods.GRADFORGET:
			topWords = getWeightedKeywords();
			break;
	}
	return topWords;
}


/*
 * Return all keywords in index
 * @return array
 */
function getAllKeywords()
{
	var filtered = new Array();
	for (var i = 0; i < idx.keywordsIndex.length; i++) 
	{
		filtered.push(idx.getWord(i));
	}
	return filtered;
}

/*
 * Return all keywords
 * @return array - The index words
 */
function getFrequentKeywords()
{
	var numKeywords = parseInt(getThresholdValue());
	idx.keywordsIndex.sort(function(a,b) {return (parseInt(b[IndexFields.WORDFREQ])*(parseInt(b[IndexFields.UPVOTE]) - parseInt(b[IndexFields.DOWNVOTE]) + 1)) - (parseInt(a[IndexFields.WORDFREQ])*(parseInt(a[IndexFields.UPVOTE]) - parseInt(a[IndexFields.DOWNVOTE]) + 1))});
	
	var filtered = new Array();
	for (var i = 0; i < idx.keywordsIndex.length; i++) 
	{
		if (i < numKeywords)
		{
			filtered.push(idx.getWord(i));
		}
		else
		{
			break;
		}
	}
	return filtered;
}

/*
 * Get latest keywords from index
 * @return array
 */
function getLatestKeywords()
{
	var windowSize = parseInt(getThresholdValue());
	idx.keywordsIndex.sort(function(a,b) {return (parseInt(b[IndexFields.POSITION])*(parseInt(b[IndexFields.UPVOTE]) - parseInt(b[IndexFields.DOWNVOTE]) + 1)) - (parseInt(a[IndexFields.POSITION])*(parseInt(a[IndexFields.UPVOTE]) - parseInt(a[IndexFields.DOWNVOTE]) + 1))});
	
	var filtered = new Array();
	for (var i = 0; i < idx.keywordsIndex.length; i++) 
	{
		if (cht.getThreadNum() - idx.getPosition(i) < windowSize)
		{
			filtered.push(idx.getWord(i));
		}
		else
		{
			break;
		}
	}
	return filtered;
}

/*
 * Get keywords by weight
 * @return array
 */
function getWeightedKeywords()
{
	var weightThresh = parseFloat(getThresholdValue());
	idx.keywordsIndex.sort(function(a,b) {return (parseInt(b[IndexFields.WEIGHT])*(parseInt(b[IndexFields.UPVOTE]) - parseInt(b[IndexFields.DOWNVOTE]) + 1)) - (parseInt(a[IndexFields.WEIGHT])*(parseInt(a[IndexFields.UPVOTE]) - parseInt(a[IndexFields.DOWNVOTE]) + 1))});

	var filtered = new Array();
	for (var i = 0; i < idx.keywordsIndex.length; i++) 
	{
		if (idx.getWeight(i) >= weightThresh)
		{
			filtered.push(idx.getWord(i));
		}
		else
		{
			break;
		}
	}
	return filtered;
}

/*
 * Refreshes the index output
 * @param array topWords - List of top keywords
 */
function reprintIndex(topWords) 
{
	var out = null;
	for (var i = 0; i < idx.getIndex().length; i++) 
	{
		out += '<tr>';
		
		var word = idx.getWord(i);
		if (topWords.indexOf(word) == -1)
		{
			word = '<span class="text-muted">' + word + '</span>';
		}
		else
		{
			word = '<span class="text-primary"><b>' + word + '</b></span>';
		}

		out += '<td>' + word + '</td>';
		switch (getMethods())
		{
			case Methods.GRADFORGET:
				out += '<td>&nbsp;</td>'; // Position ignore
				out += '<td>&nbsp;</td>'; // Frequency ignore
				out += '<td>' + idx.getWeight(i) + '</td>';
				break;
			case Methods.TIMEWINDOW:
				out += '<td>' + idx.getPosition(i) + '</td>';
				out += '<td>&nbsp;</td>'; // Frequency ignore
				out += '<td>&nbsp;</td>'; // Weight ignore
				break;
			case Methods.WORDFREQ:
			default:
				out += '<td>&nbsp;</td>'; // Position ignore
				out += '<td>' + idx.getFrequency(i) + '</td>';
				out += '<td>&nbsp;</td>'; // Weight ignore
				break;
		}
		out += '<td>' + idx.getUpVotes(i) + '</td>';
		out += '<td>' + idx.getDownVotes(i) + '</td>';
		out += '</tr>';
	}
	$('#keywords-index').html(out);
}

/*
 * Query PubMed for recommendations
 */
function getRecommendations()
{
	var start = performance.now();
	var topWords = getTopKeywords();
	var allWords = getAllKeywords();
	
	topWords = topWords.toString();
	allWords = allWords.toString();

	if (topWords == this.lastTopWords || topWords.length == 0) 
	{
		return;
	}

	var exact = $('#med_words').val() == 1 ? true : false;
	var args = {'topWords': topWords, 'allWords': allWords};
	$('#recommend').html("<span class='text-muted'>" + LOADING + "</span>");
	ajx.call('recommend.php', args, function(data)
	{
		var response = $(data);
		var end = performance.now();
		var time = end - start;
		var timelapse = 'Retrieved in ' + (time/1000).toFixed(2) + 's';
		$('#runtime').html(timelapse);

		var avg_score = parseFloat(response.find('#avg_score').text()).toFixed(2);
		var min_score = parseFloat(response.find('#min_score').text()).toFixed(2);
		var max_score = parseFloat(response.find('#max_score').text()).toFixed(2);
		var recs = response.find('p');
		$('#recommend').html(recs);
		
		var recsPos = 0;
		recs.find('.article-id').each(function(index) 
		{
			recsPos++;
			if (recsPos > 3) 
			{
				return;
			}
			var artId = $(this).text();
			var found = recsIndex.indexOf(artId);
			if (found == -1)
			{
				recsIndex.push(artId);
				recsNew++;
			}
			else
			{
				recsRepeat++;
			}
		});
		$('#totalrecs').text(recsNew + recsRepeat);
		$('#uniquerecs').text(recsNew);
		$('#repeatrecs').text(recsRepeat);
		$('#scores_add').append("<tr><td>" + cht.getThreadNum() + "</td><td>" + avg_score + "</td></tr>");
	});
	this.lastTopWords = topWords;
}

/*
 * Record up vote and manipulate index
 * @param array titleWords - Words matched in title
 * @param array abstractWords - Words matched in abstract
 */
function upVote(titleWords, abstractWords)
{
	titleWords = titleWords.concat(abstractWords);
	for(var i in titleWords)
	{
		var ind = idx.find(titleWords[i]);
		idx.voteUp(ind);
	}
	getRecommendations();
}

/*
 * Record down vote and manipulate index
 * @param array titleWords - Words matched in title
 * @param array abstractWords - Words matched in abstract
 */
function downVote(titleWords, abstractWords, ctrl)
{
	titleWords = titleWords.concat(abstractWords);
	for(var i in titleWords)
	{
		var ind = idx.find(titleWords[i]);
		idx.voteDown(ind);
	}
	getRecommendations();
}

/*
 * Get number of keywords or window size
 * @return int
 */
function getThresholdValue()
{
	return $('#threshValue').val();	
}

/*
 * Get selected method
 * @return int
 */
function getMethods()
{
	return parseInt($('#method').val());
}

/*
 * Blocking function
 * @return boolean
 */
function isProcessing()
{
	return $('#recommend').text() == LOADING;
}
