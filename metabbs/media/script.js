Form.getSubmitButton = function (form) {
	return $(form).getInputs('submit')[0];
}

function checkForm(form) {
	className = 'blank'
	var valid = !$(form).getElements().collect(function (el) {
		if (!el.value && !el.hasClassName('ignore')) {
			el.addClassName(className);
			return false;
		} else {
			el.removeClassName(className);
			return true;
		}
	}).include(false);

	if (valid) {
		var submitButton = Form.getSubmitButton(form);
		//new Insertion.After(submitButton, '<span id="sending">Sending...</span>');
		submitButton.disable();
	} else {
		document.getElementsByClassName(className)[0].focus();
	}
	
	return valid;
}

function validateForm(event) {
	var valid = true;
	var form = this;

	$(form).select('.check').each(function (el) {
		if (!el.value) {
			valid = false;
			el.addClassName('field_error');
		} else {
			el.removeClassName('field_error');
		}
	})

	if (valid) {
		var submitButton = Form.getSubmitButton(form);
		//new Insertion.After(submitButton, '<span id="sending">Sending...</span>');
		//submitButton.disable();
	} else {
		$$('.field_error')[0].focus();
		event.stop()
	}
}

function toggleAll(form, bit) {
	$(form).getInputs('checkbox').each(function (el) {
		el.checked = bit;
	});
}

function loadReplyForm(id, url) {
	var form = $('reply-form');
	if (form) {
		form.hide();
		var hide = form.parentNode.id == id;
		form.remove();
		if (hide) return false;
	}
	new Ajax.Updater($(id).getElementsByClassName('comment')[0], url, {
		method: 'GET',
		insertion: Insertion.After
	});
}

function addComment(form, list) {
	var data = Form.serialize(form);
	if (!checkForm(form)) return false;
	new Ajax.Updater({success: list}, $(form).action, {
		parameters: data,
		insertion: Insertion.Bottom,
		onFailure: function (transport) {
			alert(transport.responseText);
		},
		onComplete: function (transport) { 
			var submitButton = Form.getSubmitButton(form);
			submitButton.enable();
			if ($('comment_password') != null)
				$('comment_password').value = '';
			$('comment_body').value = '';
			triggerDialogLinks();
		}
	});
	return false;
}

function deleteComment(form, id, leaveEntry) {
	var submitButton = Form.getSubmitButton(form);
	submitButton.disable();
	new Ajax.Request(form.action, {
		parameters: Form.serialize(form),
		onSuccess: function (transport) {
			if (!leaveEntry)
				$('comment_' + id).remove()
			else {
				// recalDepth
				$('comment_' + id).replace(transport.responseText)
			}
			closeDialog()
		},
		onFailure: function () {
			alert('암호가 틀렸습니다.')
			var submitButton = Form.getSubmitButton(form);
			submitButton.enable();
		}
	});
}

function markCommentParents() {
	$$('li').filter(function (comment) {
		return comment.identify().startsWith('comment_')
	}).each(function (comment) {
		var i = 0;
		var p = comment.classNames().filter(function (cl) {
			if (!cl.startsWith('parent-'))
				return true;
			else {
				i++;
				return i == 1;
			}
		})
		comment.className = p.join(' ')
	}).each(function (comment) {
		var classes = comment.classNames().filter(function (cl) {
			return cl.startsWith('parent-');
		})

		$$('.parent-' + comment.id.split('_')[1]).each(function (el) {
			classes.each(function (cl) { el.addClassName(cl) })
		})
	})
}

function findReplyEntryFor(id) {
	markCommentParents();

	var entry = $$('.parent-' + id).last()
	if (!entry)
		entry = $('comment_' + id)
	return entry
}

function recalcDepth(comment) {
	markCommentParents();
	var depth = comment.classNames().filter(function (cl) {
		return cl.startsWith('parent-')
	}).size() - 1
	comment.style.marginLeft = (depth * 2) + 'em'
}

function replyComment(form, id) {
	var data = Form.serialize(form);
	if (!checkForm(form)) return false;
	var entry = findReplyEntryFor(id);
	new Ajax.Updater({success: entry}, form.action, {
		parameters: data,
		insertion: Insertion.After,
		onFailure: function (transport) {
			alert(transport.responseText);
		},
		onComplete: function (transport) {
			var comment = entry.next('li');
			recalcDepth(comment);

			triggerDialogLinks();
			closeDialog();
			comment.scrollTo();
		}
	});
	return false;
}

function editComment(form, id) {
	var submitButton = Form.getSubmitButton(form);
	submitButton.disable();
	var origMargin = $('comment_' + id).style.marginLeft
	new Ajax.Request(form.action, {
		parameters: Form.serialize(form),
		onSuccess: function (transport) {
			$('comment_' + id).replace(transport.responseText)
			$('comment_' + id).style.marginLeft = origMargin

			triggerDialogLinks()
			closeDialog()
		},
		onFailure: function () {
			alert('암호가 틀렸습니다.')
			var submitButton = Form.getSubmitButton(form);
			submitButton.enable();
		}
	});
}

function addFileEntry() {
	new Insertion.Bottom('uploads', '<li><input type="file" name="upload[]" size="50" class="ignore" /></li>');
}

function setPostAttribute(form, key, value) {
	var attr = $(form).getInputs(null, 'meta['+key+']');
	if (attr[0]) {
		attr[0].value = value;
	} else {
		new Insertion.Bottom(form, '<input type="hidden" name="meta['+key+']" value="'+value+'" />');
	}
}

function checkUserID(field) {
	var id = $F(field);
	if (!id) return;
	new Ajax.Updater('notification', location.href, {
		method: 'get',
		parameters: { user: id }
	});
}

function highlightText(text, element) {
	var descendats = element.descendants();
	if (descendats != null) {
		descendats.each(function (el) {
			$A(el.childNodes).each(function (node) {
				if (node.nodeType == 3) {
					var value = node.nodeValue;
					var re = new RegExp("("+text+")", "ig");
					var offset = 0;
					var lastNode = node;
					while (true) {
						var match = re.exec(value);
						if (!match) break;
						var a = lastNode.splitText(match.index - offset);
						var b = a.splitText(match[0].length);
						var span = document.createElement('span');
						span.appendChild(document.createTextNode(match[0]));
						span.style.backgroundColor = '#ff0';
						a.parentNode.replaceChild(span, b.previousSibling);
						lastNode = b;
						offset = re.lastIndex;
					}
				}
			});
		});
	}
}
function highlightSearchKeyword() {
	var kw = location.search.toQueryParams()['keyword'];
	if (!kw) return;
	if ($('body')) highlightText(kw, $('body'));
}

// dialog

function addDialogOverlay() {
	var overlay = document.createElement('div');
	var content = document.createElement('div');
	overlay.id = 'dialog-overlay';
	overlay.style.display = 'none';
	content.id = 'dialog';
	overlay.appendChild(content);
	document.body.appendChild(overlay);
}

function addCloseButton() {
	new Insertion.Top('dialog', '<a href="#" class="dialog-close" id="dialog-close">X</a>');
	$('dialog-close').focus();
}

function fixIEDocumentHeight(mode) {
	if (!Prototype.Browser.IE) return;
	if (mode == true) {
		$$('html', 'body').each(function (el) {
			el.setAttribute('originalHeight', el.style.height);
			el.style.height = {html: '93%', body: '100%'}[el.tagName.toLowerCase()];
		});
	} else {
		$$('html', 'body').each(function (el) {
			el.style.height = el.getAttribute('originalHeight');
		});
	}
}

function getScrollTop() {
	if (document.documentElement && document.documentElement.scrollTop)
		return document.documentElement.scrollTop;
	else if (document.body)
		return document.body.scrollTop;
}

function openDialog(href) {
	new Ajax.Request(href, {
		method: 'get',
		onComplete: function (xhr) {
			fixIEDocumentHeight(true);
			var overlay = $('dialog-overlay');
			var dialog = $('dialog');
			overlay.style.top = getScrollTop() + 'px';
			dialog.update(xhr.responseText);
			overlay.show();

			addCloseButton();
			triggerCloseLinks();
			fixFormActions(href);
			dialog.style.top = '50%';
			dialog.style.marginTop = '-' + parseInt(dialog.getHeight()/2) + 'px';
		}
	});
}

function triggerDialogLinks() {
	$$('a.dialog').each(function (link) {
		Event.observe(link, 'click', function (ev) {
			openDialog(this.href);
			Event.stop(ev);
		}.bindAsEventListener(link));
	});
}

function closeDialog() {
	$('dialog-overlay').hide();
	fixIEDocumentHeight(false);
}

function triggerCloseLinks() {
	$$('#dialog a.dialog-close').each(function (link) {
		Event.observe(link, 'click', function (ev) {
			closeDialog();
			Event.stop(ev);
		});
	});
}

function fixFormActions(href) {
	$$('#dialog form').each(function (form) {
		if (!form.getAttribute('action')) form.action = href;
	});
}

Event.observe(window, 'load', function () {
	highlightSearchKeyword();
	addDialogOverlay();
	triggerDialogLinks();
	$$('form').each(function (form) {
		$(form).observe('submit', validateForm)
	});
	try { 
		document.execCommand('BackgroundImageCache', false, true); 
	} catch (e) {}
});
