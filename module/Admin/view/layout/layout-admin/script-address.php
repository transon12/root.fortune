
<script type="text/javascript">
$(function() {
//console.log("da zo scrip");
	$(document).on('change','#country_id',function(e){
		//var id = $(this).attr("value");
		var id = $(this).val();
		//console.log("Id: " + id);
	    if(id != "" && id != "0"){
    		$.ajax({
    			method: "POST",
    			url: '/settings/cities/get-cities-as-countries',
    			data: '&modal=1&id=' + id
    		}).done(function( data ) {
    			if(data == 'error'){
    				alert("Loading error! Contact admin");
    			}else{
        			//console.log(data);
    				$("#city_id").html(data);
    			}
    		});
	    }
		return false;
	});

	$(document).on('change','#city_id',function(e){
		//var id = $(this).attr("value");
		var id = $(this).val();
	    if(id != "" && id != "0"){
    		$.ajax({
    			method: "POST",
    			url: '/settings/districts/get-districts-as-cities',
    			data: '&modal=1&id=' + id
    		}).done(function( data ) {
        		//alert(data);
    			if(data == 'error'){
    				alert("Loading error! Contact admin");
    			}else{
    				$("#district_id").html(data);
    			}
    		});
	    }
		return false;
	});

	$(document).on('change','#district_id',function(e){
		//var id = $(this).attr("value");
		var id = $(this).val();
	    if(id != "" && id != "0"){
    		$.ajax({
    			method: "POST",
    			url: '/settings/wards/get-wards-as-districts',
    			data: '&modal=1&id=' + id
    		}).done(function( data ) {
        		//alert(data);
    			if(data == 'error'){
    				alert("Loading error! Contact admin");
    			}else{
    				$("#ward_id").html(data);
    			}
    		});
	    }
		return false;
	});

});
</script>
