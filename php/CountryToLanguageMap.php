<?php

final class CountryToLanguageMapping {

  public static mapLanguageCountry = NULL;

  public static function getCountry($language) {
    if (array_key_exists($language, self::$mapLanguageCountry)) {
      return self::$mapLanguageCountry[$language][0];
    }
    return $country;
  }

  /** No need to execute this manually. Runs on load of class.**/
  public static function init() {
    self::$mapLanguageCountry = array();
    self::$mapLanguageCountry["fre"] = "fra";
  }   
}

CountryToLanguageMap::init();

?>
