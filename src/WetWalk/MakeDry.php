<?php

/**
 * WetWalk\MakeDry class - Parse PHP file and traverse to find method calls to convert into echo call
 *
 * Author: Nino Labrador (nino.labrador@codingavenue.com)
 */

namespace WetWalk;

use WetWalk\Parser;
use PhpParser\PrettyPrinter;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Expr\Variable;

class MakeDry
{
    protected $target;
    protected $methods;
    protected $skip_methods;

    /**
     * Constructor Method
     * @param $target [string] - PHP Full file path to be parsed
     * @param $methods [array] - Other non built-in methods to be converted to echo
     * @param $skip_methods [array] - Skip these method calls
     */
    public function __construct($target, $methods = [], $skip_methods = [])
    {
        $this->target  = $target;
        $this->methods = $methods;
        $this->skip_methods = $skip_methods;
    }

    /**
     * Parse file and traverse to find FuncCall and MethodCall methods
     * Change FuncCall and MethodCall into an Echo_ class
     * This would echo the actual function or method call - work as dryrun
     */
    public function convert()
    {
        $parser = new Parser($this->target);
        $parsed_files = $parser->parse();
        $prettyPrinter = new PrettyPrinter\Standard;
        $isclass = null;

        foreach ($parsed_files as $parsed_file) {
            $code = $parsed_file['parsed'];
            $filename = $parsed_file['filename'];
        
            echo "Converting file $filename...";

            foreach ($code as $c_index => $object) {
                if (get_class($object) == 'PhpParser\Node\Stmt\Namespace_') {
                    $isclass = true;
                    foreach ($object->stmts as $index => $line) {
                        if (get_class($line) == 'PhpParser\Node\Stmt\Class_') {
                            $code[$c_index]->stmts[$index] = $this->convertClass($line);
                        }
                    }
                }
                
                if (get_class($object) == 'PhpParser\Node\Stmt\Class_') {
                    $isclass = true;
                    $code[$c_index] = $this->convertClass($object);
                }
            }

            if (!$isclass) {
                $code = $this->convertScript($code);
            }
            
            $this->createDryFile($prettyPrinter->prettyPrintFile($code), $filename);
        }
    }

    public function createDryFile($code_string, $filename)
    {
        $file = $this->getDryRunDir() . "/" . $filename;

        echo "\nCreating file $file...\n";

        file_put_contents($file, $code_string);

        echo "Done.\n";
    }

    /**
     * Convert class function/method codes into echo
     * Used for parsing PHP classes
     *
     * @param $stmt_class - PhpParser\Node\Stmt\Class_ object
     * returns updated $stmt_class
     */
    public function convertClass($stmt_class)
    {
        foreach ($stmt_class->stmts as $index => $code_line) {
            if (isset($code_line->stmts)) {
                foreach ($code_line->stmts as $index => $method_line) {
                    if (isset($method_line->stmts)) {
                        $code_line->stmts[$index] = $this->methodCalls($method_line);
                    } else {
                        $code_line->stmts[$index] = $this->checkUpdateCode($method_line);
                    }
                }
            }
        }

        return $stmt_class;
    }

    /**
     * Convert function/method codes into echo
     * Used for parsing PHP scripts
     *
     * @param $code_lines [array]  - Array of parse code lines from parse method
     * returns updated $code_lines
     */
    public function convertScript($code_lines)
    {
        foreach ($code_lines as $index => $code_line) {
            if (isset($code_line->stmts)) {
                $code_lines[$index] = $this->methodCalls($code_line);
            } else {
                $code_lines[$index] = $this->checkUpdateCode($code_line);
            }
        }

        return $code_lines;
    }

    /**
     * Check line if function call or method call and convert to echo
     */
    public function checkUpdateCode($code_line) {
        $append_echo    = null;
        $orig_code_line = $code_line;

        if (get_class($code_line) == 'PhpParser\Node\Expr\Assign') {
            $code_line = $code_line->expr;

            $function = null;
            if (get_class($code_line) == 'PhpParser\Node\Expr\MethodCall') {
                $function = $code_line->name;
            }
            if (get_class($code_line) == 'PhpParser\Node\Expr\FuncCall') {
                $function = $code_line->name->parts[0];
            }
        
            if ($function && in_array($function, $this->skip_methods)) {
                return $orig_code_line;
            }

            $append_echo = new String_("Assign variable to function call "); 
        }

        if (get_class($code_line) == 'PhpParser\Node\Expr\FuncCall') {
            return $this->convertEcho($code_line, $append_echo);
        }
        if (get_class($code_line) == 'PhpParser\Node\Expr\MethodCall') {
            if (in_array($code_line->name, $this->methods)) {
                return $this->convertEcho($code_line, $append_echo);
            }
        }

        return $orig_code_line;
    }

    /**
     * Find method/function calls to convert into echo
     * @param $method_line - PhpParser\Node\Stmts\* objects
     * returns updated values of $method_line
     */
    public function methodCalls($method_line)
    {
        if (get_class($method_line) == 'PhpParser\Node\Stmt\If_') {
            if ($method_line->else) {
                $this->methodCalls($method_line->else);
            }

            if (count($method_line->elseifs) > 0) {
                foreach ($method_line->elseifs as $elseif) {
                    $this->methodCalls($elseif);
                }
            }
        }

        foreach ($method_line->stmts as $index => $inner_line) {
            if (isset($inner_line->stmts)) {
                $method_line->stmts[$index] = $this->methodCalls($inner_line);
            } else {
                $method_line->stmts[$index] = $this->checkUpdateCode($inner_line);
            }
        }

        return $method_line;
    }

    /**
     * Handles the convertion of function/method call into echo
     * @param $method_line - PhpParser\Node\Expr\MethodCall(FuncCall)
     * @param $append_echo - PhpParser\Node\Expr\Echo_ object
     *
     * returns new PhpParser\Node\Expr\Echo_ object
     */
    public function convertEcho($method_line, $append_echo = null)
    {
        if (get_class($method_line) == 'PhpParser\Node\Expr\MethodCall') {
            $function = $method_line->name;
        } else {
            $function = $method_line->name->parts[0];
        }
        
        if (in_array($function, $this->skip_methods)) {
            return $method_line;
        }

        $echo_params = [];

        if ($append_echo) {
            $echo_params[] = $append_echo;
        }

        $echo_params[] = new String_("{$function}(");

        if ($function == 'exec') {
            $echo_params[] = $method_line->args[0]->value;
        }
        else {
            foreach ($method_line->args as $arg) {
                $echo_params[] = $arg->value;
            }
        }

        $echo_params[] = new String_(')');
        $echo_params[] = new String_("\n", ['kind' => 2]);

        return new Echo_($echo_params);
    }

    /**
     * Get/Create directory where the new version of the file will be created
     */
    public function getDryRunDir()
    {
        $dir = null;

        if (is_dir($this->target)) {
            $dir = $this->target;
        } else {
            $dir = dirname($this->target);
        }

        $dir .= "/DryRun";

        if (!file_exists($dir)) {
            mkdir($dir);
        }

        return $dir;
    }
}
