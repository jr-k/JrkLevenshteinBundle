<?php

namespace Jrk\LevenshteinBundle\ORM\Doctrine\DQL;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;

class LevenshteinFunction extends FunctionNode
{
    public $firstStringExpression = null;
    public $secondStringExpression = null;
    public static $functionName = 'LEVENSHTEIN';

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return self::$functionName.'(' .
            $this->firstStringExpression->dispatch($sqlWalker) . ', ' .
            $this->secondStringExpression->dispatch($sqlWalker) .
            ')';
    }

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        // levenhstein(str1, str2)
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
CREATE DEFINER='{$user}'@'{$host}' FUNCTION `{$functionName}`(s1 VARCHAR(255), s2 VARCHAR(255)) RETURNS INT(11) DETERMINISTIC
BEGIN
	DECLARE s1_len, s2_len, i, j, c, c_temp, cost INT;
	DECLARE s1_char CHAR;
	DECLARE cv0, cv1 VARBINARY(256);
	SET s1_len = CHAR_LENGTH(s1), s2_len = CHAR_LENGTH(s2), cv1 = 0x00, j = 1, i = 1, c = 0;
	IF s1 = s2 THEN
		RETURN 0;
	ELSEIF s1_len = 0 THEN
		RETURN s2_len;
	ELSEIF s2_len = 0 THEN
		RETURN s1_len;
	ELSE
		WHILE j <= s2_len DO
			SET cv1 = CONCAT(cv1, UNHEX(HEX(j))), j = j + 1;
		END WHILE;
		WHILE i <= s1_len DO
			SET s1_char = SUBSTRING(s1, i, 1), c = i, cv0 = UNHEX(HEX(i)), j = 1;
			WHILE j <= s2_len DO
				SET c = c + 1;
				IF s1_char = SUBSTRING(s2, j, 1) THEN SET cost = 0; ELSE SET cost = 1; END IF;
				SET c_temp = CONV(HEX(SUBSTRING(cv1, j, 1)), 16, 10) + cost;
				IF c > c_temp THEN SET c = c_temp; END IF;
				SET c_temp = CONV(HEX(SUBSTRING(cv1, j+1, 1)), 16, 10) + 1;
				IF c > c_temp THEN SET c = c_temp; END IF;
				SET cv0 = CONCAT(cv0, UNHEX(HEX(c))), j = j + 1;
			END WHILE;
			SET cv1 = cv0, i = i + 1;
		END WHILE;
	END IF;
	RETURN c;
END;
EOT;
		

	}
}
