// Copyright 2011. Eric Beach. All Rights Reserved.

goog.provide('app.emailarchive.UserSearchBoxUnit');

goog.require('goog.ui.AutoComplete.Basic');
goog.require('goog.ui.LabelInput');
goog.require('goog.ui.Component');
goog.require("goog.net.XhrIo");
goog.require('goog.events');
goog.require('goog.dom');

/**
 * @constructor
 */
app.emailarchive.UserSearchBoxUnit = function(bootstrapThis, searchBoxReadyCallbackFunction) {
	this._bootstrapThis = bootstrapThis;
	this._searchBoxReadyCallbackFunction = searchBoxReadyCallbackFunction;
}

/**
 * @type {goog.ui.AutoComplete.Basic}
 * @private
 */
app.emailarchive.UserSearchBoxUnit.prototype._searchAutoComplete = null;

/**
 * @type {app.emailarchive.bootstrap}
 * @private
 */
app.emailarchive.UserSearchBoxUnit.prototype._bootstrapThis = null;

/**
 * @private
 */
app.emailarchive.UserSearchBoxUnit.prototype._searchBoxReadyCallbackFunction = null;

/**
 * @type {Element}
 * @private
 */
app.emailarchive.UserSearchBoxUnit.prototype._searchContainer = null;

/**
 * @type {boolean}
 * @private
 */
app.emailarchive.UserSearchBoxUnit.prototype._isReadyForDisplay = false;

/**
 * @type {HTMLInputElement}
 * @private
 */
app.emailarchive.UserSearchBoxUnit.prototype._usernameSearchInputBox = null;

/**
 * @type {Element}
 * @private
 */
app.emailarchive.UserSearchBoxUnit.prototype._searchStatusMessageBox = null;

/**
 * @type {Array.<string>}
 * @private
 */
app.emailarchive.UserSearchBoxUnit.prototype._searchIndex = new Array();

/**
 * @enum {number}
 * @private
 */
app.emailarchive.UserSearchBoxUnit.prototype.SearchIndexStatus = {
	SUCCESS : 1,
	SUCCESS_TOO_MANY_RESULTS: 8,
	ERROR: 0,
	ERROR_DOMAIN_API_PERMISSIONS: 7,
	ERROR_USER_API_PERMISSIONS: 6,
	NOT_LOADED: 2,
	ERROR_NO_OAUTH_TOKENS: 5,
	ERROR_INVALID_USER: 4
};

/**
 * @type {number}
 * @private
 */
app.emailarchive.UserSearchBoxUnit.prototype._searchIndexStatus = app.emailarchive.UserSearchBoxUnit.prototype.SearchIndexStatus.NOT_LOADED;

/**
 * Kick off the search box unit
 */
app.emailarchive.UserSearchBoxUnit.prototype.construct = function() {
	this.getSearchIndex();
}

/**
 * @return {Element}
 */
app.emailarchive.UserSearchBoxUnit.prototype.getDom = function() {
	return this._searchContainer;
}

/**
 * 
 * @return {boolean}
 */
app.emailarchive.UserSearchBoxUnit.prototype.isReadyForDisplay = function() {
	return this._isReadyForDisplay;
}

/**
 * @private
 */
app.emailarchive.UserSearchBoxUnit.prototype.constructDom = function() {
	this._searchContainer = goog.dom.createDom('div', {'id': 'search-container'});
	
	this._usernameSearchInputBox = /** @type {HTMLInputElement} */ (goog.dom.createDom('input', {'type': 'textbox', 'id': 'usernameSearchInputBox', 'class' : 'search-box'}));
	goog.dom.appendChild(this._searchContainer, this._usernameSearchInputBox);
	
	// only add autocomplete is the search index is fully successful (i.e., returned successfully and was not truncated by 100 user limit in Provisoining API)
	if (this._searchIndexStatus == this.SearchIndexStatus.SUCCESS) {
		this._searchAutoComplete = new goog.ui.AutoComplete.Basic(this._searchIndex, this._usernameSearchInputBox, false);	
	}
	
	var exportButton = goog.dom.createDom('input', {'type': 'button', 'value' : 'Export', 'class' : 'button search-button'});
	goog.dom.appendChild(this._searchContainer, exportButton);
	goog.events.listen(exportButton, goog.events.EventType.CLICK, this.handleSearchButtonClick, false, this);
	
	var inputBoxHelper = new goog.ui.LabelInput('Search for a user to export mail');
	inputBoxHelper.decorate(this._usernameSearchInputBox);
	
	this._searchStatusMessageBox = goog.dom.createDom('div', {'class' : 'search-status-message'});
	goog.dom.appendChild(this._searchContainer, this._searchStatusMessageBox);
	
	if (this._searchIndexStatus == this.SearchIndexStatus.ERROR_DOMAIN_API_PERMISSIONS)
	{
		this.setSearchStatusMessage('A permissions error occurred. Please make sure you have the Provisioning API enabled in your Google Apps Control Panel. Please see this <a href="http://support.google.com/a/bin/answer.py?hl=en&answer=60757">Google Apps help center article</a> for more.');
	}
	else if (this._searchIndexStatus == this.SearchIndexStatus.ERROR_USER_API_PERMISSIONS)
	{
		this.setSearchStatusMessage('A permissions error occurred. Please make sure you\'re a Google Apps administrator and you have granted Apps-Apps.info access to the Email Audit API.');
	}
	
	this._isReadyForDisplay = true;
	this._searchBoxReadyCallbackFunction(this._bootstrapThis);
}

/**
 * @private
 */
app.emailarchive.UserSearchBoxUnit.prototype.clearSearchBox = function() {
	this._usernameSearchInputBox.value = '';
}

/**
 * @private
 */
app.emailarchive.UserSearchBoxUnit.prototype.handleSearchButtonClick = function(e) {
	var searchedUsername = this._usernameSearchInputBox.value;
	if (searchedUsername.length > 3)
	{
		this.submitUsernameToExport(searchedUsername);
	}
	else
	{
		this.setSearchStatusMessage('Please enter a full email address to export.');
		return false;
	}
}

/**
 * 
 * @param {string} message A description of the status of the username export
 * @private
 */
app.emailarchive.UserSearchBoxUnit.prototype.setSearchStatusMessage = function(message) {
	this._searchStatusMessageBox.innerHTML = message;
}

/**
 * @enum {number}
 * @private
 */
app.emailarchive.UserSearchBoxUnit.prototype.SearchUserInputValidationStatus = {
	
	// search index of usernames did not load, so we will not be able to validate
	UNABLE_TO_VALIDATE: 0,
	
	//  
	USERNAME_VALIDATED : 1,
	
	//
	USERNAME_INVALID: 2,
	
	// username has not yet been checked
	USERNAME_NOT_CHECKED: 3
};

/**
 * @param {string} username The username to export.
 * @private
 */
app.emailarchive.UserSearchBoxUnit.prototype.submitUsernameToExport = function(username) {
	/**
	 * STEP 1: Check input username
	 */
	if (this._searchIndexStatus != this.SearchIndexStatus.SUCCESS)
	{
		//username search index (i.e., list of users) was not successfully loaded, so we will not be able to validate user list on client end
		var usernameValidationStatus = this.SearchUserInputValidationStatus.UNABLE_TO_VALIDATE;
	}
	else
	{
		// search index successfully loaded; can validate against search index
		var usernameValidationStatus = this.SearchUserInputValidationStatus.USERNAME_NOT_CHECKED;
		var i;
		for (i = 0; i < this._searchIndex.length; i++)
		{
			if (this._searchIndex[i] === username)
			{
				usernameValidationStatus = this.SearchUserInputValidationStatus.USERNAME_VALIDATED;
			}
		}

		if (usernameValidationStatus != this.SearchUserInputValidationStatus.USERNAME_VALIDATED)
		{
			//username was not validated by the search index, so it must be invalid
			usernameValidationStatus = this.SearchUserInputValidationStatus.USERNAME_INVALID;
		}
	}

	if (usernameValidationStatus == this.SearchUserInputValidationStatus.USERNAME_INVALID)
	{
		this.setSearchStatusMessage('The username you attempted to queue for download, ' + username +  ', is not a valid username.');
    	this.clearSearchBox();
        this._isReadyForDisplay = true;
        this._bootstrapThis.checkWhetherToDeactivateLoadingOverlay();
        return false;
	}
	
	/**
	 * STEP 2: Username is valid (or could not be checked as index of users didn't load), submit XHR
	 */
	var request = new goog.net.XhrIo();
	
	goog.events.listen(request, "complete", function() {
        if (request.isSuccess()) {
	    	try
	    	{
	        	var responseObj = request.getResponseJson();
	        }
	    	catch(err)
	    	{
	    		this.setSearchStatusMessage('An error occurred in attempting to queue mailbox ' + username +  ' for export.');
	        	this.clearSearchBox();
	            this._isReadyForDisplay = true;
	            this._bootstrapThis.checkWhetherToDeactivateLoadingOverlay();
	            return false;
	    	}
        	
	    	if (responseObj == null
            	|| responseObj == undefined)
        	{
        		// Error occurred, incomplete response; 
	    		this.setSearchStatusMessage('An error occurred in attempting to queue mailbox ' + username +  ' for export.');
        	}        	
	    	else if (responseObj['response_status'] == null
        		|| responseObj['response_status'] == undefined)
        	{
        		this.setSearchStatusMessage('An error occurred in attempting to queue mailbox ' + username +  ' for export.');
        	}
        	else if (responseObj['response_status'] == 9)
        	{
        		// ajax request lacks necessary authorization; force reload of page
        		window.location = '/identity_check_start.php?auth_mode=active&final_landing_url=/emailarchive/';
        	}
        	else if (responseObj['response_status'] == 4)
        	{
        		// Error occurred, incomplete response; autocomplete will not work
        		this.setSearchStatusMessage('The user ' + username +  ' is not valid.');        		
        	}
        	else if (responseObj['response_status'] == 7
        			|| responseObj['response_status'] == 6)
        	{
        		this.setSearchStatusMessage('A permissions error occurred in attempting to queue mailbox ' + username +  ' for export. Please make sure your domain qualifies for the Email Audit API and you have the Provisioning API enabled in your Control Panel.');
        	}
        	else if (responseObj['response_contents']
    		&& responseObj['response_status'] == 10)
        	{
        		var strPad = function(number, length)
        		{
        		    var str = '' + number;
        		    while (str.length < length) {
        		        str = '0' + str;
        		    }
        		    return str;	
        		}
        		
        		var nowDate = new Date();
        		this._bootstrapThis.getUserExportHistoryBoxUnit().appendUserExportInstance(username, nowDate.getUTCFullYear() + '-' + strPad((nowDate.getUTCMonth() + 1), 2) + '-' + strPad(nowDate.getUTCDate(), 2) + ' ' + strPad(nowDate.getUTCHours(), 2) + '-' + strPad(nowDate.getUTCMinutes(), 2), 'Pending');
        		this._bootstrapThis.getUserExportHistoryBoxUnit().populateDomSkeleton();
        		this.setSearchStatusMessage('Successfully queued mailbox ' + username +  ' for export. Mailboxes typically take anywhere from four hours to four days to be processed by Google before they are available here.');	
        	}
        	else
        	{
        		this.setSearchStatusMessage('An error occurred in attempting to queue mailbox ' + username +  ' for export.');	
        	}
        } else {
        	this.setSearchStatusMessage('An error occurred in attempting to queue mailbox ' + username +  ' for export.');     
        }
        
    	this.clearSearchBox();
        this._isReadyForDisplay = true;
        this._bootstrapThis.checkWhetherToDeactivateLoadingOverlay();
    }, false, this);
	
	this._isReadyForDisplay = false;
	this._bootstrapThis.activateLoadingOverlay(this._bootstrapThis.OverlayType.CENTER_SCREEN, "Processing Mailbox Export Request", "It will take about a minute for Google to process the mailbox export request.");
	
	request.send("ajax/mailbox_export_request.php?email_address=" + username, "GET");
}

/**
 * @private
 */
app.emailarchive.UserSearchBoxUnit.prototype.getSearchIndex = function() {
	var request = new goog.net.XhrIo();
	
	goog.events.listen(request, "complete", function() {
        if (request.isSuccess()) {
        	try
        	{
        		var responseObj = request.getResponseJson();
        	}
        	catch(err)
        	{
        		// Error parsing response as JSON; autocomplete will not work
        	}
        	
        	if (responseObj == null
        		|| responseObj == undefined)
        	{
        		// Error occurred, incomplete response; autocomplete will not work
        		this._searchIndexStatus = this.SearchIndexStatus.SUCCESS;
        	}
        	else if (responseObj['response_status'] == null
            		|| responseObj['response_status'] == undefined)
        	{
        		// Error occurred, incomplete response; autocomplete will not work
        		this._searchIndexStatus = this.SearchIndexStatus.ERROR;
        	}
        	else if (responseObj['response_status'] == 9)
        	{
        		//ajax request lacks necessary authorization; force reload of page
        		window.location = '/identity_check_start.php?auth_mode=active&final_landing_url=/emailarchive/';
        	}
        	else if (responseObj['response_status'] == 7)
        	{
        		// Error occurred, incomplete response; autocomplete will not work
        		this._searchIndexStatus = this.SearchIndexStatus.ERROR_DOMAIN_API_PERMISSIONS;
        	}
        	else if (responseObj['response_status'] == 6)
        	{
        		// Error occurred, incomplete response; autocomplete will not work
        		this._searchIndexStatus = this.SearchIndexStatus.ERROR_USER_API_PERMISSIONS;
        	}
        	else if (responseObj['response_status'] == 4)
        	{
        		// Error occurred, incomplete response; autocomplete will not work
        		this._searchIndexStatus = this.SearchIndexStatus.ERROR_INVALID_USER;
        	}
        	else if (responseObj['response_status'] == 5)
        	{
        		// error occurred, no OAuthRepositoryToken; major problems
        		this._searchIndexStatus = this.SearchIndexStatus.ERROR;
        	}
        	else if (responseObj['response_contents'] != null
        		&& responseObj['response_contents'] != undefined
        		&& responseObj['response_contents']
        		&& responseObj['response_status'] == 10)
        	{
        		this._searchIndex = responseObj['response_contents'];
        		if (this._searchIndex.length >= 100) {
        			this._searchIndexStatus = this.SearchIndexStatus.SUCCESS_TOO_MANY_RESULTS;
        		}
        		else
    			{
        			this._searchIndexStatus = this.SearchIndexStatus.SUCCESS;
    			}
        	}
        	else
        	{
        		// list of usernames failed to successfully load; autocomplete will not work
        		this._searchIndexStatus = this.SearchIndexStatus.ERROR;
        	}
        	
        	// now that search index is populated with usernames, construct the dom 
        	this.constructDom();
        } else {
            window.console.log(
                "Something went wrong in the ajax call. Error code: ", request.getLastErrorCode(),
                " - message: ", request.getLastError()
            );     
        }
    }, false, this);
	
	request.send("ajax/get_user_list.php", "GET");
}
