<?php //defined ('MICRODATA') or exit ( 'Forbidden Access' ); ?>

<div id="content-header">
	<h1>User Member</h1>
</div> <!-- #content-header -->	

<div id="content-container">

<h3 class="heading">User List</h3>

<div class="row">

	<div class="col-md-12">

		<div class="table-responsive">
		<!--
		<a href="<?=$basedomain?>setting/adduser"><button class="btn btn-default ui-tooltip btn-info" data-toggle="tooltip" data-placement="top" title="Add New User">New User</button></a>-->
		<table class="table table-striped table-bordered table-hover table-highlight table-checkable" data-provide="datatable" 
								data-display-rows="10"
								data-info="true"
								data-search="true"
								data-length-change="true"
								data-paginate="true">
			<thead>
			
			<tr>
				<th>No</th>
				<th data-sortable="true">Name</th>
				<th data-sortable="true">Email</th>
				<th data-sortable="true">Register Date</th>
				<th data-sortable="true">User Type</th>
				<th data-sortable="true">Status</th>
				<th class="text-center">Actions</th>
			</tr>
			</thead>
			<tbody>
			<?php
			$no = 1;
			if($data['item']){
				foreach($data['item'] as $key => $value){
				if($value['usertype']==1){
					$usertype = 'Administrator';
				}elseif($value['usertype']==2){
					$usertype = 'Employer';
				} elseif($value['usertype']==3){
					$usertype = 'Job Seeker';
				}elseif($value['usertype']==4){
					$usertype = 'Alumni';
				}elseif($value['usertype']==5){
					$usertype = 'Mahasiswa';
				}elseif($value['usertype']==6){
					$usertype = 'Corporate';
				}elseif($value['usertype']==7){
					$usertype = 'Company';
				}elseif($value['usertype']==8){
					$usertype = 'Advertise';
				}
			?>
			<tr>
				<td><?=$no++?></td>
				<td><?=$value['name']?></td>
				<td><?=$value['email']?></td>
				<td><?=date('d-m-Y',strtotime($value['register_date']));?></td>
				<td><?=$usertype?></td>
				<td><?=$value['n_status']==1 ? '<label style="color:green">Active</label>' : '<label style="color:red">Pending</label>';?></td>
				<!--<td></td>-->

				<td class="text-center">
					<?php
					// pr($_SESSION);
					// if ($_SESSION['admin']['id']==$value['id']):
					?>
					<a href="<?=$basedomain?>setting/upd_usrmember/?id=<?=$value['id']?>"><button class="btn btn-xs ui-tooltip btn-warning" data-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-pencil"></i></button></a>&nbsp;
					<?php //endif;?>
					
					<a href="<?=$basedomain?>setting/del_usrmember/?id=<?=$value['id']?>"><button class="btn btn-xs ui-tooltip btn-primary" data-toggle="tooltip" data-placement="right" title="Delete" onclick="return confirm('Hapus user ini?');"><i class="fa fa-times"></i></button></a>
					</td>
			</tr>
			<?php }} ?>
			</tbody>
			<tfoot>
			<tr>
				<td colspan="8"></td>
			</tr>
			</tfoot>
		</table>
		</div>
	</div>
</div>
</div> <!-- /#content-container -->