jQuery(document).ready(function() {
	
	var uuid = jQuery('a.mynaSuggest').attr('rel').split(' ')[0];
	var expt = new Myna.Experiment(uuid);
		
	function suggestCallback(suggestion) {
		var links = jQuery('a.mynaSuggest');    
		links.html(suggestion.choice);
		var target = jQuery();
		jQuery.each(links,function(key,value) {
			target = jQuery(value).attr('href');
			suggestion.rewardOnClick(value, target);
		});
	}

	expt.suggest(suggestCallback);

});