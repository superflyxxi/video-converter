<?php

function getEnvWithDefault($env, $default)
{
    if (getEnv($env)) {
        return getEnv($env);
    } else {
        return $default;
    }
}

?>
