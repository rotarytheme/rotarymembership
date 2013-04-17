<?php
	function get_memberhsip_layout($rotarymembership) {
		$memberTable = '<div class="rotarymembershipcontainer"><div class="rotarymembershipheader"><h2>Membership Directory</h2><div class="rotaryselections">
			<div><input type="radio" id="nameorder1" name="nameorder" value="firstname"/><span>First Last Name</span><input type="radio" id="nameorder2" name="nameorder" value="lastname" checked="checked"/><span>Last, First Name</span><div class="committeecontainer"><span id="committeelabel">Committees</span><select id="commitees">'.$rotarymembership->get_committees_for_membertable().'</select></div></div>
			</div>
			<table cellspacing="0" cellpadding="0" border="0" id="rotarymembers" class="display">	
       		<thead>
			<tr>	
        		<th class="fullname">Name</th>		         
        		<th>Classification</th>		         
        		<th>Partner</th>                         
        		<th>Cell/Home Phone</th>                         
        		<th>Business Phone</th>                         
        		<th>Email</th>
				<th class="hide">ID</th>
        	</tr>
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
					<h4>Member Since</h4>
					<p class="membersince"></p>
				</div>
				<div class="memberdetailsarea">
					<div class="company">
						<h3>Company Info</h3>
						<div class="alignleft">
							<h4>Bus Postion</h4>
							<p class="jobtitle"></p>
						</div>
						<div class="alignleft larger">	
							<h4>Bus Name</h4>
							<p class="busname"></p>
						</div>
						<div class="clearleft">
							<h4>Bus Web</h4>	
							<p class="busweb"></p>
						</div>	
					</div>
					<div class="contact">
						<h3>Contact</h3>
						<div class="alignleft">
							<h4>Cell</h4>
							<p class="cellphone"></p>
							<h4>Home</h4>
							<p class="homephone"></p>
						</div>
						<div class="alignleft larger">
							<h4>Office</h4>
							<p class="officephone"></p>
							<h4>Email</h4>
							<p class="email"></p>
						</div>
					</div>
					<div class="partner">
						<h3>Partner</h3>
						<div class="alignleft">
							<h4>Spouse</h4>
							<p class="partnername"></p>
						</div>	
						<div class="alignleft">
							<h4>Anniversary</h4>
							<p class="anniversarydate"></p>
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