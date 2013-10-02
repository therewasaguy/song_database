<?php

	require 'phpDataMapper/Base.php';

	$budget = 0;
	$end_value = null;
	$error = "";	
	$ns_message = ""; //text to display in Admin Song Entry area
		
	
//create the Song class
	class Song extends phpDataMapper_Base  //data mapper!
	{
	
	protected $_datasource = "songs";
	
		// Define your DATABASE fields as public class properties
		public $id = array('type' => 'int', 'primary' => true, 'serial' => true); //this is ID
		public $song_title = array('type' => 'string', 'required' => true);
		public $url = array('type' => 'string', 'required' => true);
		public $artist = array('type' => 'string', 'required' => true);
		public $song_image = array('type' => 'string', 'required' => true);		
		
		//see if this works! http://phpdatamapper.com/documentation/usage/table-relationships/ 
		public $licenses = array('type' => 'relation', 'relation' => 'HasMany', 'mapper' => 'License', 'where' => array('license_id' => 'entity.id'));

//default __construct is something that datamapper uses
	
		function add_song($_title, $_url, $_artist, $_img) {
			$this->song_title = $_title;
			$this->url = $_url;
			$this->artist = $_artist;
			$this->song_image = $_img;		
		}
	}

//create the License class
	class License extends phpDataMapper_Base //data mapper!
	{

		protected $_datasource = "licenses";

		public $id = array('type' => 'int', 'primary' => true, 'serial' => true);
		public $license_id = array('type' => 'int', 'key' => true, 'required' => true);
		public $media_type = array('type' => 'string', 'required' => true);
		public $commercial = array('type' => 'string', 'required' => true);
		public $project_name = array('type' => 'string', 'required' => true);
		public $project_budget = array('type' => 'string', 'required' => true);
		public $fee = array('type' => 'string', 'required' => true);
		//see if this works! http://phpdatamapper.com/documentation/usage/table-relationships/ 
	//public $licensed_song = array('type' => 'relation', 'relation' => 'HasOne', 'mapper' => 'Song', 'where' => array('song_title' => 'entity.song_title'));

		
		function add_license($_mtype, $_comm, $_song, $_project_name, $_project_budget, $_fee) {
			$this->media_type = $_mtype;
			$this->commercial = $_comm;
			$this->licensed_song = $_song;
			$this->project_name = $_project_name;
			$this->project_budget = $_project_budget;
			$this->fee = $_fee;
		}
	}

//more one-time database setup
// 1. Setup the adapter to the database
//$databaseAdapter = new phpDataMapper_Adapter_Mysql('database hostname', 'database name', 'database user', 'password');
$databaseAdapter = new phpDataMapper_Adapter_Mysql('mysql.itp.jasonsigal.cc', 'commlabweb', 'commlabweb', 'fuckyou69');
// 2. Create the model, a new object of your class type just to provide a template
$licenseWriter = new License($databaseAdapter);
$licenseWriter->migrate();
$songWriter = new Song($databaseAdapter);
$songWriter->migrate();

// Create an empty object
$songTemp = $songWriter->get();
// Fill it in
//$songTemp->song_title = "Cool Beat";
//$songTemp->url = "http://freemusicarchive.org/music/bopd";
//$songTemp->artist = "BOPD";
//$songTemp->song_image = "http://upload.wikimedia.org/wikipedia/en/b/b2/Sonic_Youth_Goo.jpg";
// Save it in the database
//$songWriter->save($songTemp);

$licenseTemp = $licenseWriter->get();
$licenseTemp->license_id = sizeof($licenseWriter->all()) + 1;
$licenseTemp->media_type = "Radio";
$licenseTemp->commercial = "no";
$licenseTemp->project_name = "my projecto";
$licenseTemp->project_budget = "100.20";	
$licenseTemp->fee = "12.12";
//$licenseTemp->licensed_song = 
$licenseWriter->save($licenseTemp);

/*calculating the end value based on parameters*/
	if (isset($_POST["p_budget"])) {
		/*default way to calculate end value*/
		$end_value = ($_POST["p_budget"]/5);
		
		
		/* if it's TV, add 50%, if it's a game subtract 25%, if it's radio -50%, other do nothing */
		if (isset($_POST["media"]) && $_POST["media"]=="tv") {
			$end_value = $end_value * 1.5;
			}  /*if it's a game, subtract 25%*/
		else  if (isset($_POST["media"]) && $_POST["media"]=="game") {
			$end_value = $end_value * .75;
			} 
		else  if (isset($_POST["media"]) && $_POST["media"]=="radio") {
			$end_value = $end_value * .50;
			} 
		
		
		/* Commercial or not? */
		if (isset($_POST["comm"]) && $_POST["comm"]=="is") {
			$end_value = $end_value * 2;
			} 
		else if (isset($_POST["comm"]) && $_POST["comm"]=="is not") {
			$end_value = 'free!<br /> Please follow the terms of this license 
			<a rel="license" href="http://creativecommons.org/licenses/by-nc/3.0/us/deed.en_US"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-nc/3.0/us/88x31.png" /></a><br />Attribution: This work by <a xmlns:cc="http://creativecommons.org/ns#" href="http://jasonsigal.cc" property="cc:attributionName" rel="cc:attributionURL">Jason Sigal</a> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nc/3.0/us/deed.en_US">Creative Commons Attribution-NonCommercial 3.0 United States License</a>';
			}	
		}	
		
/*compute a new license (and add a new license to the database*/
	if (!isset($_POST["submit"])) {
		$error = "";
		$license =	'Use of $_POST["s_title"] in $_POST["p_project"] will be $end_value';	
		}
	else if (isset($_POST["submit"]) &&
		!isset($_POST["media"]) ||
		!isset($_POST["comm"]) ||
		!isset($_POST["s_title"]) ||
		!isset($_POST["p_title"]) ||
		!isset($_POST["p_budget"])
		) {
		$error = "please fill out all of the forms";
		}
	else {
		$error = "";
		$show_license =	"Use of <span class='variable'>" . $_POST["s_title"] . "</span> in <span class='variable'>" . $_POST["p_title"] . "</span> will be <span class='variable'>$" . $end_value . "</span>. That's because the project is a <span class='variable'>" . $_POST["media"] . "</span> and it <span class='variable'>" . $_POST["comm"] . " commercial</span>.";	

//2d0: add the license to the database
		$licenseTemp = $licenseWriter->get();
		$licenseTemp->license_id = sizeof($licenseWriter->all()) + 1;
		$licenseTemp->media_type = $_POST["media"];
		$licenseTemp->commercial = $_POST["comm"];
		$licenseTemp->project_name = $_POST["p_title"];
		$licenseTemp->project_budget = $_POST["p_budget"];	
		$licenseTemp->fee = $end_value;
		//$licenseTemp->licensed_song = 
		$licenseWriter->save($licenseTemp);

		}

/*add a new song to the database*/
	if (!isset($_POST["add_new_song"])) {
		$ns_message = "";
		}
	else if (isset($_POST["add_new_song"]) &&
		($_POST["ns_artist"] == "") ||
		($_POST["ns_title"] == "") ||
		($_POST["ns_url"] == "") ||
		($_POST["ns_image"] == "")
		) {
		$ns_message = "please fill out all of the forms";
		}
	else {
		$songTemp = $songWriter->get();
//why doesn't this work?		$songTemp->add_song($_POST["ns_title"],$_POST["ns_url"],$_POST["ns_artist"],$_POST["ns_image"]);
		// Fill it in
		$songTemp->song_title = $_POST["ns_title"];
		$songTemp->url = $_POST["ns_url"];
		$songTemp->artist = $_POST["ns_artist"];
		$songTemp->song_image = $_POST["ns_image"];
		// Save it in the database
		$songWriter->save($songTemp);

		//save it to the db
		$songWriter->save($songTemp);
		$ns_message = "You added a new song, '" . $_POST["ns_title"] ."' ! There are currently this many songs in the database:" . sizeof($songWriter->all());
		}

//erase song database
	if (isset($_POST["erase_songs"])) {
		$songTemp = $songWriter->all();
		while (count($songTemp) > 1) {
			array_pop($songTemp);
		}
	}

?>

<head>
<title>BOPD Sync Calculator</title>
<style>
body {
	background:#333;
	color:"yellow";
	font-size:20px;
	}
a {
	color:"red";
	}
.variable {
	color:"green";
	text-decoration:none;
	}

</style>
</head>

<html>
	<body>
		<div id="content">
		<div id="welcome" float="right" width="200"><img src="images/bopd.jpg" align="right"><br>Hello this is <a href="http://freemusicarchive.org/music/BOPD/Old_Paper_Houses/">BOPD</a>. If you'd like to use my music in your project, you must first use this form to calculate your fee. </div>

		<!-- Radio Buttons -->
		<div id="leftsidepanel" style="display:block;">
		<h2>Describe Your Project</h2>
		<div id="radiobuttons" style="width:300px; display:block; float:left;">
		<form method="POST" action="song_index.php">
			<input type="radio" name="media" value="tv" <?php if ($_POST["media"]=="tv"){ echo("checked");} ?>>TV / Film / YouTube<br>
			<input type="radio" name="media" value="game" <?php if ($_POST["media"]=="game"){ echo("checked");}?>>Video Game / Interactive<br>
			<input type="radio" name="media" value="radio" <?php if ($_POST["media"]=="radio"){ echo("checked");}?>>Radio / Podcast<br>
			<input type="radio" name="media" value="other" <?php if ($_POST["media"]=="other"){ echo("checked");}?>>Other			
			<br /><br />
			<input type="radio" name="comm" value="is" <?php if ($_POST["comm"]=="is"){ echo("checked");}?> >Commercial<br>
			<input type="radio" name="comm" value="is not" <?php if ($_POST["comm"]=="is not"){ echo("checked");}?> >NonCommercial
			</div>
			
			<div id="text fields" style="width:500px; display:block; float:left;">
			<div>BOPD Song Title
<!--			<input type="text" name="s_title" value="BOPD Song Title" style="width: 500px">
-->
			<!--loop thru songs for dropdown menu-->
		<select name ="s_title">
				<?PHP foreach ($songWriter->all() as $i) {	
				?>
				  <option style="width: 500px" value="<?php echo($i->song_title); ?>">
				  <?php echo($i->id); echo(". "); echo($i->song_title); ?></option>
				<?	}	 ?>
			</select>


			</div>
			<br />			
			<div>Your Project Name
			<input type="text" name="p_title" value="<?php if (isset($_POST["p_title"])){ print($_POST["p_title"]);} else { echo("Your Project Name"); } ?>" style="width: 500px">
			</div>
						<br />

			<div>Your Project's Total Budget (please enter a number)
			<input type="text" name="p_budget" value="<?php if (isset($_POST["p_budget"])){ print($_POST["p_budget"]);} else { echo("Your Project's Total Budget (please enter a number)"); } ?>" style="width: 500px">
			<br />
			</div>
			<br>
			<div style="align:right; float:right; ">			
			<input type="submit" name="submit" value="Submit It" size="200";>
			</div>
			</div>

			</form>
		</div>

	<div id="results" style="float:left;">
	<br /> <br /> <br />
		<div id="error" style="color:red;background-color:yellow;"><?php echo $error; ?> </div>
			<div id="license">
				<h3><?php echo $show_license; /* echo $end_value; ?>.	The project is <span style="color:red;"><?php echo $_GET["media"];?></span> and <span style="color:red;"><?php if ($_GET["comm"] == "no") { echo "is not"; } else { echo "is"; } ?> commercial</span>.	*/?>			
				</h3>
			</div>

		</div>
	</div>


<!--admin mode-->
	<div id="add_songs" style="clear:left">
	<p><?php echo $ns_message ;?></p>
	<!--toggle admin mode-->
	<form method="POST" action="song_index.php">
		<select name="toggle_admin">
			<option value="0">Regular Mode</option>
			<option value="1" <?php if($_POST["toggle_admin"] == "1"){ echo("selected");}?>>Admin Mode</option>
		</select>
		<input type="submit" value="Change Mode!">
	</form>

<!--only display this if admin mode is true-->
<?php if($_POST["toggle_admin"]){?>	
	<div style="background-color:#fff;color:#000;">
	<form method="POST" action="song_index.php">
		<p>Enter New Song Title</p>
		<input type="text" name="ns_title" value="">
		<p>Artist Name</p><input type="text" name="ns_artist" value="">
		<p>URL</p><input type="text" name="ns_url" value="">
		<p>Image URL</p><input type="text" name="ns_image" value="">
		<input type="submit" name="add_new_song" value="Submit New Song" text-size="200";>	
	</form>
	<div>
	<form method="POST" action="song_index.php">
		<p>Erase song library</p>
		<input type="submit" name="erase_songs" value="Erase Database" text-size="300";>
	</form>	
	</div>
<?
}
?>
	</div><!--close content-->		
	</body>
</html>
