<?php

namespace AcMarche\Presse;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AcMarchePresseBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
