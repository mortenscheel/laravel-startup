<?php

namespace MortenScheel\LaravelStartup;

use PhpParser\Node\Expr;
use PhpParser\PrettyPrinter\Standard;

class CustomPrettyPrinter extends Standard
{
    protected function pExpr_Array(Expr\Array_ $node)
    {
        if (empty($node->items)) {
            return '[]';
        }
        $result = '[';
        foreach ($node->items as $item) {
            $comments = $item->getAttribute('comments', []);
            if ($comments) {
                $result .= "\n" . $this->pComments($comments);
            }
            $result .= "\n" . $this->p($item) . ',';
        }
        return $result . "\n]";
    }
}
