<?php

/**
 * `CREATE` statement.
 *
 * @package    SqlParser
 * @subpackage Statements
 */
namespace SqlParser\Statements;

use SqlParser\Parser;
use SqlParser\Statement;
use SqlParser\Token;
use SqlParser\TokensList;
use SqlParser\Fragments\ArrayFragment;
use SqlParser\Fragments\DataTypeFragment;
use SqlParser\Fragments\FieldDefFragment;
use SqlParser\Fragments\FieldFragment;
use SqlParser\Fragments\OptionsFragment;
use SqlParser\Fragments\ParamDefFragment;

/**
 * `CREATE` statement.
 *
 * @category   Statements
 * @package    SqlParser
 * @subpackage Statements
 * @author     Dan Ungureanu <udan1107@gmail.com>
 * @license    http://opensource.org/licenses/GPL-2.0 GNU Public License
 */
class CreateStatement extends Statement
{

    /**
     * Options for `CREATE` statements.
     *
     * @var array
     */
    public static $OPTIONS = array(

        // CREATE TABLE
        'TEMPORARY'                     => 1,

        // CREATE VIEW
        'OR REPLACE'                    => array(2, 'var='),
        'ALGORITHM'                     => array(3, 'var='),
        // `DEFINER` is also used for `CREATE FUNCTION / PROCEDURE`
        'DEFINER'                       => array(4, 'var='),
        'SQL SECURITY'                  => array(5, 'var'),

        'DATABASE'                      => 6,
        'EVENT'                         => 6,
        'FUNCTION'                      => 6,
        'INDEX'                         => 6,
        'PROCEDURE'                     => 6,
        'SERVER'                        => 6,
        'TABLE'                         => 6,
        'TABLESPACE'                    => 6,
        'TRIGGER'                       => 6,
        'USER'                          => 6,
        'VIEW'                          => 6,

        // CREATE TABLE
        'IF NOT EXISTS'                 => 7,
    );

    /**
     * All table options.
     *
     * @var array
     */
    public static $TABLE_OPTIONS = array(
        'ENGINE'                        => array(1, 'var='),
        'AUTO_INCREMENT'                => array(2, 'var='),
        'AVG_ROW_LENGTH'                => array(3, 'var'),
        'CHARACTER SET'                 => array(4, 'var='),
        'CHARSET'                       => array(4, 'var='),
        'DEFAULT CHARACTER SET'         => array(4, 'var='),
        'DEFAULT CHARSET'               => array(4, 'var='),
        'CHECKSUM'                      => array(5, 'var'),
        'DEFAULT COLLATE'               => array(5, 'var='),
        'COLLATE'                       => array(6, 'var='),
        'COMMENT'                       => array(7, 'var='),
        'CONNECTION'                    => array(8, 'var'),
        'DATA DIRECTORY'                => array(9, 'var'),
        'DELAY_KEY_WRITE'               => array(10, 'var'),
        'INDEX DIRECTORY'               => array(11, 'var'),
        'INSERT_METHOD'                 => array(12, 'var'),
        'KEY_BLOCK_SIZE'                => array(13, 'var'),
        'MAX_ROWS'                      => array(14, 'var'),
        'MIN_ROWS'                      => array(15, 'var'),
        'PACK_KEYS'                     => array(16, 'var'),
        'PASSWORD'                      => array(17, 'var'),
        'ROW_FORMAT'                    => array(18, 'var'),
        'TABLESPACE'                    => array(19, 'var'),
        'STORAGE'                       => array(20, 'var'),
        'UNION'                         => array(21, 'var'),
    );

    /**
     * All function options.
     *
     * @var array
     */
    public static $FUNC_OPTIONS = array(
        'COMMENT'                       => array(1, 'var='),
        'LANGUAGE SQL'                  => 2,
        'DETERMINISTIC'                 => 3,
        'NOT DETERMINISTIC'             => 3,
        'CONTAINS SQL'                  => 4,
        'NO SQL'                        => 4,
        'READS SQL DATA'                => 4,
        'MODIFIES SQL DATA'             => 4,
        'SQL SECURITY DEFINER'          => array(5, 'var'),
    );

    /**
     * All trigger options.
     *
     * @var array
     */
    public static $TRIGGER_OPTIONS = array(
        'BEFORE'                        => 1,
        'AFTER'                         => 1,
        'INSERT'                        => 2,
        'UPDATE'                        => 2,
        'DELETE'                        => 2,
    );

    /**
     * The name of the entity that is created.
     *
     * Used by all `CREATE` statements.
     *
     * @var FieldFragment
     */
    public $name;

    /**
     * The options of the entity (table, procedure, function, etc.).
     *
     * Used by `CREATE TABLE`, `CREATE FUNCTION` and `CREATE PROCEDURE`.
     *
     * @var OptionsFragment
     *
     * @see static::$TABLE_OPTIONS
     * @see static::$FUNC_OPTIONS
     * @see static::$TRIGGER_OPTIONS
     */
    public $entityOptions;

    /**
     * If `CREATE TABLE`, a list of fields in the new table.
     * If `CREATE VIEW`, a list of columns.
     *
     * Used by `CREATE TABLE` and `CREATE VIEW`.
     *
     * @var FieldDefFragment[]|ArrayFragment
     */
    public $fields;

    /**
     * If `CREATE TRIGGER` the name of the table.
     *
     * Used by `CREATE TRIGGER`.
     *
     * @var FieldFragment
     */
    public $table;

    /**
     * The return data type of this routine.
     *
     * Used by `CREATE FUNCTION`.
     *
     * @var DataTypeFragment
     */
    public $return;

    /**
     * The parameters of this routine.
     *
     * Used by `CREATE FUNCTION` and `CREATE PROCEDURE`.
     *
     * @var ParamDefFragment[]
     */
    public $parameters;

    /**
     * The body of this function or procedure. For views, it is the select
     * statement that gets the
     *
     * Used by `CREATE FUNCTION`, `CREATE PROCEDURE` and `CREATE VIEW`.
     *
     * @var Token[]|string
     */
    public $body = array();

    /**
     * @return string
     */
    public function build()
    {
        if ($this->options->has('TABLE')) {
            return 'CREATE '
                . OptionsFragment::build($this->options) . ' '
                . FieldFragment::build($this->name) . ' '
                . FieldDefFragment::build($this->fields) . ' '
                . OptionsFragment::build($this->entityOptions);
        } elseif ($this->options->has('VIEW')) {
            $tmp = '';
            if (!empty($this->fields)) {
                $tmp = ArrayFragment::build($this->fields);
            }
            return 'CREATE '
                . OptionsFragment::build($this->options) . ' '
                . FieldFragment::build($this->name) . ' '
                . $tmp . ' AS ' . TokensList::build($this->body) . ' '
                . OptionsFragment::build($this->entityOptions);
        } elseif ($this->options->has('TRIGGER')) {
            return 'CREATE '
                . OptionsFragment::build($this->options) . ' '
                . FieldFragment::build($this->name) . ' '
                . OptionsFragment::build($this->entityOptions) . ' '
                . 'ON ' . FieldFragment::build($this->table) . ' '
                . 'FOR EACH ROW ' . TokensList::build($this->body);
        } elseif (($this->options->has('PROCEDURE'))
            || ($this->options->has('FUNCTION'))
        ) {
            $tmp = '';
            if ($this->options->has('FUNCTION')) {
                $tmp = 'RETURNS ' . DataTypeFragment::build($this->return);
            }
            return 'CREATE '
                . OptionsFragment::build($this->options) . ' '
                . FieldFragment::build($this->name) . ' '
                . ParamDefFragment::build($this->parameters) . ' '
                . $tmp . ' ' . TokensList::build($this->body);
        }
        return '';
    }

    /**
     * @param Parser     $parser The instance that requests parsing.
     * @param TokensList $list   The list of tokens to be parsed.
     *
     * @return void
     */
    public function parse(Parser $parser, TokensList $list)
    {
        ++$list->idx; // Skipping `CREATE`.

        // Parsing options.
        $this->options = OptionsFragment::parse($parser, $list, static::$OPTIONS);
        ++$list->idx; // Skipping last option.

        // Parsing the field name.
        $this->name = FieldFragment::parse(
            $parser,
            $list,
            array(
                'noAlias' => true,
                'noBrackets' => true,
                'skipColumn' => true,
            )
        );
        ++$list->idx; // Skipping field.

        if ($this->options->has('TABLE')) {
            $this->fields = FieldDefFragment::parse($parser, $list);
            ++$list->idx;

            $this->entityOptions = OptionsFragment::parse(
                $parser,
                $list,
                static::$TABLE_OPTIONS
            );
        } elseif (($this->options->has('PROCEDURE'))
            || ($this->options->has('FUNCTION'))
        ) {
            $this->parameters = ParamDefFragment::parse($parser, $list);
            if ($this->options->has('FUNCTION')) {
                $token = $list->getNextOfType(Token::TYPE_KEYWORD);
                if ($token->value !== 'RETURNS') {
                    $parser->error(
                        '\'RETURNS\' keyword was expected.',
                        $token
                    );
                } else {
                    ++$list->idx;
                    $this->return = DataTypeFragment::parse(
                        $parser,
                        $list
                    );
                }
            }
            ++$list->idx;

            $this->entityOptions = OptionsFragment::parse(
                $parser,
                $list,
                static::$FUNC_OPTIONS
            );
            ++$list->idx;

            for (; $list->idx < $list->count; ++$list->idx) {
                $token = $list->tokens[$list->idx];
                $this->body[] = $token;
            }
        } else if ($this->options->has('VIEW')) {
            $token = $list->getNext(); // Skipping whitespaces and comments.

            // Parsing columns list.
            if (($token->type === Token::TYPE_OPERATOR) && ($token->value === '(')) {
                --$list->idx; // getNext() also goes forward one field.
                $this->fields = ArrayFragment::parse($parser, $list);
                ++$list->idx; // Skipping last token from the array.
                $list->getNext();
            }

            // Parsing the `AS` keyword.
            for (; $list->idx < $list->count; ++$list->idx) {
                $token = $list->tokens[$list->idx];
                if ($token->type === Token::TYPE_DELIMITER) {
                    break;
                }
                $this->body[] = $token;
            }
        } else if ($this->options->has('TRIGGER')) {
            // Parsing the time and the event.
            $this->entityOptions = OptionsFragment::parse(
                $parser,
                $list,
                static::$TRIGGER_OPTIONS
            );
            ++$list->idx;

            $list->getNextOfTypeAndValue(Token::TYPE_KEYWORD, 'ON');
            ++$list->idx; // Skipping `ON`.

            // Parsing the name of the table.
            $this->table = FieldFragment::parse(
                $parser,
                $list,
                array(
                    'noAlias' => true,
                    'noBrackets' => true,
                    'skipColumn' => true,
                )
            );
            ++$list->idx;

            $list->getNextOfTypeAndValue(Token::TYPE_KEYWORD, 'FOR EACH ROW');
            ++$list->idx; // Skipping `FOR EACH ROW`.

            for (; $list->idx < $list->count; ++$list->idx) {
                $token = $list->tokens[$list->idx];
                $this->body[] = $token;
            }
        }
    }
}
