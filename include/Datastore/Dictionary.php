<?php
/*
	Declares Database field names
	Any fields added here should be 
	added to their respective tables
	(batch_quality,items,locations,results,results_raw)
*/
class DbDictionary{
	var $copies = array(
		'name'=>'copies',
		'attr'=>array('rw'),
		'type'=>'tinyinteger',
		'description'=>'Number of objects'
		);
	var $date_collection = array(
		'name'=>'date_collection',
		'attr'=>array('rw'),
		'type'=>'date',
		'description'=>'Collection Date'
		);
	var $date_receipt = array(
		'name'=>'date_receipt',
		'attr'=>array('rw'),
		'type'=>'date',
		'description'=>'Receipt Date'
		);
	var $date_ship = array(
		'name'=>'date_ship',
		'attr'=>array('rw'),
		'type'=>'date',
		'description'=>'Ship Date'
		);
	var $date_visit = array(
		'name'=>'date_visit',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Visit Date'
		);
	var $destination = array(
		'name'=>'destination',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Destination'
		);
	var $error_damage = array(
		'name'=>'error_damage',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>''
		);
	var $error_delay = array(
		'name'=>'error_delay',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>''
		);
	var $error_label = array(
		'name'=>'error_label',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>''
		);
	var $error_other = array(
		'name'=>'error_other',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>''
		);
	var $error_temp = array(
		'name'=>'error_temp',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>''
		);
	var $error_volume = array(
		'name'=>'error_volume',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>''
		);
	var $family = array(
		'name'=>'family',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Sample Family'
		);
	var $id = array(
		'name'=>'id',
		'attr'=>array('ro'),
		'type'=>'integer',
		'description'=>'table id'
		);
	var $id_alq = array(
		'name'=>'id_alq',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Unique Aliquot ID'
		);
	var $id_ancillary = array(
		'name'=>'id_ancillary',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Ancillary ID'
		);
	var $id_barcode = array(
		'name'=>'id_barcode',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Alternate Barode'
		);
	var $id_batch = array(
		'name'=>'id_batch',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'batch uuid'
		);
	var $id_encounter = array(
		'name'=>'id_encounter',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'encounter id'
		);
	var $id_lot = array(
		'name'=>'id_lot',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Lot Number'
		);
	var $id_parent = array(
		'name'=>'id_parent',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'UUID of parent'
		);
	var $id_study = array(
		'name'=>'id_study',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Study Name'
		);
	var $id_subject = array(
		'name'=>'id_subject',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Subject ID'
		);
	var $id_uuid = array(
		'name'=>'id_uuid',
		'attr'=>array('ro'),
		'type'=>'string',
		'description'=>'uuid'
		);
	var $id_visit = array(
		'name'=>'id_visit',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Visit #'
		);
	var $import_source = array(
		'name'=>'import_source',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>''
		);
	var $name_created = array(
		'name'=>'name_created',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Created by Name'
		);
	var $name_last_updated = array(
		'name'=>'name_last_updated',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>''
		);
	var $name_shipper = array(
		'name'=>'name_shipper',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Shipped By'
		);
	var $notes = array(
		'name'=>'notes',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Notes'
		);
	var $num_order = array(
		'name'=>'num_order',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>'Printeger Order'
		);
	var $quality = array(
		'name'=>'quality',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>'Shipment Condition'
		);
	var $quant_cur = array(
		'name'=>'quant_cur',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Current Amount'
		);
	var $quant_init = array(
		'name'=>'quant_init',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Total Volume'
		);
	var $quant_thaws = array(
		'name'=>'quant_thaws',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>'Number of Thaws'
		);
	var $sample_source = array(
		'name'=>'sample_source',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Anatomical Source'
		);
	var $sample_type = array(
		'name'=>'sample_type',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Sample Type'
		);
	var $sequence = array(
		'name'=>'sequence',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>'Sequence'
		);
	var $shipment_type = array(
		'name'=>'shipment_type',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Shipment Type'
		);
	var $shipped = array(
		'name'=>'shipped',
		'attr'=>array('rw'),
		'type'=>'enum',
		'description'=>'Shipped'
		);
	var $specnotavail = array(
		'name'=>'specnotavail',
		'attr'=>array('rw'),
		'type'=>'enum',
		'description'=>'Spec Not Avail'
		);
	var $status = array(
		'name'=>'status',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>'Collect Status'
		);
	var $task = array(
		'name'=>'task',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>''
		);
	var $timestamp = array(
		'name'=>'timestamp',
		'attr'=>array('rw'),
		'type'=>'timestamp',
		'description'=>''
		);
	var $type = array(
		'name'=>'type',
		'attr'=>array('rw'),
		'type'=>'enum',
		'description'=>'Object Type'
		);
	var $alq_num = array(
		'name'=>'alq_num',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Aliquot Number'
		);
	var $alq_num2 = array(
		'name'=>'alq_num2',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>'aliquot no'
		);
	var $alq_pos = array(
		'name'=>'alq_pos',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'aliquot pos'
		);
	var $alq_tot = array(
		'name'=>'alq_tot',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>'Number of Aliquots Total'
		);
	var $alq_wpos = array(
		'name'=>'alq_wpos',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>'Number of Aliquots with Positions'
		);
	var $comment1 = array(
		'name'=>'comment1',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Comments'
		);
	var $comment2 = array(
		'name'=>'comment2',
		'attr'=>array('rw'),
		'type'=>'text',
		'description'=>'Notes'
		);
	var $comment3 = array(
		'name'=>'comment3',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Comments 2'
		);
	var $comment_trans = array(
		'name'=>'comment_trans',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Transaction Notes'
		);
	var $consumed = array(
		'name'=>'consumed',
		'attr'=>array('rw'),
		'type'=>'binary',
		'description'=>''
		);
	var $date_create = array(
		'name'=>'date_create',
		'attr'=>array('rw'),
		'type'=>'date',
		'description'=>'Aliquot Creation Date'
		);
	var $date_freeze = array(
		'name'=>'date_freeze',
		'attr'=>array('rw'),
		'type'=>'date',
		'description'=>'Freeze Date'
		);
	var $date_sam_create = array(
		'name'=>'date_sam_create',
		'attr'=>array('rw'),
		'type'=>'date',
		'description'=>'Sample Creation Date'
		);
	var $date_trans = array(
		'name'=>'date_trans',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Transaction Date'
		);
	var $divX = array(
		'name'=>'divX',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>''
		);
	var $divY = array(
		'name'=>'divY',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>''
		);
	var $errorflag = array(
		'name'=>'errorflag',
		'attr'=>array('rw'),
		'type'=>'enum',
		'description'=>'QC Flag'
		);
	var $hemolyzation = array(
		'name'=>'hemolyzation',
		'attr'=>array('rw'),
		'type'=>'enum',
		'description'=>''
		);
	var $id_barcode2 = array(
		'name'=>'id_barcode2',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>''
		);
	var $id_guaid = array(
		'name'=>'id_guaid',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Globally Unique Aliquot ID'
		);
	var $id_gusid = array(
		'name'=>'id_gusid',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Globally Unique Sample ID'
		);
	var $name_owner = array(
		'name'=>'name_owner',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Owner Name'
		);
	var $quant_cur_tot = array(
		'name'=>'quant_cur_tot',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Total Current Amount'
		);
	var $sample_type2 = array(
		'name'=>'sample_type2',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Sample Type 2'
		);
	var $seq = array(
		'name'=>'seq',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>'Sequence Number'
		);
	var $seq_box = array(
		'name'=>'seq_box',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>'Box Sequence Number'
		);
	var $share = array(
		'name'=>'share',
		'attr'=>array('rw'),
		'type'=>'binary',
		'description'=>''
		);
	var $stressed = array(
		'name'=>'stressed',
		'attr'=>array('rw'),
		'type'=>'binary',
		'description'=>'sample was stressed by environment'
		);
	var $test_type = array(
		'name'=>'test_type',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Test Type'
		);
	var $time_sam_create = array(
		'name'=>'time_sam_create',
		'attr'=>array('rw'),
		'type'=>'time',
		'description'=>'Sample Creation Time'
		);
	var $transaction = array(
		'name'=>'transaction',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Transaction Activity'
		);
	var $unit = array(
		'name'=>'unit',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>'size of box or postition'
		);
	var $date_moved = array(
		'name'=>'date_moved',
		'attr'=>array('rw'),
		'type'=>'date',
		'description'=>'Moved Date'
		);
	var $freezer = array(
		'name'=>'freezer',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Freezer Name'
		);
	var $id_container = array(
		'name'=>'id_container',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>'cric/items/id'
		);
	var $id_item = array(
		'name'=>'id_item',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>'cric/items/id'
		);
	var $id_site = array(
		'name'=>'id_site',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>'location'
		);
	var $subdiv1 = array(
		'name'=>'subdiv1',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Subdivision 1 Position'
		);
	var $subdiv2 = array(
		'name'=>'subdiv2',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Subdivision 2 Position'
		);
	var $subdiv3 = array(
		'name'=>'subdiv3',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Subdivision 3 Position'
		);
	var $subdiv4 = array(
		'name'=>'subdiv4',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Subdivision 4 Position'
		);
	var $subdiv5 = array(
		'name'=>'subdiv5',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Subdivision 5 Position'
		);
	var $calibrator = array(
		'name'=>'calibrator',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>'calibrator lot number'
		);
	var $cleaner = array(
		'name'=>'cleaner',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>'cleaner lot number'
		);
	var $cv = array(
		'name'=>'cv',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>''
		);
	var $datetime_assay = array(
		'name'=>'datetime_assay',
		'attr'=>array('rw'),
		'type'=>'datetime',
		'description'=>''
		);
	var $id_assay = array(
		'name'=>'id_assay',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>''
		);
	var $id_instrument = array(
		'name'=>'id_instrument',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>''
		);
	var $id_lab = array(
		'name'=>'id_lab',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>''
		);
	var $id_pull_header = array(
		'name'=>'id_pull_header',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>''
		);
	var $id_results_raw = array(
		'name'=>'id_results_raw',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>'results_raw table id'
		);
	var $id_retest = array(
		'name'=>'id_retest',
		'attr'=>array('rw'),
		'type'=>'binary',
		'description'=>'is retest'
		);
	var $id_rungroup = array(
		'name'=>'id_rungroup',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>''
		);
	var $ignore = array(
		'name'=>'ignore',
		'attr'=>array('rw'),
		'type'=>'tinyinteger',
		'description'=>''
		);
	var $qc = array(
		'name'=>'qc',
		'attr'=>array('rw'),
		'type'=>'tinyinteger',
		'description'=>''
		);
	var $raw = array(
		'name'=>'raw',
		'attr'=>array('rw'),
		'type'=>'tinyinteger',
		'description'=>''
		);
	var $reagent = array(
		'name'=>'reagent',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>'reagent lot number'
		);
	var $reviewed = array(
		'name'=>'reviewed',
		'attr'=>array('rw'),
		'type'=>'tinyinteger',
		'description'=>'Reviewed'
		);
	var $units = array(
		'name'=>'units',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Units'
		);
	var $uqc = array(
		'name'=>'uqc',
		'attr'=>array('rw'),
		'type'=>'enum',
		'description'=>'Universal QC'
		);
	var $value = array(
		'name'=>'value',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>''
		);
	var $assay_code = array(
		'name'=>'assay_code',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>''
		);
	var $barcode_source = array(
		'name'=>'barcode_source',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'unique id of source labware'
		);
	var $dilution = array(
		'name'=>'dilution',
		'attr'=>array('rw'),
		'type'=>'decimal',
		'description'=>''
		);
	var $layout_plate = array(
		'name'=>'layout_plate',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'well id - assay plate'
		);
	var $name_plate = array(
		'name'=>'name_plate',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>''
		);
	var $position_plate = array(
		'name'=>'position_plate',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'well position - assay plate'
		);
	var $position_source = array(
		'name'=>'position_source',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'row column on source labware'
		);
	var $units_calculated = array(
		'name'=>'units_calculated',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Calculated Units'
		);
	var $units_measured = array(
		'name'=>'units_measured',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'Measured Units'
		);
	var $value_1 = array(
		'name'=>'value_1',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'duplicate 1'
		);
	var $value_2 = array(
		'name'=>'value_2',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'duplicate 2'
		);
	var $value_calculated = array(
		'name'=>'value_calculated',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'integererpreted value'
		);
	var $value_measured = array(
		'name'=>'value_measured',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'raw measured value'
		);
	var $volumestatus = array(
		'name'=>'volumestatus',
		'attr'=>array('rw'),
		'type'=>'tinyinteger',
		'description'=>'Volume Status'
		);
        var $wavelength = array(
		'name'=>'wavelength',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'wavelength'
		);
        var $original = array(
		'name'=>'original',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'original value for blinded'
		);
        var $obscured = array(
		'name'=>'obscured',
		'attr'=>array('rw'),
		'type'=>'string',
		'description'=>'obscured value for blinded'
		);
        var $increment = array(
		'name'=>'increment',
		'attr'=>array('rw'),
		'type'=>'integer',
		'description'=>'date increment for blinded'
		);
}
