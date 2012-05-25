var curvarurl = jQuery('#newvarurl').attr('href');
var curexpurl = jQuery('#newexpurl').attr('href');

jQuery('#mynawp_new_variant').keyup(function(){
	var newvar = jQuery('#mynawp_new_variant').val();
	jQuery('#newvarurl').attr('href', curvarurl + newvar);
});

jQuery('#mynawp_new_experiment').keyup(function(){
	var newexp = jQuery('#mynawp_new_experiment').val();
	jQuery('#newexpurl').attr('href', curexpurl + newexp);
});

jQuery('.deleteexp').click(function(){
	var answer = confirm('Are you sure you want to delete this experiment? This cannot be undone.');
	if ( answer ) {
		var curuuid = jQuery('#mynawp_uuid_string').val();
		var delexpuuid = jQuery('.deleteexp').attr('rel');
		var newcuruuid = curuuid.replace(delexpuuid, " ");
		jQuery("#mynawp_uuid_string").val(newcuruuid);
		var sendData = jQuery("#optionspost").serialize();
		jQuery.post("options.php", sendData, function(response) {
			// Do something here, if desired
	    });
		return true;
	} else {
		return false;
	}
});

jQuery('.delete_var').click(function(){
	var answer = confirm('Are you sure you want to delete this experiment? This cannot be undone.');
	if ( answer ) {
		return true;
	} else {
		return false;
	}
});