<?php
$config = new PhpCsFixer\Config();
return $config->setRules([
        '@PSR2' => true,
        'braces' => ['position_after_functions_and_oop_constructs' => 'same'],
    ])->setIndent("\t")
;
?>
