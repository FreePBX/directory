$(document).ready(function() {
 $('#new_dir').click(function(){
	window.location.href = 'config.php?type=<?php echo $type ?>&display=directory&action=add';
	})
});
$(document).ready(function(){
	//show/hide add button/dropdown
	$('#addbut').click(function(){
		$('#addusersel').val('none');//reset select box
		$(this).fadeOut(250,
		function(){
			$('#addrow').fadeIn(250, function() {
        $(this).prop("colspan",5);
      });
		});
		return false;
	});
	$('form[name=frm_dirform]')[0].onsubmit = function() {
		if($("#dirname").val() ==""){
			return warnInvalid($("#dirname"),_("Directory Name Can not be Blank"));
		}
		if (dirnames.indexOf($("#dirname").val()) >= 0) {
			return warnInvalid($("#dirname"),_("Directory Name Already Exist"));
		}
	}
	//add row button
	$('#addrow').change(function(){
    var val = $('#addusersel').val().trim();
    if(val != "|") {
      if($.inArray(val, inuse) > -1) {
        alert(_("This entry has already been added"));
        $('#addusersel').val('none');
        return false;
      }
      inuse.push(val);
    }
		$(this).fadeOut(250,
		function(){
			$('#addbut').not("span").fadeIn(250, function() {
        $(this).find("span").hide();
        $(this).prop("colspan", 5);
      });
		});
		if($('#addusersel').val() != 'none'){
			var rownum=$('[class^=entrie]').length+1;
			//increment id untill we find one that isnt being used
			while($('.entrie' + rownum).length == 1){
				rownum++;
			}
			addrow($('#addusersel').val() + '|' + rownum);
		}
		return false;
	});

	//set toggle value for text-box hint text
	$("form").submit(function() {
    $(".dpt-title").each(function() {
      if($(this).val() == $(this).data("defText")) {
        $(this).val("");
      }
    });
	});

	//delete row when trash can is clicked
	$(document).on('click', '.trash-tr', function() {
    var $this = this;
	$(this).parents('tr').fadeOut(500,
		function(){
      if($.inArray($($this).data("name"), inuse) > -1) {
        delete(inuse[$.inArray($($this).data("name"), inuse)]);
      }
      $(this).remove();
		});
	});


});


//add a new entry to the table
function addrow(user){
	$.ajax({
		type: 'POST',
	  url: location.href,
	  data: 'ajaxgettr='+encodeURIComponent(user)+'&quietmode=1',
	  success: function(data) {
	    $('#dir_entries_tbl > tbody:last').append(data);
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown) {
      var msg = _("An Error occurred trying to contact the server adding a row, no reply.");
      alert(msg);
    }
  });
}

function linkFormatter(value){
	var html = '<a href="?display=directory&view=form&id='+value['id']+'"><i class="fa fa-pencil"></i></a>';
	html += '&nbsp;<a href="?display=directory&action=delete&id='+value['id']+'" class="delAction"><i class="fa fa-trash"></i></a>';
	return html;
}
function defaultFormatter(value){
	var sy = '';
	var sn = '';
	if(value['default']){
		sy = ' CHECKED';
	}
	var html;
		html = '<span class="radioset">';
		html += '<input type="radio" name="default" id="default'+value['id']+'" value="V'+value['id']+'" '+sy+' onChange="dirUpdateDefault(this)">';
		html += '<label for="default'+value['id']+'">'+_("Select")+'</label>';
		html += '</span>';
	return html;
}
function dirUpdateDefault(item){
	var id = $(item).val().replace('V','');
	$.get("?display=directory&Submit=Submit&def_dir="+id);
}
