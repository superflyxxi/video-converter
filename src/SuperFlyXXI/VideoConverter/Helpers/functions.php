<?php
namespace SuperFlyXXI\VideoConverter;

function getEnvWithDefault($env, $default)
{
    if (getEnv($env)) {
        return getEnv($env);
    } else {
        return $default;
    }
}
