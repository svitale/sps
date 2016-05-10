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
TEXT 270,50,"1",180,2,2,"'.$sample_type.'"
TEXT 290,60, "1",90,2,2,"'.$id_study.'"
TEXT 50,250, "1",0,2,2,"'.$uuidShort.'"
TEXT 30,100, "1",90,2,2,"Rack"
DMATRIX 70,70,48,48,x3,48,48,"'.$id_uuid.'"
PRINT 1,1
';

