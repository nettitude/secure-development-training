/**
 * WordPress Spreadsheet User Interface. Release 0.6
 * all modifications (c) 2007 Tim Rohrer, released under GPL2
 *  
 * based on (TrimPath's original Gnu Public License notice follows)
 **
 * TrimPath Spreadsheet. Release 1.0.15
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

(function() { // Using a closure to keep global namespace clean.
    var theMath     = Math;
    var theIsNaN    = isNaN;
    var theParseInt = parseInt;

    if (TrimPath == null)
        TrimPath = new Object();
    if (TrimPath.TEST == null)
        TrimPath.TEST = new Object(); // For exposing to testing only.

    //////////////////////////////////////////////////////////////////////////////
    // This file depends on spreadsheet_engine.js.  It holds all UI related code
    // and dependencies on a DOM/DHTML environment.
    //
    // Coding/design note: we've tried mightily to not hold onto any DOM 
    // objects or nodes as state in our code due to IE's infamous memory leak.  
    // See http://trimpath.com/project/wiki/InelegantJavaScript
    //
    var log = function(msg) {
        if (document != null) {
            var log = document.getElementById("TrimPath_log");
            if (log) {
                log.appendChild(document.createTextNode(msg));
                log.appendChild(document.createElement("BR"));
            }
        }
    }

    var makeEventHandler = function(handler) {
        return function(evt) {
            evt = (evt) ? evt : ((window.event) ? window.event : null);
            if (evt) {
                var target = (evt.target) ? evt.target : evt.srcElement;
                return handler(evt, target);
            }
        }
    }

    if (TrimPath.spreadsheetEvents == null)
        TrimPath.spreadsheetEvents = { 
            onBeforeCalc : [],
            onAfterCalc : [],
            onChanged  : [], onBeforeChanged  : [], 
            onCellEdit : [], onBeforeCellEdit : [], 
            onCellEditDone     : [], 
            onCellEditAbandon  : [], 
            onCellEditActivate : [], onBeforeCellEditActivate : [], 
            onCellMouseDown    : [],
            onCellMouseUp      : [],
            onAfterDecorate    : []
        };

    var runSpreadsheetEvents = TrimPath.runSpreadsheetEvents = function(eventName, arg, extra) {
        var events = TrimPath.spreadsheetEvents[eventName];
        if (events != null) {
            for (var i = 0; i < events.length; i++)
                events[i](arg, extra);
        }
    }

    var spreadsheet = TrimPath.spreadsheet = {
        ERROR                    : TrimPath.spreadsheetEngine.ERROR,
        DEFAULT_ROW_HEADER_WIDTH : "30px",
        lastKeyCode              : null,
        isIE : (navigator.userAgent.toLowerCase().indexOf("msie") != -1) ? true : false,

        initDocument : function(doc) {
            if (doc == null)
                doc = document;
            var tables = doc.body.getElementsByTagName("TABLE");    
            for (var i = 0; i < tables.length; i++)
                if (isClass(tables[i], "spreadsheet"))
                    spreadsheet.decorateSpreadsheet(tables[i], doc);
        },
        decorateSpreadsheet : function(tableBody, doc, recalc) {
            var d = new Date();
            TrimPath.spreadsheet.logTime('begin decorateSpreadsheet');
            if (doc == null)
                doc = document;
            if (tableBody.id == null ||
                tableBody.id.length <= 0)
                tableBody.id = genId("spreadsheet");

            var filterForFirstColumn = function(node) { // Keeps the first td/th of each tr.
                if (node.nodeType != 1)
                    return true;
                if ((node.tagName == "COL") &&
                    (node.parentNode == tableBody ||           // Mozilla has TABLE.COL structure.
                     node.parentNode.parentNode == tableBody)) // IE has TABLE.COLGROUP.COL structure.
                    return isFirstChild(node);
                if ((node.parentNode.parentNode.parentNode == tableBody) &&
                    (node.tagName == "TD" || node.tagName == "TH"))
                    return isFirstChild(node);
                return true;
            }

            var filterForTHEAD = function(node) { // Keeps thead, col, colgroup, but not tbody and tfoot.
                if (node.nodeType != 1)
                    return true;
                if ((node.parentNode == tableBody) &&
                    (node.tagName == "TBODY" ||
                     node.tagName == "TFOOT"))
                    return false;
                return true;
            }

            // Categorize the parents of tableBody.
            var parents = { spreadsheetEditor : null, spreadsheetScroll : null, spreadsheetBars : null };
            categorizeParents(tableBody, parents);

            with (parents) {
                if (spreadsheetBars != null) {
                    if (spreadsheetBars.id == null ||
                        spreadsheetBars.id.length <= 0)
                        spreadsheetBars.id = tableBody.id + "_spreadsheetBars";

                    var spreadsheetBarsOutside = spreadsheetBars.className.indexOf('spreadsheetBarsOutside') >= 0;

                    // Add row & column headers to tableBody, if not already.
                    if (tableBody.rows[0] != null && 
                        tableBody.rows[0].cells[0] != null &&
                        !isBarItem(tableBody.rows[0].cells[0])) { 
                        var numColumns = countColumns(tableBody);

                        // Prepend a new row for column headers.
                        var row = doc.createElement("TR");
                        row.id = tableBody.id + "_spreadsheetBar";

                        for (var cell, i = 0; i < numColumns + 1; i++) {
                            cell = doc.createElement("TH");
                            cell.className = "spreadsheetBarItem";
                            cell.noWrap = true;
                            cell.innerHTML = (i <= 0) ? "&nbsp;" : '<div>' + TrimPath.spreadsheetEngine.columnLabelString(i) + '</div>';
                            row.appendChild(cell);
                        }
                        
                        var thead = tableBody.getElementsByTagName("THEAD")[0];
                        if (thead == null) {
                            thead = doc.createElement("THEAD");
                            tableBody.insertBefore(thead, (tableBody.rows[0] != null ? tableBody.rows[0].parentNode : tableBody.firstChild)); 
                        }
                        thead.insertBefore(row, thead.firstChild); 

                        // Prepend row headers to each row.
                        for (var cell, i = 1; i < tableBody.rows.length; i++) {
                            cell = doc.createElement("TH");
                            cell.className = "spreadsheetBarItem";
                            cell.innerHTML = i;
                            if (tableBody.rows[i].cells[0])
                                tableBody.rows[i].insertBefore(cell, tableBody.rows[i].cells[0]);
                            else
                                tableBody.rows[i].appendChild(cell);
                        }
                        
                        // Prepend one colgroup/col element that covers the new row headers.
                        var existingColGroup = tableBody.getElementsByTagName("COLGROUP");
                        var cols = tableBody.getElementsByTagName("COL");
                        if (cols != null &&
                            cols.length > 0) {
                            var col = doc.createElement("COL");
                            col.className = "spreadsheetBarItem";
                            col.setAttribute("width", spreadsheet.DEFAULT_ROW_HEADER_WIDTH);
                            if (existingColGroup != null &&
                                existingColGroup.length > 0) {
                                cols[0].parentNode.insertBefore(col, cols[0]);
                            } else { //for existing spreadsheets created on firefox.
                                var colGroup = document.createElement("COLGROUP");
                                cols[0].parentNode.insertBefore(colGroup, cols[0]);
                                colGroup.appendChild(col);
                                for (var i = 0; i< cols.length; i++) {
                                    colGroup.appendChild(cols[i]);
                                }
                            }

                        }
                    }

                    // Copy a truncated tableBody to be the spreadsheetBarLeft.
                    var spreadsheetBarLeft = doc.getElementById(spreadsheetBars.id + "_spreadsheetBarLeft");
                    if (spreadsheetBarLeft == null) {
                        spreadsheetBarLeft           = copyNodeTree(tableBody, filterForFirstColumn, doc);
                        spreadsheetBarLeft.id        = spreadsheetBars.id + "_spreadsheetBarLeft";
                        spreadsheetBarLeft.className = "spreadsheetBarLeft";
                        
                        //2007 modification as opera doesn't calculate smallest width
                        if (navigator.appName.indexOf("Opera") != -1 ) 
                           { spreadsheetBarLeft.width     = "30"; }
                        else { spreadsheetBarLeft.width     = ""; // Let browser calculate smallest width
                        } //end 2007 mods
                        TrimPath.spreadsheet.syncRowHeights(tableBody, spreadsheetBarLeft);

                        var spreadsheetBarsContainer = spreadsheetBars;
                        if (spreadsheetBarsOutside)
                            spreadsheetBarsContainer = document.getElementById(spreadsheetBarLeft.id + "Container");
                        if (spreadsheetBarsContainer != null)
                            spreadsheetBarsContainer.appendChild(spreadsheetBarLeft);

                    }
   
                    for (var i = 1; i < spreadsheetBarLeft.rows.length; i++)
                        spreadsheetBarLeft.rows[i].onmousedown = rowResizer.start;

                    // Copy a truncated tableBody to be the spreadsheetBarTop.
                    var spreadsheetBarTop = doc.getElementById(spreadsheetBars.id + "_spreadsheetBarTop");
                    if (spreadsheetBarTop == null) {
                        spreadsheetBarTop           = copyNodeTree(tableBody, filterForTHEAD, doc);
                        spreadsheetBarTop.id        = spreadsheetBars.id + "_spreadsheetBarTop";
                        spreadsheetBarTop.className = "spreadsheetBarTop";

                        var spreadsheetBarsContainer = spreadsheetBars;
                        if (spreadsheetBarsOutside)
                            spreadsheetBarsContainer = document.getElementById(spreadsheetBarTop.id + "Container");
                        if (spreadsheetBarsContainer != null)
                            spreadsheetBarsContainer.appendChild(spreadsheetBarTop);
                    }

                    for (var cells = spreadsheetBarTop.rows[0].cells, i = 1; i < cells.length; i++)
                        cells[i].onmousedown = columnResizer.start;

                    // Copy a truncated tableBody to be the spreadsheetBarCorner.
                    var spreadsheetBarCorner = doc.getElementById(spreadsheetBars.id + "_spreadsheetBarCorner");
                    if (spreadsheetBarCorner == null) {
                        spreadsheetBarCorner = copyNodeTree(tableBody, 
                            function(node) { return filterForFirstColumn(node) && filterForTHEAD(node); }, 
                            doc);
                        spreadsheetBarCorner.id        = spreadsheetBars.id + "_spreadsheetBarCorner";
                        spreadsheetBarCorner.className = "spreadsheetBarCorner";
                        //2007 modification as opera doesn't calculate smallest width
                        if (navigator.appName.indexOf("Opera") != -1 ) 
                           { spreadsheetBarCorner.width     = "30"; }
                        else { spreadsheetBarCorner.width     = ""; // Let browser calculate smallest width
                        } //end 2007 mods

                        var spreadsheetBarsContainer = spreadsheetBars;
                        if (spreadsheetBarsOutside)
                            spreadsheetBarsContainer = document.getElementById(spreadsheetBarCorner.id + "Container");
                        if (spreadsheetBarsContainer != null)
                            spreadsheetBarsContainer.appendChild(spreadsheetBarCorner);

                        if (spreadsheetBarCorner.rows[0].cells[0] != null)
                            spreadsheetBarCorner.rows[0].cells[0].onclick = cornerMouseClick;
                    }

                    for (var i = 0; i < tableBody.rows.length; i++)             // Hidden so that underlying tableBody bars
                        tableBody.rows[i].cells[0].style.visiblity = "hidden";  // don't show during resizing columns/rows.
                    tableBody.rows[0].style.visibility = "hidden"; 
                    tableBody.style.top = tableBody.style.left = "0";
                }

                if (spreadsheetScroll != null) {
                    if (spreadsheetScroll.id == null ||
                        spreadsheetScroll.id.length <= 0)
                        spreadsheetScroll.id = tableBody.id + "_spreadsheetScroll";
                    if (spreadsheetScroll.onresize == null)
                        spreadsheetScroll.onresize = makeBarAdjustor(tableBody.id, null, true);
                    if (spreadsheetScroll.onscroll == null)
                        spreadsheetScroll.onscroll = makeBarAdjustor(tableBody.id);
                    spreadsheetScroll.onscroll();
                }

                if (spreadsheetEditor != null) {
                    if (spreadsheetEditor.id == null ||
                        spreadsheetEditor.id.length <= 0)
                        spreadsheetEditor.id = tableBody.id + "_spreadsheetEditor";

                    
                    //Populate the controls.
                    var controls = doc.getElementById(spreadsheetEditor.id + "_spreadsheetControls");
                    if (controls == null) {
                        controls           = doc.createElement("DIV");
                        controls.id        = spreadsheetEditor.id + "_spreadsheetControls";
                        controls.className = "spreadsheetControls";
                        spreadsheetEditor.insertBefore(controls, spreadsheetEditor.firstChild);
                        
                        // 2007 modifications
                        controls.innerHTML = ' <span class="spreadsheetStyle spreadsheetStyleFont">' +
                                '<a href="#fontWeight:bold"  onclick="return TrimPath.spreadsheet.styleToggle(event)"><img src="js/icons/text_bold.png" alt="B" title="bold" /></a>' +
                                '<a href="#fontStyle:italic" onclick="return TrimPath.spreadsheet.styleToggle(event)"><img src="js/icons/text_italic.png" alt="I " title="italic" /></a>' +
                                '<a href="#textDecoration:underline" onclick="return TrimPath.spreadsheet.styleToggle(event)"><img src="js/icons/text_underline.png" alt="U" title="underline" /></a>' +
                                '  <a href="#textAlign:left"   class="spreadsheetStyleAlignLeft"   onclick="return TrimPath.spreadsheet.styleToggle(event)"><img src="js/icons/text_align_left.png" alt="&lt;&lt;" title="align left" /></a>' +
                                '<a href="#textAlign:center" class="spreadsheetStyleAlignCenter" onclick="return TrimPath.spreadsheet.styleToggle(event)"><img src="js/icons/text_align_center.png" alt="==" title="align center" /></a>' +
                                '<a href="#textAlign:right"  class="spreadsheetStyleAlignRight"  onclick="return TrimPath.spreadsheet.styleToggle(event)"><img src="js/icons/text_align_right.png" alt="&gt;&gt;" title="align right" /></a>' +
                                '  <a href="#addRow"  onclick="return TrimPath.spreadsheet.addRow(event)"><img src="js/icons/table_row_insert.png" alt="+row" title="add row" /></a>' +
                                '  <a href="#addCol"  onclick="return TrimPath.spreadsheet.addCol(event)"><img src="js/icons/table_col_insert.png" alt="+col" title="add column" /></a>' +
                                '  <a href="#fixedButton"  class="spreadsheetStyleAlignRight2"  onclick="return TrimPath.spreadsheet.fixedButton(event)"><img src="js/icons/fix2.png" alt="fix2" title="fix 2 decimal places" /></a>' +
                            '</span>';                        
                    }

                    TrimPath.spreadsheet.setupTdIds(tableBody);

                    // Register onclick for tableBody td elements.
                    visitCells(tableBody, function(tr, td, r, c) {
                        if (!isBarItem(td)) {
                            td.onmousedown = cellOnMouseDown;
                            td.ondblclick  = cellOnMouseDoubleClick;
                        }
                    });
                }
            }

            if (recalc != false &&
                !isClass(tableBody, "spreadsheetCalcOff"))
                TrimPath.spreadsheet.calc(tableBody);

            // Re-mark the appropriate header bars as active/selected, since the bars were just created.
            for (var i = 0; i < tableBody.rows.length; i++) {
                for (var row = tableBody.rows[i].cells, j = 0; j < row.length; j++) {
                    var td = row[j];
                    if (td.tagName == "TD" && 
                        TrimPath.spreadsheet.isCellActive(tableBody, td))
                        TrimPath.spreadsheet.setActive(tableBody, td);
                }
            }
            
            runSpreadsheetEvents("onAfterDecorate", tableBody);
            
            TrimPath.spreadsheet.logTime('end decorateSpreadsheet', d);
        },
        undecorateSpreadsheet : function(tableBody, barsOnly, doc) {
            if (doc == null)
                doc = document;
            if (tableBody != null &&
                tableBody.id != null) {
                if (doc.getElementById(tableBody.id + "_spreadsheetBars_spreadsheetBarCorner") != null) {
                    for (var ths = tableBody.getElementsByTagName("TH"), i = ths.length - 1; i >= 0; i--)
                        if (isBarItem(ths[i]))
                            ths[i].parentNode.removeChild(ths[i]);
                    var cols = tableBody.getElementsByTagName("COL");
                    if (cols != null &&
                        cols[0] != null)
                        cols[0].parentNode.removeChild(cols[0]);
                }
                TrimPath.spreadsheet.teardownTdIds(tableBody);
                removeElementById(tableBody.id + "_spreadsheetBar", doc);
                removeElementById(tableBody.id + "_spreadsheetBars_spreadsheetBarTop", doc);
                removeElementById(tableBody.id + "_spreadsheetBars_spreadsheetBarLeft", doc);
                removeElementById(tableBody.id + "_spreadsheetBars_spreadsheetBarCorner", doc);
                if (barsOnly != true) // Treats null as false.
                    removeElementById(tableBody.id + "_spreadsheetEditor_spreadsheetControls", doc);
            }
        },
        syncRowHeights : function(tableBody, barLeft) {
            var sDate= new Date();
            TrimPath.spreadsheet.logTime('&nbsp;syncRowHeights start');
            var spreadsheetBarLeft = barLeft;
            if (spreadsheetBarLeft == null) {
                var spreadsheetBarsId = tableBody.id + "_spreadsheetBars"
                spreadsheetBarLeft = document.getElementById(spreadsheetBarsId + "_spreadsheetBarLeft");
            }
            TrimPath.spreadsheet.logTime('&nbsp;syncRowHeights spreadsheetBarLeft ' + spreadsheetBarLeft);
            if (spreadsheetBarLeft != null) {
                for (var i = 0; i < tableBody.rows.length; i++)             // Synchronize row heights.
                    spreadsheetBarLeft.rows[i].style.height = tableBody.rows[i].offsetHeight;
            }
            TrimPath.spreadsheet.logTime('&nbsp;syncRowHeights end', sDate);
        },
        setupTdIds : function(tableBody) {
            // Register id's and onclick for tableBody td elements.
            visitCells(tableBody, function(tr, td, r, c) {
                if (!isBarItem(td))
                    td.id = getTdId(tableBody, r, c);
            });
        },
        teardownTdIds : function(tableBody) {
            for (var i = 0, tds = tableBody.getElementsByTagName("TD"); i < tds.length; i++)
                tds[i].id = "";
        },
        cellEdit : function(tableBody, row, col, focus, bClearAllActive) {
            if (tableBody != null) {
                var td = getTd(tableBody, row, col);
                if (td != null) {
                    TrimPath.spreadsheet.cellEditActivate(tableBody, row, col, bClearAllActive);

                    runSpreadsheetEvents("onBeforeCellEdit", tableBody);

                    var inputFormula = getInputFormula(tableBody.id);
                    if (inputFormula != null) {
                        TrimPath.spreadsheet.repaintInputFormula(tableBody, focus, true);   
                        inputFormula.value = getTdValueFormula(td);   
                    }

                    runSpreadsheetEvents("onCellEdit", tableBody);
                }
            }
        },
        cellEditActivate : function(tableBody, row, col, bClearAllActive) {
            if (tableBody != null) {
                var td = getTd(tableBody, row, col);
                if (td != null) {
                    TrimPath.spreadsheet.cellEditDone(tableBody, bClearAllActive);
    
                    runSpreadsheetEvents("onBeforeCellEditActivate", tableBody);

                    var inputFormula = getInputFormula(tableBody.id);
                    if (inputFormula != null)
                        inputFormula.style.display = "none";
    
                    if (bClearAllActive != false) // Treats null == true.
                        clearAllActive(tableBody);
    
                    setActive(tableBody, td);
                    setActiveMain(tableBody, td);
                    TrimPath.spreadsheet.formulaBarSync(tableBody.id, getTdValueFormula(td));
    
                    var labelLocation = getLabelLocation(tableBody.id);
                    if (labelLocation != null)
                        labelLocation.innerHTML = TrimPath.spreadsheetEngine.columnLabelString(col) + row;

                    runSpreadsheetEvents("onCellEditActivate", tableBody);
                }
            }
        },
        cellEditDone : function(tableBody, bClearAllActive) { 
            // Any changes to the input controls are stored back into the table, with a recalc.
            var labelLocation = getLabelLocation(tableBody.id);
            var inputFormula  = getInputFormula(tableBody.id);
            if (labelLocation != null &&
                inputFormula != null &&
                inputFormula.style.display != "none") {
                var loc = TrimPath.spreadsheetEngine.parseLocation(labelLocation.innerHTML);
                if (loc != null) {
                    //2007 mods: only change if cell is unlocked and not autolocked
                    var td = getTd(tableBody, loc[0], loc[1]);
                    if (! (td.className.indexOf("locked") >= 0 
                          || td.className.indexOf("auto_locked") >= 0) ) {
                        cellEditDoneBody(tableBody, inputFormula.value.replace("(Cell locked) ",""), loc, bClearAllActive);
                    }
                }
            }
        },
        cellEditAbandon : function(tableBody, bClearAllActive) { 
            var hadChanges = false;
            var labelLocation = getLabelLocation(tableBody.id);
            var inputFormula  = getInputFormula(tableBody.id);
            if (labelLocation != null &&
                inputFormula != null) {
                var loc = TrimPath.spreadsheetEngine.parseLocation(labelLocation.innerHTML);
                if (loc != null) {
                    var td = getTd(tableBody, loc[0], loc[1]);
                    if (td != null)
                        hadChanges = getTdValueFormula(td) != inputFormula.value;
                }

                runSpreadsheetEvents("onCellEditAbandon", tableBody);
                if (bClearAllActive != false) { // Treats null == true.
                    clearAllActive(tableBody);
                    labelLocation.innerHTML = "&nbsp;";
                }
                inputFormula.blur();
                inputFormula.value = "";
                inputFormula.style.display = "none";

                TrimPath.spreadsheet.formulaBarSync(tableBody.id, "");
            }
            return hadChanges;
        },
        moveCellFocus : function(tableBody, deltaUpDown, deltaLeftRight, focus, bClearAllActive) {
            var loc = currentLocation(tableBody.id);
            if (loc != null) {
                var barFudge = isBarItem(tableBody.rows[0].cells[0]) ? 1 : 0;
                var maxRow = tableBody.rows.length - 1;
                var maxCol = tableBody.rows[0].cells.length - 1;
                var td = null;
                while (loc[0] >= barFudge && loc[1] >= barFudge &&
                       loc[0] - barFudge <= maxRow &&
                       loc[1] - barFudge <= maxCol) {
                    loc[0] += (deltaUpDown || 0);
                    loc[1] += (deltaLeftRight || 0);
                    td = getTd(tableBody, loc[0], loc[1]);
                    if (td != null)
                        break;
                }
                if (td != null && !isBarItem(td)) {
                    loc[0] = theMath.max(barFudge, theMath.min(loc[0] - barFudge, tableBody.rows.length - 1) + barFudge);
                    loc[1] = theMath.max(barFudge, theMath.min(loc[1] - barFudge, tableBody.rows[0].cells.length - 1) + barFudge);
                    TrimPath.spreadsheet.cellEdit(tableBody, loc[0], loc[1], focus, bClearAllActive);
                }
            }
        },
        formulaMouseDown : makeEventHandler(function(evt, inputFormula) {
            TrimPath.spreadsheet.lastKeyCode = 0;
        }),
        formulaMouseDoubleClick : makeEventHandler(function(evt, inputFormula) {
            TrimPath.spreadsheet.lastKeyCode = 0;
        }),
        formulaMouseClick : makeEventHandler(function(evt, inputFormula) {
            TrimPath.spreadsheet.lastKeyCode = 0;
            var tableBody = document.getElementById(inputFormula.id.split('_')[0]);
            if (inputFormula == null)
                inputFormula = getInputFormula(tableBody.id);
            var inputFormulaBar = getInputFormulaBar(tableBody.id);
            TrimPath.spreadsheet.formulaBarRepaint(tableBody.id, inputFormula, inputFormulaBar, true);
        }),
        formulaKeyDown : makeEventHandler(function(evt, inputFormula) {
            var tableBody = document.getElementById(inputFormula.id.split('_')[0]);
            if (tableBody != null) {
                var keyCode = (evt.keyCode) ? evt.keyCode : evt.which;
                if (keyCode == 9) // TAB key doesn't tab out of formula input control.
                    return false;

                /* 2007 mods: ctrl-l Locks and unlocks cells only if not dirty
                if (keyCode == 76 && evt.ctrlKey == true) { // control-l
                    var loc = TrimPath.spreadsheetEngine.parseLocation(document.getElementById(tableBody.id + "_spreadsheetEditor_spreadsheetControls_loc").innerHTML);
                    if (loc != null) {
                        td = getTd(tableBody, loc[0], loc[1]);
                        if (td.className.indexOf("auto_locked") == -1) {  //only if not auto_locked 
                            if (td.className.indexOf("locked") == -1) {   //if not locked
                                td.className = "locked " + td.className;
                                inputFormula.className = "locked " + inputFormula.className;
                               if (inputFormula.value.indexOf("(Cell locked) ") == -1 ) {
                                  inputFormula.value = "(Cell locked) " + inputFormula.value;
                                  inputFormula.focus();
                                }  
                                
                             }
                            // else if already locked (and not autolocked), unlock the cell
                            else {  
                                td.className = td.className.replace(/locked/, "");
                                inputFormula.className = inputFormula.className.replace("locked", "");
                                while (inputFormula.value.indexOf("(Cell locked) ") >= 0) { 
                                  inputFormula.value = inputFormula.value.replace("(Cell locked) ", "");
                                }
                           }
                        TrimPath.spreadsheet.lastKeyCode = null;
                        return false;
                        }
                    }
                }
                //end 2007 mods */
                
                if (keyCode == 38 || keyCode == 40 ||
                    ((TrimPath.spreadsheet.lastKeyCode == null ||
                      inputFormula.value.length <= 0) &&
                     (keyCode == 37 || keyCode == 39))) {
                    var bClearAllActive = true;

                    var deltaRow = deltaCol = 0;
                    if (keyCode == 38) // UP
                        deltaRow = -1;
                    if (keyCode == 40) // DOWN
                        deltaRow = 1;
                    if (keyCode == 37) // LEFT
                        deltaCol = -1;
                    if (keyCode == 39) // RIGHT
                        deltaCol = 1;

                    var loc = currentLocation(tableBody.id);
                    if (loc != null && 
                        TrimPath.spreadsheet.lastKeyCode == null &&
                        getTd(tableBody, loc[0] + deltaRow, loc[1] + deltaCol) != null) {
                        clearActive(tableBody, getTd(tableBody, loc[0], loc[1]));
                        bClearAllActive = false;
                    }

                    TrimPath.spreadsheet.moveCellFocus(tableBody, deltaRow, deltaCol, true, bClearAllActive);

                    TrimPath.spreadsheet.lastKeyCode = null;
                    inputFormula.select();
                    inputFormula.focus();
                    return false;
                }

                TrimPath.spreadsheet.lastKeyCode = keyCode;
            }
        }),
        formulaKeyPress : makeEventHandler(function(evt, inputFormula) {
            var tableBody = document.getElementById(inputFormula.id.split('_')[0]);
            if (tableBody != null) {
                var keyCode = (evt.keyCode) ? evt.keyCode : evt.which;
                if (keyCode == 27) { // ESC key.
                    TrimPath.spreadsheet.cellEditAbandon(tableBody);
                    return false;
                }
                if ((keyCode == 36 ||  // HOME
                     keyCode == 35) && // END
                    evt.ctrlKey == true) {
                    var row = (keyCode == 36 ? 1 : tableBody.rows.length - (isBarItem(tableBody.rows[0].cells[0]) ? 1 : 0));
                    TrimPath.spreadsheet.cellEditActivate(tableBody, row, 1);
                    TrimPath.spreadsheet.lastKeyCode = null;
                }
            }
            return true;
        }),
        formulaKeyUp : makeEventHandler(function(evt, inputFormula) {
            TrimPath.spreadsheet.formulaBarSync(inputFormula.id.split('_')[0]);
        }),
        formulaBarKeyUp : makeEventHandler(function(evt, inputFormulaBar) {
            TrimPath.spreadsheet.formulaBarSyncBack(inputFormulaBar.id.split('_')[0], true);
        }),
        formulaBarKeyPress : makeEventHandler(function(evt, inputFormulaBar) {
            return TrimPath.spreadsheet.formulaKeyPress(evt, inputFormulaBar);
        }),
        formulaBarSync : function(tableBodyId, value) {
            if (formulaBarSyncBusy) 
                return;
            formulaBarSyncBusy = true;
            var inputFormula    = getInputFormula(tableBodyId);
            var inputFormulaBar = getInputFormulaBar(tableBodyId);
            if (inputFormula != null &&
                inputFormulaBar != null) {
                if (value == null)
                    value = (inputFormula.style.display != 'none' ? inputFormula.value : '');
                inputFormulaBar.value = value;
                TrimPath.spreadsheet.formulaBarRepaint(tableBodyId, inputFormula, inputFormulaBar);
            }
            formulaBarSyncBusy = false;
        },
        formulaBarSyncBack : function(tableBodyId, enlarge) {
            if (formulaBarSyncBusy) 
                return;
            formulaBarSyncBusy = true;
            var inputFormula    = getInputFormula(tableBodyId);
            var inputFormulaBar = getInputFormulaBar(tableBodyId);
            if (inputFormula != null &&
                inputFormulaBar != null) {
                var v = inputFormulaBar.value;
                if (inputFormula.style.display == 'none') {
                    var loc = currentLocation(tableBodyId);
                    if (loc != null)
                        TrimPath.spreadsheet.cellEdit(document.getElementById(tableBodyId), loc[0], loc[1], false, false);
                }
                if (inputFormula.style.display != 'none')
                    inputFormula.value = v;                    
                TrimPath.spreadsheet.formulaBarRepaint(tableBodyId, inputFormula, inputFormulaBar, enlarge);
            }
            formulaBarSyncBusy = false;
        },
        formulaBarRepaint : function(tableBodyId, inputFormula, inputFormulaBar, enlarge) {
            if (inputFormula == null)
                inputFormula = getInputFormula(tableBodyId);
            if (inputFormulaBar == null)
                inputFormulaBar = getInputFormulaBar(tableBodyId);
            if (inputFormulaBar != null) {
                var rows = 1;
                if (inputFormula.style.display != 'none' && enlarge == true) {  //treats null as false
                    rows = inputFormulaBar.value.split('\n').length;
                    if (rows > 1 || inputFormulaBar.value.length > 50)
                        rows = 5;
                }

                inputFormulaBar.style.height   = String(rows) + ".5em";
                inputFormulaBar.style.overflow = (rows > 1 ? 'auto' : 'hidden');
                inputFormulaBar.style.position = "relative";
            }
        },
        styleToggle : makeEventHandler(function(evtIgnored, aLink, tableBody) {
            aLink = getParent(aLink, "A");
            if (tableBody == null) 
                tableBody = document.getElementById(getParent(aLink, "DIV").id.split("_")[0]);
            TrimPath.spreadsheet.styleToggleAction(tableBody, aLink.href.split('#')[1]);
            return false;
        }),
        styleToggleAction : function(tableBody, action) {
            if (tableBody != null &&
                action != null &&
                action.length > 0) { // Example: fontWeight:bold.
                runSpreadsheetEvents("onBeforeChanged", tableBody, { activeOnly: true });

                var changed = 0;
                var actionSplit = action.split(':');
                for (var tds = tableBody.getElementsByTagName("TD"), i = 0, val = null; i < tds.length; i++) {
                    var td = tds[i];
                    if (isActive(tableBody, td)) {
                        if (val == null) {
                            if (td.style[actionSplit[0]] != actionSplit[1])
                                td.style[actionSplit[0]] = val = actionSplit[1];
                            else
                                td.style[actionSplit[0]] = val = "";
                        } else
                            td.style[actionSplit[0]] = val;
                        changed += 1;
                    }
                }
                if (changed > 0)    
                    runSpreadsheetEvents("onChanged", tableBody, { countChangedCells: changed });
            }
            TrimPath.spreadsheet.repaintInputFormula(tableBody);
        },
        
        //new code 2007
        addRow : makeEventHandler(function(evtIgnored, aLink, tableBody, td, action) {
            // base routine copied from above
            aLink = getParent(aLink, "A");
            if (tableBody == null) 
                tableBody = document.getElementById(getParent(aLink, "DIV").id.split("_")[0]);
            if (tableBody != null) {
                //this will be useful when inserting a row before or after...
                if (td == null) {
                    var loc = TrimPath.spreadsheetEngine.parseLocation(document.getElementById(tableBody.id + "_spreadsheetEditor_spreadsheetControls_loc").innerHTML);
                    if (loc != null)
                        td = getTd(tableBody, loc[0], loc[1]);
                }
                //find lastRow
                var spreadsheetBarLeft = document.getElementById(tableBody.id + "_spreadsheetBars_spreadsheetBarLeft");
                lastRow = spreadsheetBarLeft.rows.length - 1;        
                tr = tableBody.rows[lastRow];
        
                //create new ss row (tr element) and set its size to last rowsize
                var trNew = document.createElement("TR");
                if (tr.style.height != null)
                    trNew.style.height = tr.style.height;
        
                //create new th element for ss bar with rownum, append to new row 
                var thNew = document.createElement("TH");
                thNew.className = "spreadsheetBarItem";
                trNew.appendChild(thNew);
        
                //loop through all the columns
                for (var i = 1, numCols = tableBody.rows[0].cells.length - 1; i <= numCols; i++) {
                  trNew.appendChild(document.createElement("TD"));
                }
        
                // append after last row
                tr.parentNode.appendChild(trNew);
                    
                //undecorate and redecorate
                TrimPath.spreadsheet.undecorateSpreadsheet(tableBody);
                TrimPath.spreadsheet.decorateSpreadsheet(tableBody);
            }
        }),
        
        addCol : makeEventHandler(function(evtIgnored, aLink, tableBody, td, action) {
            aLink = getParent(aLink, "A");
            if (tableBody == null) 
                tableBody = document.getElementById(getParent(aLink, "DIV").id.split("_")[0]);
            if (tableBody != null) {
                //get last column
                lastCol = tableBody.rows[0].cells.length - 1;
                
                //add new column to all rows
                for (var r = 1; r < tableBody.rows.length; r++) { 
                    tableBody.rows[r].appendChild(document.createElement("TD"));
                }
                
                //add column header
                var col = tableBody.getElementsByTagName("COL")[lastCol];
                colNew = document.createElement("COL");
                colNew.width = 100;
                col.parentNode.insertBefore(colNew, col.nextSibling);  //no insertAfter, uses nextSibling
                tableBody.width = parseInt(tableBody.width) + parseInt(colNew.width);

                //undecorate and redecorate
                TrimPath.spreadsheet.undecorateSpreadsheet(tableBody);
                TrimPath.spreadsheet.decorateSpreadsheet(tableBody);
            }
        }),
        
        fixedButton : makeEventHandler(function(evtIgnored, aLink, tableBody, td, action) {
            // base routine copied from above
            aLink = getParent(aLink, "A");
            if (tableBody == null) 
                tableBody = document.getElementById(getParent(aLink, "DIV").id.split("_")[0]);
            if (tableBody != null) {
                //get td
                if (td == null) {
                    var loc = TrimPath.spreadsheetEngine.parseLocation(document.getElementById(tableBody.id + "_spreadsheetEditor_spreadsheetControls_loc").innerHTML);
                    if (loc != null) {
                        td = getTd(tableBody, loc[0], loc[1]);
                        var s = td.getAttribute("formula");
                        var inputFormula = getInputFormula(tableBody.id);
                        // don't fix2 locked or auto-locked cells
                        if (td.className.indexOf("locked") >= 0 || td.className.indexOf("auto_locked") >= 0) { }
                        else {
                            if (s == null || s.length <= 0) {
                              var s0 = "=FIXED(" + td.innerHTML + ")";
                              td.setAttribute("formula", s0);
                                  inputFormula.value = s0;
                                  TrimPath.spreadsheet.calc(tableBody);
                            }
                            else { 
                                if (s.charAt(0) == "=" && s.indexOf("=FIXED") != 0 ) {
                                  var s0 = "=FIXED(" + s.substr(1,s.length-1) + ")";
                                  td.setAttribute("formula", s0);
                                  inputFormula.value = s0;
                                  TrimPath.spreadsheet.calc(tableBody);
                                }
                            }
                        }
                    }
                }
            }
        }),

        //resume 1.0.15
        logTime : function(txt, startDate) {
            return;
            var logDiv = $('spreadsheet_log');
            if (logDiv == null) {
                logDiv = document.createElement('div');
                logDiv.id = 'spreadsheet_log';
                logDiv.style.display = 'block';
                document.body.appendChild(logDiv);
            }
            var now = new Date();
            logDiv.innerHTML = logDiv.innerHTML + " " + txt +  " Time: " + now.getTime() + "<br/>\n";
            if (startDate != null)
                logDiv.innerHTML = logDiv.innerHTML + "&nbsp;&nbsp;" + txt +  " Elapsed Time: " + (now.getTime()-startDate.getTime()) + "<br/>\n";
            logDiv.innerHTML = logDiv.innerHTML + "<hr/>\n";
        },
        context : {},
        calc : function(tableBody, fuel) {
            if (tableBody == null)
                return;
            var info = { cellProvider : new TableCellProvider(tableBody.id),
                         context      : TrimPath.spreadsheet.context,
                         fuel         : fuel };
            runSpreadsheetEvents("onBeforeCalc", tableBody, info);
            var startDate= new Date();
            TrimPath.spreadsheet.logTime('&nbsp;beforeCalc');
            var result = TrimPath.spreadsheetEngine.calc(info.cellProvider, info.context, info.fuel);
            TrimPath.spreadsheet.logTime('&nbsp;afterCalc', startDate);

            var extra = { changedCells    : info.cellProvider.changedCells,
                          calculatedCells : info.cellProvider.calculatedCells };
            runSpreadsheetEvents("onAfterCalc", tableBody, extra);

            TrimPath.spreadsheet.repaint(tableBody); 
            return result; // TODO: Revisit the results of calc call when we hook up fuel.
        },
        showFormulas : function(tableBody) {
            for (var i = 0; i < tableBody.rows.length; i++) {
                for (var tr = tableBody.rows[i], j = 0; j < tr.cells.length; j++) {
                    var formula = tr.cells[j].getAttribute("formula");
                    if (formula != null &&
                        formula.length > 0)
                        tr.cells[j].innerHTML = formula;
                }
            }
        },
        shiftFormulas : function(tableBody, row, col, rowDelta, colDelta) {
            // Rewrite any formulas that refer to cells >= {row, col} location by {rowDelta, colDelta} amount.
            for (var i = 0; i < tableBody.rows.length; i++) {
                for (var tr = tableBody.rows[i], j = 0; j < tr.cells.length; j++) {
                    var formula = tr.cells[j].getAttribute("formula");
                    if (formula != null &&
                        formula.length > 0) {
                        var result = TrimPath.spreadsheet.shiftFormula(formula, row, col, rowDelta, colDelta);
                        if (result != formula)
                            tr.cells[j].setAttribute("formula", result);
                    }
                }
            }
        },
        shiftFormula : function(formula, row, col, rowDelta, colDelta) {
            // Rewrite a formula string that refer to cells >= {row, col} location by {rowDelta, colDelta} amount.
            return formula.replace(/(\$?[a-zA-Z]+)(\$?[0-9]+)/g, 
                function(ignored, colStr, rowStr) {
                    if (col >= 1 && colDelta != 0 && colStr[0] != '$') {
                        var x = TrimPath.spreadsheetEngine.columnLabelIndex(colStr.toUpperCase());
                        if (x >= col)
                            colStr = TrimPath.spreadsheetEngine.columnLabelString(x + colDelta);
                    }
                    if (row >= 1 && rowDelta != 0 && rowStr[0] != '$') {
                        var y = theParseInt(rowStr);
                        if (y >= row)
                            rowStr = String(y + rowDelta);
                    }
                    return colStr + rowStr;
                });
        },
        repaint : function(tableBody) {
            for (var divs = tableBody.getElementsByTagName("DIV"), i = 0; i < divs.length; i++) {
                var div = divs[i];
                if (div.className.indexOf("spreadsheetCellOverflow") >= 0) // IE hack for overflow cells.
                    div.innerHTML = div.innerHTML + "";
            }
        },
        repaintInputFormula : function(tableBody, focus, show) {
            if (tableBody.id != null) {
                var loc = currentLocation(tableBody.id);
                if (loc != null) {
                    var td = getTd(tableBody, loc[0], loc[1]);
                    if (td != null) {
                        var inputFormula = getInputFormula(tableBody.id);
                        if (inputFormula != null &&
                            (show || inputFormula.style.display != "none")) {
                            if (inputFormula.parentNode != tableBody.parentNode)
                                tableBody.parentNode.appendChild(inputFormula);

                            inputFormula.style.display  = "none";
                            inputFormula.style.position = "absolute";
                            inputFormula.style.left     = (td.offsetLeft) + "px";
                            if (TrimPath.spreadsheet.isIE) {
                                inputFormula.style.top      = (td.offsetTop - 2) + "px";
                                inputFormula.style.width    = (td.clientWidth + 2) + "px";
                            } else {
                                inputFormula.style.top      = (td.offsetTop - 3) + "px";
                                inputFormula.style.width    = (td.clientWidth) + "px";
                            }
                            inputFormula.style.height   = (td.clientHeight + 1) + "px";
                            styleCopy(td, inputFormula);
   
                            var v = td.getAttribute("formula");
                            if (v == null ||
                                v.length <= 0)
                                inputFormula.style.textAlign = td.style.textAlign;
                            else
                                inputFormula.style.textAlign = "left";

                            inputFormula.style.display = "";

                            if (focus != false) {
                                inputFormula.select();
                                inputFormula.focus();
                            }
                        }
                    }
                }
            }
        }
    }

    var cellEditDoneBody = function(tableBody, inputValue, loc, bClearAllActive, moreWork, moreWorkArgs) {
        var rangeRows   = inputValue.replace(/\r/g, '').split('\n'); // Split by \n and \t to treat range input correctly.
        var manyChanged = (rangeRows.length > 1 || 
                           rangeRows[0].split('\t').length > 1);

        runSpreadsheetEvents("onBeforeChanged", tableBody, { activeOnly: !manyChanged });

        var result = applyRangeToLocation(tableBody, rangeRows, loc);

        if (moreWork != null)
            moreWork.apply(null, moreWorkArgs);

        if (result.changedCells.length > 0)
            runSpreadsheetEvents("onChanged", tableBody, { 
                countChangedCells : (manyChanged ? null : result.changedCells.length) 
            });

        var extra = { recalc       : result.changedCells.length > 0, 
                      changedCells : result.changedCells };

        runSpreadsheetEvents("onCellEditDone", tableBody, extra);

        if (bClearAllActive != false) // Treats null == true.
            clearAllActive(tableBody);

        if (extra.recalc == true) {
            if (!isClass(tableBody, "spreadsheetCalcOff"))
                TrimPath.spreadsheet.calc(tableBody);
        }

        if (result.redecorate) {
            TrimPath.spreadsheet.undecorateSpreadsheet(tableBody, true);
            TrimPath.spreadsheet.decorateSpreadsheet(tableBody);
        }
    }

    var formulaBarSyncBusy = false;

    var getInputFormula = TrimPath.spreadsheet.getInputFormula = function(tableBodyId) {
        return document.getElementById(tableBodyId + "_spreadsheetEditor_spreadsheetControls_formula");
    }
    var getInputFormulaBar = TrimPath.spreadsheet.getInputFormulaBar = function(tableBodyId) {
        return document.getElementById(tableBodyId + "_spreadsheetEditor_spreadsheetControls_formulaBar");
    }
    var getLabelLocation = TrimPath.spreadsheet.getLabelLocation = function(tableBodyId) {
        return document.getElementById(tableBodyId + "_spreadsheetEditor_spreadsheetControls_loc");
    }
    var currentLocation = TrimPath.spreadsheet.currentLocation = function(tableBodyId) {
        return TrimPath.spreadsheetEngine.parseLocation(getLabelLocation(tableBodyId).innerHTML)
    }

    var visitCells = TrimPath.spreadsheet.visitCells = function(tableBody, tdVisitor, trVisitor) {
        // Encapsulates the rowspan/colspan algorithm.
        var rowSpans = {};
        for (var r = 0; r < tableBody.rows.length; r++) {
            var tr = tableBody.rows[r];                       
            var cOffset = 0;
            for (var c = 0; c < tr.cells.length; c++) {
                cOffset = visitCellsRowSpansHelper(rowSpans, c, cOffset)
                var td = tr.cells[c];
                if (tdVisitor != null &&
                    tdVisitor(tr, td, r, c + cOffset) == false)
                    return;
                if (td.rowSpan != null && td.rowSpan > 1)
                    rowSpans[c + cOffset] = [ td.rowSpan - 1, td.colSpan ];
                if (td.colSpan != null && td.colSpan > 1)
                    cOffset += td.colSpan - 1;
            }
            cOffset = visitCellsRowSpansHelper(rowSpans, c, cOffset)
            if (trVisitor != null &&
                trVisitor(tr, r, c + cOffset) == false)
                return;
        }
    }

    var visitCellsRowSpansHelper = function(rowSpans, c, cOffset) {
        var rowSpan = rowSpans[c + cOffset];
        while (rowSpan != null) {
            rowSpan[0] -= 1;
            if (rowSpan[0] <= 0)
                rowSpans[c + cOffset] = null;
            cOffset += rowSpan[1];
            rowSpan = rowSpans[c + cOffset];
        }
        return cOffset;
    }

    var countColumns = TrimPath.spreadsheet.countColumns = function(tableBody) {
        var ncolsMax = 0;
        visitCells(tableBody, null, function(tr, row, ncols) {
            if (ncolsMax < ncols)
                ncolsMax = ncols;
        });
        return ncolsMax - (isBarItem(tableBody.rows[0].cells[0]) ? 1 : 0);
    }

    var dragInfo = {};

    var cellOnMouseDown = makeEventHandler(function(evt, target) {
        var td = getParent(target, "TD");
        if (td != null) {
            var tdLoc = getTdLocation(td);
            var tableBody = getParent(td, "TABLE");
            if (tableBody != null && tdLoc != null) {
                TrimPath.spreadsheet.lastKeyCode = null;

                runSpreadsheetEvents("onCellMouseDown", tableBody);

                var labelLocation = getLabelLocation(tableBody.id);
                var inputFormula  = getInputFormula(tableBody.id);
                if (labelLocation != null &&
                    labelLocation.innerHTML.length > 0 &&
                    inputFormula != null &&
                    inputFormula.value.length > 0 &&
                    inputFormula.value.charAt(0) == '=') {
                    if ("=([:,*/+-".indexOf(inputFormula.value.charAt(inputFormula.value.length - 1)) >= 0) {
                        // Append cell location to currently edited formula based on mouse click.
                        inputFormula.value += TrimPath.spreadsheetEngine.columnLabelString(tdLoc[1]) + tdLoc[0];
                        inputFormula.select(); // TODO: We should actually move the caret to the end.
                        inputFormula.focus();  //       But, don't know how to do that cross-browser yet.
                        return false;
                    }
                }

                TrimPath.spreadsheet.cellEditDone(tableBody);
                TrimPath.spreadsheet.cellEditAbandon(tableBody);
                setActive(tableBody, td);
                setActiveMain(tableBody, td);
                TrimPath.spreadsheet.formulaBarSync(tableBody.id, getTdValueFormula(td));
                labelLocation.innerHTML = TrimPath.spreadsheetEngine.columnLabelString(tdLoc[1]) + tdLoc[0];

                dragInfo = { tableBody   : tableBody,
                             startTd     : td,
                             startLoc    : tdLoc,
                             lastTd      : td,
                             activeTds   : {},
                             timeout     : null,
                             timeoutStep : 0,
                             eventPageXY : null,
                             spreadsheetScroll  : document.getElementById(tableBody.id + "_spreadsheetScroll"),
                             spreadsheetBarLeft : document.getElementById(tableBody.id + "_spreadsheetBars_spreadsheetBarLeft"),
                             spreadsheetBarTop  : document.getElementById(tableBody.id + "_spreadsheetBars_spreadsheetBarTop") };
                dragInfo.indexTr1 = getIndexTr(tableBody, 1);
                dragInfo.indexTd1 = getIndexTd(tableBody, dragInfo.indexTr1, 1);
                if (dragInfo.spreadsheetScroll != null)
                    dragInfo.spreadsheetScrollXY = findElementPageXY(dragInfo.spreadsheetScroll);
                document.onmousemove = cellOnMouseMove;
                document.onmouseup   = cellOnMouseUp;

                return false;
            }
        }
        return true;
    });

    var cellOnMouseMove = makeEventHandler(function(evt, target) {
        if (dragInfo != null) {
            dragInfo.eventPageXY = findEventPageXY(evt);

            var td = getParent(target, "TD");
            if (td != null) {
                var tableBody = getParent(td, "TABLE");
                if (tableBody == dragInfo.tableBody) {
                    var loc = getTdLocation(td);
                    if (loc != null) {
                        loc[0] = theMath.max(loc[0], dragInfo.startLoc[0]);
                        loc[1] = theMath.max(loc[1], dragInfo.startLoc[1]);
                        var newActiveTds = {};
                        for (var r = dragInfo.startLoc[0]; r <= loc[0]; r++) {
                            for (var c = dragInfo.startLoc[1]; c <= loc[1]; c++) {
                                newActiveTds[r + "," + c] = getTd(tableBody, r, c, dragInfo.indexTr1 + r - 1, dragInfo.indexTd1 + c - 1);
                            }
                        }
                        for (var k in dragInfo.activeTds) {
                            if (newActiveTds[k] == null)
                                clearActive(tableBody, dragInfo.activeTds[k], dragInfo.spreadsheetBarLeft, dragInfo.spreadsheetBarTop);
                        }
                        for (var k in newActiveTds) {
                            if (typeof(newActiveTds[k]) == 'object')
                                setActive(tableBody, newActiveTds[k], dragInfo.spreadsheetBarLeft, dragInfo.spreadsheetBarTop);
                        }
                        dragInfo.activeTds = newActiveTds;
                        dragInfo.lastTd    = td;
                        setActiveMain(tableBody, dragInfo.startTd);
                        return false;
                    }
                }
            }

            if (dragInfo.timeout == null) {
                dragInfo.timeout = setTimeout("TrimPath.spreadsheet.scrollTimerHandler()", 200);
                dragInfo.timeoutStep = 0;
            }
        }

        return false;
    });

    TrimPath.spreadsheet.scrollTimerHandler = function() {
        if (dragInfo != null) {
            dragInfo.timeout = null;

            if (dragInfo.eventPageXY != null) {
                var spreadsheetScroll = dragInfo.spreadsheetScroll;
                if (spreadsheetScroll != null) {
                    if (dragInfo.eventPageXY[1] < dragInfo.spreadsheetScrollXY[1] - 5) {
                        dragInfo.timeoutStep = Math.min(0, dragInfo.timeoutStep);
                        spreadsheetScroll.scrollTop = Math.max(0, spreadsheetScroll.scrollTop + (10 * dragInfo.timeoutStep));
                        dragInfo.timeoutStep = (dragInfo.timeoutStep != null ? dragInfo.timeoutStep - 1 : 0);
                    }
                    if (dragInfo.eventPageXY[1] > dragInfo.spreadsheetScrollXY[1] + 5 + dragInfo.spreadsheetScroll.offsetHeight) {
                        dragInfo.timeoutStep = Math.max(0, dragInfo.timeoutStep);
                        spreadsheetScroll.scrollTop = Math.min(spreadsheetScroll.scrollHeight, 
                                                               spreadsheetScroll.scrollTop + (10 * dragInfo.timeoutStep));
                        dragInfo.timeoutStep = (dragInfo.timeoutStep != null ? dragInfo.timeoutStep + 1 : 0);
                    }
                    dragInfo.timeout = setTimeout("TrimPath.spreadsheet.scrollTimerHandler()", 100);
                }
            }
        }
    }

    var rangeTextLocs = {}; // The location for the current rangeText.
    var rangeTextPrev = {}; // The location and text value for the previous rangeText state.

    var cellOnMouseUp = makeEventHandler(function(evt, target) {
        var rangeText = document.getElementById(dragInfo.tableBody.id + "_rangeText");
        if (rangeText != null) {
            if (window /*.parent*/ == window) { // 2007 NOT Only when not in frame/iframe, due to scrolling issues.
                var range = [];
                var lastLoc = getTdLocation(dragInfo.lastTd);
                lastLoc[0] = theMath.max(lastLoc[0], dragInfo.startLoc[0]);
                lastLoc[1] = theMath.max(lastLoc[1], dragInfo.startLoc[1]);
                for (var r = dragInfo.startLoc[0]; r <= lastLoc[0]; r++) {
                    if (r > dragInfo.startLoc[0])
                        range.push("\n");
                    for (var c = dragInfo.startLoc[1]; c <= lastLoc[1]; c++) {
                        if (c > dragInfo.startLoc[1])
                            range.push("\t");
                        var td = getTd(dragInfo.tableBody, r, c, dragInfo.indexTr1 + r - 1, dragInfo.indexTd1 + c - 1);
                        if (td != null) {
                            if (evt.ctrlKey == true)
                                range.push(getTdValueFormatted(td));
                            else
                                range.push(getTdValueFormula(td));
                        }
                    }
                }

                rangeTextLocs.startLoc = dragInfo.startLoc;
                rangeTextLocs.endLoc   = lastLoc;
    
                rangeText.value = range.join("");
                rangeText.select(); // Only select(), no focus() which would make IE incorrectly scroll to the hidden rangeText.
            }
        }
        runSpreadsheetEvents("onCellMouseUp");

        dragInfo = {};
        document.onmousemove = document.onmouseup = null;
        return false;
    });

    var cellOnMouseDoubleClick = makeEventHandler(function(evt, target) {
        var td = getParent(target, "TD");
        if (td != null) {
            var tdLoc = getTdLocation(td);
            var tableBody = getParent(td, "TABLE");
            if (tableBody != null && tdLoc != null) {
                var inputFormula = getInputFormula(tableBody.id);
                if (inputFormula != null) {
                    TrimPath.spreadsheet.cellEdit(tableBody, tdLoc[0], tdLoc[1], false);
                    inputFormula.focus();
                    TrimPath.spreadsheet.lastKeyCode = 0; // Forces left/right arrow keys to work in inputFormula, not navigation.
                }
            }
        }
        return false;
    });

    var makeBarResizer = function(xyDimension, clientAttr, thIndexGetter, thPreviousGetter, 
                                  barItemsGetter, firstTHGetter, barItemSizeGetter, barItemSizeSetter, 
                                  tableSizeGetter, tableSizeSetter, activeMarker) {
        var barResizer = {
            start : makeEventHandler(function(evt, target) {
                var thEl = thTarget = getParent(target, "TH");
                if (thEl != null) {
                    var barTable = getParent(thEl, "TABLE");
                    if (barTable != null) {
                        var tableBody = document.getElementById(barTable.id.split("_")[0]);
                        if (tableBody != null) {
                            if (document.getElementById(tableBody.id + "_spreadsheetEditor") == null)
                                return;

                            var scrollOffsetXY = [ 0, 0 ];
                            var spreadsheetBars   = document.getElementById(tableBody.id + "_spreadsheetBars");
                            var spreadsheetScroll = document.getElementById(tableBody.id + "_spreadsheetScroll");
                            if (spreadsheetScroll != null && 
                                spreadsheetBars != null &&
                                spreadsheetBars.className.indexOf('spreadsheetBarsOutside') < 0)
                                scrollOffsetXY = [ spreadsheetScroll.scrollLeft, spreadsheetScroll.scrollTop ];

                            var thPageXY, edgeDelta, eventPageXY = findEventPageXY(evt, scrollOffsetXY);
                            while (thEl != null && thIndexGetter(thEl) > 0) {
                                thPageXY = findElementPageXY(thEl);
                                edgeDelta = thPageXY[xyDimension] + thEl[clientAttr] - eventPageXY[xyDimension];
                                if (theMath.abs(edgeDelta) <= 3)
                                    break;
                                thEl = thPreviousGetter(thEl);
                            }

                            TrimPath.spreadsheet.cellEditDone(tableBody);
                            TrimPath.spreadsheet.cellEditAbandon(tableBody);
                            if (thEl != null && thIndexGetter(thEl) > 0) {
                                dragInfo = { barTable       : barTable,                                   // TODO: IE mem leak here?
                                             barItems       : barItemsGetter(barTable),                   // TODO: IE mem leak here?
                                             barPageXY      : findElementPageXY(firstTHGetter(barTable)), // The pageXY of the first non-corner TH.
                                             edgeDelta      : edgeDelta,
                                             startIndex     : thIndexGetter(thEl),
                                             startSizes     : [],
                                             scrollOffsetXY : scrollOffsetXY };
                                for (var i = 0; i < dragInfo.barItems.length; i++) {
                                    dragInfo.startSizes[i] = theParseInt(barItemSizeGetter(dragInfo.barItems[i]));
                                    if (theIsNaN(dragInfo.startSizes[i]))
                                        return;
                                }
                                document.onmousemove = barResizer.drag;
                                document.onmouseup   = barResizer.stop;
                                return false;
                            } else {
                                // Not resizing, so just mark the entire row or column as active.
                                activeMarker(tableBody, thIndexGetter(thTarget));
                            }
                        }
                    }
                }
            }),
            drag : makeEventHandler(function(evt, target) {
                if (dragInfo.barTable != null) {
                    var newSizes = dragInfo.startSizes.slice(0); // Make a copy.
                    var v = findEventPageXY(evt, dragInfo.scrollOffsetXY)[xyDimension] - dragInfo.barPageXY[xyDimension] + dragInfo.edgeDelta;
                    if (v > 0) {
                        for (var sizeTotal = 0, i = 1; i < dragInfo.startIndex; i++) {
                            if ((sizeTotal + newSizes[i]) > v) 
                                newSizes[i] = theMath.max(v - sizeTotal, 3);           // A non-zero minimum size saves many headaches.
                            sizeTotal += newSizes[i];
                        }
                        newSizes[dragInfo.startIndex] = theMath.max(v - sizeTotal, 3); // A non-zero minimum size saves many headaches.
                    }
                    for (var sizeTotal = 0, i = 1; i < newSizes.length; i++) {
                        sizeTotal += newSizes[i];
                        barItemSizeSetter(dragInfo.barItems[i], newSizes[i] + "px");
                    }
                    tableSizeSetter(dragInfo.barTable, sizeTotal + "px"); 
                    return false;
                }
            }),
            stop : makeEventHandler(function(evt, target) {
                if (dragInfo.barTable != null) {
                    var tableBody = document.getElementById(dragInfo.barTable.id.split("_")[0]);
                    if (tableBody != null) {
                        var redecorate = false;
                        var srcItems = barItemsGetter(dragInfo.barTable);
                        var dstItems = barItemsGetter(tableBody);
                        for (var i = 1; i < srcItems.length && i < dstItems.length; i++) {
                            var size = barItemSizeGetter(srcItems[i]);
                            if (size != barItemSizeGetter(dstItems[i])) {
                                barItemSizeSetter(dstItems[i], size);
                                redecorate = true;
                            }
                        }
                        tableSizeSetter(tableBody, tableSizeGetter(dragInfo.barTable) + "px");

                        if (redecorate) {
                            runSpreadsheetEvents("onChanged", tableBody);
                            TrimPath.spreadsheet.undecorateSpreadsheet(tableBody, true);
                            TrimPath.spreadsheet.decorateSpreadsheet(tableBody, null, false);
                            TrimPath.spreadsheet.repaintInputFormula(tableBody);
                        }
                    }
                }
                dragInfo = {};
                document.onmousemove = document.onmouseup = null;
                return false;
            })
        }
        return barResizer;
    }

    var columnResizer = makeBarResizer(0, "clientWidth", 
        function(th) { return th.cellIndex; }, 
        function(th) { return th.parentNode.cells[th.cellIndex - 1]; }, 
        function(barTable) { return barTable.getElementsByTagName("COL"); },
        function(barTable) { return barTable.rows[0].cells[1]; },
        function(barItem)    { return barItem.getAttribute("width"); },
        function(barItem, v) { barItem.setAttribute("width", v); },
        function(barTable)    { return barTable.getAttribute("width"); },
        function(barTable, v) { barTable.setAttribute("width", v); },
        function(tableBody, colIndex) {
            for (var r = 0; r <= tableBody.rows.length; r++) {
                var td = getTd(tableBody, r, colIndex);
                if (td != null && td.tagName == "TD" && !isBarItem(td))
                    setActive(tableBody, td);
            }
        });

    var rowResizer = makeBarResizer(1, "clientHeight", 
        function(th) { return th.parentNode.rowIndex; }, 
        function(th) { return th.parentNode.parentNode.parentNode.rows[th.parentNode.rowIndex - 1].cells[0]; }, 
        function(barTable) { return barTable.rows; },
        function(barTable) { return barTable.rows[1].cells[0]; },
        function(barItem)    { var height = barItem.style.height;
                               return (height != null && height != "") ? height : barItem.clientHeight + "px"; },
        function(barItem, v) { barItem.style.height = v; },
        function(el)    { return -1; },
        function(el, v) { },
        function(tableBody, rowIndex) { 
            var ncols = countColumns(tableBody);
            for (var c = 0; c <= ncols; c++) {
                var td = getTd(tableBody, rowIndex, c);
                if (td != null && td.tagName == "TD" && !isBarItem(td))
                    setActive(tableBody, td);
            }
        });

    var applyRangeToLocation = function(tableBody, rangeRows, loc) {
        var changedCells = [];
        var redecorate = false;

        for (var i = 0; i < rangeRows.length; i++) {
            var cols = rangeRows[i].split('\t');
            for (var j = 0; j < cols.length; j++) {
                var td = getTd(tableBody, loc[0] + i, loc[1] + j);
                if (td != null) {
                  //only update if the previous cell is not locked
                  if (td.className.indexOf("locked") < 0 || td.className.indexOf("auto_locked") < 0) {
                    var clientHeightOrig = td.clientHeight;

                    var v = cols[j].replace(/^\s*(.*?)\s*$/, '$1'); // Trim whitespace.
                    if (v.length > 0) {
                        if (v.charAt(0) == '=') {
                            if (v != td.getAttribute("formula")) {
                                changedCells.push([loc[0] + i, loc[1] + j]);
                                setTdValue(td, "");
                                td.setAttribute("formula", v);
                                // Formatting is handled during later recalc, because v is a formula.
                            }
                        } else {
                            if (v != getTdValue(td)) {
                                changedCells.push([loc[0] + i, loc[1] + j]);
                                setTdValue(td, v);
                                td.removeAttribute("formula");
                                // Formatting is handled right here (not the later recalc) because v is static (not a formula).
                                formatTd(td, v);
                            }
                        }
                    } else {
                        v = getTdValue(td);
                        if ((v != null && v.length > 0) ||
                            td.getAttribute("formula") != null)
                            changedCells.push([loc[0] + i, loc[1] + j]);
                        setTdValue(td, "");
                        td.removeAttribute("formula");
                    }

                    redecorate = redecorate || (clientHeightOrig != td.clientHeight);
                  }
                }
            }
        }

        return { changedCells : changedCells,
                 redecorate   : redecorate };
    }

    var rangeTextLastKeyDown = {};
    
    var rangeTextKeyDown = TrimPath.spreadsheet.rangeTextKeyDown = makeEventHandler(function(evt, target) {
        var keyCode = (evt.keyCode) ? evt.keyCode : evt.which;
        rangeTextLastKeyDown.keyCode = keyCode;
        rangeTextLastKeyDown.ctrlKey = evt.ctrlKey;
        rangeTextLastKeyDown.altKey  = evt.altKey;
        rangeTextLastKeyDown.value   = target.value;
        // 2007: ctrl-l Locks and unlocks cells --must go in keydown due to FF key assignments
        var tableBody = document.getElementById(target.id.split('_')[0]);
        if (tableBody != null) {
                if (keyCode == 76 && evt.ctrlKey == true) {  //control-l
                 var loc = currentLocation(tableBody.id);
                    if (loc != null) {
                        td = getTd(tableBody, loc[0], loc[1]);
                        if (td.className.indexOf("auto_locked") == -1) {    //if not auto_locked 
                            if (td.className.indexOf("locked") == -1) {         //if not locked
                                td.className = "locked " + td.className;
                                inputFormula.className = "locked " + inputFormula.className;
                                inputFormula.value = "(Cell locked) " + inputFormula.value;
                                inputFormula.select();
                                inputFormula.focus();
                             }
                            // else if already locked and not autolocked, unlock the cell
                            else {  
                                td.className = td.className.replace(/locked/, "");
                                inputFormula.className = inputFormula.className.replace("locked", "");
                                inputFormula.value = inputFormula.value.replace("(Cell locked) ", "");
                                inputFormula.select();
                                inputFormula.focus();
                           }
                        TrimPath.spreadsheet.lastKeyCode = null;
                        TrimPath.spreadsheet.cellEditAbandon(tableBody, false);
                        return false;
                        }
                    }
                }
                
        }
        // end 2007 mods
   });

    var rangeTextKeyUp = TrimPath.spreadsheet.rangeTextKeyUp = makeEventHandler(function(evt, target) {
        var tableBody = document.getElementById(target.id.split('_')[0]);
        if (tableBody != null &&
            window /*.parent*/ == window) { //2007 modification to allow ranges to work in frames...
            var keyCode = (evt.keyCode) ? evt.keyCode : evt.which;
            if (evt.ctrlKey == true || rangeTextLastKeyDown.ctrlKey == true ||
                keyCode == 46) { // delete key
                if (keyCode == 67 || keyCode == 99 || // control-c
                    keyCode == 88 ||                  // control-x
                    keyCode == 46) { 
                    if (keyCode != 46) {
                        rangeTextPrev.value    = rangeTextLastKeyDown.value;
                        rangeTextPrev.startLoc = rangeTextLocs.startLoc;
                        rangeTextPrev.endLoc   = rangeTextLocs.endLoc;
                    }
                    if (keyCode == 88 || keyCode == 46) {
                        runSpreadsheetEvents("onBeforeChanged", tableBody, { activeOnly: true });
                        var changed = 0;
                        for (var i = 0, tds = tableBody.getElementsByTagName('TD'); i < tds.length; i++) {
                            var td = tds[i];
                            if (isActive(tableBody, td)) {
                                td.removeAttribute("formula");
                                setTdValue(td, "");
                                changed += 1;
                            }
                        }
                        if (changed > 0) {
                            runSpreadsheetEvents("onChanged", tableBody, { countChangedCells: changed });
                            TrimPath.spreadsheet.calc(tableBody);
                            TrimPath.spreadsheet.undecorateSpreadsheet(tableBody, true);
                            TrimPath.spreadsheet.decorateSpreadsheet(tableBody);
                        }
                    }
                }
                if (keyCode == 86) { // control-v
                    var loc = currentLocation(tableBody.id);
                    if (loc != null) {
                        if (rangeTextPrev.value == target.value) {
                            if (loc[0] != rangeTextPrev.startLoc[0] ||
                                loc[1] != rangeTextPrev.startLoc[1]) {
                                var inputFormula = getInputFormula(tableBody.id);
                                if (inputFormula != null) {
                                    var range = TrimPath.spreadsheet.shiftFormula(target.value, 1, 1, 
                                        loc[0] - rangeTextPrev.startLoc[0],
                                        loc[1] - rangeTextPrev.startLoc[1]);
                                    
                                    cellEditDoneBody(tableBody, range, loc, false, 
                                        rangeStyleCopy, [tableBody, rangeTextPrev.startLoc, rangeTextPrev.endLoc, loc]);

                                    TrimPath.spreadsheet.cellEditAbandon(tableBody, false);
                                }
                            }
                        }
                    }
                }
            }
        }
    });

    var rangeStyleCopy = function(tableBody, srcStartLoc, srcEndLoc, dstStartLoc) {
        // Copy the style and formatting from the src range to the dst range.
        // TODO: Handle overlapping src and dst ranges better.
        var nRows = srcEndLoc[0] - srcStartLoc[0] + 1;
        var nCols = srcEndLoc[1] - srcStartLoc[1] + 1;
        for (var r = 0; r < nRows; r++) {
            for (var c = 0; c < nCols; c++) {
                var tdSrc = getTd(tableBody, srcStartLoc[0] + r, srcStartLoc[1] + c);                
                var tdDst = getTd(tableBody, dstStartLoc[0] + r, dstStartLoc[1] + c);
                if (tdSrc && tdDst) {
                    styleCopy(tdSrc, tdDst);
                    tdDst.style.textAlign = tdSrc.style.textAlign;
                    setTdFormat(tdDst, getTdFormat(tdSrc));
                    if (hasTdFormat(tdDst))
                        formatTd(tdDst, getTdValue(tdDst));
                }
            }
        }
    }

    var styleCopy = TrimPath.spreadsheet.styleCopy = function(src, dst) {
        dst.style.color           = src.style.color;
        dst.style.backgroundColor = src.style.backgroundColor;
        dst.style.fontSize        = src.style.fontSize;
        dst.style.fontStyle       = src.style.fontStyle;
        dst.style.fontWeight      = src.style.fontWeight;
        dst.style.fontFamily      = src.style.fontFamily;
        dst.style.textDecoration  = src.style.textDecoration;
        // Note: we don't copy textAlign because of the inputFormula case.
    }

    var cornerMouseClick = makeEventHandler(function(evt, target) {
        var tableBodyId = getParent(target, "TABLE").id.split('_')[0];
        if (tableBodyId != null) {
            var tableBody = document.getElementById(tableBodyId);
            if (tableBody != null) {
                var activate = !isActive(tableBody, getTd(tableBody, 1, 1));
                TrimPath.spreadsheet.cellEditDone(tableBody);
                TrimPath.spreadsheet.cellEditAbandon(tableBody);
                if (activate) {
                    for (var i = 0, tds = tableBody.getElementsByTagName("TD"); i < tds.length; i++) {
                        var td = tds[i];
                        if (!isBarItem(td))
                            setActive(tableBody, td);
                    }
                } else {
                    clearAllActive(tableBody);
                }
            }
        }
        return false;
    });

    var findEventPageXY = function(evt, scrollOffsetXY) { 
        if (evt.offsetX || evt.offsetY) {
            var targetPageXY = findElementPageXY((evt.target) ? evt.target : evt.srcElement);
            return [ evt.offsetX + targetPageXY[0], evt.offsetY + targetPageXY[1] ];
        }
        if (scrollOffsetXY == null) // The scrollOffsetXY hack is because Mozilla's pageXY doesn't handle scrolled divs.
            scrollOffsetXY = [0, 0];
        if (evt.pageX || evt.pageY)
            return [ evt.pageX + scrollOffsetXY[0], evt.pageY + scrollOffsetXY[1]];
        return [ evt.clientX + document.body.scrollLeft, evt.clientY + document.body.scrollTop ];
    }

    var findElementPageXY = TrimPath.spreadsheet.findElementPageXY = function(obj) { // From ppk quirksmode.org.
        var point = [0, 0];
        if (obj.offsetParent) {
            while (obj.offsetParent) {
                point[0] += obj.offsetLeft;
                point[1] += obj.offsetTop;
                obj = obj.offsetParent;
            }
        } else if (obj.x)
            return [ obj.x, obj.y ];
        return point;
    }

    var activeCells = new Array();

    var isActive = TrimPath.spreadsheet.isCellActive = function(tableBody, td) {
        if (tableBody != null && td != null)
            return td.className.indexOf("spreadsheetCellActive") >= 0;
        return false;
    }

    var setActive = TrimPath.spreadsheet.setActive = function(tableBody, td, spreadsheetBarLeft, spreadsheetBarTop) {
        if (tableBody != null &&
            td != null) {
            if (td.className.indexOf("spreadsheetCellActive") < 0) {
                td.className += (td.className.length <= 0 ? "spreadsheetCellActive" : " spreadsheetCellActive");
                activeCells.push(td);
            }
            if (document.getElementById(tableBody.id + "_spreadsheetBars_spreadsheetBarCorner") != null) {
                var loc = getTdLocation(td);
                if (spreadsheetBarLeft == null)
                    spreadsheetBarLeft = document.getElementById(tableBody.id + "_spreadsheetBars_spreadsheetBarLeft");
                var barItem = spreadsheetBarLeft.rows[loc[0]].cells[0];
                if (barItem != null &&
                    barItem.className.indexOf("spreadsheetBarItemSelected") < 0)
                    barItem.className += (barItem.className.length <= 0 ? "spreadsheetBarItemSelected" : " spreadsheetBarItemSelected");
                if (spreadsheetBarTop == null)
                    spreadsheetBarTop = document.getElementById(tableBody.id + "_spreadsheetBars_spreadsheetBarTop");
                var barItem = spreadsheetBarTop.rows[0].cells[loc[1]];
                if (barItem != null &&
                    barItem.className.indexOf("spreadsheetBarItemSelected") < 0)
                    barItem.className += (barItem.className.length <= 0 ? "spreadsheetBarItemSelected" : " spreadsheetBarItemSelected");
            }
        }
    }

    var RE_CLASS_CELL_ACTIVE  = /\s*spreadsheetCellActive/g;
    var RE_CLASS_BAR_SELECTED = /\s*spreadsheetBarItemSelected/g;

    var clearActive = function(tableBody, td, spreadsheetBarLeft, spreadsheetBarTop) {
        if (tableBody != null &&
            td != null) {
            activeCells.splice(activeCells.indexOf(td), 1);
            td.className = td.className.replace(RE_CLASS_CELL_ACTIVE, "");
            if (document.getElementById(tableBody.id + "_spreadsheetBars_spreadsheetBarCorner") != null) {
                var loc = getTdLocation(td);
                if (loc != null) {
                    if (spreadsheetBarLeft == null)
                        spreadsheetBarLeft = document.getElementById(tableBody.id + "_spreadsheetBars_spreadsheetBarLeft");
                    var barItem = spreadsheetBarLeft.rows[loc[0]].cells[0];
                    barItem.className = barItem.className.replace(RE_CLASS_BAR_SELECTED, "");
                    if (spreadsheetBarTop == null)
                        spreadsheetBarTop = document.getElementById(tableBody.id + "_spreadsheetBars_spreadsheetBarTop");
                    var barItem = spreadsheetBarTop.rows[0].cells[loc[1]];
                    barItem.className = barItem.className.replace(RE_CLASS_BAR_SELECTED, "");
                }
            }
        }
        clearActiveMain(tableBody);
    }

    var clearAllActive = TrimPath.spreadsheet.clearAllActive = function(tableBody) {
        for (var i = 0; i < activeCells.length; i++)  {
            var td = activeCells[i];
            if (td.className != "")
                td.className = td.className.replace(RE_CLASS_CELL_ACTIVE, "");
        }
        activeCells.clear();
        if (document.getElementById(tableBody.id + "_spreadsheetBars_spreadsheetBarCorner") != null) {
            var spreadsheetBarLeft = document.getElementById(tableBody.id + "_spreadsheetBars_spreadsheetBarLeft");
            for (var i = 0, ths = spreadsheetBarLeft.getElementsByTagName('TH'); i < ths.length; i++) {
                var th = ths[i];
                if (th.className != "")
                    th.className = th.className.replace(RE_CLASS_BAR_SELECTED, "");
            }
            var spreadsheetBarTop = document.getElementById(tableBody.id + "_spreadsheetBars_spreadsheetBarTop");
            for (var i = 0, ths = spreadsheetBarTop.getElementsByTagName('TH'); i < ths.length; i++) {
                var th = ths[i];
                if (th.className != "")
                    th.className = th.className.replace(RE_CLASS_BAR_SELECTED, "");
            }
        }
        clearActiveMain(tableBody);
    }

    var setActiveMain = TrimPath.spreadsheet.setActiveMain = function(tableBody, td) {
        // In a range of selected cells, this is the "main" cell that would be edited 
        // on any keyboard input, usually the top-left cell.

        var thickness   = 2;
        var thicknessPx = thickness + "px";
        var adjustPx    = 1;
        if (TrimPath.spreadsheet.isIE) 
            adjustPx = 0;

        if (tableBody != null && td != null) {
            var el = document.getElementById(tableBody.id + "_activeMain_top");
            if (el == null) {
                el = document.createElement("DIV");
                el.id = tableBody.id + "_activeMain_top";
                el.className = "spreadsheetCellActiveMain";
                el.innerHTML = "";
                tableBody.parentNode.appendChild(el);
            }
            el.style.display  = "";
            el.style.position = "absolute";
            el.style.left     = (td.offsetLeft) + "px";
            if (TrimPath.spreadsheet.isIE)
                el.style.top  = (td.offsetTop - thickness) + "px";
            else
                el.style.top  = (td.offsetTop - thickness - 1) + "px";
            el.style.width    = (td.clientWidth) + "px";
            el.style.height   = thicknessPx;

            var el = document.getElementById(tableBody.id + "_activeMain_bottom");
            if (el == null) {
                el = document.createElement("DIV");
                el.id = tableBody.id + "_activeMain_bottom";
                el.className = "spreadsheetCellActiveMain";
                tableBody.parentNode.appendChild(el);
            }
            el.style.display  = "";
            el.style.position = "absolute";
            el.style.left     = (td.offsetLeft) + "px";
            if (TrimPath.spreadsheet.isIE)
                el.style.top  = (td.offsetTop + td.clientHeight + 1) + "px";
            else
                el.style.top  = (td.offsetTop + td.clientHeight) + "px";
            el.style.width    = (td.clientWidth) + "px";
            el.style.height   = thicknessPx;

            var el = document.getElementById(tableBody.id + "_activeMain_left");
            if (el == null) {
                el = document.createElement("DIV");
                el.id = tableBody.id + "_activeMain_left";
                el.className = "spreadsheetCellActiveMain";
                tableBody.parentNode.appendChild(el);
            }
            el.style.display  = "";
            el.style.position = "absolute";
            if (TrimPath.spreadsheet.isIE) {
                el.style.left = (td.offsetLeft - thickness - adjustPx + 1) + "px";
                el.style.top  = (td.offsetTop - thickness) + "px";
            } else {
                el.style.left = (td.offsetLeft - thickness - adjustPx) + "px";
                el.style.top  = (td.offsetTop - thickness - 1) + "px";
            }
            el.style.width    = thicknessPx;
            el.style.height   = (td.clientHeight + thickness + thickness + 1) + "px";

            var el = document.getElementById(tableBody.id + "_activeMain_right");
            if (el == null) {
                el = document.createElement("DIV");
                el.id = tableBody.id + "_activeMain_right";
                el.className = "spreadsheetCellActiveMain";
                tableBody.parentNode.appendChild(el);
            }
            el.style.display  = "";
            el.style.position = "absolute";
            el.style.left     = (td.offsetLeft + td.clientWidth) + "px";
            if (TrimPath.spreadsheet.isIE)
                el.style.top  = (td.offsetTop - thickness) + "px";
            else
                el.style.top  = (td.offsetTop - thickness - 1) + "px";
            el.style.width    = thicknessPx;
            el.style.height   = (td.clientHeight + thickness + thickness + 1) + "px";
        }
    }

    var clearActiveMain = function(tableBody) {
        if (tableBody != null) {
            var el = document.getElementById(tableBody.id + "_activeMain_top");
            if (el != null)
                el.style.display = "none";
            var el = document.getElementById(tableBody.id + "_activeMain_bottom");
            if (el != null)
                el.style.display = "none";
            var el = document.getElementById(tableBody.id + "_activeMain_left");
            if (el != null)
                el.style.display = "none";
            var el = document.getElementById(tableBody.id + "_activeMain_right");
            if (el != null)
                el.style.display = "none";
        }
    }

    var getIndexTr = function(tableBody, row) {          // The row is 1-based.
        if (isBarItem(tableBody.rows[0].cells[0]))
            row++;
        return row - 1;                                  // A indexTr is 0-based.
    }

    var getIndexTd = function(tableBody, indexTr, col) { // The col is 1-based.
        var tr = tableBody.rows[indexTr];                // A indexTr is 0-based.
        if (tr != null &&
            isBarItem(tr.cells[0]))
            col++;
        return col - 1;                                  // A indexTd is 0-based.
    }

    var getTdId = TrimPath.spreadsheet.getTdId = function(tableBody, row, col) {
        if (tableBody != null)
            return tableBody.id + "_" + row + "x" + col;      
        return null;
    }

    var getTd = TrimPath.spreadsheet.getTd = function(tableBody, row, col) {
        if (tableBody != null)
            return document.getElementById(getTdId(tableBody, row, col));      
        return null;
    }

    var getTdLocation = TrimPath.spreadsheet.getTdLocation = function(td) {
        if (td.id != null &&
            td.id != "") {
            var result = td.id.split("_")[1];
            if (result != null) {
                var loc = result.split("x");
                loc[0] = theParseInt(loc[0]);
                loc[1] = theParseInt(loc[1]);
                return loc;
            }
        }
        // The following only works when there are no merged cells.
        var col = td.cellIndex + 1;
        if (isBarItem(td.parentNode.cells[0]))
            col--;
        var row = td.parentNode.rowIndex + 1;
        if (isBarItem(getParent(td, "TABLE").rows[0].cells[0]))
            row--;
        return [ row, col ]; // The row and col are 1-based.
    }

    var getTdValueHolder = function(td) {
        if (hasTdFormat(td) && 
            td.childNodes != null &&
            td.childNodes.length >= 1) {
            var holder = td.childNodes[0];
            if (holder != null && 
                holder.tagName == 'SPAN' &&
                holder.className == 'spreadsheetCellData') {
                return holder;
            }
        }
        return null;
    }
    var RE_CRNL = /[\n\r]/g;
    var getTdValue = TrimPath.spreadsheet.getTdValue = function(td) {
        var holder = getTdValueHolder(td) || td;
        return holder.innerHTML.replace(RE_CRNL, '');
    }
    var getTdValueFormula = function(td) {
        var v = td.getAttribute("formula");
        if (v == null ||
            v.length <= 0)
            v = getTdValue(td);
        //2007 modification re locked cell protection
        if (td.className.indexOf("locked") >= 0 || 
            td.className.indexOf("auto_locked") >= 0) {  
            if (v.indexOf("(Cell locked) ") < 0) {
              v = "(Cell locked) " + v; 
            }
        }
        //end 2007 mods
        return v;
    }
    var makeTdValueHTML = TrimPath.spreadsheet.makeTdValueHTML = function(td, v) {
        v = v || "";
        if (v.length > 0 &&
            hasTdFormat(td))
            v = '<span class="spreadsheetCellData">' + v + '</span>';
        return v;
    }
    var setTdValue = TrimPath.spreadsheet.setTdValue = function(td, v) {
        td.innerHTML = makeTdValueHTML(td, v);
    }
    var getTdFormat = TrimPath.spreadsheet.getTdFormat = function(td) {
        return td.getAttribute("format"); 
    }
    var getTdFormatSpec = TrimPath.spreadsheet.getTdFormatSpec = function(td) {
        return parseFormat(getTdFormat(td));
    }
    var hasTdFormat = TrimPath.spreadsheet.hasTdFormat = function(td) {
        var fmt = getTdFormat(td);
        return fmt != null && fmt.length > 0;
    }
    var setTdFormat = TrimPath.spreadsheet.setTdFormat = function(td, fmt) {
        var v = getTdValue(td);
        if (fmt != null && fmt.length > 0)
            td.setAttribute("format", fmt); 
        else
            td.removeAttribute("format");
        setTdValue(td, v);
    }

    var parseFormat = TrimPath.spreadsheet.parseFormat = function(fmt) {
        if (fmt != null &&
            fmt.length > 0) {
            try {
                return eval('({' + fmt + '})');
            } catch (e) {
            }
        }
        return null;
    }

    var formatTd = TrimPath.spreadsheet.formatTd = function(td, val) {
        // Called in 2 cases: during a recalc on a formula cell (which may not have any previous value or value holder)
        // and during cellEditDone on a static value cell.
        var spec = getTdFormatSpec(td);
        if (spec != null) {
            var v = makeTdValueHTML(td, getTdValue(td));
            if (val != null &&
                (typeof(val) != "string" || val.length > 0)) {
                var valFormatted = formatValue(val, spec);
                if (spec.wrap == 'n')
                    v += '<div class="spreadsheetCellFormatted spreadsheetCellOverflow">' + valFormatted + '</div>';
                else
                    v += '<div class="spreadsheetCellFormatted">' + valFormatted + '</div>';
            }
            td.innerHTML = v;
        }
    }

    var getTdValueFormatted = function(td) {
        for (var i = 0, divs = td.getElementsByTagName('DIV'); i < divs.length; I++) {
            if (divs[i].className.indexOf('spreadsheetCellFormatted') >= 0)
                return divs[i].innerHTML;
        }
        return getTdValue(td);
    }

    var formatValue = TrimPath.spreadsheet.formatValue = function(val, spec, formatters) {
        formatters = formatters || TrimPath.formatters;
        formatter = formatters[spec.format || 'none'];
        if (formatter != null)
            return formatter.call(null, val, spec);
        return val;
    }

    if (TrimPath.formatters == null) 
        TrimPath.formatters = {};
    TrimPath.formatters.none = function(val, spec) { return val; };
    TrimPath.formatters.num = function(val, spec) {
        var funcs = TrimPath.spreadsheetEngine.standardFunctions;
        var numType = spec.numType;
        if (numType == 'c') {
            var decimals = spec['places']
            if (decimals != null)
                decimals = parseInt(decimals);            
            val = funcs.DOLLAR(funcs.N(val), decimals, spec['curr']);
        }
        if (numType == 'p')
            val = (funcs.N(val) * 100) + '%';
        return val;
    }

    var TableCellProvider = function(tableBodyId) {
        this.tableBodyId = tableBodyId;
        this.cells       = {};
        this.ncols       = null;
        this.nrows       = null;
        var tableBody = document.getElementById(this.tableBodyId);
        if (tableBody != null)
            this.nrows = tableBody.rows.length - (isBarItem(tableBody.rows[0].cells[0]) ? 1 : 0);
        this.changedCells    = [];
        this.calculatedCells = [];
    }

    TableCellProvider.prototype.getCell = function(row, col) {
        if (this.nrows != null &&
            this.nrows < row) 
            return null;
        if (typeof(col) == "string")
            col = TrimPath.spreadsheetEngine.columnLabelIndex(col);
        var key  = row + "," + col;
        var cell = this.cells[key];
        if (cell == null) {
            var tableBody = document.getElementById(this.tableBodyId);
            if (tableBody != null) {
                var td = getTd(tableBody, row, col);
                if (td != null)
                    cell = this.cells[key] = new TableCell(this, this.tableBodyId, row, col);
                else
                    cell = this.cells[key] = fakeCell;
            }
        }
        return cell;
    }

    TableCellProvider.prototype.getNumberOfColumns = function(row) {
        if (this.ncols == null) {
            var tableBody = document.getElementById(this.tableBodyId);
            if (tableBody != null)
                this.ncols = countColumns(tableBody);
        }
        return this.ncols;
    }

    TableCellProvider.prototype.toString = function() {
        var tableBody = document.getElementById(this.tableBodyId);
        for (var result = "", i = 0; i < tableBody.rows.length; i++)
            result += tableBody.rows[i].innerHTML.replace(RE_CRNL, "") + "\n";
        return result;
    }

    var EMPTY_VALUE = {};

    var TableCell = function(tableCellProvider, tableBodyId, row, col) {
        this.tableCellProvider = tableCellProvider;
        this.tableBodyId = tableBodyId;
        this.row = row;
        this.col = col;
        this.value = EMPTY_VALUE; // Cache of value, where "real" value is usually the TD.innerHTML.
    }
    TableCell.prototype = new TrimPath.spreadsheetEngine.Cell();
    TableCell.prototype.getTd = function() { 
        return getTd(document.getElementById(this.tableBodyId), this.row, this.col); 
    }
    TableCell.prototype.setValue = function(v, e) { // Called by recalc engine on formula cells with the formula's output.
        var td = this.getTd();
        if (td != null) {
            var changed = (e != null || v != getTdValue(td));
            if (changed)            
                this.tableCellProvider.changedCells.push([this.row, this.col]);
    
            this.error = e; 
            this.value = v;
    
            setTdValue(td, v);
            if (e == null && hasTdFormat(td))
                formatTd(td, v);

            this.tableCellProvider.calculatedCells.push([this.row, this.col]);
        }
    }
    TableCell.prototype.getValue = function() { 
        var v = this.value;
        if (v === EMPTY_VALUE && this.getFormula() == null) {
            var td = this.getTd();
            if (td != null) {
                v = getTdValue(td);
                v = this.value = (v.length > 0 ? TrimPath.spreadsheetEngine.parseFormulaStatic(v) : null); 
            }
        }
        return (v === EMPTY_VALUE ? null : v);
    }

    TableCell.prototype.getFormulaFunc = function()  { return this.formulaFunc; };
    TableCell.prototype.setFormulaFunc = function(v) { this.formulaFunc = v; };

    TableCell.prototype.getFormula = function()  { 
        var td = this.getTd();
        if (td != null)
            return td.getAttribute("formula"); 
        return null;
    };
    TableCell.prototype.setFormula = function(v) { 
        var td = this.getTd();
        if (td != null) {
            if (v != null &&
                v.length > 0) 
                td.setAttribute("formula", v); 
            else 
                td.removeAttribute("formula");
        }
    }

    var fakeCell = new TableCell("", -1, -1);

    var isBarItem = function(el) {
        return el != null &&
               el.className != null &&
               el.className.search(/(^|\\s)spreadsheetBarItem(\\s|$)/) >= 0;
    }

    var isClass = TrimPath.spreadsheet.isClass = function(el, className) {
        return el != null &&
               el.className != null &&
               el.className.search('(^|\\s)' + className + '(\\s|$)') >= 0; // TODO: Might want to cache the regexp.
    }

    var copyNodeTree = function(src, filterFunc, nodeFactory) { // Copies ELEMENTS, their attributes, and TEXT nodes.
        if (nodeFactory == null)
            nodeFactory = document;
        var dst = null;
        if (filterFunc == null ||
            filterFunc(src) == true) {
            if (src.nodeType == 1) { // ELEMENT_NODE
                dst = nodeFactory.createElement(src.tagName);
                for (var i = 0; i < src.attributes.length; i++) {
                    var key = src.attributes[i].name;
                    if (key != "id") {
                        var val = src.getAttribute(key);
                        if (val != null && 
                            val != "")
                            dst.setAttribute(key, val);
                    }
                }
                for (var i = 0; i < src.childNodes.length; i++) {
                    var dstChild = copyNodeTree(src.childNodes[i], filterFunc, nodeFactory);
                    if (dstChild != null)
                        dst.appendChild(dstChild);
                }
            } else if (src.nodeType == 3) // TEXT_NODE
                dst = nodeFactory.createTextNode(src.data);
        }
        return dst;
    }

    var isFirstChild = function(node) {
        while (node.previousSibling != null &&
               node.previousSibling.nodeType != 1)
            node = node.previousSibling;
        return node.previousSibling == null;
    }

    var getParent = TrimPath.spreadsheet.getParent = function(node, tagName) {
        while (node != null && 
               node.tagName != tagName)
            node = node.parentNode;
        return node;
    }

    var categorizeParents = function(node, parentClasses) {
        while (node != null && node != document.documentElement) {
            for (var name in parentClasses)
                if (isClass(node, name)) {
                    parentClasses[name] = node;
                    break;
                }
            node = node.parentNode;
        }
    }

    var removeElementById = function(id, doc) {
        if (doc == null)
            doc = document;
        var el = doc.getElementById(id);
        if (el != null) 
            el.parentNode.removeChild(el);
    }

    var genId = function(prefix) {
        if (prefix == null)
            prefix = "id";
        return prefix + new Date().getTime() + "-" + theMath.floor(theMath.random() * 1000000);
    }

    var makeBarAdjustor = TrimPath.spreadsheet.makeBarAdjustor = function(tableBodyId, spreadsheetScrollId, repaint) { 
        // Returns an event handler for onscroll and onresize.
        if (spreadsheetScrollId == null)
            spreadsheetScrollId = tableBodyId + "_spreadsheetScroll";
        return function() { // The evt is ignored.
            var spreadsheetScroll = document.getElementById(spreadsheetScrollId);
            if (spreadsheetScroll != null) {
                var spreadsheetBarCorner = document.getElementById(tableBodyId + "_spreadsheetBars_spreadsheetBarCorner");
                var spreadsheetBarLeft   = document.getElementById(tableBodyId + "_spreadsheetBars_spreadsheetBarLeft");
                var spreadsheetBarTop    = document.getElementById(tableBodyId + "_spreadsheetBars_spreadsheetBarTop");

                if (spreadsheetBarTop != null)
                    spreadsheetBarTop.style.top = spreadsheetScroll.scrollTop;
                if (spreadsheetBarLeft != null)
                    spreadsheetBarLeft.style.left = spreadsheetScroll.scrollLeft;
                if (spreadsheetBarCorner != null) {
                    spreadsheetBarCorner.style.top  = spreadsheetScroll.scrollTop;
                    spreadsheetBarCorner.style.left = spreadsheetScroll.scrollLeft;
                }

                if (repaint == true)
                    TrimPath.spreadsheet.repaint(document.getElementById(tableBodyId));
            }
        }
    }

    TrimPath.spreadsheet.toCompactSource = function(node) {
        var out = [];
        TrimPath.spreadsheet.toCompactSourceArray(node, out);
        return out.join('');
    }

    TrimPath.spreadsheet.toCompactSourceArray = function(node, out) {
        if (node.nodeType == 1) { // ELEMENT_NODE
            if ((node.id != null        && node.id.indexOf("spreadsheetBar") >= 0) ||
                (node.className != null && node.className.indexOf("spreadsheetBar") >= 0))
                return;
            if (node.tagName.charAt(0) == '/') // IE hack, where <IMG></IMG> becomes <IMG><//IMG>.
                return;
            out.push("<");
            out.push(node.tagName);
            for (var i = 0; i < node.attributes.length; i++) {
                var key = node.attributes[i].name;
                var val = node.getAttribute(key);
                if (val != null && 
                    val != "") {
                    if (key == "id" && node.tagName == "TD")
                        continue; // Remove decorated ids.
                    if ((key == "contentEditable" && val == "inherit") ||
                        (key == "start" && node.tagName == "IMG") ||
                        (key == "class") || 
                        (key == "rowspan") || 
                        (key == "colspan"))
                        continue; // IE hack.
                    if (typeof(val) == "string") {
                        out.push(' ' + key + '="' + val.replace(/"/g, "'") + '"');
                    } else if (key == "style" && val.cssText != "") {
                        out.push(' style="' + val.cssText + '"');
                    }
                }
            }
            if (node.className != null &&             // IE hack, where class doesn't appear in attributes.
                node.className.length > 0)
                out.push(' class="' + node.className.replace("spreadsheetCellActive", "") + '"');
            if (node.colSpan > 1)                     // IE hack, where colspan doesn't appear in attributes.
                out.push(' colspan="' + node.colSpan + '"');
            if (node.rowSpan > 1)                     // IE hack, where rowspan doesn't appear in attributes.
                out.push(' rowspan="' + node.rowSpan + '"');
            if (node.tagName == "COL")                // IE hack, which doesn't like <COL..></COL>.
                out.push('/>');
            else {
                out.push(">");
                for (var i = 0; i < node.childNodes.length; i++)
                    TrimPath.spreadsheet.toCompactSourceArray(node.childNodes[i], out);
                out.push("</" + node.tagName + ">");
            }
        } else if (node.nodeType == 3) // TEXT_NODE
            out.push(node.data.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'));
    }

    TrimPath.spreadsheet.toPrettySource = function(node, prefix) {
        if (prefix == null)
            prefix = "";
        var result = "";
        if (node.nodeType == 1) { // ELEMENT_NODE
            if ((node.id != null        && node.id.indexOf("spreadsheetBar") >= 0) ||
                (node.className != null && node.className.indexOf("spreadsheetBar") >= 0))
                return "";
            result += "\n" + prefix + "<" + node.tagName;
            for (var i = 0; i < node.attributes.length; i++) {
                var key = node.attributes[i].name;
                var val = node.getAttribute(key);
                if (val != null && 
                    val != "") {
                    if (key == "contentEditable" && val == "inherit")
                        continue; // IE hack.
                    if (typeof(val) == "string")
                        result += " " + key + '="' + val.replace(/"/g, "'") + '"';
                    else if (key == "style" && val.cssText != "")
                        result += ' style="' + val.cssText + '"';
                }
            }
            if (node.childNodes.length <= 0)
                result += "/>";
            else {
                result += ">";
                var childResult = "";
                for (var i = 0; i < node.childNodes.length; i++)
                    childResult += TrimPath.spreadsheet.toPrettySource(node.childNodes[i], prefix + "  ");
                result += childResult;
                if (childResult.indexOf('\n') >= 0)
                    result += "\n" + prefix;
                result += "</" + node.tagName + ">";
            }
        } else if (node.nodeType == 3) // TEXT_NODE
            result += node.data.replace(/^\s*(.*)\s*$/g, "$1");
        return result;
    }
    
    //2007 mods--general key handling routine
    TrimPath.onDocumentKeyDown = makeEventHandler(function(evt, target) {
      var keyCode = (evt.keyCode) ? evt.keyCode : evt.which;
      //save button
      if (evt.ctrlKey == true && keyCode == 83) { // Control-S saves
          var saveButton = document.getElementById('save_button');
          if (saveButton != null)
              saveButton.click();
          return false;
      }

      var tableBody = document.getElementById('s1');
      if (tableBody != null) {
          var inputFormula = getInputFormula(tableBody.id);
          if (inputFormula != null) {
              if ((target != null) &&                           //use this handler only when
                  ((target.tagName != "INPUT" &&                //not in an input box
                    target.tagName != "TEXTAREA") ||            //not in a textarea
                   (target.id.indexOf("_rangeText") >= 0) ||    //do use if in a range 
                   (keyCode == 13) ||                           //use for all enter key combos
                   (keyCode == 9))) {                           //use for all tab presses 
                  if (evt.ctrlKey == true && keyCode == 13) {   //ctrl-enter enters but no move
                      TrimPath.spreadsheet.cellEditDone(tableBody, false);
                      TrimPath.spreadsheet.cellEditAbandon(tableBody, false);
                      TrimPath.spreadsheet.lastKeyCode = null;
                      TrimPath.spreadsheet.moveCellFocus(tableBody,  0,  0, false);
                      return false;
                  }
                  if ((keyCode == 13) ||   //enter
                      (keyCode == 9) ||    //tab
                      (keyCode >= 37 && keyCode <= 40  //arrow keys
                       && evt.ctrlKey == false && evt.altKey == false &&  //no alt and ctrl arrow
                       TrimPath.spreadsheet.lastKeyCode == null)) {
                      if (keyCode == 37 || (keyCode == 9 && evt.shiftKey == true))   // left arrow
                          TrimPath.spreadsheet.moveCellFocus(tableBody,  0, -1, false);
                      if (keyCode == 39 || (keyCode == 9 && evt.shiftKey == false))  // right  arrow
                          TrimPath.spreadsheet.moveCellFocus(tableBody,  0,  1, false);
                      if (keyCode == 40 || (keyCode == 13 && evt.shiftKey == false)) // down  arrow
                          TrimPath.spreadsheet.moveCellFocus(tableBody,  1,  0, false);
                      if (keyCode == 38 || (keyCode == 13 && evt.shiftKey == true))  // up arrow or shift-enter
                          TrimPath.spreadsheet.moveCellFocus(tableBody, -1,  0, false);

                      TrimPath.spreadsheet.lastKeyCode = null;
                      inputFormula.select();
                      inputFormula.focus();
                      return false;
                  }
                  //TODO Add alignment commands?
                  if (evt.ctrlKey == true) {
                      if (keyCode == 85) {  //ctrl-u
                          TrimPath.spreadsheet.styleToggleAction(tableBody, "textDecoration:underline");
                          return false;
                      }
                      if (keyCode == 66) {  //ctrl-b
                          TrimPath.spreadsheet.styleToggleAction(tableBody, "fontWeight:bold");
                          return false;
                      }
                      if (keyCode == 73) {  //ctrl-i
                          TrimPath.spreadsheet.styleToggleAction(tableBody, "fontStyle:italic");
                          return false;
                      }
                  }
              }
              //TODO Should ctrl-end be bottom right or bottom left?
              if (target != null)  {
                  var loc = TrimPath.spreadsheet.currentLocation(tableBody.id);
                  if (loc != null) {
                      if (keyCode == 33 || // pgUp
                          keyCode == 34) { // pgDown
                          var rowChange = Math.min(10, Math.max(-10, (keyCode == 33 ? 1 - loc[0] : tableBody.rows.length - 1 - loc[0])));
                          TrimPath.spreadsheet.lastKeyCode = null;
                          TrimPath.spreadsheet.moveCellFocus(tableBody, rowChange, 0, false);
                          inputFormula.select();
                          inputFormula.focus();
                          return false;
                      }
                      if ( (evt.ctrlKey == true) &&
                           (keyCode == 36 ||     // ctrl-Home key
                            keyCode == 35) ) {    // ctrl-End key
                          var row = (keyCode == 36 ? 1 : tableBody.rows.length - 1);
                          TrimPath.spreadsheet.cellEditActivate(tableBody, row, 1);
                          TrimPath.spreadsheet.lastKeyCode = null;
                          TrimPath.spreadsheet.moveCellFocus(tableBody,  0, 0, false);  //autoscroll
                          inputFormula.select();
                          inputFormula.focus();
                          return false;
                      }
                  }
              }
          }
      }
    })
 
    //2007 mods
    TrimPath.init = function() {

        var startDate= new Date();
        TrimPath.spreadsheet.logTime('&nbsp;init start');

        setTimeout("TrimPath.fineTuneScreen('s1')", 100);

        TrimPath.spreadsheet.logTime('&nbsp;init done', startDate);

        document.onkeydown = TrimPath.onDocumentKeyDown
    }

    //this routine cleans up the screen
    TrimPath.fineTuneScreen = function(tableBodyId) {
        var startDate= new Date();
        TrimPath.spreadsheet.logTime("&nbsp;fineTuneScreen start");

        var tableBody = document.getElementById(tableBodyId);
        if (tableBody != null) {
            TrimPath.spreadsheet.decorateSpreadsheet(tableBody);

            var spreadsheetBars = document.getElementById('s1_spreadsheetBars');
            if (spreadsheetBars != null) {
                var spreadsheetBarsOutside = spreadsheetBars.className.indexOf("spreadsheetBarsOutside") >= 0;
                if (spreadsheetBarsOutside) {
                    var spreadsheetBarCorner = document.getElementById('s1_spreadsheetBars_spreadsheetBarCorner');

                    var spreadsheetScroll = document.getElementById('s1_spreadsheetScroll');
                    if (spreadsheetScroll != null) {
                        spreadsheetScroll.onscroll = TrimPath.onResizeScroll;
                        TrimPath.onResizeScroll();
                    }
                }
            }

            var barLeft = document.getElementById(tableBodyId + '_spreadsheetBars_spreadsheetBarLeft');
            if (barLeft != null &&
                barLeft.clientHeight != tableBody.clientHeight) { 
                TrimPath.spreadsheet.syncRowHeights(tableBody, barLeft);
            }

            TrimPath.spreadsheet.repaint(tableBody); 

        }
        TrimPath.spreadsheet.logTime("&nbsp;fineTuneScreen end", startDate);

    }

    //this routine resizes the spreadsheet grid to the display size and handles scrolling
    TrimPath.onResizeScroll = function() {
        var spreadsheetScroll = document.getElementById("s1_spreadsheetScroll");
        if (spreadsheetScroll != null) {
            var spreadsheetBars             = document.getElementById("s1_spreadsheetBars");
            var spreadsheetBarTop           = document.getElementById("s1_spreadsheetBars_spreadsheetBarTop");
            var spreadsheetBarTopContainer  = document.getElementById("s1_spreadsheetBars_spreadsheetBarTopContainer");
            var spreadsheetBarLeft          = document.getElementById("s1_spreadsheetBars_spreadsheetBarLeft");
            var spreadsheetBarLeftContainer = document.getElementById("s1_spreadsheetBars_spreadsheetBarLeftContainer");
            var spreadsheetBarCorner        = document.getElementById("s1_spreadsheetBars_spreadsheetBarCorner");
            var gridContainer               = document.getElementById("s1_gridContainer");

            var barTopHeight    = spreadsheetBarTopContainer.offsetHeight;
            var barLeftHeight   = spreadsheetBarLeftContainer.offsetWidth;
            var gridHeight = gridContainer.offsetHeight;

            gridContainer.parentNode.style.width = document.body.clientWidth;

            //adjust gridSize just once
            if (gridContainer.style.height == null ||
                gridContainer.style.height == "") {
                buttonHeight = 56;  //no buttons
                if(navigator.appName.indexOf("Explorer") != -1) 
                    { buttonHeight += 20;}  //+20 if IE 
                if(navigator.appName.indexOf("Netscape") != -1) 
                    { buttonHeight += 20;}  //+20 if Netscape 
                if ( document.URL.match("interactive") || document.URL.match("edit") ) 
                    { buttonHeight += 104; } // +104 for buttons if interactive
                //height ranges from a min of 400 to a max of 600 in size
                gridHeight = Math.max(400, 
                      Math.min(600, document.body.clientHeight - buttonHeight));
            }
            spreadsheetBars.style.left = -(barLeftHeight) + "px";
            spreadsheetBars.style.top  = -(barTopHeight)  + "px";

            spreadsheetScroll.style.height = gridHeight - barTopHeight - 2;
            spreadsheetScroll.style.width  = document.body.clientWidth - barLeftHeight - 5;

            spreadsheetBarTopContainer.style.height  = barTopHeight;
            spreadsheetBarTopContainer.style.width   = document.body.clientWidth - barLeftHeight;
            spreadsheetBarLeftContainer.style.height = gridHeight - barTopHeight;
            spreadsheetBarLeftContainer.style.width  = barLeftHeight;

            spreadsheetBarTop.style.left  = -(spreadsheetScroll.scrollLeft + barLeftHeight);
            spreadsheetBarTop.style.top   = 0;
            spreadsheetBarLeft.style.left = 0;
            spreadsheetBarLeft.style.top  = -(spreadsheetScroll.scrollTop + barTopHeight);

            spreadsheetBarCorner.style.left = 0; 
            spreadsheetBarCorner.style.top  = 0; 
        }
    }

}) ();
