<?php
namespace SuperFlyXXI\VideoConverter\Normalizers;

use SuperFlyXXI\VideoConverter\Requests\Request;

interface Normalizer
{
    public function normalize(Request $oRequest, int $index): Request;
}
