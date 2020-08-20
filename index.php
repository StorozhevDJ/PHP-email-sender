<HTML>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<!-- TinyMCE -->
<script type="text/javascript" src="tiny_mce3/tiny_mce.js"></script>
<script type="text/javascript">
	tinyMCE.init({
		// General options
		mode : "textareas",
		theme : "advanced",
		plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave,visualblocks",

		// Theme options
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft,visualblocks",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,

		// Example content CSS (should be your site CSS)
		content_css : "css/content.css",

		// Drop lists for link/image/media/template dialogs
		template_external_list_url : "lists/template_list.js",
		external_link_list_url : "lists/link_list.js",
		external_image_list_url : "lists/image_list.js",
		media_external_list_url : "lists/media_list.js",

		// Style formats
		style_formats : [
			{title : 'Bold text', inline : 'b'},
			{title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
			{title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
			{title : 'Example 1', inline : 'span', classes : 'example1'},
			{title : 'Example 2', inline : 'span', classes : 'example2'},
			{title : 'Table styles'},
			{title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
		],

		// Replace values for the template plugin
		template_replace_values : {
			username : "Some User",
			staffid : "991234"
		}
	});
</script>
<!-- /TinyMCE -->

<body bgcolor=Seashell>
<?php



/****************************************************
* Function for send mail
*/
function MySendMail($from, $file, $title,  $mess)
{
$Date = date( "Y.m.d" );
$Time = date( "H:i:s" );

// Read email list from file to mail array
$mail_array = file( $file );

$log_file = "mymail_list_log.txt";

// Open log file for read only and read content to buffer
if ( file_exists($log_file) )
   {
     $Fpr = fopen( $log_file,"r" );
     if ( $Fpr )
        {
          // Block and read file
          flock( $Fpr,LOCK_SH );
          $Bufer = fread( $Fpr,filesize($log_file) );
          flock( $Fpr,LOCK_UN );
          fclose( $Fpr );
          //Write new data to file (on top) then add old data from buffer (to bottom)
          $Fpw = fopen( $log_file,"w" );
          if ( $Fpw )
             {
               for($i=0; $i<10; ++$i )
                  {
                    if ( flock($Fpw,LOCK_EX) ) break;
                    else sleep(1);
                  }
               fwrite( $Fpw,$Date." ".$Time."	".$from."\r\n\"".$title."\"\r\n\"".$mess."\"\r\n\r\n\r\n");
               fwrite( $Fpw,$Bufer );
               flock( $Fpw,LOCK_UN );
               fclose( $Fpw );
         }
     else exit( ":-)" );
   }
   else exit( ":-)" );
  }
else exit( ":-)" );


print "Письмо \"".$title."\" отправлено c email ".$from."<br><table border=2><tr align=center><td><b>№</b></td><td><b>Кв.</b></td><td><b>ФИО</b></td><td><b>e-mail</b></td></tr>";


// Include php mailer class
require 'class.phpmailer.php'; 

$mail = new PHPMailer();
$mail->From = $from;

$mail->FromName = "ЖСК \"Рубин2\"";
$mail->IsHTML(true);        // Set mail content as HTML
$mail->CharSet= "UTF-8";
$mail->Subject = $title;
$mess=html_entity_decode($mess);

//If file was attached, then attach to mail
if(isset($_FILES['attachfile'])) {
	if($_FILES['attachfile']['error'] == 0){
	$mail->AddAttachment($_FILES['attachfile']['tmp_name'], $_FILES['attachfile']['name']);
	}
}
//If image file was attached, then attach and insert this image to mail body
if(isset($_FILES['attachimage'])) {
	if($_FILES['attachimage']['error'] == 0){
	if (!$mail->AddEmbeddedImage($_FILES['attachimage']['tmp_name'], 'my-attach', 'image.gif', 'base64', $_FILES['attachimage']['type'])) 
		 die ($mail->ErrorInfo); 
	$mess .= '<img src="cid:image.gif" border=0>';
	}
}
$mail->Body = $mess;
	
for($i=0;$i<count($mail_array);$i++)
	{
	list( $to,$kw,$FIO) = explode( "	",$mail_array[$i] );
	$mail->AddAddress($to, $FIO);

	// отправляем наше письмо 
	if (!$mail->Send()) die ('Mailer Error: '.$mail->ErrorInfo);
	else print "<tr><td>".($i+1).".</td><td>".$kw."</td><td>".$FIO."</td><td>".$to."</td></tr>";
	$mail->ClearAddresses();
	}
echo '</table><hr>';
echo 'Спасибо! Ваше письмо отправлено.';
echo $mess;
}



// If button "submit" pressed
if (!empty($_POST['submit']))
	{
		if($_POST['submit'])
		{
		$title = substr(htmlspecialchars(trim($_POST['title'])), 0, 1000);
		$mess =  substr(htmlspecialchars(trim($_POST['mess'])), 0, 1000000); 


		if ($_POST['list']=='rubin2')
			{
			$from='mail@rubin2.ru';
			//file with mail list 
			$file = "gsk_rubin2_mail.csv";
			}else
		if ($_POST['list']=='test')
			{
			$from='test@rubin2.ru';
			$file = "test.csv";
			}
		else
			{
			echo 'НЕ выбран список адресов для рассылки';
			exit();
			}
		MySendMail($from, $file, $title,  $mess);
		}
	}
else
	{
	?>
	<form action="" method=post enctype="multipart/form-data">
		<div align="center">
		Teма<br>
		<input type="text" name="title" size="40"><br>
		Сообщение<br>
		<textarea name="mess" rows="30" cols="100" style="width:100%"></textarea><br>
		<input type="radio" name="list" value="test"> Test<br>
		<input type="radio" name="list" value="rubin2"> ЖСК Рубин 2<br>
		Файл <input name="attachfile" type="file" size="28"><br />
		Изображение <input name="attachimage" type="file" size="28"><br />
		<input type="submit" name="submit" value="Отправить"><br>
		</div>
	</form>
	<?php
	}
?>
</body>
<!--Version 2014.06.24-->
</HTML>
