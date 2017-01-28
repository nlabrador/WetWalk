<?php

/**
 * WetWalk\Parser class - Parse PHP file using PhpParser module
 *
 * Author: Nino Labrador (nino.labrador@codingavenue.com)
 */

namespace WetWalk;

use PhpParser\ParserFactory;

class Parser
{
    protected $target;

    /**
     * Constructor method
     * @param $target [string] - Full file path of file or directory of files to be converted
     */
    public function __construct($target)
    {
        $this->target = $target;
    }

    /**
     * Check if directory or file
     * If directory we extract all PHP files inside it
     *
     * return array of parsed file PhpParser\Node objects
     */
    public function parse()
    {
        $target = $this->target;
        $parsed = [];

        if (is_dir($target)) {
            $files = scandir($target);

            foreach ($files as $file) {
                if (preg_match("/\.php$/", $file)) {
                    $file_path = $target."/".$file;

                    $parsed[] = [
                        'filename' => $file,
                        'parsed'   => $this->parseFile($file_path)
                    ];
                }
            }
        } else {
            $parsed[] = [
                'filename' => basename($target),
                'parsed'   => $this->parseFile($target)
            ];
        }

        return $parsed;
    }

    /**
     * Parse file using ParserFactory parse method
     * @param $file [string] - Full file path
     *
     * return PhpParser\Node object
     */
    public function parseFile($file)
    {
        $parser = ( new ParserFactory )->create(ParserFactory::PREFER_PHP7);

        try {
            $stmts = $parser->parse(file_get_contents($file));
        } catch (Error $e) {
            echo 'Parse Error: ', $e->getMessage();
        }

        return $stmts;
    }
}
