<?php
lib('menu');
/*Create a new container or sample from the given uuid*/

function newItem($id) {
	$types_array = array(
		'box',
		'rack',
		'shelf',
		'freezer'
	);
    print "Create a new container:<br/>";
    //print "Choose sample parameters on the left and rescan to create a sample.<br/>";
?>
<table id="tl_navTable" class="navTable" cellspacing="0" cellpadding="0" style="width: 90px; table-layout: fixed;">
    <?php
        global $color;
        if (isset($_SESSION['params'])) {
            $array_params = retArrayParamsById($_SESSION['params'], array(
                'destination',
                'sample_type'
            ));
print_r($array_params);
        } else {
            $array_params =array();
        }
        if (isset($array_params['destination']) && count($array_params['destination']) > 0) {
            echo "<div>Destination</div>";
            foreach($array_params['destination'] as $destination) {
                if(isset($color{$destination})) {
                  $dest_color = $color{$destination};
                } else {
                  $dest_color = '#888888';
                }
                if (!isset($_SESSION['destination'])) {
                    $_SESSION['destination'] = $destination;
                }
                if ($_SESSION['destination'] == $destination) {
                    $un = '';
                    $bgcolor = '#FFFFFF';
                    $fgcolor = '#' . $dest_color;
                } else {
                    $un = 'un';
                    $bgcolor = '#' . $dest_color;
                    $fgcolor = '#000000';
                }
                echo "<div class=\"buttonlib_" . $un . "selected\" style=\"background-color:" . $bgcolor . "; color: " . $fgcolor . "\" title=\"\"onclick=\"newDest('" . $id . "','" . $destination . "')\">" . $destination . "</div>";
            }
        } else {
            print selectParam('destination');
        }
        if (isset($array_params['sample_type']) && count($array_params['sample_type']) > 0) {
            echo "<div>Sample Type</div>";
            foreach($array_params['sample_type'] as $sample_type) {
                if (!isset($_SESSION['sample_type'])) {
                    $_SESSION['sample_type'] = $sample_type;
                }
                if ($_SESSION['sample_type'] == $sample_type) {
                    $un = '';
                    $bgcolor = '#FFFFFF';
                    $fgcolor = '#000000';
                } else {
                    $un = 'un';
                    $bgcolor = '#8F8F8F';
                    $fgcolor = '#FFFFFF';
                }
                echo "<div class=\"buttonlib_" . $un . "selected\" style=\"background-color:" . $bgcolor . "; color: " . $fgcolor . "\" title=\"\"onclick=\"newSampleType('" . $id . "','" . $sample_type . "')\">" . $sample_type . "</div>";
            }
        } else {
            print selectParam('sample_type');
        }
    ?>
    
    <table id="tl_navTable" class="navTable" cellspacing="0" cellpadding="0" style="width: 245px; table-layout: fixed;">
        <tbody>
        <tr>
        <td class="tablib_emptyTab" style="text-align: left; display: none;">
        </td>
        <td>
        <div style="overflow: hidden; width: 100%;">
        <table id="tl_14_header" class="tablib_table" cellspacing="0" cellpadding="0">
            <tbody>
            <tr>
            <td class="tablib_emptyTab"> </td>
            <td class="tablib_spacerTab"> </td>
            
            <?php
                $i = 0;
                while ($i < count($types_array)) {
                    $type = $types_array[$i];
                    if (!isset($_SESSION['type'])) {
                        $_SESSION['type'] = $type;
                    }
                    if ($_SESSION['type'] == $type) {
                        $un = '';
                    } else {
                        $un = 'un';
                    }
                    echo "<td class=\"tablib_" . $un . "selected\" title=\"\"onclick=\"newType('" . $id . "','" . $type . "')\">" . $type . "</td>";
                    echo "<td class=\"tablib_spacerTab\"> </td>";
                    $i++;
                }
            ?>
            <td class="tablib_emptyTab"> </td>
            </tr>
            </tbody>
        </table>
    </table>
    </div>
    </td>
    <td class="tablib_emptyTab" style="text-align: right; display: none;">
    </td>
    </tr>
    </tbody>
</table>
<?php
        if (isset($_SESSION['type']) && (file_exists($GLOBALS['root_dir'] . '/include/items/'. $_SESSION['type'] . '.php'))) {
            include_once($GLOBALS['root_dir'] . '/include/items/'. $_SESSION['type'] . '.php');
        }
}
?>
