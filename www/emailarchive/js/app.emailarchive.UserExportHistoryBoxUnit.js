// Copyright 2011. Eric Beach. All Rights Reserved.

goog.provide('app.emailarchive.UserExportHistoryBoxUnit');

goog.require('goog.ui.Component');
goog.require("goog.net.XhrIo");
goog.require('goog.events');
goog.require('goog.dom');
goog.require('goog.date');


/**
 * @constructor
 */
app.emailarchive.UserExportHistoryBoxUnit = function(bootstrapThis, exportHistoryBoxReadyCallbackFunction) {
	this._bootstrapThis = bootstrapThis;
	this._exportHistoryBoxReadyCallbackFunction = exportHistoryBoxReadyCallbackFunction;
}

/**
 * @private
 */
app.emailarchive.UserExportHistoryBoxUnit.prototype.construct = function() {
	this.constructDomSkeleton();
	this.getExportHistory();
}

/**
 * @type {app.emailarchive.bootstrap}
 * @private
 */
app.emailarchive.UserExportHistoryBoxUnit.prototype._bootstrapThis = null;

/**
 * @private
 */
app.emailarchive.UserExportHistoryBoxUnit.prototype._exportHistoryBoxReadyCallbackFunction = null;

/**
 * @type {Element}
 * @private
 */
app.emailarchive.UserExportHistoryBoxUnit.prototype._exportContainer = null;

/**
 * @type {Array}
 * @private
 */
app.emailarchive.UserExportHistoryBoxUnit.prototype._exportHistory = new Array();

/**
 * @enum {number}
 * @private
 */
app.emailarchive.UserExportHistoryBoxUnit.prototype.ExportHistoryStatus = {
	SUCCESS : 1,
	ERROR: 0,
	ERROR_DOMAIN_API_PERMISSIONS: 7,
	ERROR_USER_API_PERMISSIONS: 6,
	NOT_LOADED: 2,
	ERROR_NO_OAUTH_TOKENS: 5
};

/**
 * @type {number}
 * @private
 */
app.emailarchive.UserExportHistoryBoxUnit.prototype._exportHistoryStatus = app.emailarchive.UserExportHistoryBoxUnit.prototype.ExportHistoryStatus.NOT_LOADED;

/**
 * @type {boolean}
 * @private
 */
app.emailarchive.UserExportHistoryBoxUnit.prototype._isReadyForDisplay = false;

/**
 * @return {Element}
 */
app.emailarchive.UserExportHistoryBoxUnit.prototype.getDom = function() {
	return this._exportContainer;
}

/**
 * 
 * @return {boolean}
 */
app.emailarchive.UserExportHistoryBoxUnit.prototype.isReadyForDisplay = function() {
	return this._isReadyForDisplay;
}

/**
 * @param {string} username
 * @param {string} date
 * @param {string} status
 */
app.emailarchive.UserExportHistoryBoxUnit.prototype.appendUserExportInstance = function(username, date, status) {
	this._exportHistory.push({'request_date' : date, 'status' : {0 : status}, 'email_address' : {0 : username}});
	this._exportHistoryStatus = this.ExportHistoryStatus.SUCCESS;
}

/**
 * @private
 */
app.emailarchive.UserExportHistoryBoxUnit.prototype.receiveHistoryRefreshButtonClicked = function() {
	this._bootstrapThis.activateLoadingOverlay(this._bootstrapThis.OverlayType.CENTER_SCREEN, '', 'Refreshing your export history. Asking Google for data about your domain');
	this.getExportHistory();
}

/**
 * This function will populate or reflow the DOM with the current export history
 * 
 * @private
 */
app.emailarchive.UserExportHistoryBoxUnit.prototype.populateUserExportHistoryIntoDom = function() {
	
	if (!goog.dom.getElement('mailbox-export-history-header'))
	{
		var exportHistoryHeader = goog.dom.createDom('h2', {'id' : 'mailbox-export-history-header'});
		exportHistoryHeader.innerHTML = 'Mailbox Export Request History';
		goog.dom.appendChild(this._exportContainer, exportHistoryHeader);
	}
	
	/**
	 * @param {string} oldString The string to transform.
	 * @return {string} Transformed camelcase string. 
	 */
	var toCamelCaseString = function(oldString)
	{
		var newString = oldString[0].toUpperCase() + oldString.substring(1).toLowerCase();
		return newString;
	}
	
	/**
	 * @param {Object} exportRequest The export request status object.
	 * @return {string} HTML for display of export status 
	 */	
	var formatStatus = function(exportRequest)
	{
		if (exportRequest['status'][0] == 'COMPLETED')
		{
			return '<a href="ajax/download_exported_mailbox.php?fileUrl=' + exportRequest['file_url'][0] + '&requestId=' + exportRequest['request_id'][0] + '&requestEmailAddress=' + exportRequest['email_address'][0] + '&requestDate=' + exportRequest['request_date'] + '">Download</a>';
		}
		else
		{
			return toCamelCaseString(exportRequest['status'][0]);	
		}
	}
	
	/**
	 * Take a full date (e.g., "2011-10-19 03:43" or "2011-11-14-19-20") and convert it into shorter date (e.g., 10/19/11 or m/d/yy)
	 * 
	 * @param {string} inputDate full UTC date
	 * @return {string} short and more readable date
	 */
	var formatDate = function(inputDate)
	{
		var iso8601DateTime = goog.date.fromIsoString(inputDate.substr(0,10) + " " + inputDate.substr(11,2) + ":" + inputDate.substr(14,2) + "Z");
		return (iso8601DateTime.getMonth() + 1) + "/" + iso8601DateTime.getDate() + " " + iso8601DateTime.toUsTimeString(false, true);
	}
	
	if (!goog.dom.getElement('export-history-table'))
	{
		var exportHistoryTable = goog.dom.createDom('table', {'id' : 'export-history-table'});
		goog.dom.appendChild(this._exportContainer, exportHistoryTable);
	}
	else
	{
		var exportHistoryTable = goog.dom.getElement('export-history-table');
	}
	
	var tableHtml = '<tr>';
	tableHtml += '<th>Email Address</th>';
	tableHtml += '<th>Requested On</th>';
	tableHtml += '<th>Status</th>';
	tableHtml += '</tr>';
	
	for (var i in this._exportHistory)
	{	
		tableHtml += '<tr>';
		tableHtml += '<td>' + this._exportHistory[i]['email_address'][0] + '</td>';
		tableHtml += '<td>' + formatDate(this._exportHistory[i]['request_date']) + '</td>';
		tableHtml += '<td>' + formatStatus(this._exportHistory[i]) + '</td>';
		tableHtml += '</tr>';
	}
	
	exportHistoryTable.innerHTML = tableHtml;
	
	if (!goog.dom.getElement('mailbox-export-history-refresh'))
	{
		var exportHistoryRefreshContainer = goog.dom.createDom('p', {'id' : 'mailbox-export-history-refresh'});
				
		var iso8601DateTime = new goog.date.DateTime();
		var exportHistoryRefreshLastUpdated = goog.dom.createDom('span');
		exportHistoryRefreshLastUpdated.innerHTML = 'History Last Updated <span id="mailbox-export-history-last-updated-time">' + (iso8601DateTime.getMonth() + 1) + "/" + iso8601DateTime.getDate() + " " + iso8601DateTime.toUsTimeString(false, true) + '</span> &middot; ';
		goog.dom.appendChild(exportHistoryRefreshContainer, exportHistoryRefreshLastUpdated);
		
		var exportHistoryRefreshButton = goog.dom.createDom('span', {'class' : 'text-link'});
		exportHistoryRefreshButton.innerHTML = 'Refresh History';
		goog.events.listen(exportHistoryRefreshButton, goog.events.EventType.CLICK, this.receiveHistoryRefreshButtonClicked, false, this);	
		goog.dom.appendChild(exportHistoryRefreshContainer, exportHistoryRefreshButton);
		
		goog.dom.appendChild(this._exportContainer, exportHistoryRefreshContainer);
	}
	else if (goog.dom.getElement('mailbox-export-history-last-updated-time'))
	{
		var iso8601DateTime = new goog.date.DateTime();
		goog.dom.getElement('mailbox-export-history-last-updated-time').innerHTML = (iso8601DateTime.getMonth() + 1) + "/" + iso8601DateTime.getDate() + " " + iso8601DateTime.toUsTimeString(false, true);
	}
	
	if (!goog.dom.getElement("export-history-legend"))
	{
		var exportHistoryLegendTable = goog.dom.createDom('div', {'id' : 'export-history-legend'});
		exportHistoryLegendTable.innerHTML = '<p class="export-history-legend-heading">Explanation of Email Export Status</p>';
		exportHistoryLegendTable.innerHTML += '<ul>';
		exportHistoryLegendTable.innerHTML += '<li>Pending - The mailbox export request is being processed and will be available for download later</li>';
		exportHistoryLegendTable.innerHTML += '<li>Expired - The mailbox export request is no longer avaiable for download</li>';
		exportHistoryLegendTable.innerHTML += '<li>Download - The mailbox export request is completed and is ready for download</li>';
		exportHistoryLegendTable.innerHTML += '</ul>';
		goog.dom.appendChild(this._exportContainer, exportHistoryLegendTable);
	}
}

/**
 * @private
 */
app.emailarchive.UserExportHistoryBoxUnit.prototype.constructDomSkeleton = function() {
	this._exportContainer = goog.dom.createDom('div', {'id': 'export-history-container'});
}

/**
 * @private
 */
app.emailarchive.UserExportHistoryBoxUnit.prototype.populateDomSkeleton = function() {
	
	if (this._exportHistoryStatus == this.ExportHistoryStatus.SUCCESS
		&& this._exportHistory.length > 0)
	{
		// if Blank Export History previously existed, eliminate this from the DOM
		// this is for the usecase of rebuilding the DOM after the user adds their first export
		if (goog.dom.getElement("user-export-history-notice"))
		{
			goog.dom.removeNode(goog.dom.getElement("user-export-history-notice"));
		}
		this.populateUserExportHistoryIntoDom();
	}
	else if (this._exportHistoryStatus == this.ExportHistoryStatus.SUCCESS
		&& this._exportHistory.length == 0)
	{
		// need to check whether dom element exists already as this function could be called multiple times and we don't want to keep appending <p> elements
		if (!goog.dom.getElement("user-export-history-notice"))
		{
			var blankExportHistoryNotice = goog.dom.createDom('p', {'id' : 'user-export-history-notice'});
			goog.dom.appendChild(this._exportContainer, blankExportHistoryNotice);		
		}
		else
		{
			var blankExportHistoryNotice = goog.dom.getElement("user-export-history-notice");
		}
		blankExportHistoryNotice.innerHTML = 'You have not selected any mailboxes for export within the past 30 days. Please search for the username whose mailbox you would like to export and click Export.';
	}
	else
	{
		// need to check whether dom element exists already as this function could be called multiple times and we don't want to keep appending <p> elements
		if (!goog.dom.getElement("user-export-history-notice"))
		{
			var blankExportHistoryNotice = goog.dom.createDom('p', {'id' : 'user-export-history-notice'});
			goog.dom.appendChild(this._exportContainer, blankExportHistoryNotice);
		}
		else
		{
			var blankExportHistoryNotice = goog.dom.getElement("user-export-history-notice");
		}
		
		if (this._exportHistoryStatus == this.ExportHistoryStatus.ERROR_DOMAIN_API_PERMISSIONS)
		{
			var errorText = 'A permissions error occurred. Please make sure you\'re using Google Apps Business, Education, or ISP and you have granted access to Apps-Apps.info to the Email Audit API.';
		}
		else if (this._exportHistoryStatus == this.ExportHistoryStatus.ERROR_USER_API_PERMISSIONS
				|| this._exportHistoryStatus == this.ExportHistoryStatus.ERROR_NO_OAUTH_TOKENS)
		{
			var errorText = 'A permissions error occurred. Please make sure you\'re a Google Apps administrator and you have granted Apps-Apps.info access to the Email Audit API.';
		}
		else
		{
			var errorText = 'An error occurred on Google\'s end when we attempted to fetch your history of mailbox export requests.';
		}
		blankExportHistoryNotice.innerHTML = errorText;
	}
	this._isReadyForDisplay = true;
	
	// should invoke the callback function
	this._exportHistoryBoxReadyCallbackFunction(this._bootstrapThis);
}

/**
 * @private
 */
app.emailarchive.UserExportHistoryBoxUnit.prototype.getExportHistory = function() {
	
	var request = new goog.net.XhrIo();
	
	goog.events.listen(request, "complete", function() {
        if (request.isSuccess()) {
        	try
        	{
        		var responseObj = request.getResponseJson();
        	}
        	catch(err)
        	{
        		this._exportHistoryStatus = this.ExportHistoryStatus.ERROR;
        		this.populateDomSkeleton();
        		return false;
        	}
        	
        	if (responseObj == null
        		||	responseObj == undefined
        		|| responseObj['response_status'] == null
        		|| responseObj['response_status'] == undefined)
        	{
        		this._exportHistoryStatus = this.ExportHistoryStatus.ERROR;
        	}
        	else if (responseObj['response_status'] == 9)
        	{
        		//ajax request lacks necessary authorization; force reload of page
        		window.location = '/identity_check_start.php?auth_mode=active&final_landing_url=/emailarchive/';
        	}
        	else if (responseObj['response_status'] == 7)
        	{
        		this._exportHistoryStatus = this.ExportHistoryStatus.ERROR_DOMAIN_API_PERMISSIONS;
        	}
        	else if (responseObj['response_status'] == 6)
        	{
        		this._exportHistoryStatus = this.ExportHistoryStatus.ERROR_USER_API_PERMISSIONS;
        	}
        	else if (responseObj['response_status'] == 5)
        	{
        		this._exportHistoryStatus = this.ExportHistoryStatus.ERROR_NO_OAUTH_TOKENS;
        	}
        	else if (responseObj['response_contents'] != undefined
        		&& responseObj['response_contents'] != null
        		&& responseObj['response_contents']['export_requests'] != null
        		&& responseObj['response_contents']['export_requests'] != undefined
        		&& responseObj['response_contents']['export_requests']
				&& responseObj['response_status'] == 10)
        	{	
	        	this._exportHistory = responseObj['response_contents']['export_requests'];
	        	this._exportHistoryStatus = this.ExportHistoryStatus.SUCCESS;
        	}
        	else
        	{
        		//an unknown error occurred; display an error warning message in place of the normal export history box
        		this._exportHistoryStatus = this.ExportHistoryStatus.ERROR;
        	}
        	
        } else {
        	//an unknown error occurred; display an error warning message in place of the normal export history box
    		this._exportHistoryStatus = this.ExportHistoryStatus.ERROR;
        }
        
        // populate the dom skeleton and invoke the callback
        this.populateDomSkeleton();
    }, false, this);
	
	try {
		request.send("ajax/get_export_status_history.php", "GET");	
	}
	catch(err)
	{
		this._exportHistoryStatus = this.ExportHistoryStatus.ERROR;
		this.populateDomSkeleton();
	}
}
