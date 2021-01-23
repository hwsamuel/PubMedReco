/*
 * AJAX facade
 * @author Hamman Samuel hwsamuel@ualberta.ca
 */

const AJAXMethod = {POST: 'POST', GET: 'GET'};
const AJAXReturn = {JSON: 'JSON'};

/*
 * Constructor
 * @param AJAXMethod method - Method for calling
 * @param AJAXReturn type - Type of data to return
 */
function AJAX(method, type)
{
	this.method = method;
	this.type = type;

	this.call = call;
}

/*
 * Invoke method
 * @param string url - Server URL to call
 * @param object args - Arguments
 * @param function callback - Function to call to process data returned from server script
 */
function call(url, args, callback)
{
	var request = $.ajax
	({
		type: this.method,
		url: url,
		data: args,
		dataType: this.type
	});

	request.complete(function (data, status)
	{
		callback(data.responseText);
	});
}
