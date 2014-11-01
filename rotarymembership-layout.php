<?php
	function get_memberhsip_layout($rotarymembership, $projects, $id) {
		$divID = 'rotarymembers';
		$title = 'Membership Directory';
		$deleteCol = '';
		if (isset( $projects ) && strlen( $projects ) > 1 ) {
			$divID = 'rotary' . $projects;
		}
		$dataID = '';
		$select = '';
		$hide = '';
		if (isset($id) && strlen( $id ) > 1 )  {
			$dataID = ' data-id="'.$id.'"';
			$hideClass = ' class="hide"';
			$title = 'Participants';
			$deleteCol = '<th>Delete</th>';
			$select = '<div><div class="usercontainer"><select id="newparticipants">'.$rotarymembership->get_users_for_membertable_select().'</select></div>';
		}
		else {
			$select = '<div><input type="radio" id="nameorder1" name="nameorder" value="firstname"/><span>First Last Name</span><input type="radio" id="nameorder2" name="nameorder" value="lastname" checked="checked"/><span>Last, First Name</span><div class="committeecontainer"><span id="committeelabel">Committees</span><select id="commitees">'.$rotarymembership->get_committees_for_membertable().'</select></div></div>';

		}

		$memberTable = '<div class="rotarymembershipcontainer"><div class="rotarymembershipheader"><h2>'.$title.'</h2><div class="rotaryselections">'
			.$select.
			'</div>
			<table cellspacing="0" cellpadding="0" border="0" id="'.$divID.'" class="display"'.$dataID.'>	
       		<thead>
			<tr>	
        		<th class="fullname">Name</th>		         
        		<th'.$hideClass.'>Classification</th>		         
        		<th'.$hideClass.'>Partner</th>                         
        		<th>Cell/Home Phone</th>                         
        		<th>Business Phone</th>                         
        		<th>Email</th>
        		<th class="hide">ID</th>'.$deleteCol.
        	'</tr>
			</thead>
			<tbody></tbody>
        	</table>
			<div id="rotarymemberdialog">
			<div class="dialogtop">
			   <div class="namearea">
					<h2 class="membername"></h2>
					<p class="classification"></p>
				</div>
				<div class="addressarea">
					<p class="addressdetails"></p>
				</div>	
			</div>
			<div class="dialogmain">
				<div class="personalinfoarea">
					<div class="profilepicture"></div>
					<div class="profilepicturebottom"></div>
					<h4>Birthday</h4>
					<p class="birthday"></p>
					<h4>Anniversary</h4>
					<p class="anniversarydate"></p>	
					<h4>Member Since</h4>
					<p class="membersince"></p>
				</div>
				<div class="memberdetailsarea">
					<div class="company">
						<h3>Company</h3>
						<div class="clearleft">	
							<h4>Name</h4>
							<p class="busname"></p>
						</div>
						<div class="clearleft">
							<h4>Title</h4>
							<p class="jobtitle"></p>
						</div>
						<div class="clearleft">
							<h4>Web</h4>	
							<p class="busweb"></p>
						</div>	
					</div>
					<div class="contact">
						<h3>Contact</h3>
						<div class="clearleft">
							<h4>Cell</h4>
							<p class="cellphone"></p>
							<h4>Home</h4>
							<p class="homephone"></p>
						</div>
						<div class="clearleft larger">
							<h4>Business</h4>
							<p class="officephone"></p>
							<h4>Email</h4>
							<p class="email"></p>
						</div>
					</div>
					<div class="partner">
						<h3>Partner</h3>
						<div class="clearleft">
							<h4>Spouse</h4>
							<p class="partnername"></p>
						</div>	
					</div>
					
				</div>
			</div>
			<div class="dialogbottom"></div>
			</div></div></div>
			';
		return $memberTable;
	}
?>