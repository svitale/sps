<?php 
// screen broken into 2 sections
// - action container
// - data container
?>

<div id="datacontainer">
<div id="workbook" style="width: 600px; border: 1px solid rgb(192, 192, 192); background-color: rgb(217, 223, 205);">
<div id="colnames" style="display: none; border: 1px solid rgb(192, 192, 192); background-color: rgb(217, 223, 205);"></div>
<div id="worksheet_1" style="display: none; border: 1px solid rgb(192, 192, 192); background-color: rgb(217, 223, 205);"></div>
                <fieldset><legend>Print Blanks</legend>
               <input type="text" id="idIn" name="idIn" autocomplete="off" class="input" value="" size="2" onchange="printBlanks();">
               <input type="hidden" id="copiesIn" name="copiesIn" value="2">
               <input type="hidden" id="formatIn" name="formatIn" value="blank">
                </div>
                </fieldset>

