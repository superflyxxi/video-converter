<?php
namespace SuperFlyXXI\VideoConverter\Helpers;

class EnvHelper
{
    public static function getEnvWithDefault($env, $default)
    {
        if (getEnv($env)) {
            return getEnv($env);
        } else {
            return $default;
        }
    }
}
