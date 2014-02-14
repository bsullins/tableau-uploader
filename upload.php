<?php

	/*
	
		2014-02-11 bsullins
		x	added CSRF protection
		x	escapeshellcmd for shell -- user input already escaped 
		x	completed XSS functions
		x	completed MIME Type Checking
	
	*/
	
	error_reporting(E_ERROR | E_PARSE);
	
	try {
	
		session_start();
		
		//extend the time limit
		set_time_limit(800);
		
		//debug == 1 show output
		$debug=0;

		
		//XSS Mitigation Functions
		function xssafe($data,$encoding='UTF-8')
		{
			return htmlspecialchars($data,ENT_QUOTES,$encoding);
		}
		
		function xecho($data)
		{
			echo xssafe($data);
		}
		
		//CSRF Mitigation Function
		function csrfsafe($string){
			return escapeshellcmd($string);
		}
		
		//send details to team when something new is uploaded
		function alert($msg, $mtext) {
		
			if ($msg==0) {
				
				$subject = "TSU - Upload Failed";
				
			} else {
				$subject = "TSU - Data Source Uploaded by " . $_SERVER['PHP_AUTH_USER'];
			}
	 
			// In case any of our lines are larger than 70 characters, we should use wordwrap()
			$mtext = wordwrap($mtext, 70, "\r\n");

			// Send
			mail('someone@myco.com', $subject, $mtext);
		
		}
		
			$allowedExts = array("csv");
			$temp = explode(".", $_FILES["file"]["name"]);
			$extension = end($temp);
			$name = xssafe($temp[0]);
			$shellname = csrfsafe($temp[0]);
			$upFile = $name . "." . $extension;
			$saveDir = "uploads";
			$project = xssafe($_POST["project"]);
			$tempName =  $_FILES["file"]["tmp_name"];
			$mimeType = $_FILES["file"]["type"];
			
			
			//tableau vars
			$tabcmd = '"c:\\Program Files\\Tableau\\Tableau Server\\8.1\\bin\\tabcmd"'; //this needs to be updated with wherever you have tabcmd.exe
			$tabLogin = 'login -s https://localhost -u admin --no-certcheck -p admin';  //every tableau instance has a diff config so make sure this works before entering it
			$loginCmd = $tabcmd . ' ' . $tabLogin;
			$publishCmd = $tabcmd . ' publish "' . $saveDir . '\\' . $name .'.tde" -n "' . $shellname . '" -r "' . $project . '" -o --no-certcheck"';
			
			//breaking when files get large
			
			// $finfo = new finfo(FILEINFO_MIME_TYPE);
			// $fileContents = file_get_contents($tempName);
			// $mimeType = $finfo->buffer($fileContents);
			
			
			
			//email vars
			
			// The message
			$m = "A new data source has been published to Tableau Server by " . $_SERVER['PHP_AUTH_USER'];
			$m .= " with the following details: \r\n";
			$m .= "\r\n";
			$m .= "mimeType=".$mimeType."\r\n";	
			$m .=  "Upload: " . $_FILES["file"]["name"] . "\r\n";
			$m .=  "Name: " . $name . "\r\n"; 
			$m .=  "Extension: " . $extension . "\r\n";	
			$m .= "Size: " . ($_FILES["file"]["size"] / 1024) . " kB \r\n";
			$m .= "Temp file: " . $tempName . "\r\n";
			$m .= "upload: " . $upFile . "\r\n";
			
		
		// Cross Site Request Forgery
		if ( $_SESSION['key'] != $_POST['key'] || !isset($_SESSION['key']) ) {
			//echo "session=" .$_SESSION['key'] . "<br/>";
			//echo "post=" .$_POST['key'] . "<br/>";
			echo "<h1>Oops! Looks like something was wrong with that request. Please try clearing your cache, restarting your browser and trying again.</h1>";
		} else {
		
			if (
				($mimeType == "text/csv" || $mimeType == "application/csv"
				|| $mimeType == "text/x-c"	|| $mimeType == "text/plain")
				&& ($_FILES["file"]["size"] < 1073741824) //1GB limit
				&& in_array($extension, $allowedExts))
			{
				if ($_FILES["file"]["error"] > 0)
				{
					xecho ("Return Code: " . $_FILES["file"]["error"] );
				}
				else
				{
						if ($debug==1) {
						echo "mimeType=".$mimeType."<br/>";
						xecho ("Upload: " . $_FILES["file"]["name"]);
						echo ("<br/>");
						xecho ("Name: " . $name ); 
						echo ("<br/>");
						xecho ("Extension: " . $extension);
						echo ("<br/>");
						echo ("Type: " . $_FILES["file"]["type"] . "<br/>");
						echo ("Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>");
						echo ("Temp file: " . $tempName . "<br>");
						xecho ("upload: " . $upFile );
						echo ("<br/>");

						}
					
					try {
						//copy file to uploads dir
						move_uploaded_file($tempName,
							$saveDir . "/" . $upFile );
						if($debug==1){echo ("<br/>Stored in: " . $saveDir . "/" . $upFile . "<br/>");}
						
						echo ("**** Converting to Tableau Extract ****<br/>");
						
							//convert to extract
						
							//echo 'extract-upload.py "'. $upFile. '"<br/>';
							$uploadStatus = exec('extract-upload.py "'. $upFile. '"');
							
							if ( !strpos($uploadStatus, 'TDE') ) {
								throw new Exception('<h1>Upload Failed :( </h1>'.$uploadStatus);
							} else {
								echo $uploadStatus;
							}
							
							echo ("<br/><br/>**** Uploading to Tableau Server ****<br/>");
						
							//login first   
							//echo(escapeshellcmd($loginCmd));
							$loginStatus = rtrim(str_replace("=", "", exec($loginCmd)."...<br/>"));
							
							if ( !strpos($loginStatus, 'Succeeded') ) {
								throw new Exception('<h1>Login Failed :( </h1>'.$loginStatus);
							} else {
								echo $loginStatus;
							}
							
					
							// now publish that shit!							
							$pubStatus = (rtrim(str_replace("=", "", exec($publishCmd))));
							

							if ( !strpos($pubStatus, 'http') ) {
								throw new Exception('<h1>Publish Failed :( </h1>'.$pubStatus);
							} else {
								echo ("<h3>Congrats!</h3> Your data source is now available at: " . $pubStatus) ;
								echo "<p>What's next? Checkout this video tutorial - <a href=\"http://www.screencast.com/t/OcIcjAAE\" target=\"_blank\">Visually Exploring Data from the Web</a></p>";	
							}
							
							//delete csv
							unlink($saveDir . '\\' . $upFile);
							
							//delete extract
							unlink($saveDir . '\\' . $name .'.tde');
							
							alert(1, $m);
							
					} catch (Exception $e) {
					
						echo 'Caught exception: ',  $e->getMessage(), "<br/>";
						alert(0, $e->getMessage());
						//delete csv
						unlink($saveDir . '\\' . $upFile);
							
						//delete extract
						unlink($saveDir . '\\' . $name .'.tde');
				
					}
					
					
				}
			}
			else
			{
				echo "<h1>Invalid file :(</h1>";
				echo "<p>Make sure your file is a comma separated values (.csv) and is less then 1GB in size</p>";
				if ($debug==1) {
						echo "mimeType=".$mimeType."<br/>";
						xecho ("Upload: " . $_FILES["file"]["name"] );
						echo ("<br/>");
						xecho ("Name: " . $name ); 
						echo ("<br/>");
						xecho ("Extension: " . $extension);
						echo ("<br/>");
						echo ("Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>");
						echo ("Temp file: " . $tempName . "<br>");
						xecho ("upload: " . $upFile );
						echo ("<br/>");

						}
			}
		}

	} catch (Exception $e) {
	
		echo "<h1>Oops!</h1></p>Looks like we hit a speed bup :(</p><p>";
		echo $e->getMessage();
		echo "</p>";
		mail("someone@myco.com", "TSU - Fatal Error", $e->getMessage());
	
	}

		
?> 
