<?php
	
	//add your security here, we use OpenLDAP at Mozilla
	session_start();
	if ( !isset($_SESSION['key']) ) {			
			$_SESSION['key'] = bin2hex(openssl_random_pseudo_bytes(128));
		}
?>

<html>
	<head>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>		
		<title>Tableau Server Upload Utility (beta)</title>
		<!-- add some nice ajaxy feel to it later
		<script>
			$(document).ready(function(){
			  $("form").submit(function(){
				$("body").replaceWith('<p>Please wait while we upload your data to Tableau</p><img src="ajax_loader_gray_512.gif" width="200" height="200"/>');
			  });
			});
		</script>
		-->
		<style>
			body {
				
			max-width:800px;
			max-height:600px;

			}
		</style>
	</head>
	<body>
		<h1>Tableau Server Upload Utility</h1>
		<p>Upload your data to Tableau Server and then start analyzing all directly from the web. First find your .csv file to upload. You may also refresh an existing data source by simply uploading
		a file with the same name using this form. Once uploaded you should be able to access your data at <a href="https://myserver/datasources" target="_blank">https://myserver/datasources</a></p>
		<strong>File Requirements</strong>
		<ul>
			<li>Under 1GB</li>
			<li>Comma Separated Values (.csv) Format</li>
		</ul>
		<form name="f_upload" action="upload.php" method="post" enctype="multipart/form-data">
			<!--<label for="project">Project:</label>-->
			<input type="hidden" name="project" value="Templates" />
			<label for="file">Filename:</label>
			<input type="file" name="file" id="file"><br/><br>
			<input type="submit" name="submit" value="Upload">
			<input type="hidden" name="key" value="<?php echo $_SESSION['key']; ?>"/>
		</form>

	</body>
</html>
