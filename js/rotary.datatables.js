jQuery(document).ready(function($) {
	var rotaryDataTables = {
		init: function() {
			$("#rotarymemberdialog").dialog({
				autoOpen: false,
				dialogClass: 'rotarydialog',
				width: 600
			});
			rotaryTable = $('#rotarymembers').dataTable({
				'bProcessing': true,
				'bServerSide': false,
				'sAjaxSource': rotarydatatables.ajaxURL + '?action=rotarymembers',
				'aLengthMenu': [
					[20, 50, 100, -1],
					[20, 50, 100, 'All']
				],
				'iDisplayLength': -1,
				'aoColumnDefs': [{
					'sClass': 'hide userid',
					'aTargets': [6]
				}, {
					'sClass': 'username',
					'aTargets': [0]
				}, {
					'sClass': 'email',
					'aTargets': [5]
				}],
				'fnServerData': function(sSource, aoData, fnCallback) { /* Add some extra data to the sender */
					//alert($('#commitees option:selected').val());
					aoData.push({
						'name': 'nameorder',
						'value': $("input[name=nameorder]:checked").val()
					});
					aoData.push({
						"name": "commitees",
						"value": $("#commitees option:selected").val()
					});
					aoData.push({
						"name": "rotarynonce",
						"value": rotarydatatables.tableNonce
					});
					$.getJSON(sSource, aoData, function(json) { /* Do whatever additional processing you want on the callback, then tell DataTables */
						fnCallback(json);
					});
				}
			});
			projectsTable = $('#rotaryprojects').dataTable({
				'bProcessing': true,
				'bServerSide': false,
				'sAjaxSource': rotarydatatables.ajaxURL + '?action=rotarymembers',
				'aLengthMenu': [
					[20, 50, 100, -1],
					[20, 50, 100, 'All']
				],
				'iDisplayLength': -1,
				'aoColumnDefs': [{
					'sClass': 'hide userid',
					'aTargets': [6]
				}, {
					'sClass': 'username',
					'aTargets': [0]
				}, {
					'sClass': 'delete',
					'bSortable' : false, 
					'aTargets': [7]
				},{
					'sClass': 'email',
					'aTargets': [5]
				}, {
					'sClass': 'hide',
					'aTargets': [1]
				}, {
					'sClass': 'hide',
					'aTargets': [2]
				}],
				'fnServerData': function(sSource, aoData, fnCallback) { /* Add some extra data to the sender */
					//alert($('#commitees option:selected').val());
					aoData.push({
						'name': 'nameorder',
						'value': $("input[name=nameorder]:checked").val()
					});
					aoData.push({
						"name": "id",
						"value": $("#rotaryprojects").data("id")
					});
					aoData.push({
						"name": "rotarynonce",
						"value": rotarydatatables.tableNonce
					});
					$.getJSON(sSource, aoData, function(json) { /* Do whatever additional processing you want on the callback, then tell DataTables */
						fnCallback(json);
					});
				}
			});
			$(document).on('click', '#rotarymembers td.username, #rotaryprojects td.username', this.showDetails);
			$(document).on('click', '#rotaryprojects td.delete', this.deleteMember);
			$('.rotaryselections input[name=nameorder]').on('click', this.reloadMembers);
			$('.rotaryselections #commitees').on('change', this.reloadMembers);
			$('.rotaryselections #newparticipants').on('change', this.addProjectMembers);
		},
		reloadMembers: function(e) {
			rotaryTable.fnReloadAjax();
		},
		deleteMember: function(e) {
			var $rotaryProjects = $('#rotaryprojects');
			$userID  = ($(this).siblings('.userid').text());	
			var $imgoing = $('.imgoing');					
			jQuery.ajax({
				type: 'post',
				dataType: 'json',
				url: rotarydatatables.ajaxURL,
				data: {
					action: 'deleteprojectmember',
					project_id: $rotaryProjects.data('id'),
					user_id: $userID,
					nonce: rotarydatatables.tableNonce
				},
				success: function(response, textStatus, jqXHR) {
					if (200 == jqXHR.status && 'success' == textStatus) {
						if ('success' === response.status) {					
							if (parseInt($userID) === parseInt(response.message)) {
								if ($imgoing.length) {
									$imgoing.removeClass('going');
									$imgoing.prev('.imgoingtext').text("I'm not going");

								}
							}
							projectsTable.fnReloadAjax();
						}
					}
				}
				});
		},
		addProjectMembers: function(e) {
			var $rotaryProjects = $('#rotaryprojects');
			var $userID = $('#newparticipants option:selected').val();
			var $imgoing = $('.imgoing');
			jQuery.ajax({
				type: 'post',
				dataType: 'json',
				url: rotarydatatables.ajaxURL,
				data: {
					action: 'projectmembers',
					project_id: $rotaryProjects.data('id'),
					user_id: $userID,
					nonce: rotarydatatables.tableNonce
				},
				success: function(response, textStatus, jqXHR) {
					if (200 == jqXHR.status && 'success' == textStatus) {
						if ('success' === response.status) {					
							if (parseInt($userID) === parseInt(response.message)) {
								if ($imgoing.length) {
									$imgoing.addClass('going');
									$imgoing.prev('.imgoingtext').text("I'm going");

								}
							}
							projectsTable.fnReloadAjax();
						}
					}
				}
				});
			}, 
			displayMember: function(ajaxResponse) {
				var memberDetailData = jQuery.parseJSON(ajaxResponse),
					$rotarymemberdialog = $('#rotarymemberdialog');
				$rotarymemberdialog.find('.membername').html(memberDetailData.memberName);
				$rotarymemberdialog.find('.addressdetails').html(memberDetailData.memberAddress);
				$rotarymemberdialog.find('.classification').html(memberDetailData.classification);
				$rotarymemberdialog.find('.busname').html(memberDetailData.company);
				$rotarymemberdialog.find('.jobtitle').html(memberDetailData.jobTitle);
				$rotarymemberdialog.find('.cellphone').html(memberDetailData.cellphone);
				$rotarymemberdialog.find('.homephone').html(memberDetailData.homephone);
				$rotarymemberdialog.find('.officephone').html((memberDetailData.businessphone));
				$rotarymemberdialog.find('.email').html(memberDetailData.email);
				$rotarymemberdialog.find('.partnername').html(memberDetailData.partnername);
				$rotarymemberdialog.find('.anniversarydate').html(memberDetailData.anniversarydate);
				$rotarymemberdialog.find('.birthday').html(memberDetailData.birthday);
				$rotarymemberdialog.find('.busweb').html(memberDetailData.busweb);
				$rotarymemberdialog.find('.membersince').html(memberDetailData.membersince);
				$rotarymemberdialog.find('.dialogbottom').html('<p class="clubname">' + memberDetailData.clubname + '</p><p class="rotaryclub">Rotary Club</p><p class="rotaractclub">Rotaract Club</p>');
				$rotarymemberdialog.find('.profilepicture img').remove();
				if ($.trim(memberDetailData.profilepicture)) {
					$rotarymemberdialog.find('.profilepicture').append('<img src="' + memberDetailData.profilepicture + '" alt="' + memberDetailData.memberName + '" title="' + memberDetailData.memberName + '"/>');
				}
				if ($rotarymemberdialog.dialog('isOpen')) {
					$rotarymemberdialog.dialog('close');
				}
				$rotarymemberdialog.dialog('open');
			},
			showDetails: function() {
				memberID = ($(this).siblings('.userid').text());
				$.get(
				rotarydatatables.ajaxURL, {
					// here we declare the parameters to send along with the request
					// this means the following action hooks will be fired:
					// wp_ajax_nopriv_myajax-submit and wp_ajax_myajax-submit
					action: 'rotarymemberdetails',
					memberID: memberID
				}, rotaryDataTables.displayMember);
			}
		};
		rotaryDataTables.init();
	});