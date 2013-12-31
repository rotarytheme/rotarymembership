jQuery(document).ready(function($) {
	var rotaryDataTables = {
	   init: function() {
		    $( "#rotarymemberdialog" ).dialog({
				autoOpen: false,
				dialogClass: 'rotarydialog',
				width:600
			 });
			rotaryTable = $('#rotarymembers').dataTable( {
				'bProcessing': true,
				'bServerSide': false,
				'sAjaxSource': rotarydatatables.ajaxURL+'?action=rotarymembers',
				'aLengthMenu': [[20, 50, 100, -1], [20, 50, 100, 'All']],
				'iDisplayLength': -1,
				'aoColumnDefs': [
      				{ 'sClass': 'hide userid', 'aTargets': [6]},
					{ 'sClass': 'username',  'aTargets': [0]},
					{ 'sClass': 'email',  'aTargets': [5]}
   				 ],
				'fnServerData': function ( sSource, aoData, fnCallback ) {
			/* Add some extra data to the sender */
			//alert($('#commitees option:selected').val());
					aoData.push( { 'name': 'nameorder', 'value': $("input[name=nameorder]:checked").val()} );
					aoData.push( { "name": "commitees", "value": $("#commitees option:selected").val()} );
					aoData.push( { "name": "rotarynonce", "value": rotarydatatables.tableNonce} );
					$.getJSON( sSource, aoData, function (json) { 
			  			
					/* Do whatever additional processing you want on the callback, then tell DataTables */
					fnCallback(json)
			} );
		}
	});
		$( document ).on('click', '#rotarymembers td.username', this.showDetails);
		$('.rotaryselections input[name=nameorder], .rotaryselections #commitees').on('click', this.reloadMembers);
		$('.rotaryselections #commitees').on('change', this.reloadMembers);
		},
		reloadMembers : function(e) {
			rotaryTable.fnReloadAjax();
		},
		displayMember : function(ajaxResponse) {
			var memberDetailData = jQuery.parseJSON(ajaxResponse);
			$('#rotarymemberdialog .membername').html(memberDetailData.memberName);
			$('#rotarymemberdialog .addressdetails').html(memberDetailData.memberAddress);
			$('#rotarymemberdialog .classification').html(memberDetailData.classification);
			$('#rotarymemberdialog .busname').html(memberDetailData.company);
			$('#rotarymemberdialog .jobtitle').html(memberDetailData.jobTitle);
			$('#rotarymemberdialog .cellphone').html(memberDetailData.cellphone);
			$('#rotarymemberdialog .homephone').html(memberDetailData.homephone);
			$('#rotarymemberdialog .officephone').html((memberDetailData.businessphone));
			$('#rotarymemberdialog .email').html(memberDetailData.email);
			$('#rotarymemberdialog .partnername').html(memberDetailData.partnername);
			$('#rotarymemberdialog .anniversarydate').html(memberDetailData.anniversarydate);
			$('#rotarymemberdialog .birthday').html(memberDetailData.birthday);
			$('#rotarymemberdialog .busweb').html(memberDetailData.busweb);
			$('#rotarymemberdialog .membersince').html(memberDetailData.membersince);
			$('#rotarymemberdialog .dialogbottom').html('<p class="clubname">'+memberDetailData.clubname+'</p><p class="rotaryclub">Rotary Club</p><p class="rotaractclub">Rotaract Club</p>');
			$('#rotarymemberdialog .profilepicture img').remove();
			if ($.trim(memberDetailData.profilepicture)) {
				$('#rotarymemberdialog .profilepicture').append('<img src="'+ memberDetailData.profilepicture + '" alt="'+memberDetailData.memberName + '" title="' + memberDetailData.memberName + '"/>');
			}
			var $rotarymemberdialog = $('#rotarymemberdialog');
			if ($rotarymemberdialog.dialog('isOpen')) {
				$rotarymemberdialog.dialog('close');
			}
			$rotarymemberdialog.dialog( 'open' );
		},
		showDetails : function() {
			memberID = ($(this).siblings('.userid').text());
			$.get(
  					rotarydatatables.ajaxURL,
    				{
        	// here we declare the parameters to send along with the request
        	// this means the following action hooks will be fired:
        	// wp_ajax_nopriv_myajax-submit and wp_ajax_myajax-submit
        				action : 'rotarymemberdetails',
						memberID : memberID,
    				}, rotaryDataTables.displayMember);
			
		}
		
	};
	
	rotaryDataTables.init();
	
});