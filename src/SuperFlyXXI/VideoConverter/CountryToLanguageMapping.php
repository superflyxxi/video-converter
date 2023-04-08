<?php
namespace SuperFlyXXI\VideoConverter;

final class CountryToLanguageMapping
{
    public static $mapLanguageCountry = null;

    public static function getCountry($language)
    {
        if (array_key_exists($language, self::$mapLanguageCountry)) {
            return self::$mapLanguageCountry[$language];
        }
        return $language;
    }

    /**
     * No need to execute this manually.
     * Runs on load of class.*
     */
    public static function init()
    {
        self::$mapLanguageCountry = [];
        // https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
        self::$mapLanguageCountry["alb"] = "sqi";
        self::$mapLanguageCountry["arm"] = "hye";
        self::$mapLanguageCountry["baq"] = "eus";
        self::$mapLanguageCountry["bur"] = "mya";
        self::$mapLanguageCountry["chi"] = "zho";
        self::$mapLanguageCountry["cze"] = "ces";
        self::$mapLanguageCountry["dut"] = "nld";
        self::$mapLanguageCountry["fre"] = "fra";
        self::$mapLanguageCountry["geo"] = "kat";
        self::$mapLanguageCountry["ger"] = "deu";
        self::$mapLanguageCountry["gre"] = "ell";
        self::$mapLanguageCountry["ice"] = "isl";
        self::$mapLanguageCountry["mac"] = "mkd";
        self::$mapLanguageCountry["may"] = "msa";
        self::$mapLanguageCountry["mao"] = "mri";
        self::$mapLanguageCountry["per"] = "fas";
        self::$mapLanguageCountry["slo"] = "slk";
        self::$mapLanguageCountry["tib"] = "bod";
        self::$mapLanguageCountry["wel"] = "cym";
    }
}

CountryToLanguageMapping::init();
