<?php 
require_once(dirname(__FILE__) . '/db.php');
require_once(dirname(__FILE__) . '/common_functions.php');

// remove all session variables
@ob_start();
session_start();
session_destroy();
header("Location: ../spear");

//---------------------------------------------------------------

//Clear junk tracker images
$mail_template_ids = $mbfs = $attachment_file_ids =  [];
$doc = new DOMDocument();

$result = mysqli_query($conn, "SELECT mail_template_id,mail_template_content,attachment,timage_type FROM tb_core_mailcamp_template_list")->fetch_all(MYSQLI_ASSOC);

foreach ($result as $row) {
	if($row['timage_type'] == 2);
    	array_push($mail_template_ids, $row['mail_template_id']);

   	foreach (json_decode($row['attachment'],true) as $att)
   		if(!empty($att['file_id']))
   			array_push($attachment_file_ids, $att['file_id']);

	@$doc->loadHTML($row['mail_template_content']);
	$tags = $doc->getElementsByTagName('img');
	foreach ($tags as $tag) {
	    $src = $tag->getAttribute('src');
	    $queries = getQueryValsFromURL($src);
	    if(!empty($queries['mbf']))
	    	array_push($mbfs, $queries['mbf']);
	}
}

$files = glob("uploads/timages/*.timg");	//tracker images - based on tid
foreach ($files as $file)
  if(!in_array(basename($file,'.timg'), $mail_template_ids))
  	unlink($file);

$files = glob("uploads/attachments/*.att");		//usaved attachments - based on attachment ids
foreach ($files as $file)
  if(!in_array(basename($file,'.att'), $attachment_file_ids))
  	unlink($file);

$files = glob("uploads/attachments/*.mbf");		//unsaved mail body files - based on img src url with mbd parameter
foreach ($files as $file){
    if(!in_array(explode("_", basename($file,'.mbf'))[1], $mbfs))	//eg: if 1611333260
  		unlink($file);
}


//Delete junk payload file uploads
$pl_ids = [];
$result = mysqli_query($conn, "SELECT pl_id FROM tb_pl_list")->fetch_all(MYSQLI_ASSOC);
foreach ($result as $row)
  array_push($pl_ids, $row['pl_id']);

$files = glob("payloads/uploads/*.pdata");
foreach ($files as $file)
  if(!in_array(basename($file,'.pdata'), $pl_ids))
    unlink($file);

//Delete junk sniperhost file uploads
$file_ids = [];
$result = mysqli_query($conn, "SELECT hf_id FROM tb_hf_list")->fetch_all(MYSQLI_ASSOC);
foreach ($result as $row)
  array_push($file_ids, $row['hf_id']);

$files = glob("sniperhost/hf_files/*.hfile");
foreach ($files as $file)
  if(!in_array(basename($file,'.hfile'), $file_ids))
    unlink($file);

//Delete junk sniperhost text uploads
$file_ids = [];
$result = mysqli_query($conn, "SELECT ht_id FROM tb_ht_list")->fetch_all(MYSQLI_ASSOC);
foreach ($result as $row)
  array_push($file_ids, $row['ht_id']);

$files = glob("sniperhost/ht_files/*.ptdata");
foreach ($files as $file)
  if(!(in_array(basename($file,'_in.ptdata'), $file_ids) || in_array(basename($file,'_out.ptdata'), $file_ids)))
    unlink($file);

//Delete public dashboard access table entries for deleted campaigns
$file_ids = $arr_clearList = [];
$result = mysqli_query($conn, "SELECT campaign_id FROM tb_core_mailcamp_list")->fetch_all(MYSQLI_ASSOC);
foreach ($result as $row)
  array_push($file_ids, $row['campaign_id']);

$result = mysqli_query($conn, "SELECT tracker_id FROM tb_core_web_tracker_list")->fetch_all(MYSQLI_ASSOC);
foreach ($result as $row)
  array_push($file_ids, $row['tracker_id']);

$result = mysqli_query($conn, "SELECT ctrl_ids FROM tb_access_ctrl")->fetch_all(MYSQLI_ASSOC);
foreach ($result as $row){
  $ctrl_ids = json_decode($row['ctrl_ids']);

  if(!in_array($ctrl_ids[0], $file_ids))
    deleteEntry($conn,json_encode($ctrl_ids));
  else
  if(count($ctrl_ids)==2){
    if(!in_array($ctrl_ids[1], $file_ids))
      deleteEntry($conn,json_encode($ctrl_ids));
  }
}

function deleteEntry(&$conn,$ctrl_ids){
  $stmt = $conn->prepare("DELETE FROM tb_access_ctrl WHERE ctrl_ids = ?");
  $stmt->bind_param("s", $ctrl_ids);
  $stmt->execute();
  $stmt->close();
}

?>
