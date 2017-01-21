<?php

namespace Tests\PHPSA\Compiler\Expression\Operators\Bitwise;

use PhpParser\Node;
use PHPSA\CompiledExpression;
use PHPSA\Compiler\Expression;
use Tests\PHPSA\Compiler\Expression\AbstractBinaryOp;

class BitwiseAndTest extends AbstractBinaryOp
{
    /**
     * @param $a
     * @param $b
     * @return bool
     */
    protected function process($a, $b)
    {
        return $a & $b;
    }

    /**
     * @return array
     */
    protected function getSupportedTypes()
    {
        return [
            CompiledExpression::INTEGER,
            CompiledExpression::DOUBLE,
            CompiledExpression::BOOLEAN,
        ];
    }

    /**
     * @param Node\Scalar $a
     * @param Node\Scalar $b
     * @return Node\Expr\BinaryOp\BitwiseAnd
     */
    protected function buildExpression($a, $b)
    {
        return new Node\Expr\BinaryOp\BitwiseAnd($a, $b);
    }
}
