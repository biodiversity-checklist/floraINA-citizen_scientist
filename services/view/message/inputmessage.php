<?php //defined ('MICRODATA') or exit ( 'Forbidden Access' );?>

<script>
$(function () {
	$('#postdate').datepicker ();
	$('#expiredate').datepicker ();
	
	$("#isi").jqte();
	
	$('input:checkbox, input:radio').iCheck({
		checkboxClass: 'icheckbox_minimal-blue',
		radioClass: 'iradio_minimal-blue',
		inheritClass: true
	})
})
</script>

<div id="content-header">
	<h1>New Message</h1>
</div> <!-- #content-header -->	

<div id="content-container">
	<form id="validate-enhanced" class="form parsley-form" method="post" action="<?=$basedomain?>sendmessage/messageinp" enctype="multipart/form-data">

		<div class="row">
			<div class="col-md-9">
				<div class="portlet">
				
					<div class="portlet-header">

						<h3>
							<i class="fa fa-tasks"></i>
							Form Message
						</h3>

					</div> <!-- /.portlet-header -->
				
					<div class="portlet-content">
				
						
						
						<div class="col-sm-6">
							<div class="form-group">
							<label for="text-input">To (Email)</label>
									<input type="text" name="receive" class="form-control" value="<?=(isset($data['item'][0]['title']) ? $data['item'][0]['title'] : '');?>" data-required="true"/>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
							<label for="text-input">Subject</label>
									<input type="text" name="subject" class="form-control" value="<?=(isset($data['item'][0]['brief']) ? $data['item'][0]['brief'] : '');?>" />
							</div>
						</div>
					
						
						<div class="col-sm-12">
							<div class="form-group">
							<label for="textarea-input">Content</label>
									<textarea name="content" id="isi"  cols="10" rows="20" class="form-control"><?=(isset($data['item'][0]['content']) ? $data['item'][0]['content'] : '');?></textarea>
							</div>
							
						</div>
						<div class="col-sm-6">
							<input type="submit" class="btn btn-primary" value="Submit" id="submit"/>
						</div>
					</div>
				</div>
				
				
			</div>
		
		</div>
	
	</form>
</div>