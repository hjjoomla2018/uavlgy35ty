(function($){
	function recreateThumbnails(id,type) {
		if (type != '' && type != 'item' && type != 'producer' && type != 'category'){
			return false;
		}
		
		var allButtons = $('button.recreator_button');
		var logArea = $('#djc_thumbrecreator_log');
		var startFrom = $('#djc_thumbrecreator_start');
		
		if (window.DJCatalog2AllowRecreation == false) {
			allButtons.each(function(){$(this).removeAttr('disabled');});
			window.DJCatalog2AllowRecreation = true;
			logArea.html('STOPPED BY USER!\n'+logArea.html());
			return;
		}
		
		if (!id && startFrom) {
			if (parseInt(startFrom.val()) > 0) {
				id = parseInt(startFrom.val());
			}
		}
		
		$.ajax({
			url: 'index.php?option=com_djcatalog2&task=thumbs.go&tmpl=component&format=raw&image_id=' + id + '&type=' + type,
		    method: 'post',
		    encoding: 'utf-8',
		}).done(function(response){
			var recProgressBar = $('#djc_progress_bar');
			var recProgressPercent = $('#djc_progress_percent');
			
			if (response == 'end') {
				allButtons.each(function(){$(this).removeAttr('disabled');});
				recProgressBar.css('width','100%');
				recProgressPercent.html('100%');
				logArea.html('DONE!\n'+logArea.html());
				return true;
			} else if (response == 'error') {
				logArea.html('Unexpected error\n' + logArea.html());
				allButtons.each(function(){$(this).removeAttr('disabled');});
				recProgressBar.css('width','0');
				recProgressPercent.html('0%');
			}
			else {
				var jsonObj = null;
				try {
					jsonObj = JSON.decode(response);
				} catch(err) {
					logArea.html('ERROR!'+ response + '\n' + logArea.html());
					if (startFrom) {
						startFrom.val(parseInt(id)+1);
					}
					return recreateThumbnails(parseInt(id)+1, type);
				}

				var percentage = (((jsonObj.total - jsonObj.left) / jsonObj.total) * 100);

				recProgressBar.css('width',percentage + '%');
				recProgressPercent.html(percentage.toFixed(2) + '%');
				logArea.html(('OK! ID:TYPE:NAME='+ jsonObj.id +':' + jsonObj.type + ':' + jsonObj.name + '\n') + logArea.html());
				if (startFrom) {
					startFrom.val(jsonObj.id);
				}
				return recreateThumbnails(jsonObj.id, type);
			}
		});
	}

	function purgeThumbnails() {
		$.ajax({
		    url: 'index.php?option=com_djcatalog2&task=thumbs.purge&tmpl=component&format=raw',
		    method: 'post',
		    encoding: 'utf-8'
		}).done(function(response) {
	    	alert(response);
	    	window.location.replace(window.location.toString());
		});
	}
	
	function resmushitImages() {
		
		var logArea = $('#djc_rmit_resmushit_log');
			
		$.ajax({
		    url: 'index.php?option=com_djcatalog2&task=thumbs.resmushit&tmpl=component&format=raw',
		    method: 'post',
		    encoding: 'utf-8'
		}).done(function(response) {
	    	var recProgressBar = $('#djc_rmit_progress_bar');
			var recProgressPercent = $('#djc_rmit_progress_percent');
			
			if (response == 'end') {
				recProgressBar.css('width','100%');
				recProgressPercent.html('100%');
				logArea.html('DONE!\n'+logArea.html());
				return true;
			} else if (response == 'error') {
				logArea.html('Unexpected error\n' + logArea.html());
				recProgressBar.css('width','0');
				recProgressPercent.html('0%');
			}
			else {
				var jsonObj = null;
				try {
					jsonObj = JSON.decode(response);
				} catch(err) {
					logArea.html('ERROR: '+ response + '\n' + logArea.html());
					return resmushitImages();
				}

				var percentage = ((jsonObj.optimized / jsonObj.total) * 100);

				recProgressBar.css('width',percentage + '%');
				recProgressPercent.html(percentage.toFixed(2) + '%');
				logArea.html('['+jsonObj.percent.toFixed(2)+'%] ' + jsonObj.path + '\n' + logArea.html());
				
				return resmushitImages();
			}
		});
	}

	$(document).ready(function(){
		var recButton = $('#djc_start_recreation');
		var recItemButton = $('#djc_start_recreation_item');
		var recCatButton = $('#djc_start_recreation_category');
		var recProdButton = $('#djc_start_recreation_producer');
		
		var allButtons = $('button.recreator_button');
		
		var recProgressBar = $('#djc_progress_bar');
		var recProgressPercent = $('#djc_progress_percent');
		
		var stopButton = $('#djc_thumbrecreator_stop');
		if (stopButton) {
			stopButton.on('click',function(){
				window.DJCatalog2AllowRecreation = false;
			});
		}
		
		this.DJCatalog2AllowRecreation = true;
		
		if (recButton && recProgressBar && recProgressPercent) {
			allButtons.each(function(){$(this).removeAttr('disabled');});
			recButton.on('click',function(){
				window.DJCatalog2AllowRecreation = true;
				allButtons.each(function(){$(this).attr('disabled', 'disabled');});
				recreateThumbnails(0,'');
			});
			recItemButton.on('click',function(){
				window.DJCatalog2AllowRecreation = true;
				allButtons.each(function(){$(this).attr('disabled', 'disabled');});
				recreateThumbnails(0,'item');
			});
			recCatButton.on('click',function(){
				window.DJCatalog2AllowRecreation = true;
				allButtons.each(function(){$(this).attr('disabled', 'disabled');});
				recreateThumbnails(0,'category');
			});
			recProdButton.on('click',function(){
				window.DJCatalog2AllowRecreation = true;
				allButtons.each(function(){$(this).attr('disabled', 'disabled');});
				recreateThumbnails(0,'producer');
			});
		}
		
		var clearButton = $('#djc_start_deleting');
		if (clearButton) {
			clearButton.removeAttr('disabled');
			clearButton.on('click',function(){
				recButton.attr('disabled', 'disabled');
				purgeThumbnails();
			});
		}
		
		var resmushit = $('#djc_rmit_resmushit_images');
		if(resmushit) {
			resmushit.removeAttr('disabled');
			resmushit.on('click',function(){
				resmushit.attr('disabled', 'disabled');
				resmushitImages(resmushit);
			});
		}
	});
})(jQuery);