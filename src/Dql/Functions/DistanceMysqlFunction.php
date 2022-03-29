<?php

namespace AppBundle\Dql\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\AST\PathExpression;

class DistanceMysqlFunction extends FunctionNode
{
    public $lat1 = null;
    public $long1 = null;
    public $lat2 = null;
    public $long2 = null;

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER); // (2)
        $parser->match(Lexer::T_OPEN_PARENTHESIS); // (3)
        $this->lat1 = $parser->ArithmeticPrimary(); // (4)
        $parser->match(Lexer::T_COMMA); // (5)
        $this->long1 = $parser->ArithmeticPrimary(); // (6)
        $parser->match(Lexer::T_COMMA); // (5)
        $this->lat2 = $parser->ArithmeticPrimary(); // (4)
        $parser->match(Lexer::T_COMMA); // (5)
        $this->long2 = $parser->ArithmeticPrimary(); // (6)
        $parser->match(Lexer::T_CLOSE_PARENTHESIS); // (3)
    }

    public function getSql(SqlWalker $sqlWalker)
    {

        return 'get_distance(' .
            $this->lat1->dispatch($sqlWalker) . ',' .
            $this->long1->dispatch($sqlWalker) . ',' .
            $this->lat2->dispatch($sqlWalker) . ',' .
            $this->long2->dispatch($sqlWalker) .
            ')';
    }
}