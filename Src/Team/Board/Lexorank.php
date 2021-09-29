<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

/**
 * Lexorank for Kantodo
 * @author Lukáš Koliandr
 */

namespace Kantodo\Team\Board;

use InvalidArgumentException;

class Lexorank
{

    private $JUMP; // recommended power of 2
    private $BLOCK_COUNT;
    private $BLOCK_SIZE; // recommended number less than 3 within

    /////////////////////
    /// DO NOT CHANGE ///
    /////////////////////

    private $RANK_SIZE;
    private $MIN = 'A';
    private $MAX = 'z';

    private $MIN_VALUE;
    private $MAX_VALUE;

    // count of single block combinations
    private $COMB_COUNT; // 52

    /**
     * @param   int  $JUMP         recommended power of 2
     * @param   int  $BLOCK_COUNT  recommended number less than 15
     * @param   int  $BLOCK_SIZE   recommended number less than 3 within
     *
     * @throws InvalidArgumentException $JUMP or $BLOCK_COUNT or $BLOCK_SIZE is less than 1
     * @return  void
     **/
    public function __construct(int $JUMP = 8, int $BLOCK_COUNT = 5, int $BLOCK_SIZE = 3)
    {
        if ($JUMP < 1 || $BLOCK_COUNT < 1 || $BLOCK_SIZE < 1) {
            throw new InvalidArgumentException();
        }

        $this->JUMP        = $JUMP;
        $this->BLOCK_COUNT = $BLOCK_COUNT;
        $this->BLOCK_SIZE  = $BLOCK_SIZE;
        $this->RANK_SIZE   = $BLOCK_COUNT * $BLOCK_SIZE;

        $this->MIN_VALUE  = str_repeat($this->MIN, $BLOCK_SIZE);
        $this->MAX_VALUE  = str_repeat($this->MAX, $BLOCK_SIZE);
        $this->COMB_COUNT = $this->Normalize($this->MAX);

    }

    /**
     * Generate rank
     *
     * @param   string  $previus
     * @param   string  $next
     *
     * @return  string|false  return false on not enough space between 2 lexoranks
     */
    public function generate(string $previus = null, string $next = null)
    {
        // $before is not set
        if (strlen($previus) == 0 || $previus === null) {
            $previus = $this->MIN_VALUE;
        }

        // $after is not set
        if (strlen($next) == 0 || $next === null) {
            $next = $this->MAX_VALUE;
        }

        $rank  = "";
        $index = 0;

        while (true) {
            // not enough space between 2 lexoranks
            if ($index > $this->RANK_SIZE) {
                return false;
            }

            $before = $this->getBlock($previus, $index, $this->MIN);
            $after  = $this->getBlock($next, $index, $this->MAX);

            if ($before === $next) {
                $rank .= $before;
                $index += $this->BLOCK_SIZE;
                continue;
            }

            $middle = $this->getMiddle($before, $after);

            if ($middle === $before || $middle === $after) {
                $rank .= $before;
                $index += $this->BLOCK_SIZE;
                continue;
            }

            $rank .= $middle;
            break;

        }

        return $rank;

    }

    /**
     * Get block from string
     *
     * @param   string  $str          rank
     * @param   int     $index
     * @param   string  $defaultChar
     *
     * @return  string block
     */
    public function getBlock(string $str, int $index, string $defaultChar)
    {
        $block  = "";
        $strlen = strlen($str);
        $i      = 0;

        for ($i = 0; $i < $this->BLOCK_SIZE; $i++) {
            if ($strlen <= $index + $i) {
                $block .= str_repeat($defaultChar, $this->BLOCK_SIZE - $i);
                return $block;
            }
            $block .= $str[$index + $i];
        }
        return $block;
    }

    /**
     * Convert character to ASCII numeric code and normalize it
     *
     * @param   string  $char
     *
     * @return  int        normalized ascii numeric code
     */
    public function normalize(string $char)
    {
        $ascii = ord($char);

        // uppercase
        if ($ascii <= 90) {
            return $ascii - 64;
        }

        // lowercase
        return $ascii - 70;
    }

    /**
     * Convert block to Number
     *
     * @throws InvalidArgumentException block has not valid size
     *
     * @param   string  $block
     *
     * @return  int
     */
    public function blockToNumber(string $block)
    {
        /*
        ╭──────╮
        ╭──┤LEGEND├────────────────────────────────────────────────────╮
        │  ╰──────╯                                                    │
        │ Symbol │ Description                                         │
        ├────────┼─────────────────────────────────────────────────────┤
        │   ^    │ Power (5^2 = 25)                                    │
        ├────────┼─────────────────────────────────────────────────────┤
        │   F    │ First block character from left                     │
        ├────────┼─────────────────────────────────────────────────────┤
        │   S    │ Second block character from left                    │
        ├────────┼─────────────────────────────────────────────────────┤
        │   T    │ Third block character from left                     │
        ├────────┴─────────────────────────────────────────────────────┤
        │ BLOCK = 52^2 * (F - 1) + 52^1 * (S - 1) + 52^0 * (T - 1) + 1 │
        ╰──────────────────────────────────────────────────────────────╯

        ╭────────────────╮
        │ BLOCK_SIZE = 3 │
        ╰────────────────╯
        example:
        AAB = 52^2 * (1 - 1) + 52^1 * (1 - 1) + 52^0 * (2 - 1) + 1
        AAB = 0 + 0 + 1 + 1
        AAB = 2
        ...
        ...
        zzz = 52^2 * (52 - 1) + 52^1 * (52 - 1) + 52^0 * (52 - 1) + 1
        zzz = 2704 * 51 + 52 * 51 + 51 + 1
        zzz = 140 608

         */

        if (strlen($block) != $this->BLOCK_SIZE) {
            throw new InvalidArgumentException();
        }

        $num = 0;

        for ($i = 0; $i < $this->BLOCK_SIZE - 1; $i++) {
            $tmp = ($this->normalize($block[$i]) - 1) * pow($this->COMB_COUNT, $this->BLOCK_SIZE - 1 - $i);
            $num += $tmp;
        }

        $num += $this->normalize($block[$this->BLOCK_SIZE - 1]);

        return $num;
    }

    /**
     * Convert normalized number to block
     *
     * @param   int  $number  normalized number
     *
     * @return  string block
     */
    public function numberToBlock(int $number)
    {
        $block = "";

        for ($i = $this->BLOCK_SIZE - 1; $i >= 0; $i--) {
            $pow = pow($this->COMB_COUNT, $i);

            $quotient = floor($number / $pow);

            $number = $number % $pow;

            if ($number > 0) {
                $quotient++;
            }

            if ($quotient == 0) {
                $block .= $this->MIN;
            }

            // uppercase
            else if ($quotient <= 26) {
                $block .= chr($quotient + 64);
            }

            // lowercase
            else {
                $block .= chr($quotient + 70);
            }

        }

        return $block;

    }

    /**
     * Calculate the middle between 2 blocks
     *
     * @param   string  $blockBefore
     * @param   string  $blockAfter
     *
     * @return  string
     */
    public function getMiddle(string $blockBefore, string $blockAfter)
    {
        $blockBeforeNum = $this->blockToNumber($blockBefore);
        $blockAfterNum  = $this->blockToNumber($blockAfter);

        if ($blockBefore === $this->MIN_VALUE && $blockAfter === $this->MAX_VALUE) {
            $middle = floor(($blockBeforeNum + $blockAfterNum) / 2);
            return $this->numberToBlock($middle);
        }

        if ($blockBefore === $this->MIN_VALUE) {
            $tmpJump = $this->JUMP;

            while ($blockAfterNum - $tmpJump <= $blockBeforeNum - 1) {
                $tmpJump /= 2;
                if ($tmpJump < 1) {
                    $tmpJump = 0;
                    break;
                }

            }

            $middle = $blockAfterNum - $tmpJump;

            return $this->numberToBlock($middle);
        }

        $tmpJump = $this->JUMP;

        while ($blockBeforeNum + $tmpJump >= $blockAfterNum) {
            $tmpJump /= 2;
            if ($tmpJump < 1) {
                $tmpJump = 0;
                break;
            }

        }

        $middle = $blockBeforeNum + $tmpJump;

        return $this->numberToBlock($middle);

    }

}
