<?php

class TestConvertClass
{
    private $private;

    public function __construct()
    {
        $this->private = true;
    }

    public function simpleIf()
    {
        system("ls");

        if ($this->private) {
            system("ls");
            $this->otherMethod();
        } else {
            system("ls");
        }
    }

    public function foreachLoop()
    {
        foreach (['test'] as $test) {
            system("ls");
            $this->otherMethod();
        }
    }

    public function foreachIf()
    {
        foreach (['test'] as $test) {
            system("ls");

            if ($this->private) {
                if ($this->private) {
                    if ($this->private) {
                        if ($this->private) {
                            system("ls");
                            $this->otherMethod();
                        }
                    }
                }
            }
        }
    }

    public function otherMethod()
    {
        system("ls");
    }
}
