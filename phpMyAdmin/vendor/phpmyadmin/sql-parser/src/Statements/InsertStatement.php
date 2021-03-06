<?php

/**
 * `INSERT` statement.
 */

namespace PhpMyAdmin\SqlParser\Statements;

<<<<<<< HEAD
use PhpMyAdmin\SqlParser\Components\Array2d;
use PhpMyAdmin\SqlParser\Components\IntoKeyword;
use PhpMyAdmin\SqlParser\Components\OptionsArray;
use PhpMyAdmin\SqlParser\Components\SetOperation;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Statement;
use PhpMyAdmin\SqlParser\Token;
use PhpMyAdmin\SqlParser\TokensList;
=======
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Token;
use PhpMyAdmin\SqlParser\TokensList;
use PhpMyAdmin\SqlParser\Statement;
use PhpMyAdmin\SqlParser\Components\IntoKeyword;
use PhpMyAdmin\SqlParser\Components\Array2d;
use PhpMyAdmin\SqlParser\Components\OptionsArray;
use PhpMyAdmin\SqlParser\Components\SetOperation;
>>>>>>> 963d7f7adf76dfd7a7dbc54b828074e76cfb4d65

/**
 * `INSERT` statement.
 *
 * INSERT [LOW_PRIORITY | DELAYED | HIGH_PRIORITY] [IGNORE]
 *     [INTO] tbl_name
 *     [PARTITION (partition_name,...)]
 *     [(col_name,...)]
 *     {VALUES | VALUE} ({expr | DEFAULT},...),(...),...
 *     [ ON DUPLICATE KEY UPDATE
 *       col_name=expr
 *         [, col_name=expr] ... ]
 *
 * or
 *
 * INSERT [LOW_PRIORITY | DELAYED | HIGH_PRIORITY] [IGNORE]
 *     [INTO] tbl_name
 *     [PARTITION (partition_name,...)]
 *     SET col_name={expr | DEFAULT}, ...
 *     [ ON DUPLICATE KEY UPDATE
 *       col_name=expr
 *         [, col_name=expr] ... ]
 *
 * or
 *
 * INSERT [LOW_PRIORITY | HIGH_PRIORITY] [IGNORE]
 *     [INTO] tbl_name
 *     [PARTITION (partition_name,...)]
 *     [(col_name,...)]
 *     SELECT ...
 *     [ ON DUPLICATE KEY UPDATE
 *       col_name=expr
 *         [, col_name=expr] ... ]
 *
 * @category   Statements
 *
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GPL-2.0+
 */
class InsertStatement extends Statement
{
    /**
     * Options for `INSERT` statements.
     *
     * @var array
     */
    public static $OPTIONS = array(
        'LOW_PRIORITY' => 1,
        'DELAYED' => 2,
        'HIGH_PRIORITY' => 3,
        'IGNORE' => 4,
    );

    /**
     * Tables used as target for this statement.
     *
     * @var IntoKeyword
     */
    public $into;

    /**
     * Values to be inserted.
     *
     * @var ArrayObj[]|null
     */
    public $values;

    /**
     * If SET clause is present
     * holds the SetOperation.
     *
     * @var SetOperation[]
     */
    public $set;

    /**
     * If SELECT clause is present
     * holds the SelectStatement.
     *
     * @var SelectStatement
     */
    public $select;

    /**
     * If ON DUPLICATE KEY UPDATE clause is present
     * holds the SetOperation.
     *
     * @var SetOperation[]
     */
    public $onDuplicateSet;

    /**
     * @return string
     */
    public function build()
    {
        $ret = 'INSERT ' . $this->options
            . ' INTO ' . $this->into;

        if ($this->values != null && count($this->values) > 0) {
            $ret .= ' VALUES ' . Array2d::build($this->values);
        } elseif ($this->set != null && count($this->set) > 0) {
            $ret .= ' SET ' . SetOperation::build($this->set);
        } elseif ($this->select != null && strlen($this->select) > 0) {
            $ret .= ' ' . $this->select->build();
        }

        if ($this->onDuplicateSet != null && count($this->onDuplicateSet) > 0) {
            $ret .= ' ON DUPLICATE KEY UPDATE ' . SetOperation::build($this->onDuplicateSet);
        }

        return $ret;
    }

    /**
     * @param Parser     $parser the instance that requests parsing
     * @param TokensList $list   the list of tokens to be parsed
     */
    public function parse(Parser $parser, TokensList $list)
    {
        ++$list->idx; // Skipping `INSERT`.

        // parse any options if provided
        $this->options = OptionsArray::parse(
            $parser,
            $list,
            static::$OPTIONS
        );
        ++$list->idx;

        /**
         * The state of the parser.
         *
         * Below are the states of the parser.
         *
         *      0 ---------------------------------[ INTO ]----------------------------------> 1
         *
         *      1 -------------------------[ VALUES/VALUE/SET/SELECT ]-----------------------> 2
         *
         *      2 -------------------------[ ON DUPLICATE KEY UPDATE ]-----------------------> 3
         *
         * @var int
         */
        $state = 0;

        /**
         * For keeping track of semi-states on encountering
         * ON DUPLICATE KEY UPDATE ...
         */
        $miniState = 0;

        for (; $list->idx < $list->count; ++$list->idx) {
            /**
             * Token parsed at this moment.
             *
             * @var Token
             */
            $token = $list->tokens[$list->idx];

            // End of statement.
            if ($token->type === Token::TYPE_DELIMITER) {
                break;
            }

            // Skipping whitespaces and comments.
            if (($token->type === Token::TYPE_WHITESPACE) || ($token->type === Token::TYPE_COMMENT)) {
                continue;
            }

            if ($state === 0) {
                if ($token->type === Token::TYPE_KEYWORD
<<<<<<< HEAD
                    && $token->keyword !== 'INTO'
                ) {
                    $parser->error('Unexpected keyword.', $token);
                    break;
                }

                ++$list->idx;
                $this->into = IntoKeyword::parse(
                    $parser,
                    $list,
                    array('fromInsert' => true)
                );

                $state = 1;
            } elseif ($state === 1) {
                if ($token->type === Token::TYPE_KEYWORD) {
                    if ($token->keyword === 'VALUE'
                        || $token->keyword === 'VALUES'
=======
                    && $token->value !== 'INTO'
                ) {
                    $parser->error('Unexpected keyword.', $token);
                    break;
                } else {
                    ++$list->idx;
                    $this->into = IntoKeyword::parse(
                        $parser,
                        $list,
                        array('fromInsert' => true)
                    );
                }

                $state = 1;
            } elseif ($state === 1) {
                if ($token->type === Token::TYPE_KEYWORD) {
                    if ($token->value === 'VALUE'
                        || $token->value === 'VALUES'
>>>>>>> 963d7f7adf76dfd7a7dbc54b828074e76cfb4d65
                    ) {
                        ++$list->idx; // skip VALUES

                        $this->values = Array2d::parse($parser, $list);
<<<<<<< HEAD
                    } elseif ($token->keyword === 'SET') {
                        ++$list->idx; // skip SET

                        $this->set = SetOperation::parse($parser, $list);
                    } elseif ($token->keyword === 'SELECT') {
=======
                    } elseif ($token->value === 'SET') {
                        ++$list->idx; // skip SET

                        $this->set = SetOperation::parse($parser, $list);
                    } elseif ($token->value === 'SELECT') {
>>>>>>> 963d7f7adf76dfd7a7dbc54b828074e76cfb4d65
                        $this->select = new SelectStatement($parser, $list);
                    } else {
                        $parser->error(
                            'Unexpected keyword.',
                            $token
                        );
                        break;
                    }
                    $state = 2;
                    $miniState = 1;
                } else {
                    $parser->error(
                        'Unexpected token.',
                        $token
                    );
                    break;
                }
            } elseif ($state == 2) {
                $lastCount = $miniState;

<<<<<<< HEAD
                if ($miniState === 1 && $token->keyword === 'ON') {
                    ++$miniState;
                } elseif ($miniState === 2 && $token->keyword === 'DUPLICATE') {
                    ++$miniState;
                } elseif ($miniState === 3 && $token->keyword === 'KEY') {
                    ++$miniState;
                } elseif ($miniState === 4 && $token->keyword === 'UPDATE') {
=======
                if ($miniState === 1 && $token->value === 'ON') {
                    ++$miniState;
                } elseif ($miniState === 2 && $token->value === 'DUPLICATE') {
                    ++$miniState;
                } elseif ($miniState === 3 && $token->value === 'KEY') {
                    ++$miniState;
                } elseif ($miniState === 4 && $token->value === 'UPDATE') {
>>>>>>> 963d7f7adf76dfd7a7dbc54b828074e76cfb4d65
                    ++$miniState;
                }

                if ($lastCount === $miniState) {
                    $parser->error(
                        'Unexpected token.',
                        $token
                    );
                    break;
                }

                if ($miniState === 5) {
                    ++$list->idx;
                    $this->onDuplicateSet = SetOperation::parse($parser, $list);
                    $state = 3;
                }
            }
        }

        --$list->idx;
    }
}
