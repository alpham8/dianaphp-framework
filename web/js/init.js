/* BEGIN Helper and Config */
var $editor;
var arSplit = window.location.pathname.split('/');

// remove the last slash if it is there
if (arSplit[arSplit.length - 1] === "")
{
	arSplit.pop();
}

var iPointer = arSplit.length - 1;
var sPointed = arSplit[iPointer];

// removes all framework params before
while (sPointed.indexOf('=') > -1 || sPointed.indexOf(window.encodeURIComponent('=')) > -1)
{
	arSplit.pop();
	iPointer--;
	sPointed = arSplit[iPointer];
}

arSplit.pop();
arSplit.pop();

var config =
	{
		basepath:  window.location.protocol
							+ "//"
							+ window.location.hostname
							+ arSplit.join("/") + "/"
	};
console.log("basepath=", config.basepath);
var formValidator;
var requestHelper =
	{
		loadDialog: function($selector, sDialogName, arDialogOptions, completeCallback)
					{
						$.get(config.basepath + "ajax/" + sDialogName,
							  function (data)
							  {
								if (typeof data.header.error !== "undefined")
								{
									if ($("#errorMsg").length === 0)
									{
										$('<div id="errorMsg" style="display:none;">' + data.header.error + '</div>')
											.appendTo("body");
									}
									else
									{
										$("#errorMsg").html(data.header.error);
									}
									$("#errorMsg").dialog(
														  {
															tile: "Exceptions in XHR!",
															closeOnEscape: false
														  }
														);
								}
								$selector.html(data.body);
								$selector.dialog(arDialogOptions);
								completeCallback();
							  },
							  'json');
					},
		sendRequest: function(action, sendMsg, bPost, completeCallback)
					{
						if (bPost === true)
						{
							$.post(config.basepath + "ajax/" + action,
								   sendMsg,
								   completeCallback,
								   "json");
						}

						else
						{
							$.get(config.basepath + "ajax/" + action,
							  sendMsg,
							  completeCallback,
							  "json");
						}
					},
		showLoaderOvlerlay: function(sActionName, arData)
							{
								$("#loaderOverlay")
									.dialog({
												closeOnEscape: false,
												draggable: true,
												modal: true,
												resizeable: false,
												height: 145,
												width: 170
											});
								$.post(config.basepath + "ajax/" + sActionName,
									   arData,
									   function (data)
									   {
											$("#loaderOverlay").dialog("close");
											if (typeof data.header.error !== "undefined")
											{
												if ($("#errorMsg").length === 0)
												{
													$('<div id="errorMsg" style="display:none;">' + data.header.error + '</div>')
														.appendTo("body");
												}
												else
												{
													$("#errorMsg").html(data.header.error);
												}
												$("#errorMsg").dialog(
																	  {
																		tile: "Exceptions in XHR!",
																		closeOnEscape: false
																	  }
																	);
											}
									   },
									'json');
							}
	};
/* END Helper and Config */

/* BEGIN global variables */
var $elem;
/* END end global variables */


/* BEGIN jQuery Validate custom Methods */

/* END jQuery Validate custom Methods */

/* BEGIN functions */

/** END functions */


/* BEGIN ready event */

/** END ready event */
