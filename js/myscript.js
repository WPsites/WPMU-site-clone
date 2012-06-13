jQuery(document).ready(function(){
	jQuery("#tabs").tabs({fx: { opacity: 'toggle' }, selected: 1 });
	jQuery('input[type=checkbox]').tzCheckbox({labels:['Yes','No']});
	jQuery("#singlebox").hide();
	jQuery(".tzCheckBox").click(function() {
		if (jQuery('input[name="acswpmu_multiple"]:checked').val() == "on") {
		   jQuery("#multiplebox").show("slow");
		   jQuery("#singlebox").hide();
		} else {
			jQuery("#singlebox").show("slow");
			jQuery("#multiplebox").hide();
		}
	});
});