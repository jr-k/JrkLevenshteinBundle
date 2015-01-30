<?php

namespace Jrk\LevenshteinBundle\ORM\Doctrine\DQL;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;

class LevenshteinRatioFunction extends FunctionNode
{
    public $firstStringExpression = null;
    public $secondStringExpression = null;
    public static $functionName = 'LEVENSHTEIN_RATIO';


    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return self::$functionName.'(' .
            $this->firstStringExpression->dispatch($sqlWalker) . ', ' .
            $this->secondStringExpression->dispatch($sqlWalker) .
            ')';
    }

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        // levenhstein_ratio(str1, str2)
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->firstStringExpression = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->secondStringExpression = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public static function getFunctionName()
    {
        return self::$functionName;
    }

    public static function getImportSql($user = 'root', $host = 'localhost')
    {
        $functionName = self::$functionName;

        return <<<EOT
CREATE DEFINER='{$user}'@'{$host}' FUNCTION `{$functionName}`(s1 VARCHAR(255), s2 VARCHAR(255)) RETURNS int(11) DETERMINISTIC
BEGIN
    DECLARE s1_len, s2_len, max_len INT;
    SET s1_len = LENGTH(s1), s2_len = LENGTH(s2);
    IF s1_len > s2_len THEN SET max_len = s1_len; ELSE SET max_len = s2_len; END IF;
    RETURN ROUND((1 - LEVENSHTEIN(s1, s2) / max_len) * 100);
END;
EOT;


    }
}
