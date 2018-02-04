
<head>
<script src="jquery-3.1.0.min.js"></script>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css"/>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css"/>
<link rel="stylesheet" type="text/css" href="css/style.css">
<link href="https://fonts.googleapis.com/css?family=Sorts+Mill+Goudy" rel="stylesheet">

</head>


<?php
echo "
<form action='' method='post' id='form'>


<div style='width: 100%;'>
   <div style='float:left; margin-left: 30px; width: 30%; height: 100% '>

<h2> DEPLOYMENT </h2>
<br>
<h4> Choose the type of package to deploy </h4>
<br>
<select name='package'>
  <option value=''>-----</option>
  <option value='few'>FEW</option>
  <option value='api'>API</option>
  <option value='admin'>ADMIN</option>

</select>
<br><br>
<h4>Choose the package filename to deploy</h4>
<br>
<select name='filetoload'>
<option value=''>-----</option>";
$dirPath = dir('/var/www/html/PKI');
$imgArray = array();
while (($file = $dirPath->read()) !== false)
{
  if ((substr($file, -6)=="tar.gz"))
#|| (substr($file, -3)=="php") || (substr($file, -2)=="sh"))
  {
     $imgArray[ ] = trim($file);
  }
}
$dirPath->close();
sort($imgArray);
$c = count($imgArray);
for($i=0; $i<$c; $i++)
{
    echo "<option value=\"" . $imgArray[$i] . "\">" . $imgArray[$i] . "\n";
}
echo "
</select>

<br><br>
<h4> Choose the environment to deploy the package: </h4>

<br>
<select name='environment'>
  <option value=''>-----</option>
  <option value='mob01-us32c9.us.infra'>UAT</option>
  <option value='prod'>PROD - not working</option>

</select>
<br><br>
<input class='btn btn-primary' type='submit' value='Deploy'>
</form>
<br><br>
";

echo "<h2> SYNC </h2>";
echo "
<br>
<h4>Choose the type of package to sync</h4>
<br>
<select name='pack_sync'>
  <option value=''>-----</option>
  <option value='/var/www/html/'>FEW</option>
  <option value='/var/www/api/'>API</option>
  <option value='/var/www/admin/'>ADMIN</option>
</select>
<br> <br>

<h4>Choose the kind of sync to perform</h4>
<br>
<select name='type_sync'>
  <option value=''>-----</option>
  <option value='test'>TEST</option>
  <option value='write'>REAL SYNC</option>
</select>
<br><br>
<h4>Choose the environment where to perform the sync</h4>
<br>
<select name='sync_env'>
  <option value=''>-----</option>
  <option value='mob01-us32c9.us.infra'>UAT</option>
  <option value='prod'>PROD - not working</option>

</select>
<br><br>
<input class='btn btn-primary' type='submit' value='Sync'>
</form>
<br><br>
";


echo "<h2> Open manually a log</h2><br><br>";

echo "<form action='' method='post' name='form'>

<select name='getfile'>
<option value=''>-----</option>
<option value='logging.txt'>Deployment Descriptive log</option>
<option value='test_sync_log.txt'>Sync test log</option>
<option value='rsync.log'>Sync procedural log</option>
</select>
<br><br>
<input class='btn btn-primary' type='submit' value='Open'>

</form>

</div>";

echo "<div style='float:right; width: 68%'>";



function getMyFile($getfile) {
  exec("sudo scp -i /home/appsupp/.ssh/id_rsa appsupp@mob01-us32c9.us.infra:/home/appsupp/$getfile /var/www/html/PKI/");

  echo "<br>File downloaded <br>";


  echo "-------------";

  echo "<br>File opened <br>";

  $file = popen("tail -30 /var/www/html/PKI/$getfile 2>&1", "r");
  while(! feof($file))
    {
    echo fgets($file). "<br />";
    }

  fclose($file);
}



if(isset($_POST['pack_sync']) || isset($_POST['type_sync']) || isset($_POST['sync_env'])) {

if(($_POST['pack_sync'] != '') && ($_POST['type_sync'] != '') && ($_POST['sync_env'] != '')) {




$pack_sync=$_POST['pack_sync'];
$type_sync=$_POST['type_sync'];
$sync_env=$_POST['sync_env'];


$pack_sync=escapeshellarg($pack_sync);

$type_sync=escapeshellarg($type_sync);


exec("sudo ssh -i /home/appsupp/.ssh/id_rsa appsupp@$sync_env '/home/appsupp/rsync/sync.sh $pack_sync $type_sync'");
echo "Package $pack_sync synced as $type_sync to $sync_env <br>";


if($_POST['type_sync']=='test') {
  $getfile="test_sync_log.txt";
  getMyFile($getfile);
}
else if($_POST['type_sync']=='write') {
  $getfile="rsync.log";
  getMyFile($getfile);
}

}

else echo "Some option for the sync was not  selected";


}








#echo $_POST['getfile'] ;
if(isset($_POST['getfile'])) {
$getfile=$_POST['getfile'];
getMyFile($getfile);
}






if(isset($_POST['package']) || isset($_POST['filetoload']) || isset($_POST['environment'])) {

#$filetoload="/home/appsupp/HCUS-MIDDLEWARE-API_2017-06-12_19-55-50.tar.gz";
if(($_POST['package'] != '') && ($_POST['filetoload'] != '') && ($_POST['environment'] != '')) {

$filetoload=$_POST['filetoload'];


$pack=$_POST['package'];
$filetoload=escapeshellarg($filetoload);

$pack=escapeshellarg($pack);

$environment=$_POST['environment'];


exec("sudo scp -i /home/appsupp/.ssh/id_rsa /var/www/html/PKI/$filetoload appsupp@$environment:/home/appsupp");
echo "File $filetoload loaded into $environment <br><br>";

sleep(20);

exec("sudo ssh -i /home/appsupp/.ssh/id_rsa appsupp@$environment 'bash -s' < deploy.sh /home/appsupp/$filetoload $pack");
echo "Package $filetoload deployed as $pack to $environment<br> ";

$getfile="logging.txt";
getMyFile($getfile);


}

else echo "Some option for the deployment was not selected";


}



?>
