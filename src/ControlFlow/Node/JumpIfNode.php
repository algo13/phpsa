<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace PHPSA\ControlFlow\Node;

use PHPSA\ControlFlow\Block;

class JumpIfNode extends AbstractNode
{
    /**
     * @var AbstractNode
     */
    protected $cond;

    /**
     * @var Block
     */
    protected $if;

    /**
     * @var Block|null
     */
    protected $else;

    public function __construct(AbstractNode $cond, Block $if)
    {
        $this->if = $if;
        $this->cond = $cond;
    }

    /**
     * @param Block $if
     */
    public function setIf(Block $if)
    {
        $this->if = $if;
    }

    /**
     * @param Block $else
     */
    public function setElse(Block $else)
    {
        $this->else = $else;
    }

    public function getSubVariables()
    {
        return [
            'cond' => $this->cond
        ];
    }

    public function getSubBlocks()
    {
        return [
            'if' => $this->if,
            'else' => $this->else,
        ];
    }
}
