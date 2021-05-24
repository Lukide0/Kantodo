/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */


/**
 * Lexorank
 * @author Lukáš Koliandr
 */


function Lexorank(before, after) 
{   
    //////////////////////
    /// YOU CAN CHANGE ///
    //////////////////////
    const JUMP = 4;
    const BLOCK_COUNT = 5; // 5 
    const BLOCK_SIZE = 3; // 3

    
    /////////////////////
    /// DO NOT CHANGE ///
    /////////////////////
    const MAX = 'z'; 
    const MIN = 'A'; 
    
    const MIN_VALUE = MIN.repeat(BLOCK_SIZE);
    const MAX_VALUE = MAX.repeat(BLOCK_SIZE);
    
    const COMB = Normalize(MAX.charCodeAt(0));
    
    if (before == '' || !before) before = MIN;
    if (after == '' || !after) after = MAX;
    
    let rank = "";
    let index = 0;
    
    
    while(true) 
    {
        if (index > SIZE * BLOCK_SIZE) break;
        
        let prev = GetBlock(before, index, MIN);
        let next = GetBlock(after, index, MAX);
        
        
        if (prev === next) 
        {
            rank += prev;
            index += BLOCK_SIZE;
            continue;
        }
        
        let mid = Middle(prev, next);
        
        if (mid === prev || mid === next) 
        {
            rank += prev;
            index += BLOCK_SIZE;
            continue;
            
        }

        rank += mid;
        break;         
    }           
    
    return rank;
    

    function GetBlock(str, i, defaultChar){
        let tmpIndex = 0;
        let tmpChar = "";
        while(tmpIndex < BLOCK_SIZE) 
        {
            if (tmpIndex + i >= str.length) {
                tmpChar += defaultChar;
            }
            else {
                tmpChar += str.charAt(tmpIndex + i);
            }
            tmpIndex++;
        }
        return tmpChar;

    }

    function Middle(min, max) 
    {
        let minN = BlockToNumber(min);
        let maxN = BlockToNumber(max);
        if (min === MIN_VALUE && max === MAX_VALUE)
        {
            mid = Math.floor((minN + maxN) / 2);               
            return NumberToBlock(mid);
        }
        
        if (min === MIN_VALUE)
        {
            let jumpTMP = JUMP;
            while(maxN - jumpTMP <= minN - 1) 
            {
                jumpTMP /= 2;
                if (jumpTMP < 1) 
                {
                    jumpTMP = 0;
                    break;
                }
            }
            
            mid = maxN - jumpTMP;
            
            return NumberToBlock(mid);
        }

        let jumpTMP = JUMP;
        while(minN + jumpTMP >= maxN) 
        {
            jumpTMP /= 2;

            if (jumpTMP < 1) 
            {
                jumpTMP = 0;
                break;
            }
        }
        mid = minN + jumpTMP;
        return NumberToBlock(mid);
    }

    function BlockToNumber(str) 
    {
        
        let tmp = 0;
        for (let i = 0; i < str.length - 1; i++) {
            let num = (Normalize(str.charCodeAt(i)) - 1) * Math.pow(COMB, BLOCK_SIZE - i - 1); // ?
            tmp += num;
        }
        tmp += Normalize(str.charCodeAt(str.length - 1));
        return tmp;                
        
    }
    function Normalize(n) 
    {
        if (n <= 90) return n - 64; // upper case
        return n - 70; // lower case
    }

    function NumberToBlock(num)
    {
        let block = "";
        for(let i = BLOCK_SIZE - 1; i >= 0; i--) 
        {
            let p = Math.pow(COMB, i);
            let quotient = Math.floor(num / p);
            
            num = (num % p);
            if (num > 0) 
            {
                quotient++;
            }

            if (quotient > 0) 
            {
                if (quotient <= 26) block += String.fromCharCode(quotient + 64);
                else block += String.fromCharCode(quotient + 70);
            } else 
            {
                block += MIN;
            }

        }

        return block;
    }
}