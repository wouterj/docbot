<?php

namespace Docbot\Tokenizer;

/**
 * @method static Token whitespace()
 * @method static Token comment()
 * @method static Token document()
 * @method static Token section()
 * @method static Token paragraph()
 * @method static Token bulletList()
 * @method static Token enumeratedList()
 * @method static Token definitionList()
 * @method static Token quotedLiteralBlock()
 * @method static Token indentedLiteralBlock()
 * @method static Token blockQuote()
 * @method static Token gridTable()
 * @method static Token simpleTable()
 * @method static Token footnote()
 * @method static Token hyperlinkTarget()
 * @method static Token directive()
 * @method static Token directiveMarker()
 * @method static Token directiveArgument()
 * @method static Token directiveOption()
 * @method static Token directiveContent()
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class Token
{
    const WHITESPACE = 0;

    const COMMENT = 1;

    const DOCUMENT = 2;
    const SECTION = 3;
    const PARAGRAPH = 4;

    const BULLET_LIST = 5;
    const ENUMERATED_LIST = 6;
    const DEFINITION_LIST = 7;

    const QUOTED_LITERAL_BLOCK = 8;
    const INDENTED_LITERAL_BLOCK = 9;
    const BLOCK_QUOTE = 10;

    const GRID_TABLE = 11;
    const SIMPLE_TABLE = 12;

    const FOOTNOTE = 13;
    const HYPERLINK_TARGET = 14;

    const DIRECTIVE = 15;
    const DIRECTIVE_MARKER = 16;
    const DIRECTIVE_ARGUMENT = 17;
    const DIRECTIVE_OPTION = 18;
    const DIRECTIVE_CONTENT = 19;

    private $type;
    private $value;
    private $tokens = [];
    private $indent;

    private function __construct($type)
    {
        $this->type = $type;
    }

    public static function create($type)
    {
        return new self($type);
    }

    public function withValue($value)
    {
        if ($this->tokens) {
            throw new \BadMethodCallException('A token cannot have a value and subtokens.');
        }

        $this->value = $value;

        return $this;
    }

    public function appendValue($value, $separator = "\n")
    {
        $this->value .= $separator.$value;

        return $this;
    }

    public function withSubToken(Token $token)
    {
        if (null !== $this->value) {
            throw new \BadMethodCallException('A token cannot have a value and subtokens.');
        }

        $this->tokens[] = $token;

        return $this;
    }

    public function atOffset($offset)
    {
        $this->indent = $offset;

        return $this;
    }

    public function type($asString = false)
    {
        if (!$asString) {
            return $this->type;
        }

        $reflection = new \ReflectionClass($this);
        $constants = array_flip($reflection->getConstants());

        return $constants[$this->type];
    }

    /**
     * The value of the token.
     *
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * The offset of this token from the start of the line.
     *
     * @return int
     */
    public function offset()
    {
        return $this->indent;
    }

    /**
     * The sub tokens of this token.
     *
     * @return Token[]
     */
    public function subTokens()
    {
        return $this->tokens;
    }

    /**
     * A shorthand to get either the value or the values of the sub tokens.
     *
     * @return mixed
     */
    public function content()
    {
        if ($this->value()) {
            return $this->value();
        }

        return array_reduce($this->subTokens(), function (&$acc, Token $token) {
            return $acc .= $token->content();
        }, '');
    }

    /**
     * Checks whether this token has the given type(s).
     *
     * @param string|array $type A single type or a list of types
     *
     * @return bool
     */
    public function isGivenType($type)
    {
        if (is_array($type)) {
            foreach ($type as $t) {
                if ($this->isOfType($t)) {
                    return true;
                }
            }

            return false;
        }

        return $this->type() === $type;
    }

    public function equals($other)
    {
        if ($other instanceof self) {
            return $other->type() === $this->type()
                && $other->offset() === $this->offset()
                && $other->content() === $this->content()
            ;
        }

        return $other === $this->content();
    }

    public static function __callStatic($type, array $arguments)
    {
        if (count($arguments)) {
            throw new \BadMethodCallException('Tokens cannot be constructed with arguments');
        }

        $type = preg_replace_callback('/[A-Z]/', function ($matches) {
            return '_'.$matches[0];
        }, $type);

        return self::create(constant(__CLASS__.'::'.strtoupper($type)));
    }
}
