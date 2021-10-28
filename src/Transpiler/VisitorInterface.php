<?php

namespace App\Transpiler;

use App\Parser\AbstractNode;

interface VisitorInterface
{

    public function visit(AbstractNode $node);

}