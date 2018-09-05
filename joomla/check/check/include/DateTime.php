<?php

// this class need refactoring,
// hope sometime :) will do it
//
// Ilya N.

/*

  Changes:
      2004/03/20 - change format to YEAR-MM-DD HH:MM:SS

*/

class DateTimeC{

  var $dateTimeStr;
  var $dateTimeInt;

  var $format;
  var $delim1;
  var $delim2;

  function DateTimeC(){
    $this->dateTime = '';
    $this->format = 'Y-m-d H:i:s';
    $this->delim1 = '-';
    $this->delim2 = ':';
  }

  function initByNow(){
    $this->setDateTimeStr( $this->getNowStr() );
  }

  function getNowStr(){
    return date( $this->format );
  }

  function setDateTimeStr( $dateTimeStr ){
    $this->dateTimeStr = $dateTimeStr;
    $this->convertToInt();
  }

  function setDateTimeInt( $dateTimeInt ){
    $this->dateTimeInt = $dateTimeInt;
    $this->convertToStr();
  }

  function getDateTimeStr(){
    return $this->dateTimeStr;
  }

  function getDateTimeInt(){
    return $this->dateTimeInt;
  }

  // private
  function convertToInt(){
    list( $day, $time ) = explode( ' ', $this->dateTimeStr );
    list( $y, $m, $d ) = explode( $this->delim1, $day );
    list( $h, $i, $s ) = explode( $this->delim2, $time );
    $this->dateTimeInt = mktime( $h, $i, $s, $m, $d, $y );
  }

  function convertToStr(){
    $this->dateTimeStr = date( $this->format, $this->dateTimeInt );
  }

  function isGreater( $dt, $secondInterval = 0 ){
    return ( $this->dateTimeInt > ( $dt->getDateTimeInt() + $secondInterval ) );
  }
}