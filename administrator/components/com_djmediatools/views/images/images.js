
function purgeThumbnails(button) {
	var recAjax = new Request({
	    url: 'index.php?option=com_djmediatools&task=images.purge&tmpl=component&format=raw',
	    method: 'post',
	    encoding: 'utf-8',
	    onSuccess: function(response) {
	    	button.set('text',response);
		}
	});
	recAjax.send();
}

function purgeStylesheets(button) {
	var recAjax = new Request({
	    url: 'index.php?option=com_djmediatools&task=images.purgeCSS&tmpl=component&format=raw',
	    method: 'post',
	    encoding: 'utf-8',
	    onSuccess: function(response) {
	    	button.set('text',response);
		}
	});
	recAjax.send();
}

function resmushitImages() {
	
	var logArea = document.id('djmt_resmushit_log');
		
	var recAjax = new Request({
	    url: 'index.php?option=com_djmediatools&task=images.resmushit&tmpl=component&format=raw',
	    method: 'post',
	    encoding: 'utf-8',
	    onSuccess: function(response) {
	    	var recProgressBar = document.id('djmt_progress_bar');
			var recProgressPercent = document.id('djmt_progress_percent');
			
			if (response == 'end') {
				recProgressBar.setStyle('width','100%');
				recProgressPercent.innerHTML = '100%';
				logArea.innerHTML = 'DONE!\n'+logArea.innerHTML;
				return true;
			} else if (response == 'error') {
				logArea.innerHTML = 'Unexpected error\n' + logArea.innerHTML;
				recProgressBar.setStyle('width','0');
				recProgressPercent.innerHTML = '0%';
			}
			else {
				var jsonObj = null;
				try {
					jsonObj = JSON.decode(response);
				} catch(err) {
					logArea.innerHTML = 'ERROR: '+ response + '\n' + logArea.innerHTML;
					return resmushitImages();
				}

				var percentage = ((jsonObj.optimized / jsonObj.total) * 100);

				recProgressBar.setStyle('width',percentage + '%');
				recProgressPercent.innerHTML = percentage.toFixed(2) + '%';
				logArea.innerHTML = ('['+jsonObj.percent.toFixed(2)+'%] ' + jsonObj.path + '\n') + logArea.innerHTML;
				
				return resmushitImages();
			}
		}
	});
	recAjax.send();
}

window.addEvent('domready', function(){
	
	var clearButton = document.id('djmt_delete_images');
	if (clearButton) {
		clearButton.removeAttribute('disabled');
		clearButton.addEvent('click',function(){
			clearButton.setAttribute('disabled', 'disabled');
			purgeThumbnails(clearButton);
		});
	}
	
	var clearButton2 = document.id('djmt_delete_stylesheets');
	if (clearButton2) {
		clearButton2.removeAttribute('disabled');
		clearButton2.addEvent('click',function(){
			clearButton2.setAttribute('disabled', 'disabled');
			purgeStylesheets(clearButton2);
		});
	}
	
	var resmushit = document.id('djmt_resmushit_images');
	if(resmushit) {
		resmushit.removeAttribute('disabled');
		resmushit.addEvent('click',function(){
			resmushit.setAttribute('disabled', 'disabled');
			resmushitImages(resmushit);
		});
	}
	
});