<?php
namespace SuperFlyXXI\VideoConverter\Helpers;

function getEnvWithDefault($env, $default)
{
    if (getEnv($env)) {
        return getEnv($env);
    } else {
        return $default;
    }
}
