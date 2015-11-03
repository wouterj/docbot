<?php

namespace Docbot\Test\Tokenizer;

use Docbot\Tokenizer\Token;
use Docbot\Tokenizer\Tokens;

class TokensTest extends \PHPUnit_Framework_TestCase
{
    public function testGetNextNonWhitespace()
    {
        $tokens = Tokens::fromMarkup("\n\n\nHello!");

        $this->assertTrue($tokens->current()->isGivenType(Token::WHITESPACE), 'current token is whitespace');

        $this->assertEquals(3, $tokens->getNextNonWhitespace(), 'next non whitespace token is "Hello!"');
    }

    public function testGetNextNonWhitespaceNullIfEndOfFile()
    {
        $tokens = Tokens::fromMarkup('Hello!');

        $this->assertNull($tokens->getNextNonWhitespace());
    }

    public function testGetPrevNonWhitespace()
    {
        $tokens = Tokens::fromMarkup("Hello!\n\n\n");
        $tokens->next();
        $tokens->next();
        $tokens->next();

        $this->assertTrue($tokens->current()->isGivenType(Token::WHITESPACE), 'current token is whitespace');

        $this->assertEquals(0, $tokens->getPrevNonWhitespace(), 'next non whitespace token is "Hello!"');
    }

    public function testGetPrevNonWhitespaceNullIfStartOfFile()
    {
        $tokens = Tokens::fromMarkup('Hello!');

        $this->assertNull($tokens->getPrevNonWhitespace());
    }

    public function testFindGivenKind()
    {
        $tokens = Tokens::fromMarkup(<<<RST
.. note::

    Hello!

.. [1] Some footnote
RST
        );

        $this->assertTrue($tokens->findGivenKind(Token::FOOTNOTE)->isGivenType(Token::FOOTNOTE));
        $this->assertTrue($tokens->current()->equals('.. [1] Some footnote'));
    }

    public function testFindGivenKindNullIfNotFound()
    {
        $tokens = Tokens::fromMarkup("Symfony_\n\n.. _Symfony: http://symfony.com/");

        $this->assertNull($tokens->findGivenKind(Token::FOOTNOTE));
    }

    /** @dataProvider getMarkups */
    public function testGenerateMarkup($input, $expected = null)
    {
        if (null === $expected) {
            $expected = $input;
        }

        $tokens = Tokens::fromMarkup($input);

        $this->assertEquals($expected, $tokens->generateMarkup());
    }

    public function getMarkups()
    {
        return [
            [
                <<<RST
Some text.

.. seealso::

    Check out this link_!

.. _link: http://symfony.com/
RST
            ],

            [
                <<<RST
.. sidebar:: Some Title

    Some text::

        echo 'hello';
RST
            ],

            [
                <<<RST
or PHP. Have a look at this sample of the default Symfony configuration:

.. code-block:: yaml

    # app/config/config.yml
    imports:
        - { resource: parameters.yml }
        - { resource: security.yml }
        - { resource: services.yml }

    framework:
        #esi:             ~
        #translator:      { fallbacks: ["%locale%"] }
        secret:          "%secret%"
        router:
            resource: "%kernel.root_dir%/config/routing.yml"
            strict_requirements: "%kernel.debug%"
        form:            true
        csrf_protection: true
        validation:      { enable_annotations: true }
        templating:      { engines: ['twig'] }
        default_locale:  "%locale%"
        trusted_proxies: ~
        session:         ~

    # Twig Configuration
    twig:
        debug:            "%kernel.debug%"
        strict_variables: "%kernel.debug%"

    # Swift Mailer Configuration
    swiftmailer:
        transport: "%mailer_transport%"
        host:      "%mailer_host%"
        username:  "%mailer_user%"
        password:  "%mailer_password%"
        spool:     { type: memory }

    # ...

RST
            ],

            [
                <<<RST
* Route names are used for template names;

* ``500`` errors are now managed correctly;

* Request attributes are extracted to keep our templates simple::

      <!-- example.com/src/pages/hello.php -->

      Hello <?php echo htmlspecialchars(\$name, ENT_QUOTES, 'UTF-8') ?>

* Route configuration has been moved to its own file:

  .. code-block:: php

      // example.com/src/app.php

      use Symfony\Component\Routing;

      \$routes = new Routing\RouteCollection();
      \$routes->add('hello', new Routing\Route('/hello/{name}', array('name' => 'World')));
      \$routes->add('bye', new Routing\Route('/bye'));

      return \$routes;

  We now have a clear separation between the configuration (everything
  specific to our application in ``app.php``) and the framework (the generic
  code that powers our application in ``front.php``).
RST
            ]
        ];
    }
}
