<?php
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
TEXT 30,70,"1",0,2,2,"'.$sample_type.'"
TEXT 30,110, "1",0,2,2,"'.$id_subject.'"
TEXT 30,150, "1",0,2,2,"'.$id_study.'"
TEXT 30,190, "1",0,2,2,"CTTF"
PRINT 1,1
';

