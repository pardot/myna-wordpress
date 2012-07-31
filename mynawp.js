jQuery(document).ready(function() {
	
	var uuid = jQuery('a.mynaSuggest').attr('rel').split(' ')[0];
	var expt = new Myna.Experiment(uuid);
		
	function suggestCallback(suggestion) {
		val links = jQuery('a.mynaSuggest');
    val targets = links.attr('href').get();
    
		links.html(suggestion.choice);
		links.map(function(idx, elt) {
		  suggestion.rewardOnClick(elt, targets[idx]);
		});
	}

	expt.suggest(suggestCallback);

});