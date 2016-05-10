<?php
global $title;
$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
$cacheSettings = array( ' memoryCacheSize '  => '32MB'
                      );
PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
if (file_exists($template_dir . '/'.$XLTemplate.'.xlt')) {
$objPHPExcel = PHPExcel_IOFactory::load($template_dir . '/'.$XLTemplate.'.xlt');
} else {
$objPHPExcel = new PHPExcel();
$objPHPExcel->getProperties()
->setCreator("SPS Generate")
->setLastModifiedBy("SPS")
->setTitle("'.$title.'")
->setSubject($_SESSION['task'])
->setDescription("");
}
$columnTitles = array_keys($returnArray[0]);
$objPHPExcel->setActiveSheetIndex(0);
$objWorksheet = $objPHPExcel->getActiveSheet();
$x_offset = 0;
$y_offset = 1;
	$row = $y_offset;
	$column = $x_offset;
	foreach($columnTitles as $title) {
		$objWorksheet->setCellValueByColumnAndRow($column,$row,$title);
		$column++;
	}
	$row++;
	foreach($returnArray as $subArray) {
		$column = $x_offset;
		foreach($subArray as $dooo) {
			$objWorksheet->setCellValueByColumnAndRow($column, $row,$dooo);
			$column++;
		}
	$row++;
	}
$objPHPExcel->setActiveSheetIndex(0);
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.$title.'.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output'); 

?>
