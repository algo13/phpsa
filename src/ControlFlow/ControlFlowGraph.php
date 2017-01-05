<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace PHPSA\ControlFlow;

use PhpParser\Node\Stmt\Function_;
use PHPSA\ControlFlow\Node;

class ControlFlowGraph
{
    protected $lastBlockId = 1;

    /**
     * @var Block
     */
    protected $root;

    public function __construct($statement)
    {
        $this->root = new Block($this->lastBlockId++);

        if ($statement instanceof Function_) {
            if ($statement->stmts) {
                $this->passNodes($statement->stmts, $this->root);
            }
        }
    }

    protected function passNodes(array $nodes, Block $block)
    {
        foreach ($nodes as $stmt) {
            $this->passNode($stmt, $block);
        }
    }

    protected function passNode($stmt, Block $block)
    {
        switch (get_class($stmt)) {
            case \PhpParser\Node\Expr\Assign::class:
                $this->passAssign($stmt, $block);
                break;
            case \PhpParser\Node\Stmt\Return_::class:
                $this->passReturn($stmt, $block);
                break;
            case \PhpParser\Node\Stmt\For_::class:
                $block = $this->passFor($stmt, $block);
                break;
            case \PhpParser\Node\Stmt\If_::class:
                $block = $this->passIf($stmt, $block);
                break;
            case \PhpParser\Node\Stmt\While_::class:
                $block = $this->passWhile($stmt, $block);
                break;
            case \PhpParser\Node\Stmt\Do_::class:
                $block = $this->passDo($stmt, $block);
                break;
            case \PhpParser\Node\Stmt\Throw_::class:
                $this->passThrow($stmt, $block);
                break;
            case \PhpParser\Node\Expr\Exit_::class:
                $block->addChildren(new Node\ExitNode());
                break;
            case \PhpParser\Node\Stmt\Label::class:
                $block = $this->createNewBlockIfNeeded($block);
                $block->label = $stmt->name;
                break;
            case \PhpParser\Node\Stmt\Nop::class:
                // ignore commented code
                break;
            default:
                echo 'Unimplemented ' . get_class($stmt) . PHP_EOL;
                break;
        }
    }

    /**
     * If current block is not empty, lets create a new one
     *
     * @param Block $block
     * @return Block
     */
    protected function createNewBlockIfNeeded(Block $block)
    {
        if ($block->getChildrens()) {
            $block->setExit(
                $block = new Block($this->lastBlockId++)
            );
        }

        return $block;
    }

    protected function passIf(\PhpParser\Node\Stmt\If_ $if, Block $block)
    {
        $trueBlock = new Block($this->lastBlockId++);
        $this->passNodes($if->stmts, $trueBlock);

        $jumpIf = new Node\JumpIfNode($trueBlock);

        $elseBlock = null;

        if ($if->else) {
            if ($if->else->stmts) {
                $elseBlock = new Block($this->lastBlockId++);
                $this->passNodes($if->else->stmts, $elseBlock);

                $jumpIf->setElse($elseBlock);
            }
        }

        $block->addChildren(
            $jumpIf
        );

        $exitBlock = new Block($this->lastBlockId++);
        $trueBlock->setExit($exitBlock);

        if ($elseBlock) {
            $elseBlock->setExit($exitBlock);
        }

        return $exitBlock;
    }

    protected function passFor(\PhpParser\Node\Stmt\For_ $for, Block $block)
    {
        $this->passNodes($for->init, $block);

        $block->setExit(
            $loop = new Block($this->lastBlockId++)
        );
        $this->passNodes($for->stmts, $loop);

        $loop->setExit(
            $after = new Block($this->lastBlockId++)
        );
        return $after;
    }

    protected function passDo(\PhpParser\Node\Stmt\Do_ $do, Block $block)
    {
        $loop = new Block($this->lastBlockId++);
        $this->passNodes($do->stmts, $loop);

        $block->setExit($loop);

        $cond = new Block($this->lastBlockId++);
        $loop->setExit($cond);

        $jumpIf = new Node\JumpIfNode($loop);
        $cond->addChildren($jumpIf);

        $exitBlock = new Block($this->lastBlockId++);
        $jumpIf->setElse($exitBlock);

        return $exitBlock;
    }

    protected function passWhile(\PhpParser\Node\Stmt\While_ $while, Block $block)
    {
        $cond = new Block($this->lastBlockId++);
        $block->setExit(
            $cond
        );

        $loop = new Block($this->lastBlockId++);

        $jumpIf = new Node\JumpIfNode($loop);
        $cond->addChildren($jumpIf);

        $this->passNodes($while->stmts, $loop);

        $loop->addChildren(new Node\JumpNode($cond));
        //$loop->setExit($cond);

        $after = new Block($this->lastBlockId++);
        $jumpIf->setElse($after);

        return $after;
    }

    protected function passThrow(\PhpParser\Node\Stmt\Throw_ $throw_, Block $block)
    {
        $block->addChildren(new Node\ThrowNode());
    }

    protected function passAssign(\PhpParser\Node\Expr\Assign $assign, Block $block)
    {
        $block->addChildren(new Node\AssignNode());
    }

    protected function passReturn(\PhpParser\Node\Stmt\Return_ $return_, Block $block)
    {
        $block->addChildren(new Node\ReturnNode());
    }

    /**
     * @return Block
     */
    public function getRoot()
    {
        return $this->root;
    }
}
