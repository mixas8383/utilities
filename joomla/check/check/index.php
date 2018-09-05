<?php
	define( '_JEXEC', 1 );
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	define( 'DS', DIRECTORY_SEPARATOR );
	$base = str_replace('/'.basename(dirname(__FILE__)), '', dirname(__FILE__));
	define('JPATH_BASE', $base);
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Last Updated Files</title>
		<style type="text/css">
			body
			{
				font-size: 12px;
				color: #333;
			}
			td,th,.date
			{
				border:1px solid #CCCCCC
			}
		</style>
	</head>
	<body>
		<?php 	

	if(isset($_REQUEST['do']) && isset($_REQUEST['date']) && !empty($_REQUEST['date']))
	{
		require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
		require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );	
	
		jimport('joomla.filesystem.archive');
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		jimport( 'joomla.filesystem.archive' );
	
		$ver =  phpversion();
		if(substr($ver,0,1) < '5')
		{
			$Resp['error'][] = 'PHP Version ERROR!!!';
			die(json_encode($Resp));
		}
		$dir = str_replace('/', '_', $_SERVER['HTTP_HOST']);
		$dir = str_replace('.', '_', $dir);
		$archive_ext = 'bz2'; //tar, gz, bz2
		$date = $_REQUEST['date'];
		$archive = '/check/tmp/'.$dir.'_'.$date.'.'.$archive_ext;
		$archive_path = JPATH_BASE.DS.'check'.DS.'tmp'.DS.$dir.'_'.$date.'.'.$archive_ext;

		require_once( './include/ChangedSinceFiles.php' );
		require_once( './include/DeepDir.php' );
		require_once( './include/File.php' );
		
		require_once( './include/DateTime.php' );
		global $path_exc;
		$path_exc = array();
		$path_exc[] = JPATH_BASE.DS.'error';
		$path_exc[] = JPATH_BASE.DS.'tmp';
		$path_exc[] = JPATH_BASE.DS.'cache';
		$path_exc[] = JPATH_BASE.DS.'check';
		$path_exc[] = JPATH_BASE.DS.'logs';
		$path_exc[] = JPATH_BASE.DS.'images';
		$path_exc = array_flip($path_exc);
	
		//------------ configuration -------------
		$params = array();
		$params['source_dir'] = JPATH_BASE;
		$params['dest_dir_root'] = JPATH_BASE.DS.'check'.DS.'updated';
		$params['changed_since'] = $_REQUEST['date']; // year.month.day hour:minutes:seconds
		$params['debug_mode'] = $_REQUEST['debug']; // showing log messages
		//----------------------------------------
		
		$changedSinceFiles = new ChangedSinceFiles( $params );
		$changedSinceFiles->setDeepDir( new DeepDir() );

		$changedSinceFiles->setFile( new File() );
		$changedSinceFiles->setDateTime( new DateTimeC() );
			?>
				<table cellspacing="2" cellpadding="2" align="center" width="700" style="border:1px solid #CCCCCC" >
					<tr>
						<td>
						<div style="padding:10px; display:block;line-height:25px;"><?php $changedSinceFiles->doIt();?></div>
<strong style="padding:10px; display:block;text-align:center;">დავალება შესრულებულია!</strong>
						<?php
							if($_REQUEST['archive'])
							{
								$d = str_replace(' ', '-', $_REQUEST['date']);
								$d = str_replace(':', '-', $d);
								$d = str_replace('-', '-', $d);
								$d = 'changed-'.$d;
								$archives = JFolder::Files( JPATH_BASE.DS.'check'.DS.'tmp', '.'.$archive_ext, true, true);
								foreach($archives as $a)
								{
									JFile::delete($a);
								}
								ini_set('max_execution_time', 15000);
								ini_set('memory_limit', '128M');
								$for_archive = array();
								$for_archive = JFolder::Files($params['dest_dir_root'].DS.$d, '.', false, true);
								$Folders = JFolder::Folders($params['dest_dir_root'].DS.$d, '.', false, true);
								foreach ($Folders as $fs)
								{
									$files = JFolder::Files($fs, '.', true, true);
									foreach ($files as $f)
									{
										$for_archive[] = $f;
									}
									unset($files);
								}
								$c = JArchive::create($archive_path, $for_archive, $archive_ext);
								echo '<strong style="padding:10px; display:block;text-align:center;">
											<a href="'.$archive.'" target="_blank">ჩამოტვირთეთ არქივი!</a>
											</strong>';
							}
						?>
					</td>
				</tr>
			</table>
			<br />
			<br />
	<?php
	}
	?>
			<form id="form1" name="form1" method="post" action="">
				<input type="hidden" name="do" value="1" />
				<table cellspacing="2" cellpadding="2" align="center" width="700" style="border:1px solid #CCCCCC" >
					<tr>
						<td colspan="3" style="text-align: center"><strong style="padding:10px; display:block;">შეცვლილი ფაილების ექსპორტის სისტემა</strong></td>
					</tr>
					<tr>
						<td><strong>თარიღი</strong></td>
						<td style="text-align: left">
						<input type="text" name="date" id="date" class="date" value="<?php echo date('Y-m-d 00:00:00');?>" /></td>
						<td style="text-align: left">მიუთითეთ თარიღი ფორმატით: 2011-05-05 00:00:00</td>
					</tr>
					<tr>
						<td><strong>დეტალური ინფორმაცია</strong></td>
						<td style="text-align: left"><p>
							<label>
								<input type="radio" name="debug" value="1" id="RadioGroup1_0" />
								დიახ</label>
							<label>
								<input name="debug" type="radio" id="RadioGroup1_1" value="0" checked="checked" />
								არა</label>
							<br />
						</p></td>
						<td style="text-align: left">დეტალური ინფორმაციის ჩვენება</td>
					</tr>
					<tr>
						<td style="text-align:right;"><strong> დაარქივება და ჩამოტვირთვა</strong></td>
						<td style="text-align: left"><p>
							<label>
								<input name="archive" type="radio" id="RadioGroup1_5" value="1" checked="checked" />
								დიახ</label>
							<label>
								<input name="archive" type="radio" id="RadioGroup1_4" value="0" />
								არა</label>
							<br />
						</p></td>
						<td style="text-align: left">&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td style="text-align: left"><input type="submit" name="button" id="button" value="გაშვება" />
						<input type="reset" name="button2" id="button2" value="განულება" /></td>
						<td style="text-align: left">&nbsp;</td>
					</tr>
				</table>
			</form>
		</body>
	</html>
