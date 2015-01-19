<?php if (!defined('APPLICATION')) exit();

/**
 * Inspired by http://johno.jsmf.net/knowhow/ngrams/index.php
 */
class TextGenerator {
  public static $Langs = array('cz' => 'Czech', 'de' => 'German', 'en' => 'English', 'lorem' => 'Latin', 'sk' => 'Slovak');
  public $n;
  public $table;
  public $separator;
  
  public function __construct($lang = 'en') {
    $this->n = 2;
    $this->separator = ' ';
    if(!array_key_exists($lang, TextGenerator::$Langs)) {
      $lang = array_rand(TextGenerator::$Langs);
    }
    require("tables/$lang.php");
    $this->table = LoadTable();
  }
  
  public function Get($MinLength = '80', $MaxLength = '100') {
    $length = rand($MinLength, $MaxLength);
    $out = array();
    $ngram = array();
    $arr = $this->table;

    for ($i = 0; $i < $this->n - 1; $i++) {
      $target = array_rand($arr);
      $ngram[] = $target;
      $arr = &$arr[$target];
    }

    for ($i = 0; $i < $length; $i++) {
      $arr = $this->table;
      for ($j = 0; $j < $this->n - 1; $j++) {
        $token = $ngram[$j];
        $arr = &$arr[$token];
      }

      $sum = array_sum($arr);
      $random = rand(0, $sum);
      $counter = 0;
      foreach ($arr as $token => $count) {
        $counter += $count;
        if ($counter >= $random) {
          $target = $token;
          break;
        }
      }
      $out[] = array_shift($ngram);
      array_push($ngram, $target);
    }
    return implode($this->separator, $out);
  }
}
