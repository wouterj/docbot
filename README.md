# DocBot

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

    # specifying a reviewer type (available types: rst, doc, symfony)
    $ docbot lint --types rst ...

    # or multiple types
    $ docbot lint --types rst,doc ...

## Contribute

You started reading this section? You're my hero! Feel free to submit PRs,
issues or do any other thing that benefits Docbot.

## Internals

Docbot works by triggering events. `Docbot#lint()` opens the file and triggers
`RequestFileReview::EVENT`. All reviewers are attached to this event and start
reviewing the file. If a reviewer found an error, `ReportError::EVENT` is
triggered. The reporter is attached to this event and makes sure the error is
reported.

## License

This project is created under the BSD license.
