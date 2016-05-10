<?php
if ($_SESSION['id_study'] == 'eQTL') {
        ?>
<br>
        <div>
        <div class="boxFloat" id="newItemBox9" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBox9')" onmouseout="resetCell('newItemBox9')" onclick="setItemParams('<?php echo $id?>','box','-9','9')">
        <span style="position: relative; left: 1px;">9x9</span></div></div>
        <div>
        <div class="boxFloat" id="newItemBox96" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBox96')" onmouseout="resetCell('newItemBox9')" onclick="setItemParams('<?php echo $id?>','box','-8','12')">
        <span style="position: relative; left: 1px;">micro</span></div></div>
<?php
} else if ($_SESSION['id_study'] == 'CTTF') {
        ?>
<br>
        <div>
                <div class="boxFloat" id="newItemBox9" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBox9')" onmouseout="resetCell('newItemBox9')" onclick="setItemParams('<?php echo $id?>','box','9','9')">
                        <span style="position: relative; left: 1px;">9x9</span>
                </div>
        </div>
        <div>
                <div class="boxFloat" id="newItemBox10" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBox10')" onmouseout="resetCell('newItemBox10')" onclick="setItemParams('<?php echo $id?>','box','10','10')">
                        <span style="position: relative; left: 1px;">10x10</span>
                </div>
        </div>
        <div>
                <div class="boxFloat" id="newItemBox4" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBox4')" onmouseout="resetCell('newItemBox4')" onclick="setItemParams('<?php echo $id?>','box','4','4')">
                        <span style="position: relative; left: 1px;">4x4</span>
                </div>
        </div>
<?php
} else if ($_SESSION['id_study'] == 'BioImage') {
        ?>
<br>
        <div>
        <div class="boxFloat" id="newItemBox10" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBox10')" onmouseout="resetCell('newItemBox10')" onclick="setItemParams('<?php echo $id?>','box','10','10')">
        <span style="position: relative; left: 1px;">10x10</span></div></div>
        <div class="boxFloat" id="newItemBox7" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBox7')" onmouseout="resetCell('newItemBox7')" onclick="setItemParams('<?php echo $id?>','box','7','7')">
        <span style="position: relative; left: 1px;">7x7</span></div></div>
        <div class="boxFloat" id="newItemBox7" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBox2')" onmouseout="resetCell('newItemBox2')" onclick="setItemParams('<?php echo $id?>','box','3','8')">
        <span style="position: relative; left: 1px;">3x8</span></div></div>
        <div class="boxFloat" id="newItemBox96" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBox96')" onmouseout="resetCell('newItemBox9')" onclick="setItemParams('<?php echo $id?>','box','-8','12')">
        <span style="position: relative; left: 1px;">micro</span></div></div>
<?php
} else if ($_SESSION['id_study'] == 'Surmount') {
        ?>
<br>
        <div>
        <div class="boxFloat" id="newItemBox9" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBox9')" onmouseout="resetCell('newItemBox9')" onclick="setItemParams('<?php echo $id?>','box','-9','9')">
        <span style="position: relative; left: 1px;">9x9</span></div></div>
        <div class="boxFloat" id="newItemBox10" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBox10')" onmouseout="resetCell('newItemBox10')" onclick="setItemParams('<?php echo $id?>','box','-10','10')">
        <span style="position: relative; left: 1px;">10x10</span></div></div>
        <div class="boxFloat" id="newItemBox7" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBox7')" onmouseout="resetCell('newItemBox7')" onclick="setItemParams('<?php echo $id?>','box','-7','7')">
        <span style="position: relative; left: 1px;">7x7</span></div></div>
        <div class="boxFloat" id="newItemBox7" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBox2')" onmouseout="resetCell('newItemBox2')" onclick="setItemParams('<?php echo $id?>','box','-3','8')">
        <span style="position: relative; left: 1px;">3x8</span></div></div>
        <div class="boxFloat" id="newItemBox96" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBox96')" onmouseout="resetCell('newItemBox9')" onclick="setItemParams('<?php echo $id?>','box','-8','12')">
        <span style="position: relative; left: 1px;">micro</span></div></div>

<?php 
} else {
        ?>
        <div>
        <div class="boxFloat" id="newItemBox9" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBox9')" onmouseout="resetCell('newItemBox9')" onclick="setItemParams('<?php echo $id?>','box','9','9')">
        <span style="position: relative; left: 1px;">9x9</span></div></div>
        <div class="boxFloat" id="newItemBox10" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBox10')" onmouseout="resetCell('newItemBox10')" onclick="setItemParams('<?php echo $id?>','box','10','10')">
        <span style="position: relative; left: 1px;">10x10</span></div></div>
        <div class="boxFloat" id="newItemBox6" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBox6')" onmouseout="resetCell('newItemBox6')" onclick="setItemParams('<?php echo $id?>','box','6','6')">
        <span style="position: relative; left: 1px;">6x6</span></div></div>
        <div class="boxFloat" id="newItemBoxMicor" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBoxMicor')" onmouseout="resetCell('newItemBoxMicor')" onclick="setItemParams('<?php echo $id?>','box','-4','10')">
        <span style="position: relative; left: 1px;">mecour</span></div></div>
        <div class="boxFloat" id="newItemBoxSP" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBoxSP')" onmouseout="resetCell('newItemBoxSP')" onclick="setItemParams('<?php echo $id?>','box','-8','-5')">
        <span style="position: relative; left: 1px;">8x5</span></div></div>
        <div class="boxFloat" id="newItemBoxSP" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBoxSP')" onmouseout="resetCell('newItemBoxSP')" onclick="setItemParams('<?php echo $id?>','box','-8','-6')">
        <span style="position: relative; left: 1px;">8x6</span></div></div>
        <div class="boxFloat" id="newItemBoxSPsing" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBoxSPsing')" onmouseout="resetCell('newItemBoxSPsing')" onclick="setItemParams('<?php echo $id?>','box','-8','-10')">
        <span style="position: relative; left: 1px;">8x10</span></div></div>
        <div class="boxFloat" id="newItemBoxSPsing" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBoxSPsing')" onmouseout="resetCell('newItemBoxSPsing')" onclick="setItemParams('<?php echo $id?>','box','-8','-11')">
        <span style="position: relative; left: 1px;">8x11</span></div></div>
        <div class="boxFloat" id="newItemBoxSPsing" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBoxSPsing')" onmouseout="resetCell('newItemBoxSPsing')" onclick="setItemParams('<?php echo $id?>','box','-8','12')">
        <span style="position: relative; left: 1px;">8x12</span></div></div>
        <div class="boxFloat" id="newItemBoxSP2" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBoxSP2')" onmouseout="resetCell('newItemBoxSP2')" onclick="setItemParams('<?php echo $id?>','box','-4','-6')">
        <span style="position: relative; left: 1px;">4x6</span></div></div>
        <div class="boxFloat" id="newItemBox12b4" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBoxSP2')" onmouseout="resetCell('newItemBoxSP2')" onclick="setItemParams('<?php echo $id?>','box','12','4')">
        <span style="position: relative; left: 1px;">12x4</span></div></div>
        <div class="boxFloat" id="newItemBox12b4" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222, 240, 222); cursor: pointer;" onmouseover="highlightCell('newItemBoxSP2')" onmouseout="resetCell('newItemBoxSP4')" onclick="setItemParams('<?php echo $id?>','box','-10','-3')">
        <span style="position: relative; left: 1px;">10x3</span></div></div>
<?php
}
