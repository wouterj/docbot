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
    private $tokens;
    private $indent = 0;

    private function __construct($type)
    {
        $this->type = $type;
        $this->tokens = Tokens::fromArray([]);
    }

    public static function create($type)
    {
        return new self($type);
    }

    public function withValue($value)
    {
        if (0 !== count($this->tokens)) {
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

    /**
     * @param Token[] $tokens
     *
     * @return $this
     */
    public function withSubTokens(array $tokens)
    {
        if (null !== $this->value) {
            throw new \BadMethodCallException('A token cannot have a value and subtokens.');
        }

        $this->tokens = Tokens::fromArray($tokens);

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
     * @return Tokens
     */
    public function subTokens()
    {
        return $this->tokens;
    }

    public function isCompound()
    {
        return 0 !== count($this->tokens);
    }

    /**
     * A shorthand to get either the value or the values of the sub tokens.
     *
     * @return mixed
     */
    public function content()
    {
        if (!$this->isCompound()) {
            return $this->value();
        }

        $content = '';

        foreach ($this->subTokens() as $token) {
            if ($token->isGivenType(self::DIRECTIVE_ARGUMENT)) {
                $content = rtrim($content, "\n\r").$token->value()."\n";

                continue;
            }

            $content .= implode("\n", array_map(function ($line) use ($token) {
                return str_repeat(' ', $token->offset()).$line;
            }, explode("\n", $token->content())))."\n";
        }

        return substr($content, 0, -1);
    }

    /**
     * Checks whether this token has the given type(s).
     *
     * @param int|array $type A single type or a list of types
     *
     * @return bool
     */
    public function isGivenType($type)
    {
        if (is_array($type)) {
            foreach ($type as $t) {
                if ($this->isGivenType($t)) {
                    return true;
                }
            }

            return false;
        }

        return $this->type() === $type;
    }

    public function isList()
    {
        return $this->isGivenType([self::BULLET_LIST, self::ENUMERATED_LIST]);
    }

    public function isLiteralBlock()
    {
        return $this->isGivenType([self::INDENTED_LITERAL_BLOCK, self::QUOTED_LITERAL_BLOCK]);
    }

    public function isTable()
    {
        return $this->isGivenType([self::GRID_TABLE, self::SIMPLE_TABLE]);
    }

    public function isWhitespace()
    {
        return $this->isGivenType(self::WHITESPACE);
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
