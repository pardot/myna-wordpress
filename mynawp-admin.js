jQuery(document).ready(function() {

	var curexpurl = jQuery('#newexpurl').attr('href');
	var curexpuuids = jQuery('#updateuuid').attr('href');
	
	
	jQuery('#optionspost').children('div').each(function(i) {
		var thisuuid = jQuery(this).attr('class');
		var curvarurl = jQuery(this).children('.newvarurl').attr('href');
		jQuery(this).children('.mynawp_new_variant').bind('keyup', function(){
			var newvar = jQuery(this).val();
			jQuery(this).parent().children('.newvarurl').attr('href', curvarurl + newvar);
		});
	});	
	
	jQuery('#mynawp_new_experiment').keyup(function(){
		var newexp = jQuery('#mynawp_new_experiment').val();
		jQuery('#newexpurl').attr('href', curexpurl + newexp);
	});
	
	jQuery('#updateuuid').click(function(){
		var curexpuuids = jQuery('#updateuuid').attr('href');
		var updexp = jQuery('#mynawp_uuid_string').val();
		jQuery('#updateuuid').attr('href', curexpuuids + updexp);
	});
	
	jQuery('.deleteexp').click(function(){
		var answer = confirm('Are you sure you want to delete this experiment? This cannot be undone.');
		if ( answer ) {
			return true;
		} else {
			return false;
		}
	});
	
	jQuery('.delete_var').click(function(){
		var answer = confirm('Are you sure you want to delete this variable? This cannot be undone.');
		if ( answer ) {
			return true;
		} else {
			return false;
		}
	});

});