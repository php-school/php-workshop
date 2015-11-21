<?php

namespace PhpSchool\PhpWorkshop;

use PhpParser\Error;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;

/**
 * Class CodePatcher
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CodePatcher
{
    /**
     * @var Parser
     */
    private $parser;
    
    /**
     * @var Standard
     */
    private $printer;

    /**
     * @param Parser $parser
     * @param Standard $printer
     */
    public function __construct(Parser $parser, Standard $printer)
    {
        $this->parser   = $parser;
        $this->printer  = $printer;
    }

    /**
     * @param string $code
     * @param array $modifications
     *
     * @return string
     */
    public function patch($code, array $modifications)
    {
        $statements = $this->parser->parse($code);
        
        foreach ($modifications as $modification) {
            try {
                $codeToInsert = $modification->getCode();
                $codeToInsert = sprintf('<?php %s', preg_replace('/^\s*<\?php/', '', $codeToInsert));
                $additionalStatements = $this->parser->parse($codeToInsert);
            } catch (Error $e) {
                //we should probably log this and have a dev mode or something
                continue;
            }

            switch ($modification->getType()) {
                case CodeModification::TYPE_BEFORE:
                    array_unshift($statements, ...$additionalStatements);
                    break;
                case CodeModification::TYPE_AFTER:
                    array_push($statements, ...$additionalStatements);
                    break;
            }
        }
        
        return sprintf('<?php %s', $this->printer->prettyPrint($statements));
    }
}
