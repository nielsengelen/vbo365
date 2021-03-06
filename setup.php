<?php
$success = 'setup';

if (isset($_POST['save']) && count($_POST) !== 0) {
	if (!empty($_POST['host']) && !empty($_POST['port']) && !empty($_POST['title']) && !empty($_POST['version']) && !empty($_POST['administrator']) && !empty($_POST['itemstoload'])) {
		unset($success);

		$host = $_POST['host'];
		$port = $_POST['port'];
		$title = htmlspecialchars ($_POST['title'], ENT_QUOTES);
		$version = $_POST['version'];
		$administrator = $_POST['administrator'];
		$itemstoload = $_POST['itemstoload'];
		$txt = "<?php
\$host = '".$host."'; /* Veeam Backup for Microsoft Office 365 server (hostname or IP) */
\$port = '".$port."'; /* RESTful API service port (default: 4443) */
\$title = '".$title."'; /* Custom title for the portal to be displayed in the browser title */
\$version = '".$version."'; /* RESTful API version (default: v5) */
\$administrator = '".$administrator."'; /* Allow Windows administrator accounts to be used as a login (yes or no) */
\$items = '".$itemstoload."'; /* Amount of items to load for exploring restore points (default: 250) - a higher number may impact loading performance */
?>";
		
		try {
			$fileName = 'config.php';

			if (!file_exists($fileName)) {
				throw new Exception('File not found.');
			}

			$fp = fopen($fileName, 'w');
		  
			if (!$fp) {
				throw new Exception('File open failed.');
			}  

			fwrite($fp, $txt);
			fclose($fp);

			$success = "ok";
		} catch (Exception $e) {
			$success = 'error';
			$reason = $e->getMessage();
		} 
	} else {
		$missing = array();
		
		foreach ($_POST as $key => $value) { 
			if (empty($value)) {
				array_push($missing, $key);
			}
		}
		 
		$success = 'missingparameter';
	}
}
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Setup for Unofficial Veeam Backup for Microsoft Office 365 Self Service Web Portal</title>
    <link rel="shortcut icon" href="favicon.ico" />
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
	<script src="js/jquery.min.js"></script>
</head>
<body>
<fieldset>
<legend>&nbsp;Setup for Unofficial Veeam Backup for Microsoft Office 365 Self Service Web Portal<span class="pull-right"><a href="index.php">Go to index page</a>&nbsp;&nbsp;</span></legend>
<?php
if (strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'apache') !== false) { /* Apache check for mod_rewrite */
	if (function_exists('apache_get_modules')) {
		$mod_rewrite = in_array('mod_rewrite', apache_get_modules());
	} else {
		$mod_rewrite = getenv('HTTP_MOD_REWRITE') == 'On' ? true : false;
	}
}

if (strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'microsoft-iis') !== false) { /* IIS check for mod_rewrite */
	if (isset($_SERVER['IIS_UrlRewriteModule'])) {
		$mod_rewrite = true;
	} else {
		$mod_rewrite = false;
	}
}

if (!$mod_rewrite) {
	echo '<div class="alert alert-danger text-center" role="alert"><strong>Mod_rewrite is not enabled. Enable the rewrite module in your webserver configuration before using this portal</strong></div>';
}

if (isset($success) && $success !== 'setup') {
	if ($success === 'error') {
	?>
		<script>
		$('#copytoclipboard').click(function(){
			$('textarea').select();
			document.execCommand('copy');
			return false;
		});
		</script>
		<div class="alert alert-danger text-center" role="alert"><strong>Could not write to the config.php file (<?php echo $reason; ?>). Settings have NOT been saved. Copy paste the following within the configuration file</strong></div>
		<div class="text-center">
		<textarea id="textarea" rows="7" cols="150"><?php
		echo "<?php\n\$host = '$host'; /* Veeam Backup for Microsoft Office 365 server (hostname or IP) */\n\$port = '$port'; /* RESTful API service port (default: 4443) */\n\$title = '$title'; /* Custom title for the portal to be displayed in the browser title */\n\$version = '$version'; /* RESTful API version (default: v5) */\n\$administrator = '$administrator'; /* Allow Windows administrator accounts to be used as a login (yes or no) */\n?>";
		?></textarea>
		<br>
		<button class="btn btn-primary" id="copytoclipboard">Copy to clipboard</button>
		</div>
		<br>
	<?php
	} elseif ($success === 'missingparameter') {
		echo '<div class="alert alert-danger text-center" role="alert"><strong>One or more parameters have not been filled in. Settings have NOT been saved. Configure the following parameters.</strong><br>';
		
		foreach ($missing as $k => $v) { 
			echo $v . '<br>';
		}
		
		echo '</div>';
	} else {
		echo '<div class="alert alert-info text-center" role="alert"><strong>Settings have been saved.<br>Remember to remove the setup.php file before using the portal in production</strong></div>';
	}

	unset($success);
}
?>
<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" class="form-horizontal">
<div class="form-group">
  <label class="col-md-4 control-label" for="host">Hostname</label>  
  <div class="col-md-4">
  <input type="text" class="form-control input-md" id="host" name="host" placeholder="127.0.0.1" value="127.0.0.1">
  <span class="help-block">Hostname or IP of the Veeam Backup for Office 365 server</span>  
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="port">Port</label>  
  <div class="col-md-4">
  <input type="text" class="form-control input-md" id="port" name="port" placeholder="4443 (default)" value="4443">
  <span class="help-block">Port of the Veeam Backup for Office 365 RESTful API service</span>
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="port">Portal Title</label>  
  <div class="col-md-4">
  <input type="text" class="form-control input-md" id="title" name="title" placeholder="Your Company's Self-Service Portal">
  <span class="help-block">Portal title used in browser header</span>
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="version">API Version</label>  
  <div class="col-md-4">
  <select class="form-control input-md" id="version" name="version">
    <option selected>v5</option>
	<option>v4</option>
  </select>
  <span class="help-block">RESTful API version to be used</span>
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="administrator">Allow administrator login</label>  
  <div class="col-md-4">
  <select class="form-control input-md" id="administrator" name="administrator">
	<option selected>Yes</option>
	<option>No</option>
  </select>
  <span class="help-block">Allow Windows administrator accounts to be used as a login</span>
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="itemstoload">Items to load</label>  
  <div class="col-md-4">
  <select class="form-control input-md" id="itemstoload" name="itemstoload">
	<option>500</option>
	<option selected>250</option>
	<option>200</option>
	<option>150</option>
	<option>100</option>
	<option>50</option>
	<option>30</option>
  </select>
  <span class="help-block">Select the default amount of items to load for restore sessions.</span>
  </div>
</div>
<div class="form-group">
  <div class="alert alert-warning text-center" role="alert"><strong>Clicking save will overwrite the configuration file. Make sure you have made a backup.</strong></div>
  <label class="col-md-4 control-label" for="submit"></label>
  <div class="col-md-4 text-center">
    <input type="submit" class="btn btn-primary" id="save" name="save" value="Save">
  </div>
</div>
</fieldset>
</form>
</body>
</html>