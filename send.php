<html lang="en">
<head>
<link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css" integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
</head>
<body>
<?php

function rmdir_recursive($dir) {
	foreach(scandir($dir) as $file) {
		if ('.' === $file || '..' === $file) continue;
		if (is_dir("$dir/$file")) rmdir_recursive("$dir/$file");
		else unlink("$dir/$file");
	}
	rmdir($dir);
}

function generateRandomString($length = 10) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}

$C_MIN_INTERVAL=20000;
$C_MAX_INTERVAL=10000000;

// VARIABLES CHECK

$coord=$_POST["coordinates"];
$chr=explode(':', $coord)[0];
$be=explode(':', $coord)[1];
$interval_start=intval(preg_replace("/[,]/", "", explode('-', $be)[0]));
$interval_end=intval(preg_replace("/[,]/", "", explode('-', $be)[1]));
if(($interval_end-$interval_start)<$C_MIN_INTERVAL) {
	echo "<div style=\"color: red; text-align: center;\">Bad chromosome interval (must be longer than ".strval($C_MIN_INTERVAL)." bp)</div>";
	exit();
}
if(($interval_end-$interval_start)>$C_MAX_INTERVAL) {
	echo "<div style=\"color: red; text-align: center;\">Too long interval (longer than ".strval($C_MAX_INTERVAL)." bp).<br>Contact authors if you have such a work to be done.</div>";
	exit();
}

$model_path=$_POST["model"];

$f_pointer=fopen("trained_models_for_web_3DPredictor/models_description.txt","r");
$cap=fgetcsv($f_pointer,0,"\t");
$resolution=0;
$rnaseq_model_pre="";
while(!feof($f_pointer)){
	$ar=fgetcsv($f_pointer,0,"\t");
	if($ar[1]==$_POST["model"]) {
		$forbidden=explode(",", $ar[4]);
		$chr_number=substr($chr, 3);
		$resolution=(int)$ar[3];
		$rnaseq_model_pre=$ar[6];
		if(in_array($chr_number, $forbidden)) {
			echo "<div style=\"color: red; text-align: center;\">Bad chromosome number (used in training), please choose another model</div>";
			exit();
		}
	}
}

$email=$_POST["email"];
$genome_assembly=$_POST["genome_version"];

// FILES UPLOAD

$UID=generateRandomString(16);
$uploaddir = "./_temp/".$UID."/";
mkdir($uploaddir, 0777);

if ($_POST["rna_upload_type"]=="local") {
	if (!move_uploaded_file($_FILES['rna_local']['tmp_name'], $uploaddir."rna_seq.csv")) {
		echo "<div style=\"color: red; text-align: center;\">Upload local RNA-Seq file is failed</div>";
		rmdir_recursive($uploaddir);
		exit();
	}
}

if ($_POST["ctcf_upload_type"]=="local") {
	if (!move_uploaded_file($_FILES['ctcf_local']['tmp_name'], $uploaddir."ctcf.csv")) {
		echo "<div style=\"color: red; text-align: center;\">Upload local CTCF file is failed</div>";
		rmdir_recursive($uploaddir);
		exit();
	}
}

// ftp

if ($_POST["rna_upload_type"]=="ftp") {
	$upl_cmd = 'wget -q -t 3 -O '.$uploaddir.'rna_seq.csv -nd -l 1 '.$_POST["rna_ftp"];
	$output = exec($upl_cmd, $output, $exit_code);
	if ($exit_code!=0) {
		echo "<div style=\"color: red; text-align: center;\">Upload RNA-Seq file by FTP is failed</div>";
		rmdir_recursive($uploaddir);
		exit();
	}
}

if ($_POST["ctcf_upload_type"]=="ftp") {
	$upl_cmd = 'wget -q -t 3 -O '.$uploaddir.'ctcf.csv -nd -l 1 '.$_POST["ctcf_ftp"];
	$output = exec($upl_cmd, $output, $exit_code);
	if ($exit_code!=0) {
		echo "<div style=\"color: red; text-align: center;\">Upload CTCF file by FTP is failed</div>";
		rmdir_recursive($uploaddir);
		exit();
	}
}

// FILES CHECK

$command = './_pyenv/bin/activate; ./_pyenv/bin/python3 check_file_formats.py "'.$uploaddir.'rna_seq.csv" "'.$uploaddir.'ctcf.csv" 2>&1';
$output = exec($command, $output, $exit_code);
if ($exit_code!=0) {
	echo "<div style=\"color: red; text-align: center;\">".$output."</div>";
	rmdir_recursive($uploaddir);
	exit();
}

// CHECK QUEUE

$queue_check = exec('echo $(($(screen -ls | wc -l) - 2))', $queue_check, $exit_code);
if ($queue_check>5) {
	echo "<div style=\"color: red; text-align: center;\">Too many processes queued. Please try again later.</div>";
	rmdir_recursive($uploaddir);
	exit();
}

// EXEC PIPELINE

$cmd = 'screen -dm ./pipeline.sh '.$uploaddir.' '.$genome_assembly.' '.$chr.' '.$interval_start.' '.$interval_end.' '.$model_path.' '.$email.' '.$UID.' '.$resolution.' '.$rnaseq_model_pre;
shell_exec($cmd);

echo "<script type=\"text/javascript\">alert('Good. Looks like most things are correct. Please wait for email :)');</script>";
?>
</body>
</html>
