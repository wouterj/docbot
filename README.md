# DocBot (CAUTION: very unstable!)

Welcome to DocBot! This bot will lint your documentation files according to the
[Symfony Doc Standards](http://symfony.com/doc/current/contributing/documentation/standards.html).

## Install

    $ composer global require wouterj/docbot

## Lint

    # linting a directory
    $ docbot lint some_directory/

    # linting a file
    $ docbot lint path/to/file.rst

    # ignoring files (like backup files)
    $ docbot lint --ignore *~ some_directory/
    # or
    $ docbot lint -i *~ some_directory/

    # specifying a reviewer type (available types: rst, doc, symfony)
    $ docbot lint --types rst ...
    # or
    $ docbot lint -t rst ...

    # or multiple types
    $ docbot lint -t rst -t doc ...

## Contribute

You started reading this section? You're my hero! Feel free to submit PRs,
issues or do any other thing that benefits Docbot.

## Internals

The DocBot is written as a big validator, each reviewer being a
ConstraintValidator for a class constraint of `Gnugat\Redaktilo\Text`.

## License

This project is created under the BSD license.
