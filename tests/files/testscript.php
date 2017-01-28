<?php

system("ls");

if (true) {
    system("ls");
    otherMethod();
} else {
    system("ls");
}

foreach (['test'] as $test) {
    system("ls");
    otherMethod();
}

foreach (['test'] as $test) {
    system("ls");

    if (true) {
        if (true) {
            if (true) {
                if (true) {
                    system("ls");
                    otherMethod();
                }
            }
        }
    }
}

function otherMethod()
{
    system("ls");
}
