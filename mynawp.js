jQuery('a[rel*="myna-"]').click(function(){
	var mynadata = jQuery(this).attr('rel').split(' ')[0].substr(5);
	var token = mynadata.split('|')[0].substr(4);
	var uuid = mynadata.split('|')[1];
	jQuery.getJSON('http://api.mynaweb.com/v1/experiment/' + uuid + '/reward?callback=?', { token: token, amount: '1.0' }, function(json){alert(json);});
});