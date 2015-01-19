<?php if (!defined('APPLICATION')) exit();
/* Copyright 2015 Zachary Doll */
class UserGenerator {
  public static $Langs = array('cz' => 'Czech', 'de' => 'German', 'en' => 'English', 'lorem' => 'Latin', 'sk' => 'Slovak');
  public static $Gender = array('m', 'f', 'u');
  public $Names;
  public function __construct($lang = 'en') {
    if(!array_key_exists($lang, UserGenerator::$Langs)) {
      $lang = array_rand(UserGenerator::$Langs);
    }
    require("tables/$lang-names.php");
    $this->Names = LoadNames();
  }
  
  public function Get() {
    // Pick 2 names at random
    $Name1 = $this->Names[array_rand($this->Names)];
    $Name2 = $this->Names[array_rand($this->Names)];
    return array(
        'Name' => $Name1 . $Name2,
        'Email' => $Name1 . $Name2 . '@example.com',
        'Password' => $Name2 . $Name1,
        'PasswordMatch' => $Name2 . $Name1,
        'Gender1' => UserGenerator::$Gender[array_rand(UserGenerator::$Gender)],
        'TermsOfService' => '1');
  }
}
