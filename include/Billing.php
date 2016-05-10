<?php
lib('dbi');
class Billing{
    public function refreshBillingTable() {
		if (bakeSQLRecipe('billing')) {
			return true;
		} else {
			return false;
		}
	}

/* this is the array used to generate a boxcount by freezer */
    public function summaryArray() {
        global $dbrw;
        $returnArray = array();
	$sql = "select id_study Study,freezer Freezer,sum(if(boxsize=3,quant,0)) `6x6-3 in`,sum(if(boxsize=5,quant,0)) `6x6-5 in`,sum(if(boxsize=2,quant,0)) `9x9-2 in` from (select count(*) quant,id_study,freezer,boxsize from billing_deposit where id_study is not null group by id_study,freezer,boxsize) summary group by freezer,id_study";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            print 'Could not run query: ' . mysqli_error();
            exit;
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $returnArray[] = $row;
        }
        return $returnArray;
    }

/* this is the array used to generate a short list of boxes billed for a atudy */
    public function assetsArray() {
	global $dbrw;
	$returnArray = array();
	$sql = "select id_study,pennkey pi,owner,count(*) quantity,boxsize from billing_deposit group by id_study,pennkey,owner,boxsize";
	$result = mysqli_query($dbrw,$sql);
        if (!$result) {
            print 'Could not run query: ' . mysqli_error();
            exit;
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $returnArray[] = $row;
        }
        return $returnArray;
    }

/* this is the array used to generate a short list of boxes/freezers billed for a atudy */
    public function freezerArray() {
	global $dbrw;
	$returnArray = array();
	$sql = "select id_study,pennkey pi,owner,freezer,count(*) quantity,boxsize from billing_deposit group by id_study,pennkey,owner,freezer,boxsize";
	$result = mysqli_query($dbrw,$sql);
        if (!$result) {
            print 'Could not run query: ' . mysqli_error();
            exit;
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $returnArray[] = $row;
        }
        return $returnArray;
    }

/* this is the array used to generate a detailed deposit list of boxes billed for a atudy */
    public function depositArray() {
	global $dbrw;
	$returnArray = array();
	$sql = 'select id_item id,id_study,id_uuid,id_site,freezer,subdiv1,subdiv2,subdiv3,destination,date_deposit,date_moved,owner,pennkey,boxsize from billing_deposit';
	$result = mysqli_query($dbrw,$sql);
        if (!$result) {
            print 'Could not run query: ' . mysqli_error();
            exit;
        }
        while ($row = mysqli_fetch_assoc($result)) {
	    if ($row['date_moved']==null)  {
		$row['date_moved'] = 'NULL';
            }
            $returnArray[] = $row;
        }
        return $returnArray;
    }


}
