<?php

/**
 * `UPDATE` statement.
 *
 * @package    SqlParser
 * @subpackage Statements
 */
namespace SqlParser\Statements;

use SqlParser\Statement;
use SqlParser\Fragments\FieldFragment;
use SqlParser\Fragments\LimitKeyword;
use SqlParser\Fragments\OrderKeyword;
use SqlParser\Fragments\SetKeyword;
use SqlParser\Fragments\WhereKeyword;

/**
 * `UPDATE` statement.
 *
 * UPDATE [LOW_PRIORITY] [IGNORE] table_reference
 *     SET col_name1={expr1|DEFAULT} [, col_name2={expr2|DEFAULT}] ...
 *     [WHERE where_condition]
 *     [ORDER BY ...]
 *     [LIMIT row_count]
 *
 * or
 *
 * UPDATE [LOW_PRIORITY] [IGNORE] table_references
 *     SET col_name1={expr1|DEFAULT} [, col_name2={expr2|DEFAULT}] ...
 *     [WHERE where_condition]
 *
 * @category   Statements
 * @package    SqlParser
 * @subpackage Statements
 * @author     Dan Ungureanu <udan1107@gmail.com>
 * @license    http://opensource.org/licenses/GPL-2.0 GNU Public License
 */
class UpdateStatement extends Statement
{

    /**
     * Options for `UPDATE` statements and their slot ID.
     *
     * @var array
     */
    public static $OPTIONS = array(
        'LOW_PRIORITY'                  => 1,
        'IGNORE'                        => 2,
    );

    /**
     * The clauses of this statement, in order.
     *
     * @see Statement::$CLAUSES
     *
     * @var array
     */
    public static $CLAUSES = array(
        'UPDATE'                        => array('UPDATE',      2),
        // Used for options.
        '_OPTIONS'                      => array('_OPTIONS',    1),
        // Used for updated tables.
        '_UPDATE'                       => array('UPDATE',      1),
        'SET'                           => array('SET',         3),
        'WHERE'                         => array('WHERE',       3),
        'ORDER BY'                      => array('ORDER BY',    3),
        'LIMIT'                         => array('LIMIT',       3),
    );

    /**
     * Tables used as sources for this statement.
     *
     * @var FieldFragment[]
     */
    public $tables;

    /**
     * The updated values.
     *
     * @var SetKeyword[]
     */
    public $set;

    /**
     * Conditions used for filtering each row of the result set.
     *
     * @var WhereKeyword[]
     */
    public $where;

    /**
     * Specifies the order of the rows in the result set.
     *
     * @var OrderKeyword[]
     */
    public $order;

    /**
     * Conditions used for limiting the size of the result set.
     *
     * @var LimitKeyword
     */
    public $limit;
}
