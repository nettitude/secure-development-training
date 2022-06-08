<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <META http-equiv=content-type content="text/html; charset=utf-8">
    <META content=NOARCHIVE name=ROBOTS>
    <title>WordPress Spreadsheet 0.6</title>

    <SCRIPT language=javascript src="js/prototype-1.4.0.js"></SCRIPT>
    <script language="javascript" src="js/spreadsheet_engine.js?version=1.01"></script>
    <script language="javascript" src="js/spreadsheet_ui.js?version=1.0"></script>

    <link rel="stylesheet" type="text/css" href="css/spreadsheet.css?version=1.01">
    <link rel="stylesheet" type="text/css" href="css/spreadsheet_orange.css?version=1.01" title="orange with flat buttons">
    <link rel="alternate stylesheet" type="text/css" href="css/spreadsheet_grey.css?version=1.01" title="grey with flat buttons">
    <link rel="alternate stylesheet" type="text/css" href="css/spreadsheet_orange3d.css?version=1.01" title="orange with 3dbuttons">
    <link rel="alternate stylesheet" type="text/css" href="css/spreadsheet_pastel.css?version=1.01" title="pastel with 3d buttons">
    <link rel="alternate stylesheet" type="text/css" href="css/spreadsheet_green.css?version=1.01" title="green with 3d buttons">
    
  </head>
  
  <body onresize="return TrimPath.onResizeScroll()" onload="return TrimPath.init()">
        
    <DIV class=spreadsheetEditor id=s1_spreadsheetEditor>
      <!-- This textarea is used for cut, copy and paste operations -->
      <textarea id="s1_rangeText" style="position:absolute; left:-5000;"
                onkeydown="return TrimPath.spreadsheet.rangeTextKeyDown(event);"
                onkeyup="return TrimPath.spreadsheet.rangeTextKeyUp(event);">
      </textarea>

      <DIV id=s1_spreadsheetEditor_spreadsheetControls class="spreadsheetControls">
        <span class="spreadsheetStyle spreadsheetStyleFont">
          <a onclick="return TrimPath.spreadsheet.styleToggle(event)" href="#fontWeight:bold"><img title="bold" alt="B" src="js/icons/text_bold.png"/></a>
          <a onclick="return TrimPath.spreadsheet.styleToggle(event)" href="#fontStyle:italic"><img title="italic" alt="I " src="js/icons/text_italic.png"/></a>
          <a onclick="return TrimPath.spreadsheet.styleToggle(event)" href="#textDecoration:underline"><img title="underline" alt="U" src="js/icons/text_underline.png"/></a>  
          <a onclick="return TrimPath.spreadsheet.styleToggle(event)" class="spreadsheetStyleAlignLeft" href="#textAlign:left"><img title="align left" alt="<<" src="js/icons/text_align_left.png"/></a>
          <a onclick="return TrimPath.spreadsheet.styleToggle(event)" class="spreadsheetStyleAlignCenter" href="#textAlign:center"><img title="align center" alt="==" src="js/icons/text_align_center.png"/></a>
          <a onclick="return TrimPath.spreadsheet.styleToggle(event)" class="spreadsheetStyleAlignRight" href="#textAlign:right"><img title="align right" alt=">>" src="js/icons/text_align_right.png"/></a>  
          <a onclick="return TrimPath.spreadsheet.addRow(event)" href="#addRow"><img title="add row" alt="+row" src="js/icons/table_row_insert.png"/></a>  
          <a onclick="return TrimPath.spreadsheet.addCol(event)" href="#addCol"><img title="add column" alt="+col" src="js/icons/table_col_insert.png"/></a>  
          <a onclick="return TrimPath.spreadsheet.fixedButton(event)" class="spreadsheetStyleAlignRight2" href="#fixedButton"><img title="fix 2 decimal places" alt="fix2" src="js/icons/fix2.png"/></a>
        </span>
      </DIV>
      
      <DIV class="spreadsheetFormulaBar fixedBar" id=s1_spreadsheetFormulaBar_ribbon style="DISPLAY: block; POSITION: relative">
        <TABLE width="90%">
          <TBODY>
       	    <TR>
    	        <TD style="width: 4em; text-align: left" vAlign=top noWrap align=left>
                <SPAN class=spreadsheetLocation id=s1_spreadsheetEditor_spreadsheetControls_loc>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</SPAN>
                <TEXTAREA class=spreadsheetFormula
                          onkeypress="return TrimPath.spreadsheet.formulaKeyPress(event)"
                          onmousedown="return TrimPath.spreadsheet.formulaMouseDown(event)"
                          onkeydown="return TrimPath.spreadsheet.formulaKeyDown(event)"
                          onkeyup="return TrimPath.spreadsheet.formulaKeyUp(event)"
                          id=s1_spreadsheetEditor_spreadsheetControls_formula
                          name=s1_spreadsheetEditor_spreadsheetControls_formula
                          style="DISPLAY: none; POSITION: absolute"
                          rows=1 cols=800
                          wrap=hard>
                </TEXTAREA>
              </TD>
              <TD style="padding-left: 2em" vAlign=top noWrap>
                <B><I>fx</I></B>
              </TD>
      			  <TD vAlign=top noWrap width="100%">
                <TEXTAREA onkeypress="return TrimPath.spreadsheet.formulaBarKeyPress(event)"
                        onkeyup="return TrimPath.spreadsheet.formulaBarKeyUp(event)"
                        onclick="return TrimPath.spreadsheet.formulaMouseClick(event)"
                        id=s1_spreadsheetEditor_spreadsheetControls_formulaBar
                        name=s1_spreadsheetEditor_spreadsheetControls_formulaBar
                        rows=1 
                        wrap=hard>
                </TEXTAREA>
    			    </TD>
            </TR>
          </TBODY>
        </TABLE>
      </DIV>
            
      <DIV id=s1_gridContainer style="PADDING-RIGHT: 0px; PADDING-LEFT: 0px; PADDING-BOTTOM: 0px; MARGIN: 0px; OVERFLOW: hidden; PADDING-TOP: 0px">
    	  <TABLE id=s1_grid style="PADDING-RIGHT: 0px; PADDING-LEFT: 0px; PADDING-BOTTOM: 0px; MARGIN: 0px; PADDING-TOP: 0px" cellSpacing=0 cellPadding=0 border=0>
      		<TBODY>
      			<TR>
      				<TD vAlign=top width=31>
      				  <DIV id=s1_spreadsheetBars_spreadsheetBarCornerContainer></DIV>
            	</TD>
      				<TD vAlign=top>
      					<DIV class=barContainer id=s1_spreadsheetBars_spreadsheetBarTopContainer></DIV>
            	</TD>
        		</TR>
      			<TR>
      				<TD vAlign=top width=31>
      					<DIV class=barContainer id=s1_spreadsheetBars_spreadsheetBarLeftContainer></DIV>
            	</TD>
       				<TD vAlign=top>
       					<DIV class=spreadsheetScroll id=s1_spreadsheetScroll>
      	 					<DIV class="spreadsheetBars spreadsheetBarsOutside" id=s1_spreadsheetBars>
