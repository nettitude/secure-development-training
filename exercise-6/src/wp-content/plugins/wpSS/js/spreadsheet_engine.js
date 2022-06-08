/**
 * WordPress Spreadsheet Engine. Release 0.6
 * modifications (c) 2007 Tim Rohrer, released under GPL2
 *  
 * based on (TrimPath's orginal Gnu Public License notice follows)
 **
 * TrimPath Spreadsheet. Release 1.0.15.
 * Copyright (C) 2005 - 2006 TrimPath.
 * 
 * This program is free software; you can redistribute it and/or 
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed WITHOUT ANY WARRANTY; without even the 
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 * See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */
var TrimPath;

(function(safeEval) { // Using a closure to keep global namespace clean.
    var theDate       = Date;
    var theMath       = Math;
    var theString     = String;
    var theNumber     = Number;
    var theParseInt   = parseInt;
    var theParseFloat = parseFloat;
    var theIsNaN      = isNaN;

    if (TrimPath == null)
        TrimPath = {};
    if (TrimPath.TEST == null)
        TrimPath.TEST = {}; // For exposing to testing only.

    var RE_N = /[\$,\s]/g;

    //////////////////////////////////////////////////////////////////////////////
    // The spreadsheetEngine should be DOM/DHTML independent, handling only
    // formula recalculations only, not UI.
    //
    var eng = TrimPath.spreadsheetEngine = {
        ERROR : "#VALUE!",
        standardFunctions : {
            /* FX: */ AVERAGE : function(values) { 
                var arr = foldPrepare(values, arguments);
                return eng.standardFunctions.SUM(arr) / eng.standardFunctions.COUNT(arr); 
            },
            /* synonym: */ AVG : function(values) { 
                return eng.standardFunctions.AVERAGE(values) },
            /* FX: */ COUNT : function(values) { return fold(foldPrepare(values, arguments), COUNT2, 0); },
            /* FX: */ SUM : function(values) { return fold(foldPrepare(values, arguments), SUM2, 0, true); },
            /* FX: */ MAX : function(values) { return fold(foldPrepare(values, arguments), MAX2, theNumber.MIN_VALUE, true); },
            /* FX: */ MIN : function(values) { return fold(foldPrepare(values, arguments), MIN2, theNumber.MAX_VALUE, true); },
            /* FX: */ ABS     : function(v) { return theMath.abs(eng.standardFunctions.N(v)); },
            /* FX: */ CEILING : function(v) { return theMath.ceil(eng.standardFunctions.N(v)); },
            /* FX: */ FLOOR   : function(v) { return theMath.floor(eng.standardFunctions.N(v)); },
            /* synonym : */ INT : function(v) { return theMath.floor(eng.standardFunctions.N(v)); },
            /* FX: */ ROUND   : function(v) { return theMath.round(eng.standardFunctions.N(v)); },
            /* FX: */ RAND    : function(v) { return theMath.random(); },
            /* synonym: */ RND : function(v) { return theMath.random(); },
            /* FX: */ TRUE    : function() { return true; },
            /* FX: */ FALSE   : function() { return false; },
            /* NEW 2007: */ NOW : function() { return new Date ( ); },
            /* NEW 2007: */ TODAY : function() { return Date( Math.floor( new Date ( ) ) ); },
            /* NEW 2007: */ DAYSFROM : function(year, month, day) { 
              return Math.floor( (new Date() - new Date (year, (month - 1), day)) / 86400000); },
            /* FX: */ FIXED : function(v, decimals, noCommas) { 
                if (decimals == null)
                    decimals = 2;
                var x = theMath.pow(10, decimals);
                var s = theString(theMath.round(eng.standardFunctions.N(v) * x) / x); 
                var p = s.indexOf('.');
                if (p < 0) {
                    p = s.length;
                    s += '.';
                }
                for (var i = s.length - p - 1; i < decimals; i++)
                    s += '0';
                if (noCommas == true) // Treats null as false.
                    return s;
                var arr    = s.replace('-', '').split('.');
                var result = [];
                var first  = true;
                while (arr[0].length > 0) { // LHS of decimal point.
                    if (!first)
                        result.unshift(',');
                    result.unshift(arr[0].slice(-3));
                    arr[0] = arr[0].slice(0, -3);
                    first = false;
                }
                if (decimals > 0) {
                    result.push('.');
                    var first = true;
                    while (arr[1].length > 0) { // RHS of decimal point.
                        if (!first)
                            result.push(',');
                        result.push(arr[1].slice(0, 3));
                        arr[1] = arr[1].slice(3);
                        first = false;
                    }
                }
                if (v < 0)
                    return '-' + result.join('');
                return result.join('');
            },
            /* FX: */ DOLLAR : function(v, decimals, symbol) { 
                if (decimals == null)
                    decimals = 2;
                if (symbol == null)
                    symbol = '$'
                var r = eng.standardFunctions.FIXED(v, decimals, false);
                if (v >= 0) 
                    return symbol + r; 
                return '-' + symbol + r.slice(1);
            },
            /* FX: */ VALUE : function(v) { return theParseFloat(v); },
            /* FX: */ N : function(v) { if (v == null)             return 0;
                              if (v instanceof theDate)  return v.getTime();
                              if (typeof(v) == 'object') v = v.toString();
                              if (typeof(v) == 'string') v = theParseFloat(v.replace(RE_N, ''));
                              if (theIsNaN(v))           return 0;
                              if (typeof(v) == 'number') return v;
                              if (v == true)             return 1;
                              return 0; },
            /* FX: */ PI  : function(v) { return theMath.PI; },
            /* FX: */ POWER : function(x, y) {
                return theMath.pow(x, y);
            }
        },
        calc : function(cellProvider, context, startFuel) {
            // Returns null if all done with a complete calc() run.
            // Else, returns a non-null continuation function if we ran out of fuel.  
            // The continuation function can then be later invoked with more fuel value.
            // The fuelStart is either null (which forces a complete calc() to the finish) 
            // or is an integer > 0 to slice up long calc() runs.  A fuelStart number
            // is roughly matches the number of cells to visit per calc() run.
            var calcState = { 
                cellProvider : cellProvider, 
                context      : (context != null ? context : {}),
                row          : 1, 
                col          : 1, 
                done         : false,
                stack        : [],
                calcMore : function(moreFuel) {
                    calcState.fuel = moreFuel;
                    return calcLoop(calcState);
                }
            };
            return calcState.calcMore(startFuel);
        },
        Cell : function() {} // Prototype setup is later.
    }

    for (var k in eng.standardFunctions) {
        var kLower = k.toLowerCase();
        if (kLower != k)
            eng.standardFunctions[kLower] = eng.standardFunctions[k];
    }

    var calcLoop = function(calcState) {
        with (calcState) {
            if (done == true)
                return null;
            while (fuel == null || fuel > 0) {
                if (stack.length > 0) {
                    var workFunc = stack.pop();
                    if (workFunc != null)
                        workFunc(calcState);
                } else if (cellProvider.formulaCells != null) {
                    if (cellProvider.formulaCells.length > 0) {
                        var loc = cellProvider.formulaCells.shift();
                        visitCell(calcState, loc[0], loc[1]);
                    } else {
                        done = true;
                        return null;
                    }                    
                } else {
                    if (visitCell(calcState, row, col) == true) {
                        done = true;
                        return null;
                    }

                    if (col >= cellProvider.getNumberOfColumns(row)) {
                        row = row + 1;
                        col = 1;
                    } else
                        col = col + 1; // Sweep through columns first.
                }
                
                if (fuel != null)
                    fuel -= 1;
            }
        }
        return calcState.calcMore;
    }

    var visitCell = function(calcState, r, c) { // Returns true if done with all cells.
        with (calcState) {
            var cell = cellProvider.getCell(r, c);
            if (cell == null)
                return true;

            var value = cell.getValue();
            if (value == null) {
                var formula = cell.getFormula();
                if (formula != null) {
                    var firstChar = formula.charAt(0);
                    if (firstChar == '=') {
                        var formulaFunc = cell.getFormulaFunc();
                        if (formulaFunc == null ||
                            formulaFunc.formula != formula) {
                            formulaFunc = null;
                            try {
                                var dependencies = {};
                                var body = parseFormula(formula.substring(1), dependencies, calcState);
                                formulaFunc = safeEval("var TrimPath_spreadsheet_formula = " +
                                    "function(__CELL_PROVIDER, __CONTEXT, __STD_FUNCS) { " +
                                      "with (__CELL_PROVIDER) { with (__STD_FUNCS) { " +
                                      "with (__CONTEXT) { return (" + body + "); } } } }; TrimPath_spreadsheet_formula");
                                formulaFunc.formula      = formula;
                                formulaFunc.dependencies = dependencies;
                                cell.setFormulaFunc(formulaFunc);
                            } catch (e) {
                                cell.setValue(eng.ERROR, e);
                            }
                        }
                        if (formulaFunc != null) {
                            stack.push(makeFormulaEval(r, c));

                            // Push the cell's dependencies, first checking for any cycles. 
                            var dependencies = formulaFunc.dependencies;
                            for (var k in dependencies) {
                                if (dependencies[k] instanceof Array &&
                                    checkCycles(stack, dependencies[k][0], dependencies[k][1]) == true) {
                                    cell.setValue(eng.ERROR, "cycle detected");
                                    stack.pop();
                                    return false;
                                }
                            }
                            for (var k in dependencies)
                                if (dependencies[k] instanceof Array)
                                    stack.push(makeCellVisit(dependencies[k][0], dependencies[k][1]));
                        }
                    } else
                        cell.setValue(parseFormulaStatic(formula));
                }
            }
        }
        return false;
    }

    var makeCellVisit = function(row, col) {
        var func = function(calcState) { return visitCell(calcState, row, col); };
        func.row = row;
        func.col = col;
        return func;
    }

    var RE_AMP = /&/g;
    var RE_LT  = /</g;
    var RE_GT  = />/g;

    var makeFormulaEval = function(row, col) {
        var func = function(calcState) {
            var cell = calcState.cellProvider.getCell(row, col);
            if (cell != null) {
                var formulaFunc = cell.getFormulaFunc();
                if (formulaFunc != null) {
                    try {
                        var v = formulaFunc(calcState.cellProvider, calcState.context, eng.standardFunctions);
                        if (typeof(v) == "string")
                            v = v.replace(RE_AMP, '&amp;').replace(RE_LT, '&lt;').replace(RE_GT, '&gt;')
                        cell.setValue(v);
                    } catch (e) {
                        cell.setValue(eng.ERROR, e);
                    }
                }
            }
        }
        func.row = row;
        func.col = col;
        return func;
    }

    var RE_REF_CELL  = TrimPath.spreadsheetEngine.RE_REF_CELL  = /\$?([a-zA-Z]+)\$?([0-9]+)/g;
    var RE_REF_RANGE = TrimPath.spreadsheetEngine.RE_REF_RANGE = /\$?([a-zA-Z]+)\$?([0-9]+):\$?([a-zA-Z]+)\$?([0-9]+)/g;

    // Parse formula (without "=" prefix) like "123+SUM(A1:A6)/D5" into JavaScript expression string.
    var parseFormula = TrimPath.TEST.parseFormula = function(formula, dependencies, calcState) { 
        var nrows = null;
        var ncols = null;
        if (calcState != null &&
            calcState.cellProvider != null) {
            nrows = calcState.cellProvider.nrows;
            ncols = calcState.cellProvider.ncols;
        }
        var arrayReferencesFixed = formula.replace(RE_REF_RANGE, 
            function(ignored, startColStr, startRowStr, endColStr, endRowStr) {
                var res = [];
                var startCol = columnLabelIndex(startColStr.toUpperCase());
                var startRow = theParseInt(startRowStr);
                var endCol   = columnLabelIndex(endColStr.toUpperCase());
                var endRow   = theParseInt(endRowStr);
                if (ncols != null)
                    endCol = theMath.min(endCol, ncols);
                if (nrows != null)
                    endRow = theMath.min(endRow, nrows);
                for (var r = startRow; r <= endRow; r++)
                    for (var c = startCol; c <= endCol; c++)
                        res.push(columnLabelString(c) + r);
                return "[" + res.join(",") + "]";
            }
        );
        var result = arrayReferencesFixed.replace(RE_REF_CELL, 
            function(ignored, colStr, rowStr) {
                colStr = colStr.toUpperCase();
                if (dependencies != null) 
                    dependencies[colStr + rowStr] = [theParseInt(rowStr), columnLabelIndex(colStr)]; 
                return "(getCell((" + rowStr + "),'" + colStr + "').getValue())";
            }
        );
        return result;
    }

    // Parse static formula value like "123.0" or "hello" or "'hello world" into JavaScript value.
    var parseFormulaStatic = eng.parseFormulaStatic = function(formula) { 
        if (formula == null)
            return null;
        var formulaNum = formula.replace(RE_N, '');
        var value = theParseFloat(formulaNum);
        if (theIsNaN(value))
            value = theParseInt(formulaNum);
        if (theIsNaN(value))
            value = (formula.charAt(0) == "'" ? formula.substring(1) : formula);
        return value;
    }

    var checkCycles = function(stack, row, col) {
        for (var i = 0; i < stack.length; i++) {
            var item = stack[i];
            if (item.row != null && item.col != null &&
                item.row == row  && item.col == col)
                return true;
        }
        return false;
    }

    var foldPrepare = function(firstArg, theArguments) { // Computes the best array-like arguments for calling fold().
        if (firstArg != null &&
            firstArg instanceof Object &&
            firstArg["length"] != null)
            return firstArg;
        return theArguments;
    }

    var fold = function(arr, funcOfTwoArgs, result, castToN) {
        for (var i = 0; i < arr.length; i++)
            result = funcOfTwoArgs(result, (castToN == true ? eng.standardFunctions.N(arr[i]) : arr[i]));
        return result;
    }

    var SUM2   = function(x, y) { return x + y; }
    var MAX2   = function(x, y) { return x > y ? x : y; }
    var MIN2   = function(x, y) { return x < y ? x : y; }
    var COUNT2 = function(x, y) { return (y != null) ? x + 1 : x; }

    // Cells don't know their coordinates, to make shifting easier.
    //
    eng.Cell.prototype.getError = function()     { return this.error; };
    eng.Cell.prototype.getValue = function()     { return this.value; };
    eng.Cell.prototype.setValue = function(v, e) { this.value = v; this.error = e; };

    eng.Cell.prototype.getFormula     = function()  { return this.formula; };     // Like "=1+2+3" or "'hello" or "1234.5"
    eng.Cell.prototype.setFormula     = function(v) { this.formula = v; };
    eng.Cell.prototype.getFormulaFunc = function()  { return this.formulaFunc; };
    eng.Cell.prototype.setFormulaFunc = function(v) { this.formulaFunc = v; };

    eng.Cell.prototype.toString = function() { return "Cell:[" + this.getFormula() + ": " + this.getValue() + ": " + this.getError() + "]"; }

    var columnLabelString = eng.columnLabelString = function(index) {
        // The index is 1 based.  Convert 1 to A, 2 to B, 25 to Y, 26 to Z, 27 to AA, 28 to AB.
        // TODO: Got a bug when index > 676.  675==YZ.  676==YZ.  677== AAA, which skips ZA series.
        //       In the spirit of billg, who needs more than 676 columns anyways?
        var b = (index - 1).toString(26).toUpperCase();   // Radix is 26.
        var c = [];
        for (var i = 0; i < b.length; i++) {
            var x = b.charCodeAt(i);
            if (i <= 0 && b.length > 1)                   // Leftmost digit is special, where 1 is A.
                x = x - 1;
            if (x <= 57)                                  // x <= '9'.
                c.push(String.fromCharCode(x - 48 + 65)); // x - '0' + 'A'.
            else
                c.push(String.fromCharCode(x + 10));
        }
        return c.join("");
    }

    var columnLabelIndex = eng.columnLabelIndex = function(str) {
        // Converts A to 1, B to 2, Z to 26, AA to 27.
        var num = 0;
        for (var i = 0; i < str.length; i++) {
            var digit = str.charCodeAt(i) - 65 + 1;       // 65 == 'A'.
            num = (num * 26) + digit;
        }
        return num;
    }

    var parseLocation = eng.parseLocation = function(locStr) { // With input of "A1", "B4", "F20",
        if (locStr != null &&                                  // will return [1,1], [4,2], [20,6].
            locStr.length > 0 &&
            locStr != "&nbsp;") {
            for (var firstNum = 0; firstNum < locStr.length; firstNum++)
                if (locStr.charCodeAt(firstNum) <= 57) // 57 == '9'
                    break;
            return [ theParseInt(locStr.substring(firstNum)),
                     columnLabelIndex(locStr.substring(0, firstNum)) ];
        }
        return null;
    }
}) (function(str) { return eval(str); }); // The safeEval occurs only in outer, global scope.
