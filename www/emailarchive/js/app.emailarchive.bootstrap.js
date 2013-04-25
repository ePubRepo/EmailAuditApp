// Copyright 2011. Eric Beach. All Rights Reserved.

goog.provide('app.emailarchive.bootstrap');

goog.require('app.emailarchive.UserSearchBoxUnit');
goog.require('app.emailarchive.UserExportHistoryBoxUnit');
goog.require('goog.dom');

/**
 * Bootstrap for email archive app
 * @constructor
 */
app.emailarchive.bootstrap = function() {
	
	this._centerDomContentsContainer = goog.dom.createDom('div', {'id' : 'page-center-contents-container', 'class': 'center-container'});
	goog.dom.insertSiblingAfter(this._centerDomContentsContainer, goog.dom.getElement('page-top-container'));
	
	this._userSearchBoxUnitContainer = goog.dom.createDom('div');
	goog.dom.appendChild(this._centerDomContentsContainer, this._userSearchBoxUnitContainer);
	
	this._userExportHistoryBoxContainer = goog.dom.createDom('div');
	goog.dom.appendChild(this._centerDomContentsContainer, this._userExportHistoryBoxContainer);
	
	this.constructFullPageLoadingFrame();
	this.constructCenterPageLoadingFrame();
	
	this.activateLoadingOverlay(this.OverlayType.CENTER_SCREEN, '', 'Asking Google for data about your domain');
	
	this._userSearchBoxUnit = new app.emailarchive.UserSearchBoxUnit(this, this.userSearchBoxUnitReady);
	this._userSearchBoxUnit.construct();
	
	this._userExportHistoryBoxUnit = new app.emailarchive.UserExportHistoryBoxUnit(this, this.userExportHistoryBoxUnitReady);
	this._userExportHistoryBoxUnit.construct();	
}

/**
 * @type {app.emailarchive.UserExportHistoryBoxUnit}
 * @private
 */
app.emailarchive.bootstrap.prototype._userExportHistoryBoxUnit = null;

/**
 * @type {Element}
 * @private
 */
app.emailarchive.bootstrap.prototype._userExportHistoryBoxContainer = null;

/**
 * @type {app.emailarchive.UserSearchBoxUnit}
 * @private
 */
app.emailarchive.bootstrap.prototype._userSearchBoxUnit = null;

/**
 * @type {Element}
 * @private
 */
app.emailarchive.bootstrap.prototype._userSearchBoxUnitContainer = null;

/**
 * @type {Element}
 * @private
 */
app.emailarchive.bootstrap.prototype._centerDomContentsContainer = null;

/**
 * Enum values for Overlay Type
 * 
 * @enum {number}
 */
app.emailarchive.bootstrap.prototype.OverlayType = {
	FULL_SCREEN : 1,
	CENTER_SCREEN : 2
};

/**
 * @return {app.emailarchive.UserExportHistoryBoxUnit}
 */
app.emailarchive.bootstrap.prototype.getUserExportHistoryBoxUnit = function() {
	return this._userExportHistoryBoxUnit;
}

/** 
 * @private
 */
app.emailarchive.bootstrap.prototype.constructFullPageLoadingFrame = function() {
	var loadingFrameDom = goog.dom.createDom('div', {'id': 'page-full-loading-frame', 'class' : 'page-full-loading-frame-inactive'});
	var loadingContentDom = goog.dom.createDom('div', {'id' : 'page-full-loading-contents'});
	goog.dom.appendChild(loadingFrameDom, loadingContentDom);
	goog.dom.insertSiblingBefore(loadingFrameDom, goog.dom.getElement("page-contents-container"));
}

/** 
 * @private
 */
app.emailarchive.bootstrap.prototype.constructCenterPageLoadingFrame = function() {
	var loadingFrameDom = goog.dom.createDom('div', {'id': 'page-center-loading-frame', 'class' : 'page-center-loading-frame-inactive'});
	var loadingContentDom = goog.dom.createDom('div', {'id' : 'page-center-loading-contents'});
	goog.dom.appendChild(loadingFrameDom, loadingContentDom);
	goog.dom.insertSiblingAfter(loadingFrameDom, goog.dom.getElement("page-top-container"));
}

/**
 * 
 * @param {app.emailarchive.bootstrap.prototype.OverlayType} overlayType Type of overlay (i.e., Full Screen or Center Page)
 * @param {string} messageHeader Text header of message to display to user while loading
 * @param {string} messageContents Text contents of message to display to user while loading
 * @private
 */
app.emailarchive.bootstrap.prototype.activateLoadingOverlay = function(overlayType, messageHeader, messageContents) {
	if (overlayType == this.OverlayType.FULL_SCREEN)
	{
		goog.dom.getElement("page-contents-container").className = 'page-full-contents-dimmed';
	}
	else if (overlayType == this.OverlayType.CENTER_SCREEN)
	{
		goog.dom.getElement("page-center-contents-container").className = 'page-center-contents-dimmed';
	}
	
	var loadingContents = '<div id="page-loading-header">' + messageHeader + '</div>';
	loadingContents += '<div id="page-loading-contents">' + messageContents + '</div>';
	loadingContents += '<div id="page-loading-spinner-container"><div id="page-loading-spinner"></div></div>';
	
	if (overlayType == this.OverlayType.FULL_SCREEN)
	{
		goog.dom.getElement("page-full-loading-contents").innerHTML = loadingContents;
	}
	else if (overlayType == this.OverlayType.CENTER_SCREEN)
	{
		goog.dom.getElement("page-center-loading-contents").innerHTML = loadingContents;
	}
	
	var mySpinner = new Spinner({
		  lines: 12, // The number of lines to draw
		  length: 15, // The length of each line
		  width: 10, // The line thickness
		  radius: 40, // The radius of the inner circle
		  color: '#DD4B39', // #rbg or #rrggbb
		  speed: 0.8, // Rounds per second
		  trail: 50, // Afterglow percentage
		  shadow: false // Whether to render a shadow
		}).spin(document.getElementById("page-loading-spinner")); // Place in DOM node called "page-loading-spinner"
	
	if (overlayType == this.OverlayType.FULL_SCREEN)
	{
		goog.dom.getElement("page-full-loading-frame").className = 'page-full-loading-frame-active';
	}
	else if (overlayType == this.OverlayType.CENTER_SCREEN)
	{
		goog.dom.getElement("page-center-loading-frame").className = 'page-center-loading-frame-active';
	}
}

/**
 * @private
 */
app.emailarchive.bootstrap.prototype.deactivateLoadingOverlay = function() {
	goog.dom.getElement("page-full-loading-frame").className = 'page-full-loading-frame-inactive';
	goog.dom.getElement("page-contents-container").className = 'page-full-contents-active';
	
	goog.dom.getElement("page-center-loading-frame").className = 'page-center-loading-frame-inactive';
	goog.dom.getElement("page-center-contents-container").className = 'center-container page-full-contents-active';
}

/**
 * Check whether the DOM components are in a state to be displayed and interacted with.
 */
app.emailarchive.bootstrap.prototype.checkWhetherToDeactivateLoadingOverlay = function() {
	if (this._userSearchBoxUnit.isReadyForDisplay() && this._userExportHistoryBoxUnit.isReadyForDisplay())
	{
		this.deactivateLoadingOverlay();
	}
}

/**
 * Callback function when the UserSearchBoxUnit is fully prepared
 * 
 * @param {app.emailarchive.bootstrap} bootstrapObjectInstance 
 * @private
 */
app.emailarchive.bootstrap.prototype.userSearchBoxUnitReady = function(bootstrapObjectInstance) {
	var searchContainer = bootstrapObjectInstance._userSearchBoxUnit.getDom();
	goog.dom.appendChild(bootstrapObjectInstance._userSearchBoxUnitContainer, searchContainer);
	bootstrapObjectInstance.checkWhetherToDeactivateLoadingOverlay();
}

/**
 * Callback function when the UserExportHistoryBoxUnit is fully prepared
 * 
 * @param {app.emailarchive.bootstrap} bootstrapObjectInstance 
 * @private
 */
app.emailarchive.bootstrap.prototype.userExportHistoryBoxUnitReady = function(bootstrapObjectInstance) {
	var exportHistoryContainer = bootstrapObjectInstance._userExportHistoryBoxUnit.getDom();
	goog.dom.appendChild(bootstrapObjectInstance._userExportHistoryBoxContainer, exportHistoryContainer);
	bootstrapObjectInstance.checkWhetherToDeactivateLoadingOverlay();
}

/**
 * Exposes function app.emailarchive.bootstrap so that after obfuscation, avaialable to HTML that does not have the obfuscated name
 */
goog.exportSymbol('app.emailarchive.bootstrap', app.emailarchive.bootstrap);
