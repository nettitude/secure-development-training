This is the development page for the WordPress Spreadsheet plugin (wpSS). This open-source plugin provides a simple spreadsheet that WordPress users can use to embed an interactive spreadsheet table in their WordPress blog. I use it, for example, to keep track of my miles hiked on my blog 1000 Miles or Bust! There's now a spreadsheet that lists the functions available in wpSS formulas.

Because the plugin runs as part of WordPress, it stores the spreadsheet info (as an HTML table) locally on the WordPress SQL database. This is an important difference from embedding a Google Spreadsheet, NumSum, or other spreadsheet web service into an entry--in all of those examples the spreadsheet data is stored on a separate server. It allows you, for example, much greater flexibility in how you present your spreadsheet to your readers. For example, provision is made for automatically locking certain rows or columns so that they may not be changed by users so you can develop a form that limits which cells can be modified. Future plans also include the ability to selectively present only certain columns to the logged in users, etc.
Installation:

To install, unpack the zip file into a directory called wpSS and copy that directory into the wordpress\wp-content\plugins directory on your WordPress server. Be sure to deactivate any previous versions of the plugin. Then activate the plugin using the "plugins" menu of your WordPress administration pages.  
Usage:

Note: These instructions assume you have the latest version of wpSS.

To embed a spreadsheet in a post, you simply type {spreadsheet id=x display=interactive} in your post, where x is replaced by the number of the spreadsheet you want to embed. Alternately you can use display=plain or display=table to present the spreadsheet data with limited or no interactivity--and if you omit the display variable entirely, WordPress Spreadsheet will display the spreadsheet with limited interactivity.

To create a new spreadsheet, go to the Write menu while logged into the WordPress administrator account. Choose the "wpSpreadSheet" submenu. You can save this spreadsheet as a new ID # by changing the ID # field at the bottom. Be sure you don't choose an ID # that is already used, or you will overwrite your old spreadsheet with no warning! You can then embed the spreadsheet into a post as above. This page also provides you with a convenient place to edit any previously exisitng spreadsheet, even if that spreadsheed has only limited interactivity within the post.

You can now set autolocking columns and rows from the "wpSpreadSheet" submenu under the Options menu from the WordPress admin panel. Be aware that applying autolocking to 'all' spreadsheet id #'s is very powerful. Autolocking is always turned off from the editing SpreadSheet page described above. From the Options panel, you can also choose a new default id # to select which spreadsheet which is first loaded into the editing SpreadSheet page. Be sure to pick an id # to a spreadsheet that already exists.

Individual cells may be locked (or 'protected') by pressing ctrl-L with a cell selected (but not being edited); pressing ctrl-L again will unlock the cell. Locked cells save when the spreadsheet saves (if the spreadsheet is interactive ). However, pressing ctrl-L does not unlock autolocked cells--that may only be done from the admin Options panel.

You can resize columns or rows by using the mouse to grab and drag the edge of a column or row seperator in the column or row bar. There are toolbar buttons to add more columns at the right and rows at the bottom, and to format your text.

You can copy and paste data by selecting a range using the mouse.  You can use the clipboard to transfer data between spreadsheet programs. 

There's a cheat sheet that lists all the available formula functions. Tip: Be sure to use the FIXED() function for more easily readable numbers. The "0.00" toolbar button formats irrational numbers nicely by limiting the display to only 2 decimal places unsing the FIXED function.
Download and Version History:

23 July 07: wpSS version 0.6 - Incremental release (wpSS_v0.6.zip)
22 May 07: wpSS version 0.55 - Incremental release (wpSS_v0.55.zip)
9 May 07: wpSS version 0.5 - Incremental release (wpSS_v0.5.zip)
26 April 07: wpSS version 0.4 - Initial release (wpSS_v0.4.zip)
All previous versions unreleased and for development only
Bug Reports:

Please send bug reports to bugs [at] timrohrer.com 
Technical Notes:

version 0.6 (release date 23 July 07)

    * Cut, copy and paste now work for a range of spreadsheet data.  Use the mouse to select a range of data, then use ctrl-x, ctrl-c and ctrl-v as usual.  No support yet for using shift-arrows to select a range of cells.
    * Ranges may now be successfully copied and pasted into other spreadsheet programs such as Excel or OpenOffice.  This should improve data interchangeability.
    * More AJAX-like functionality: in-place cell editing; cut, copy and paste; ctrl-s to save (when applicable); pg-up, pg-down scroll 10 lines each; ctrl-home and ctrl-end move the cursor to the beginning/end of spreadsheet respectively.
    * Cell locking (ctrl-l) now only works on the cell under the cursor when it is not being edited (i.e. just mouse clicked).  This is to prevent one from pressing ctrl-l during cell editing andthereby preventing the edits from taking place.
    * Most toolbar buttons work on ranges.  Still working on getting ranges to work with cell-locking, fixed button and so on.
    * Function names and ranges may now be lower case.
    * Tweaks to the underlying css and html for improved cross-browser display.  Added 3D enlarging toolbar button effects in alternate stylesheets.  Added several alternate stylesheets.
    * Moved the spreadsheet initialization and resizing on scroll functions from base html file into the main spreadsheet ui javascript file.
    * Eliminated the iframeheight js that was causing numerous display problems in its effort to adapt the spreadsheet height to different display/window heights.
    * Continued to fix minor display bugs in various browsers. Netscape (in Firefox mode) and Safari still have difficulty scrolling the spreadsheet using the arrow and other movement keys--use the scrollbar with the mouse as a workaround.  Safari and Opera seem exhibit a off-by-one bug for a couple of the borders of the in-place css/html box.
    * tested and works in: (WinXP) Firefox 2.0.0.5, IE 6.0.2900.2180, Opera 9.22, Netscape 8.13, SeaMonkey 1.1.1/MultiZilla 1.8.3.0j, Safari 3.0.2 public beta with WordPress 2.1.x

version 0.55 (release date 22 May 07)

    * Cell locking/cell protection. Cells may now be locked (protected) individually by any user by pressing ctrl-L with a cell selected. Autolocked columns and rows are not affected by pressing ctrl-L. Locked cells do save with the spreadsheet (if the user can save.)
    * The arrow keys and TAB keys now work as they normally would in a spreadsheet, that is: entering data and advancing to the next cell.
    * Added toolbars button to add a row at the bottom or add a column at right.
    * Added a toolbar button that makes for prettier number displays by modifying the cell's formula to =FIXED(oldcellcontents).
    * Added nicer, graphical toolbar buttons using icons from famfamfam.com.
    * Continued to fix minor display bugs in various browsers. No progress on Netscape display issues, though it is working.
    * Fixed bug in editing spreadsheet admin page where after using the save/load/clear buttons, the spreadsheet would reload with auto_locking turned back on.
    * tested and works in: (WinXP) Firefox 2.0.0.3, IE 6.0.2900.2180, Opera 9.2, Netscape 8.13, SeaMonkey 1.1.1/MultiZilla 1.8.3.0j with WordPress 2.1.x

version 0.5 (release date 9 May 07)

    * tested and works in: (WinXP) Firefox 2.0.0.3, IE 6.0.2900.2180, Opera 9.2, Netscape 8.13, SeaMonkey 1.1.1/MultiZilla 1.8.3.0j with WordPress 2.1.x
    * to facilitate spreadsheet editing: added a page called "Editing SpreadSheet" accessible as "wpSpreadsheet" from the Write menu in the admin pages. This page never has any autolocked rows or columns so the administrator can edit every cell at will.
    * enabled the autolocking of columns and rows using a "SpreadSheet Options" page accessible as "wpSpreadsheet" from the Options menu in the admin pages. The default id may also be set from the options page
    * when the display option is set to interactive, the clear button and ss_id # field do not appear unless the user is logged in as the blog administrator. Users can still save their changes to a spreadsheet presented in interactive mode.
    * the DAYSFROM function now takes months running from 1 to 12, as a normal human being would think; for example, a count for the days since 1 January 2007 would now be written as the formula =DAYSFROM(2007,1,1)
    * Known bug: when reloading a long spreadsheet with a different id #, Firefox may clip the buttons and fields at the bottom of the page. Use set default id # from the options page as a workaround.
    * minor changes to javascript routines that handle browser resizing that improve the appearance of the GUI. Scrolling now set to "no" in SS_iframe--solves Opera/IE display glitches, may cause one or two in FF. Netscape still fails to display the spreadsheet in a scrollable window due to a Netscape-specific bug; instead the entire spreadsheet appears.
    * empty spreadsheets are now 10x20, and work with the autolocking feature. Spreadsheets created with 0.4 may experience problems with autolocking.

version 0.4 (release date 26 April 07)

    * tested and works in: (WinXP) Firefox 2.0.0.3, IE 6.0.2900.2180, Opera 9.2, Netscape 8.13, SeaMonkey 1.1.1/MultiZilla 1.8.3.0j with WordPress 2.1.x
    * uses an AJAX/PHP mashup for the spreadsheet functions and storage
    * for now, cells may only be marked as "locked" only by editing the raw HTML table using PHPMyAdmin
    * for now, rows or columns may only be marked as "autolocked" only by editing the PHP code in ss_functions.php
    * based on a modified version of the GPL'd TrimPath Spreadsheet version 1.0.14
    * built-in functions in the Trimpath 1.0.14 spreadhseet_engine.js include: SUM(), COUNT(), AVERAGE(), MAX(), and MIN()
    * to which I have added added RAND(), FLOOR(), CEILING(), ABS(), FALSE(), TRUE(), and TODAY() functions
    * added alias functions AVG() for AVERAGE(), INT() for FLOOR(), and RND() for RAND()
    * added a DAYSFROM(four-digit-year, month-0-to-11 [Changed to 1 to 12 starting in version 0.5!!!], day-of-the-month) function to count the days from, for example, a count for the days since 1 January 2007 would be written as the formula =DAYSFROM(2007,0,1)
    * all functions and cell addresses (e.g. B4) must be in ALL CAPS and have parens, even if empty ones
    * added a function to automatically resize spreadsheet on Javascript window resize and onload events
    * functions can now accept out-of-bounds ranges, such as =SUM(C4:C999) when the spreadsheet only has 50 rows

Screenshots:

WordPress SpreadSheet Editing Page

WordPress SpreadSheet Options Page
