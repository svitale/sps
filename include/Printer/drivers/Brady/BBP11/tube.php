<?php
$text[1] = $sample_type;
$text[2] = $sample_source;
$text[3] = $id_study . '-' . $id_ancillary;
$text[4] = $date_visit;
$text[5] = 'CTTF-' . $id_subject;
$labelData= 'SIZE 25.40 mm,25.40 mm
SPEED 3
DENSITY 10
SET PEEL OFF
SET TEAR ON
SET CUTTER OFF
OFFSET 0.0 mm
SET RIBBON ON
DIRECTION 1,0
REFERENCE 0,0
GAP 2.5 mm,0 mm
CODEPAGE 850
CLS
DMATRIX 26,24,48,16,x2,48,16,"'.$id_uuid.'"
TEXT 30,70,"1",0,2,2,"'.$text[1].'"
TEXT 30,110, "1",0,2,2,"'.$text[2].'"
TEXT 30,150, "1",0,2,2,"'.$text[3].'"
TEXT 30,190, "1",0,2,2,"'.$text[4].'"
TEXT 30,230, "1",0,2,2,"'.$text[5].'"
PRINT 1,1
';

