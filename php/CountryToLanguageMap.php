<?php

final class CountryToLanguageMap {

  public static mapCountryLanguage = NULL;

  public static function getLanguage($country) {
    if (array_key_exists($county, self::$mapCountryLanguage)) {
      return self::$mapCountryLanguage[$country][0];
    }
    return $country;
  }

  /** No need to execute this manually. Runs on load of class.**/
  public static function init() {
    self::$mapCountryLanguage = array();
    self::$mapCountryLanguage["fra"] = "fre";
  }   
}

CountryToLanguageMap::init();

?>
