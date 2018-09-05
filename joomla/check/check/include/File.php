<?php

/*

  Changes:
      2004/03/20 - added function 'copyFile'
                 - changed function 'getModifyTime' added param


*/

class File{

  var $fileName;
  var $data;

  var $error;

  function init(){
    $this->error = '';
  }

  function setFileName( $name ){
    $this->fileName = $name;
    $this->init();
  }

  function setData( $data ){
    $this->data = $data;
  }

  function load(){
    if ( file_exists($this->fileName ) ){
      $this->data = join( '', file( $this->fileName ) );
      return true;
    }
    else{
      $this->error = 'file not exists: '.$this->fileName;
      return false;
    }
  }

  function save(){
    $f = fopen( $this->fileName, 'w' );
    fputs( $f, $this->data );
    fclose( $f );
  }

  /* dont use
  function delete(){
    unlink( $this->fileName );
  }
  */

  function getData(){
    return $this->data;
  }

  function getModifyTime( $file_path = '' ){
    if ( empty( $file_path ) ){
      return filemtime( $this->fileName );
    }
    else{
      return filemtime( $file_path );
    }
  }

  function copyFile( $from, $to ){
    copy( $from, $to );
  }

} // end class

?>