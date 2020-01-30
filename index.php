<html lang="en">

<head>

<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
<script>
function MenuClick(item) {
document.getElementById('predictor').style.display = (item == 'tool') ? 'block' : 'none';
document.getElementById('howto_content').style.display = (item == 'howto') ? 'block' : 'none';
document.getElementById('about_content').style.display = (item == 'about') ? 'block' : 'none';
document.getElementById(item).classList.add('pure-menu-selected');
if (item != 'tool') { document.getElementById('tool').classList.remove('pure-menu-selected'); }
if (item != 'howto') { document.getElementById('howto').classList.remove('pure-menu-selected'); }
if (item != 'about') { document.getElementById('about').classList.remove('pure-menu-selected'); }
}
</script>

<meta charset="utf8">
<meta name="description" content="Bioinformatics ML-based tool for chromatin structure prediction on RNA-Seq and CTCF data.">
<meta name="keywords" content="bioinformatics, chromatin structure, RNA-Seq, CTCF, HiC, 3D, ML, biology, online, tool">

<link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css" integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:300">
<link rel="stylesheet" href="https://purecss.io/css/main.css">

<title>3DPredictor Online | A tool for 3D chromatin structure prediction</title>

</head>

<body onload="document.getElementById('predictor').reset();">
<table width=100% height=100%><tr><td align="center">
<div style="width: 700px; height:800px;">

<!-- HEADER -->
<div class="header" style="border-bottom: 0px;">
<h1><span style="display: inline-block; color: rgb(223, 117, 20); font-weight: bold; font-size:118%;">3D</span>Predictor</h1>
<h2>A tool for 3D chromatin structure prediction.</h2>
</div>

<!-- MENU -->
<div class="pure-menu pure-menu-horizontal" style="height: 30px;">
<ul class="pure-menu-list">
<li id="tool" class="pure-menu-item pure-menu-selected" onclick="MenuClick('tool');"><a href="#" class="pure-menu-link">Tool</a></li>
<li id="howto" class="pure-menu-item" onclick="MenuClick('howto');"><a href="#" class="pure-menu-link">How To</a></li>
<li id="about" class="pure-menu-item" onclick="MenuClick('about');"><a href="#" class="pure-menu-link">About</a></li>
</ul>
</div>

<!-- PREDICTOR BLOCK -->
<form id="predictor" class="pure-form pure-form-aligned" enctype="multipart/form-data" action="send.php" onsubmit="ChangeBack(window.frames['result'].document);" method="post" target="result">
<fieldset style="padding: 2em;">

<!-- RNA-Seq -->
<div class="pure-control-group" title="RNA Seq Data. See Howto for details">
<label for="rna-seq_line" class="pure-u-1-3" style="text-align: left;">RNA Seq Data</label>
<span name="rna-seq_line" style="display: inline-block; text-align: left;" class="pure-radio pure-input-1-3">
<input id="rna_local_option" type="radio" name="rna_upload_type" value="local" onchange="document.getElementById('rna_local').style.display = 'inline-block'; document.getElementById('rna_ftp').style.display = 'none';" checked> Local&emsp;
<input id="rna_ftp_option" type="radio" name="rna_upload_type" value="ftp" onchange="document.getElementById('rna_local').style.display = 'none'; document.getElementById('rna_ftp').style.display = 'inline-block';"> FTP&emsp;
</span>
<input id="rna_local" type="file" name="rna_local" style="display: inline-block; height: 2.5em;" class="pure-input-1-3">
<input id="rna_ftp" type="url" name="rna_ftp" placeholder="http://your.site/file.csv" style="display: none;  height: 2.5em;" class="pure-input-1-3">
</div>

<!-- CTCF -->
<div class="pure-control-group" title="CTCF Data. See Howto for details">
<label for="ctcf_line" class="pure-u-1-3" style="text-align: left;">CTCF Data</label>
<span name="ctcf_line" style="display: inline-block; text-align: left;" class="pure-radio pure-input-1-3">
<input id="ctcf_local_option" type="radio" name="ctcf_upload_type" value="local" onclick="document.getElementById('ctcf_local').style.display = 'inline-block'; document.getElementById('ctcf_ftp').style.display = 'none';" checked> Local&emsp;
<input id="ctcf_ftp_option" type="radio" name="ctcf_upload_type" value="ftp" onclick="document.getElementById('ctcf_local').style.display = 'none'; document.getElementById('ctcf_ftp').style.display = 'inline-block';"> FTP&emsp;
</span>
<input id="ctcf_local" type="file" name="ctcf_local" style="display: inline-block; height: 2.5em;" class="pure-input-1-3">
<input id="ctcf_ftp" type="url" name="ctcf_ftp" placeholder="http://your.site/file.csv" style="display: none;  height: 2.5em;" class="pure-input-1-3">
</div>

<!--Genome version-->
<div class="pure-control-group" title="CTCF genome version">
<label for="genome_version" class="pure-u-1-3" style="text-align: left;">Genome</label>
<select id="genome_version" name="genome_version" class="pure-input-2-3" style="height: 2.5em;">
<?php
$f_pointer=fopen("genome_versions.txt","r");
$cap=fgetcsv($f_pointer,0,"\t");
while(!feof($f_pointer)){
	$ar=fgetcsv($f_pointer,0,"\t");
	if($ar[0]!="") { 
		echo "<option value=\"".$ar[1]."\">".$ar[0]."</option>"; 
	}
}
?>
</select>
</div>

<!-- Coordinates -->
<div class="pure-control-group" title="Coordinates">
<label for="coordinates" class="pure-u-1-3" style="text-align: left;">Coordinates</label>
<input type="text" id="coordinates" name="coordinates" maxlength="100" placeholder="chr17:29,421,945-29,709,134" pattern="^chr[\w]+:[\d]{1,3}(,[\d]{3})*-[\d]{1,3}(,[\d]{3})*$" class="pure-input-2-3" style="height: 2.5em;" required>
</div>

<!-- Model -->
<div class="pure-control-group" title="Model">
<label for="model" class="pure-u-1-3" style="text-align: left;">Model</label>
<select id="model" name="model" class="pure-input-2-3" style="height: 2.5em;">
<?php
$f_pointer=fopen("trained_models_for_web_3DPredictor/models_description.txt","r");
$cap=fgetcsv($f_pointer,0,"\t");
while(!feof($f_pointer)){
	$ar=fgetcsv($f_pointer,0,"\t");
	if($ar[0]!="") {
		echo "<option value=\"".$ar[1]."\">".$ar[0]."</option>";
	}
}
?>
</select>
</div>

<div style="height: 2em;"></div>

<!-- Email -->
<script>
function ChangeBack(_object) {
_object.body.style.background = "#ffffff url('motylator.gif') no-repeat center top"
}
</script>
<div class="pure-control-group">
<input type="email" name="email" class="pure-input-rounded pure-input-1-3" maxlength="255" placeholder="Your email" pattern="^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2, 4}$" required>
<input type="submit" class="pure-button pure-input-rounded pure-button-primary pure-input-1-6" value="Get Prediction!">
</div>

<!-- Result -->
<div class="pure-control-group">
<iframe name="result" src="" style="height: 3.5em; width: 100%; " frameborder="0"></iframe>
</div>

<!-- Fork us -->
<div>Fork us on <a href="https://github.com/labdevgen/3Dpredictor" target="_blank">GitHub</a>!</div>
</fieldset>
</form>

<!-- HOWTO BLOCK -->
<div id="howto_content" style="display: none; padding: 2em; text-align: left;">
<?php
$howto = file_get_contents('howto.txt');
echo $howto;
?>
</div>

<!-- ABOUT BLOCK -->
<div id="about_content" style="display: none; padding: 2em; text-align: left;">
<h3>Article</h3>
<p><i>Polina Belokopytova, Evgeniy Mozheiko, Miroslav Nuriddinov, Daniil Fishman, Veniamin Fishman.</i> Quantitative prediction of enhancer-promoter interactions</p>
<h4>Abstract</h4>
<p>Recent experimental and computational efforts provided large datasets describing 3-dimensional organization of mouse and human genomes and showed interconnection between expression profile, epigenetic status and spatial interactions of loci.
These interconnections were utilized to infer spatial organization of chromatin, including enhancer-promoter contacts, from 1-dimensional epigenetic marks.
Here we showed that predictive power of some of these algorithms is overestimated due to peculiar properties of biological data.
We proposed an alternative approach, which gives high-quality predictions of chromatin interactions using only information about gene expression and CTCF-binding.
Using multiple metrics, we confirmed that our algorithm could efficiently predict 3-dimensional architecture of normal and rearranged genomes.
</p>
<p><a href="https://doi.org/10.1101/541011" target="_blank">Read the article</a>&emsp;|&emsp;<a href="mailto:minja-f@ya.ru?subject=3DPredictor Feedback&body=Dear Mr. Fishman," target="_blank">Contact authors</a></p>
<h3>Page Development &amp; Design</h3>
<p>Polina Belokopytova, Emil Valeev</p>
<p><a href="mailto:regnveig@yandex.ru?subject=3DPredictor Online Feedback, session ID: <?php echo (rand(10000, 32767))?>" target="_blank">Contact</a></p>
</div>

</div>
</td></tr></table>

</body>
</html>
