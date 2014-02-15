<?php
	//add some security here...
	session_start();
	if ( !isset($_SESSION['key']) ) {			
			$_SESSION['key'] = bin2hex(openssl_random_pseudo_bytes(128));
		}
?>
<!doctype html>
	<head>
		<script src="jquery-1.11.0.min.js"></script>		
		<script src="jquery.form.min.js"></script> 
		<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300' rel='stylesheet' type='text/css'>
		<title>Tableau Server Upload Utility (beta)</title>		
		<style>			
			h1	{ font-family: 'Open Sans', san-serif; font-weight: 300;}
			body { padding: 5px; font-family: 'Open Sans', san-serif; font-weight: 400;}
			form { display: block; margin: 20px auto; background: #eee; border-radius: 10px; padding: 15px }

			.progress { position:relative; width:400px; border: 1px solid #ddd; padding: 1px; border-radius: 3px; }
			.bar { background-color: #B4F5B4; width:0%; height:20px; border-radius: 3px; }
			.percent { position:absolute; display:inline-block; top:3px; left:48%; }
		</style>
		<script type="text/javascript">

		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-35433268-34']);
		  _gaq.push(['_trackPageview']);

		  (function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();

		</script>
	</head>
	<body>
		<h1>Tableau Server Upload Utility (beta)</h1>
		<p>Upload your data to Tableau Server and then start analyzing all directly from the web. First find your .csv file to upload. You may also refresh an existing data source by simply uploading
		a file with the same name using this form. Once uploaded you should be able to access your data at <a href="https://dataviz.mozilla.org/datasources" target="_blank">https://dataviz.mozilla.org/datasources</a></p>
		<strong>File Requirements</strong>
		<ul>
			<li>Under 1GB</li>
			<li>Comma Separated Values (.csv) Format</li>
		</ul>
		<form id="tabup" name="f_upload" action="upload.php" method="post" enctype="multipart/form-data">
			<!--<label for="project">Project:</label>-->
			<input type="hidden" name="project" value="Templates" />
			<label for="file">Filename:</label>
			<input type="file" name="file" id="file"><br/><br>
			<input type="submit" name="submit" value="Upload">
			<input type="hidden" name="key" value="<?php echo $_SESSION['key']; ?>"/>
		</form>
		
		<div class="progress" style="visibility:hidden;">
			<div class="bar"></div >
			<div class="percent">0%</div >
		</div>
		
		
		<div id="status"></div>
		
		<script>
		(function() {
			
		var bar = $('.bar');
		var percent = $('.percent');
		var status = $('#status');
		
		var form = $('#tabup');
		   
		$('form').ajaxForm({
			beforeSend: function() {
				status.empty();
				var percentVal = '0%';
				bar.width(percentVal)
				percent.html(percentVal);
				form.html('Uploading...<img src="ajax_loader_gray_512.gif" height="50" width="50"/>');
			
				
			},
			uploadProgress: function(event, position, total, percentComplete) {
				var percentVal = percentComplete + '%';
				bar.width(percentVal)
				percent.html(percentVal);
			},
			success: function() {
				var percentVal = '100%';
				bar.width(percentVal)
				percent.html(percentVal);
				form.html('Status results below, feel free to close this window now. Happy Exploring!');
				window.resizeTo(600,800);
			
			},
			complete: function(xhr) {
				status.html(xhr.responseText);				
			}
		}); 

		})();       
		</script>
			
		
	</body>
