<?php use function GuzzleHttp\json_encode;
/* ?><button type="button" class="btn btn-outline-success block btn-lg pxt-modal" route="/admin/positions/add/" data-toggle="modal" data-target="#xSmall">
	Launch Modal
</button>
<button type="button" class="btn btn-outline-success block btn-lg pxt-modal" route="/admin/positions/add/" data-toggle="modal" data-target="#small">
	Launch Modal
</button>
<button type="button" class="btn btn-outline-success block btn-lg pxt-modal" route="/admin/positions/add/" data-toggle="modal" data-target="#defaultSize">
	Launch Modal
</button>
<button type="button" class="btn btn-outline-success block btn-lg pxt-modal" route="/admin/positions/add/" data-toggle="modal" data-target="#large">
	Launch Modal
</button>
<button type="button" class="btn btn-outline-success block btn-lg pxt-modal" route="/admin/positions/add/" data-toggle="modal" data-target="#xlarge">
	Launch Modal
</button>
<?php */ ?>
<div class="modal fade text-left" id="xSmall" tabindex="-1" role="dialog" aria-labelledby="myModalLabel20" aria-hidden="true">
	<div class="modal-dialog modal-xs" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel20">Basic Modal</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				&nbsp;
			</div>
		</div>
	</div>
</div>
<div class="modal fade text-left" id="small" tabindex="-1" role="dialog" aria-labelledby="myModalLabel19" aria-hidden="true">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel19">Basic Modal</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				&nbsp;
			</div>
		</div>
	</div>
</div>
<div class="modal fade text-left" id="defaultSize" tabindex="-1" role="dialog" aria-labelledby="myModalLabel18" aria-hidden="true" style="z-index: 10099">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel18"><i class="la la-tree"></i> Basic Modal</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				&nbsp;
    		</div>
		</div>
	</div>
</div>
<div class="modal fade text-left" id="large" tabindex="-1" role="dialog" aria-labelledby="myModalLabel17" aria-hidden="true" style="z-index: 10099">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel17">Basic Modal</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				&nbsp;
			</div>
		</div>
	</div>
</div>
<div class="modal fade text-left" id="xlarge" tabindex="-1" role="dialog" aria-labelledby="myModalLabel16" aria-hidden="true" style="z-index: 10099">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel16">Basic Modal</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				&nbsp;
			</div>
		</div>
	</div>
</div>


<script type="text/javascript">
function getDataFromIframe(dataTarget, idElement, valueId, idHidden, valueHidden, value, route){
	// console.log(dataTarget);
	// console.log(idElement);
	// console.log(valueId);
	// console.log(idHidden);
	// console.log(valueHidden);
	// console.log(value);
	// console.log("route getDataFromIframe");
	// console.log($(idElement).attr("pxt-ckeditor"));
	$(dataTarget).modal('hide');
	// $(dataTarget+'.modal-body').html('');
	if($(idElement).attr("pxt-ckeditor") === "true"){
		// console.log("vao if");
		// console.log(value);
		var elementInput = $(idHidden).select();
		document.execCommand('paste');
		elementInput.val(value);
		// $( "#" + cur.attr("id") ).trigger( "paste" );
	}else{
		// console.log("vao else");
		// console.log(value);
		$(idElement).attr(valueId, value);
		$(idHidden).attr(valueHidden, value);
	}
	$(idElement).attr("route", route);
	//$(key).attr("src", value);
	// $(key + "_hidden").val(value);
}
$(function() {
	 $( ".pxt-uploads-default" ).each(function( index ) {
		var cur = $(this);
		cur.attr("data-toggle", "modal");
		cur.attr("title", "Quản lý hình ảnh");
		var dataTarget = "xlarge";
		cur.attr("data-target", "#" + dataTarget);
		cur.addClass("pxt-modal");
		var strRoute = "<?= $this->url('settings/file-uploads', ['action' => 'iframe']) ?>";
		strRoute += "?id_element=" + cur.attr("id") + " " + cur.attr("value_id") + "&id_hidden=" + cur.attr("id_hidden") + " " + cur.attr("value_hidden");
		strRoute += "&data_target=" + dataTarget;
		cur.attr("route", "" + strRoute);
	});
    // $(document).on('paste','input.cke_dialog_ui_input_text',function(e){
    //     console.log('dang pate');
    // });
    // var iCheckDialog = 0;
    $(document).on('focusin','input.cke_dialog_ui_input_text',function(e){
    // $( "input.cke_dialog_ui_input_text" ).each(function( index ) {
        var cur = $(this);
        if(cur.attr("aria-required") == "true"){
                // sandbox = $('#' + cur.attr("id")).select();
                // console.log("sanboc val");
                // console.log(sandbox.val());
                // document.execCommand('paste');
                // sandbox.val("https://vinachg.vn/vinachg/uploads/2019/10/tem-vinacheck.jpg");
                // $( "#" + cur.attr("id") ).trigger( "paste" );
            // if(iCheckDialog == 0){
            if(cur.attr("pxt-ckeditor") !== "true"){
                var dataTarget = "xlarge";
                var strRoute = "<?= $this->url('settings/file-uploads', ['action' => 'iframe']) ?>";
                strRoute += "?id_element=" + cur.attr("id") + " value&id_hidden=" + cur.attr("id") + " value";
                strRoute += "&data_target=" + dataTarget;
				$elementUpload = '<img style="margin-left: 10px; margin-top: 1px;" src="/img/upload-24.png"';
                $elementUpload += ' id="pxt-upload" value_id="src" id_hidden="' + cur.attr("id") + '" value_hidden="value" class="pxt-modal"';
				$elementUpload += ' data-toggle="modal" title="Quản lý hình ảnh" data-target="#' + dataTarget + '"';
				$elementUpload += ' route="' + strRoute + '"/>';
				$( cur ).after( $elementUpload );
                // cur.attr("data-toggle", "modal");
                // cur.attr("title", "Quản lý hình ảnh");
                cur.attr("pxt-ckeditor", "true");
                cur.attr("style", "float: left;");
                // cur.attr("data-target", "#" + dataTarget);
                // cur.addClass("pxt-modal");
                // cur.attr("route", "" + strRoute);

                // cur.attr("value_id", "value");
                // cur.attr("id_hidden", cur.attr("id"));
                // cur.attr("value_hidden", "value");
                // iCheckDialog++;
            }
        }
    });
    // $( ".pxt-modal" ).click(function(e) {
	$(document).on('click','.pxt-modal',function(e){
		// alert("ABC");
    	$(".modal-title").text( $(this).attr("title") );
    	var dataTarget = $(this).attr('data-target');
		// alert(dataTarget);
    	// delete old html 
		// alert(route);
    	$(dataTarget + " .modal-body").html("");
		var route = $(this).attr("route");
		console.log("route modal");
		console.log(route);
		// console.log(dataTarget);
		// console.log($(this).attr("title"));
    	$.ajax({
        	method: "GET",
         	url: route,
          	data: {'modal': '1'}
    	}).done(function( data ) {
         	if(data == 'success'){
            	location.reload();
         	}else{
            	$(dataTarget + " .modal-body").html(data);
       		}
    	});
    });
	/*$(document).on('submit','#event-form-modal',function(e){
		var route = $(this).attr("route");
	    var dataTarget = $(this).attr('data-target');
	    //alert('vao scrip: ' + route);
		$.ajax({
			method: "POST",
			url: route,
			data: $( this ).serialize() + '&modal=1'
		}).done(function( data ) {
 			console.log(data);
			$(dataTarget + " .modal-body").html("");
			if(data == 'success'){
				$(dataTarget).modal('hide')
				location.reload();
			}else{
				$(dataTarget + " .modal-body").html(data);
			}
		});
		return false;
	});*/
	$(document).on('submit','#event-form-modal',function(e){
		e.preventDefault();
		var route = $(this).attr("route");
	    var dataTarget = $(this).attr('data-target');
	    // alert('vao scrip: ' + route);
		var formData = new FormData(this);

        $.ajax({
            type: 'POST',
            url: route,
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(data) {
    			if(data == 'success'){
    				$(dataTarget).modal('hide')
    				location.reload();
    			}else{
    				$(dataTarget + " .modal-body").html(data);
					//console.log(dataTarget);
					//console.log(data);
        			//console.log('toi day');
    			}
                //console.log("Thanh cong");
                //console.log(data);
            },
            error: function(data) {
                //console.log("Loi");
                //console.log(data);
    			$(dataTarget + " .modal-body").html(data);
            }
        });
		return false;
	});
	// $(document).on('hidden.bs.modal','#xlarge', function () {
	// 		alert("abc")
    // 		$("#xlarge .modal-body").html("");
    // });
	var arrDeny = <?= \Zend\Json\Json::encode($this->sessionContainer->not_permissions) ?>;
	/* $.each(arrDeny,function(index, value){
	    console.log('Index: ' + index + ', Value: ' + value);
	}); */
	$( "a" ).each(function( index ) {
		var cur = $(this);
		var href = String($(this).attr("href"));
		var route = String($(this).attr("route"));
		//console.log("--- Href ---");
		if(href != '' && href != "undefined" && href != "javascript:void(0)" && href.indexOf("#") < 0){
			//console.log("Href: " + href + " - ");
			$.each(arrDeny,function(key, value){
				value = String(value) + "/";
				// if(href.indexOf(value) >= 0){
				if(href.indexOf(value) >= 0){
			    	var parent = cur.parent("li");
			    	if(parent != ""){
						cur.remove();
						parent.remove();
					}else{
						cur.remove();
					}
			    	//console.log('Co: ' + value);
				}
			});
		}
		if(route != '' && route != "undefined"){
			//console.log("Route: " + route + " - ");
			$.each(arrDeny,function(key, value){
				value = String(value) + "/";
				if(route.indexOf(value) >= 0){
			    	var parent = cur.parent("li");
			    	if(parent != ""){
						cur.remove();
						parent.remove();
					}else{
						cur.remove();
					}
			    	//console.log('Co: ' + value);
				}
			});
		}
		//console.log( href + ' - ' + route );
	});
	 $( "ul" ).each(function( index ) {
		var cur = $(this);
		var text = String($(this).text());
		text = text.replace(/\s/g, '');
		if(text == ''){
    		var parent = cur.closest( "li" );
    		//var parent = cur.closest(".nav-item");
    		//console.log(parent.text());
    		parent.remove();
		}
	});
	/* format currency*/
		$('.format-currency').on('input', function(e){
		    $(this).val(formatCurrency(this.value.replace(/[, vnđ]/g,'')));
		}).on('keypress',function(e){
		    if(!$.isNumeric(String.fromCharCode(e.which))) e.preventDefault();
		}).on('paste', function(e){
		    var cb = e.originalEvent.clipboardData || window.clipboardData;      
		    if(!$.isNumeric(cb.getData('text'))) e.preventDefault();
		});
		function formatCurrency(number){
		    var n = number.split('').reverse().join("");
		    var n2 = n.replace(/\d\d\d(?!$)/g, "$&,");
		    return  n2.split('').reverse().join('');
		    //return  n2.split('').reverse().join('') + ' vnđ';
		}
	/* format currency*/
	/* format number*/
		$('.format-number').on('input', function(e){
		    $(this).val(formatNumber(this.value.replace(/[, ]/g,'')));
		}).on('keypress',function(e){
		    if(!$.isNumeric(String.fromCharCode(e.which))) e.preventDefault();
		}).on('paste', function(e){
		    var cb = e.originalEvent.clipboardData || window.clipboardData;      
		    if(!$.isNumeric(cb.getData('text'))) e.preventDefault();
		});
		function formatNumber(number){
		    var n = number.split('').reverse().join("");
		    var n2 = n.replace(/\d\d\d(?!$)/g, "$&,");    
		    return  n2.split('').reverse().join('') + '';
		}
	/* format number*/
	/* copy content in class */
	$(".pxt-copy").click(function(){
		var datacopy = $(this).attr("datacopy");
		//console.log(datacopy);
		var $temp = $("<input>");
		$("body").append($temp);
		$temp.val(datacopy).select();
		document.execCommand("copy");
		$temp.remove();
	});
	/* copy content in class */
	$(".table-responsive").css("overflow", "inherit");
});
</script>


