
                  </div>
                </div>
              </TD>
            </TR>
          </TBODY>
        </TABLE>
      </div>
    </div>


<?php if (!$plain) { ?>

  <form name="ss_handler" method="POST" action="ss_handler.php?ss_id=<?PHP echo "$id&display=$plain&edit=$editpage"; ?>">

		<DIV class=spreadsheetForm>
			<table>
				<tr>
        	<td>
						<DIV class=field>
							<LABEL for=ss_name>Spreadsheet name:</LABEL><BR>
                <INPUT value="<?php echo $ss_name; ?>" id=ss_name size=35 name=ss_name>
            </DIV>
					</td>
          <td>
						<DIV class=field>
            	<LABEL for=ss_description>Spreadsheet description:</LABEL><BR>
                <INPUT id=ss_description size=35 name=ss_description value="<?php echo $ss_description; ?>">
						</DIV>
					</td>

<?php  if (current_user_can('administrator')) { ?>
          <td>
						<DIV class=field>
            	<LABEL for=ss_id>SS ID#:</LABEL><BR>
                <INPUT value=<?php echo $id; ?> size =4 name=ss_id ss_id=id onblur="NumCheck(this.value)">
						</DIV>
					</td>
<?php } ?>

        </tr>
    	</table>
    </div>
    <p>
      <input type=hidden name=ss_tablehtml>
  		<INPUT id=spreadsheet_chartdata type=hidden name=ss_chartdata autocomplete="off">
    	<input type="submit" name=ss_save value="Save Spreadsheet" onclick="sendData(document.getElementById('s1'))" id="save_button">
    	<input type="submit" name=ss_load value="Reload Spreadsheet">
    
<?php  if (current_user_can('administrator')) { ?>
    	<input type="submit" name=ss_clear value="Clear Spreadsheet">
<?php } ?>
    
    </p>

  </form>

<script language="javascript">
  function NumCheck(contents) {
      if (((contents / contents) != 1) && (contents != 0)) {alert('Please enter only a number for the spreadsheet ID#')}
  }
  </script>

<script language="javascript">
  function sendData(t)
  {
    var packed = TrimPath.spreadsheet.toPrettySource(t);
    document.ss_handler.ss_tablehtml.value = packed;
    document.ss_handler.submit();
  }
  </script>

<?php } ?>

  </body>
</html>
