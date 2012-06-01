jQuery(document).ready(function() {
	
	var uuid = jQuery('a.mynaSuggest').attr('rel').split(' ')[0];
	var agent = new Myna(uuid);
	
	function errorCallback(code, message) {
		// Do something here, if you'd like.
	}
	
	function rewardCallback() {
		// Do something here, if you'd like. 
	}
	
	function suggestCallback(choice) {
		jQuery('a.mynaSuggest').html(choice);
		function onClick() {
			agent.reward(1.0, rewardCallback, errorCallback);
			jQuery('a.mynaSuggest').unbind(onClick);
		}
		jQuery('a.mynaSuggest').click(onClick);
	}

	agent.suggest(suggestCallback, errorCallback);

});