<script src="/sps/js/http_treemenu/TreeMenu.js" type="text/javascript"></script>

<?php
error_reporting(0);
	lib('querydisplay');

    require_once 'HTML/TreeMenu.php';

    function onPageLoad() {
        // set default action, update if new action posted
        if((!isset($_SESSION['task_action']))) {
            $_SESSION['task_action'] = 'display';
        }
        if(isset($_POST['task_action'])) {
            $_SESSION['task_action'] = $_POST['task_action'];
        }
        if(isset($_POST['task_action'])) {
            $_SESSION['task_action'] = $_POST['task_action'];
        }
             
        echo "<div>";
        displayHeader();
        echo "<br/></div><div>";
        displayAction();
        echo "</div>";

    }

        function displayHeader() {  
        $editActions = array(
            'showgeneralinfo'=>'General Information',
            'showparameters'=>'Study Parameters',
            'showprocess'=>'Processes',
            'showcrfcohort'=>'CRF and Cohort Info');
            //'createnewstudy'=>'Create New Study');
        
        //print header
        echo "<h2>Study Management</h2>";
        echo "<br/><b>Current action:<i>" . $editActions[$_SESSION['task_action']] . "</i></b><br/>";
        
        // print instructions for selected action
        switch ($_SESSION['task_action']) {
            case showprocess:
                echo "<i>Display the processes for a given study.<br/></i>";
                break;
            default:
                break;
        }
        
        // print change action form
        echo "<form method='POST' name='task_form'>";
        echo "<select name='task_action'>";
        foreach($editActions as $action=>$desc) {
            if($_SESSION['task_action'] == $action)
                echo "<option value = '$action' selected>$desc</option>";
            else
                echo "<option value = '$action'>$desc</option>";
        }
        echo "</select>";
        echo "<input type='submit' value = 'Change Action'>";
        echo "</form>";
    }

    function displayAction() {
        $action = $_SESSION['task_action'];
        if (!isset($action) or ($action == "---")) {
            return;
        }
        
        switch ($action) {
            case showgeneralinfo:
                $study = trim($_SESSION['id_study']);
                if (!isset($study) or (strlen($study) == 0) or ($study == 'any')) {
                    echo "No study selected.";
                    return;
                }
                else {
                    // Default Specimen generation method
                    echo "<b>Default sample generation method: </b>" . getDefaultCohortGenerationMethod($study) . "</br></br>";

                    $query = "select id_study, study_name, id_irb, id_ctrc, pi, autoassign_cohort, group_by,
                    interface as external_db, type as external_db_type
                    from studies left join extdb_header
                    on id_extdb_header = extdb_header.id
                    where id_study = '$study'";
                    
                    echo "General info for study $study";
                    displayQuery($query, "querydiv", array("row"), false, false, false);      
                }
                break;
            case showprocess:
                $study = trim($_SESSION['id_study']);
                if (!isset($study) or (strlen($study) == 0) or ($study == 'any')) {
                    echo "No study selected.";
                    return;
                }
                else {
                    echo generateProcessTreeHTML($study);
                    /*
                    $query = "select h.name, h.description, h.status, 
    				process_params.type,
    				params.param,
    				params.value

    				from process_header as h
    				inner join process_params
    				on h.id = process_params.process_header_id
    				inner join params
    				on params.id = process_params.params_id

    				where h.id_study = '$study'

    				order by h.name, process_params.type";
                    
                    echo "Processes for study $study";
                    displayQuery($query, "querydiv", array("row"), false, false, false);
                    */	
                }
                break;
            case showparameters:
                $study = trim($_SESSION['id_study']);
                if (!isset($study) or (strlen($study) == 0) or ($study == 'any')) {
                    echo "No study selected.";
                    return;
                }
                else {
                    echo generateParameterTreeHTML($study);
                }
                break;
            case 'showcrfcohort':
                $study = trim($_SESSION['id_study']);
                if (!isset($study) or (strlen($study) == 0)) {
                    echo "No study selected.";
                    return;
                }
                else {
                    // Default Specimen generation method
                    echo "<b>Default sample generation method: </b>" . getDefaultCohortGenerationMethod($study) . "</br></br>";

                    // CRF Families
                    echo generateCRFFamilyTreeHTML($study);

                    // Cohort list
                    $query = "select count(*) as total_cohorts
                    from cohort where id_study = '$study'";
                    
                    echo "<br/><h2>Cohort list study $study</h2>";
                    displayQuery($query, "querydiv", array("row"), false, false, false); 

                    // REDCap Cohort list
                    $query = "select count(*) as total_redcap_cohrts
                    from rc_cohort where id_study = '$study'";
                    
                    echo "<br/><h2>REDCap Cohort list study $study</h2>";
                    displayQuery($query, "querydiv2", array("row"), false, false, false); 
                }
                break;
            case 'createnewstudy':
                break;
            default:
                break;
        }
    }
//TODO: use Study() class for this
    function getStudy($id_study) {
        $sql = "select * from studies where id_study = '$id_study'";
        $result = mysql_query($sql);
        if (!$result) {
            return false;
        }
        $row = mysql_fetch_array($result);
        return $row;
    }

    function getDefaultCohortGenerationMethod($study) {
        $studydata = getStudy($study);

        // check to see if studies.autoassign_cohort is true
        // check to see if studies.external_db is set
        // check to see if there is a default family
        if ($studydata['autoassign_cohort'] == 1) {
            return "autoassign cohort using SPS generated cohort ids";
        } else if ($studydata['id_extdb_header'] != null) {
            return "import CRF information from REDCap";
        }
        else {
            return "no automatic cohort generation method defined";
        }
    }

    function generateProcessTreeHTML($study) {
        $query = "select h.name, h.description, h.status, 
        process_params.type,
        params.param,
        params.value

        from process_header as h
        inner join process_params
        on h.id = process_params.process_header_id
        inner join params
        on params.id = process_params.params_id

        where h.id_study = '$study'

        order by h.name, process_params.type";
        //return "foobar";
        $title = "Processes defined for study $study";
        $result = mysql_query($query);
        if (!$result){
            return "Unable to perform query: " . mysql_error();
        }
        else if (mysql_affected_rows() > 0) {
            $menu  = new HTML_TreeMenu();
            $row = mysql_fetch_array($result);
            $lastprocess = $row['name'];
            $lasttype = $row['type'];
            $process_menu = generateHTMLTreeNode($row['name'] . " - " . $row['description'] . " - " . $row['status']);
            $type_menu = generateHTMLTreeNode($row['type']);
            $type_menu->addItem(generateHTMLTreeNode( $row['param'] . ' = ' . $row['value']));
            while($row = mysql_fetch_array($result)) {
                if ($row['name'] != $lastprocess) {
                    $process_menu->addItem($type_menu);
                    $menu->addItem($process_menu);
                    $lastprocess = $row['name'];
                    $lasttype= $row['type'];
                    unset($process_menu);
                    unset($type_menu);
                    $process_menu = generateHTMLTreeNode($row['name'] . " - " . $row['description'] . " - " . $row['status']);
                    $type_menu = generateHTMLTreeNode($row['type']);
                }
                else if ($row['type'] != $lasttype) {
                    $lasttype= $row['type'];
                    $process_menu->addItem($type_menu);
                    unset($type_menu);
                    $type_menu = generateHTMLTreeNode($row['type']);
                }
                $type_menu->addItem(generateHTMLTreeNode( $row['param'] . ' = ' . $row['value']));
            }
            $menu->addItem($process_menu);
            $tree = new HTML_TreeMenu_DHTML($menu, array('images'=>'js/http_treemenu/images'));
        
            // print the tree
            $html = "<h2>$title</h2>";
            $html .= $tree->toHTML();
            return $html;
        }
        else {
            return "No CRF families defined for study $study";
        }
    }

    function generateCRFFamilyTreeHTML($study) {
        $query = "select family, sample_type, shipment_type, quantity, description
            from crf 
            where id_study = '$study'
            order by family, num_order";

        $title = "CRF Families defined for study $study";
        $result = mysql_query($query);
        if (!$result){
            return "Unable to perform query: " . mysql_error();
        }
        else if (mysql_affected_rows() > 0) {
            $menu  = new HTML_TreeMenu();
            $row = mysql_fetch_array($result);
            $lastfamily = $row['family'];
            $family_menu = generateHTMLTreeNode($row['family']);
            $family_menu->addItem(generateHTMLTreeNode('Sample type:' . $row['sample_type'] . ' Shipment type:' . $row['shipment_type'] .' Quant:' . $row['quantity']. ' <i>Description:' . $row['description'] . "</i>"));
            //$family_menu->addItem(generateHTMLTreeNode('beepboop'));
            while($row = mysql_fetch_array($result)) {
                if ($row['family'] != $lastfamily) {
                    $menu->addItem($family_menu);
                    $lastfamily = $row['family'];
                    unset($family_menu);
                    $family_menu = generateHTMLTreeNode($row['family']);
                }
                $family_menu->addItem(generateHTMLTreeNode('Sample type:' . $row['sample_type'] . ' Shipment type:' . $row['shipment_type'] .' Quant:' . $row['quantity']. ' <i>Description:' . $row['description'] . "</i>"));
                //$family_menu->addItem(generateHTMLTreeNode('beepboop'));
            }
            $menu->addItem($family_menu);
            $tree = new HTML_TreeMenu_DHTML($menu, array('images'=>'js/http_treemenu/images'));
        
            // print the tree
            $html = "<h2>$title</h2>";
            $html .= $tree->toHTML();
            return $html;
        }
        else {
            return "No CRF families defined for study $study";
        }
    }

    function generateParameterTreeHTML($study) {
        $query = "select params.param, params.id, params.value, params.description, filters.id_study
        from 
        filters inner join params
        on filters.id_param = params.id
        where filters.id_study = '$study' or id_study = 'ALL'
        order by param";

        $title = "Processes for study $study";
        $result = mysql_query($query);
        if (!$result){
            return "Unable to perform query: " . mysql_error();
        }
        else if (mysql_affected_rows() > 0) {
            $menu  = new HTML_TreeMenu();
            $row = mysql_fetch_array($result);
            $lastparam = $row['param'];
            $param_menu = generateHTMLTreeNode($row['param']);
            $param_menu->addItem(generateHTMLTreeNode($row['value'] . ' - <i>id = ' . $row['id'] . '</i>'));

            while($row = mysql_fetch_array($result)) {
                if ($row['param'] != $lastparam) {
                    $menu->addItem($param_menu);
                    $lastparam = $row['param'];
                    unset($param_menu);
                    $param_menu = generateHTMLTreeNode($row['param']);
                }
                $param_menu->addItem(generateHTMLTreeNode($row['value'] . ' - <i>id =' . $row['id'] . '</i>'));
            }
            $menu->addItem($param_menu);
            $tree = new HTML_TreeMenu_DHTML($menu, array('images'=>'js/http_treemenu/images'));
        
            // print the tree
            $html = "<h2>$title</h2>";
            $html .= $tree->toHTML();
            return $html;
        }
        else {
            return "No parameters found for study $study";
        }
    }


    /** generate an HTML_Tree node
     * 
    */
    function generateHTMLTreeNode($nameString, $countString = '', $in_id = '', $uuid = '', $idLinkFlag = false, $uuidLinkFlag = false) {
        if (strlen($countString) > 0) 
            $text = $nameString . $countString;
        else
            $text = $nameString;
        
        //$text .= "  id: $in_id  uuid: $uuid";
        
        if ($idLinkFlag)
            return new HTML_TreeNode(array('text'=>$text, 'link'=>'javascript:inventoryDetailId(\\\''. $in_id .'\\\');'));
        else if ($uuidLinkFlag)
            return new HTML_TreeNode(array('text'=>$text, 'link'=>'javascript:inventoryDetail(\\\''. $uuid .'\\\');'));
        else    
            return new HTML_TreeNode(array('text'=>$text)); 
    }

    /**
    *Function that is called when a uuid is scanned.
    *
    * @param string $table  - the table where the item is found. Either 'items' or 'batch_quality'
    * @param string $id - the id of the scanned object
    * 
    *If a box is scanned:
    *  Do the selected action on all tubes in the box (user prompt)
    *If a tube is scanned:
    *  Do the action on the given tube
    */
   //function operateScannedObject($table, $id) {
   //}

    /**
 	* called from npc.php
 	*/
    function data_array($postType) {
	$id = 1353792;
    $returnArray = array();
	
	$changeQuery = "select log.timestamp, log.event_type, log.field, log.old_value, log.new_value
					from log_items as log
					where id_item = $id
					order by timestamp desc";
	$result = mysql_query($changeQuery);
	
	while ($row = mysql_fetch_assoc($result)) {
		array_push($returnArray, $row);
	}
	return $returnArray;	
}
?>
