var win = window.dialogArguments || opener || parent || top;

function symlink_send_to_editor(h) {
	
	var win = window.dialogArguments || opener || parent || top;

	if ( typeof win.tinyMCE != 'undefined' && ( ed = win.tinyMCE.activeEditor ) && !ed.isHidden() ) {
		ed.focus();
		if (win.tinymce.isIE)
			ed.selection.moveToBookmark(tinymce.EditorManager.activeEditor.windowManager.bookmark);

		if ( h.indexOf('[caption') != -1 )
			h = ed.plugins.wpeditimage._do_shcode(h);
		
		ed.execCommand('mceInsertContent', false, h);
	} else {
		edInsertContent(edCanvas, h);
	}
	
	// tell the user we've inserted
	jQuery('#notice p').fadeIn(0).html('Image inserted...').fadeOut(2000);

	//tb_remove();

}