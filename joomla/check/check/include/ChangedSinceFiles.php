<?php

/*  

  ChangedSinceFiles
  Author: Ilya Nemihin ( nemilya@mail.ru )
  Year: 2004

*/

class ChangedSinceFiles
{
  var $_deepDir;
  var $_filteredFiles;

  function ChangedSinceFiles( $params ){
    $this->_params = $params;
  }

  function setDeepDir( $deepDir ){
    $this->_deepDir = $deepDir;
  }

  function setFile( $file ){
    $this->_file = $file;
  }

  function setDateTime( $dateTime ){
    $this->_dateTime = $dateTime;
  }

  function doIt(){
    $this->_filteredFiles = array();
    $this->_log( 'ChangedSinceFiles started' );
    $this->_log( 'doIt...' );
    $this->_init();
    $this->_loadFiles();
    $this->_filterFiles();
    $this->_createDest();
  }

  // -----------------------------------------------------

  function _init(){
    $this->_deepDir->setDir( $this->_params['source_dir'] );
    $this->_dateTime->setDateTimeStr( $this->_params['changed_since'] );
  }

  function _loadFiles(){
    $this->_log( '_loadFiles' );
    $this->_deepDir->load();
    $this->_log( '_loaded files:' );
  }


  function _filterFiles(){
    // filter all files from source dir
    // by condition for filtering
    foreach( $this->_deepDir->files as $file_path ){
      if ( $this->_isFileNeeded( $file_path ) ){
        $this->_filteredFiles[] = $file_path;
      }
    }
    $this->_log( 'filtered files:' );
    $this->_log( $this->_filteredFiles );
  }

  function _createDest(){
    // going to filtered files
    // and create same tree
    // at dest folder
    foreach( $this->_filteredFiles as $file_path ){
      $this->_createDestFile( $file_path );
    }
  }

  // ----------------------------------------------------

  function _isFileNeeded( $file_path ){
    return $this->_filterCondition( $file_path );
  }

  function _filterCondition( $file_path ){
    $this->_log( '_filterCondition for '.$file_path );
    $fileDateTime = new DateTimeC();
    $fileDateTime->setDateTimeInt( $this->_file->getModifyTime( $file_path ) );
    $this->_log( '$fileDateTime: '.$fileDateTime->dateTimeInt );
    $this->_log( '$this->_dateTime: '.$this->_dateTime->dateTimeInt );
    // if file modification is greater that 'changed_since' param
    return $fileDateTime->isGreater( $this->_dateTime );
  }

  // -----------------------------------------------------


  function _createDestFile( $file_path ){
    // create in dest directory 
    // file
    $this->_log( '_createDestFile' );
    $file_path_rel = $this->_extractFilePathRel( $file_path );
    $file_path_dest = $this->_params['dest_dir_root'].'/'.$this->_newDestFolderName().$file_path_rel;

    // add new descr filder name
    $file_path_rel = $this->_newDestFolderName().'/'.$file_path_rel;
    $parts = explode( '/', $file_path_rel);

    $growing_path = $this->_params['dest_dir_root'];
    // we start from $growing_path and add
    // parts of $parts array and create directories
    // and create file at end
    for( $i=0; $i<count($parts); $i++){
      $val = $parts[$i];
      if ( $val == '' ) continue;

      $growing_path .= '/'.$val;
      // checking for last
      if ( $i == count( $parts )-1 ){
        // we create file, because this is last item
        if ( $growing_path == $file_path_dest ){
          $this->_createFileItem( $file_path,  $file_path_dest ); // from, to
        }else{
          echo 'Error dosnt equal,';
          echo '<br />';
          echo ' file_path_dest='.$file_path_dest;
          echo '<br />';
          echo 'growing_path='.$growing_path;
		  echo '<br />';
        }

      }
      else{
        // create dir
        $this->_createDirItem( $growing_path );
      }
    }
  }

  function _extractFilePathRel( $file_path ){
    // used $this->_params['source_dir'];
    //
    return substr( $file_path, strlen( $this->_params['source_dir'] ) );
  }

  function _createFileItem( $path_source, $path_dest ){
    $this->_log( '_createFileItem '. $path_source . ' ' . $path_dest );
    if ( ! file_exists( $path_dest) ){
      $this->_file->copyFile( $path_source, $path_dest );
    }
    else{
      echo 'Error, desct file already exists - '.$path_dest;
      echo '<br />';
    }
  }

  function _createDirItem( $dirPath ){
    $this->_log( '_createDirItem '. $dirPath );
    if ( ! file_exists( $dirPath ) ){
      mkdir( $dirPath, 0777 );
    }
  }

  function _newDestFolderName(){
    $str = $this->_dateTime->getDateTimeStr();
    $str = strtr( $str, ' .:/', '------' );
    return 'changed-'.$str;
  }

  // -----------------------------------------------------
  function _log( $text ){
    if ( $this->_params['debug_mode'] ){
      if ( is_array( $text ) ){
        print_r( $text );
        echo "\n";
		echo '<br />';
      }
      else{
        echo $text."\n";
		echo '<br />';
      }
    }
  }

}


?>