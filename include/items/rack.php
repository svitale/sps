<?php
if ($_SESSION['id_study'] == 'eQTL') {
        ?>
        <div>
        <div class="rackFloat" id="newItemRack1" style="border: 1px solid rgb(0, 0, 0); background-color: #e5e3c0; cursor: pointer;" onmouseover="highlightCell('newItemRack1')" onmouseout="resetCell('newItemRack1')" onclick="setItemParams('<php?echo $id?>','rack','4','6')">
        <span style="position: relative; left: 1px;">4x6 <i>24</i></span></div></div>
        <div class="rackFloat" id="newItemRack2" style="border: 1px solid rgb(0, 0, 0); background-color: #e5e3c0; cursor: pointer;" onmouseover="highlightCell('newItemRack2')" onmouseout="resetCell('newItemRack2')" onclick="setItemParams('<?php echo $id?>','rack','4','5')">
        <span style="position: relative; left: 1px;">4x5 <i>20</i></span></div></div>
        <div class="rackFloat" id="newItemRack3" style="border: 1px solid rgb(0, 0, 0); background-color: #e5e3c0; cursor: pointer;" onmouseover="highlightCell('newItemRack3')" onmouseout="resetCell('newItemRack3')" onclick="setItemParams('<?php echo $id?>','rack','-4','5')">
        <span style="position: relative; left: 1px;">slider</i></span></div></div>
<?php
} else if ($_SESSION['id_study'] == 'PROPEL') {
?>
        <div>
        <div class="rackFloat" id="newItemRack10" style="border: 1px solid rgb(0, 0, 0); background-color: #e5e3c0; cursor: pointer;" onmouseover="highlightCell('newItemRack10')" onmouseout="resetCell('newItemRack10')" onclick="setItemParams('<?php echo $id?>','rack','4','4')">
        <span style="position: relative; left: 1px;">4x4 <i>16</i></span></div></div>
        <div class="rackFloat" id="newItemRack11" style="border: 1px solid rgb(0, 0, 0); background-color: #e5e3c0; cursor: pointer;" onmouseover="highlightCell('newItemRack11')" onmouseout="resetCell('newItemRack11')" onclick="setItemParams('<?php echo $id?>','rack','3','4')">
        <span style="position: relative; left: 1px;">3x4 <i>12</i></span></div></div>
        <div class="rackFloat" id="newItemRack12" style="border: 1px solid rgb(0, 0, 0); background-color: #e5e3c0; cursor: pointer;" onmouseover="highlightCell('newItemRack12')" onmouseout="resetCell('newItemRack12')" onclick="setItemParams('<?php echo $id?>','rack','2','5')">
        <span style="position: relative; left: 1px;">2x5 <i>10</i></span></div></div>
        <div class="rackFloat" id="newItemRack13" style="border: 1px solid rgb(0, 0, 0); background-color: #e5e3c0; cursor: pointer;" onmouseover="highlightCell('newItemRack13')" onmouseout="resetCell('newItemRack13')" onclick="setItemParams('<?php echo $id?>','rack','4','5')">
        <span style="position: relative; left: 1px;">5x4 <i>20</i></span></div></div>
<?php
} else {
?>
        <div>
        <div class="rackFloat" id="newItemShipper" style="border: 1px solid rgb(0, 0, 0); background-color: #e5e3c0; cursor: pointer;" onmouseover="highlightCell('newItemShipper')" onmouseout="resetCell('newItemShipper')" onclick="setItemParams('<?php echo $id?>','rack','5','6')">
        <span style="position: relative; left: 1px;">5x6 <i>shipper</i></span></div></div>
        <div class="rackFloat" id="newItemRack9" style="border: 1px solid rgb(0, 0, 0); background-color: #e5e3c0; cursor: pointer;" onmouseover="highlightCell('newItemRack9')" onmouseout="resetCell('newItemRack9')" onclick="setItemParams('<?php echo $id?>','rack','4','6')">
        <span style="position: relative; left: 1px;">4x6 <i>24</i></span></div></div>
        <div class="rackFloat" id="newItemRack9" style="border: 1px solid rgb(0, 0, 0); background-color: #e5e3c0; cursor: pointer;" onmouseover="highlightCell('newItemRack9')" onmouseout="resetCell('newItemRack9')" onclick="setItemParams('<?php echo $id?>','rack','4','5')">
        <span style="position: relative; left: 1px;">4x5 <i>20</i></span></div></div>
        <div class="rackFloat" id="newItemRack9" style="border: 1px solid rgb(0, 0, 0); background-color: #e5e3c0; cursor: pointer;" onmouseover="highlightCell('newItemRack54')" onmouseout="resetCell('newItemRack9')" onclick="setItemParams('<?php echo $id?>','rack','5','4')">
        <span style="position: relative; left: 1px;">5x4 <i>20</i></span></div></div>
        <div class="rackFloat" id="newItemRack6" style="border: 1px solid rgb(0, 0, 0); background-color: #e5e3c0; cursor: pointer;" onmouseover="highlightCell('newItemRack6')" onmouseout="resetCell('newItemRack6')" onclick="setItemParams('<?php echo $id?>','rack','4','4')">
        <span style="position: relative; left: 1px;">4x4 <i>16</i></span></div></div>
        <div class="rackFloat" id="newItemRack12" style="border: 1px solid rgb(0, 0, 0); background-color: #e5e3c0; cursor: pointer;" onmouseover="highlightCell('newItemRack6')" onmouseout="resetCell('newItemRack6')" onclick="setItemParams('<?php echo $id?>','rack','4','3')">
        <span style="position: relative; left: 1px;">4x3 <i>12</i></span></div></div>
        <div class="rackFloat" id="newItemRack12" style="border: 1px solid rgb(0, 0, 0); background-color: #e5e3c0; cursor: pointer;" onmouseover="highlightCell('newItemRack6')" onmouseout="resetCell('newItemRack6')" onclick="setItemParams('<?php echo $id?>','rack','3','2')">
        <span style="position: relative; left: 1px;">3x2 <i>6</i></span></div></div>
        <div class="rackFloat" id="newItemRack12" style="border: 1px solid rgb(0, 0, 0); background-color: #e5e3c0; cursor: pointer;" onmouseover="highlightCell('newItemRack6')" onmouseout="resetCell('newItemRack6')" onclick="setItemParams('<?php echo $id?>','rack','1','10')">
        <span style="position: relative; left: 1px;">1x10 <i>10</i></span></div></div>
        <div class="rackFloat" id="newItemRack13" style="border: 1px solid rgb(0, 0, 0); background-color: #e5e3c0; cursor: pointer;" onmouseover="highlightCell('newItemRack13')" onmouseout="resetCell('newItemRack13')" onclick="setItemParams('<?php echo $id?>','rack','1','13')">
        <span style="position: relative; left: 1px;">1x13 <i>13</i></span></div></div>
<?php
}
