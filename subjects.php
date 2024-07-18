<?php
//Create date object
$today = date_create();

//We want items cataloged within the a certain range of the current date.
//This line changes the date object to equal the date after which we want to see items.
//In other words if we want to see items added in the last 90 days we first create the date object
//and then use date_add to subtract 90 days.
$offset_date = date_add($today,date_interval_create_from_date_string('-90 days'));

//Format date for FOLIO's API
$API_date = date_format($offset_date, 'Y-m-d');

$subjects = array('AmericanStudies' => 'callNumber==%22E*%22%20OR%20callNumber==%22F*%22', 
'Anthropology' => 'callNumber==%22CB*%22%20OR%20callNumber==%22CC*%22%20OR%20callNumber==%22GN*%22%20OR%20callNumber==%22GR*%22%20OR%20callNumber==%22GT*%22', 
'ArtandArtHistory'=>'callNumber==%22BH*%22%20OR%20callNumber==%22N*%22%20OR%20callNumber==%22TR*%22%20OR%20callNumber==%22TT*%22', 
'AsianStudies' => 'callNumber==%22DS*%22%20OR%20callNumber==%22PJ*%22%20OR%20callNumber==%22PL*%22', 
'Biography' => 'callNumber==%22CT*%22', 
'Biology' => 'callNumber==%22QH*%22%20OR%20callNumber==%22QK*%22%20OR%20callNumber==%22QL*%22%20OR%20callNumber==%22QM*%22%20OR%20callNumber==%22QP*%22%20OR%20callNumber==%22QR*%22%20OR%20', 
'Business' => 'callNumber==%22HF*%22%20OR%20callNumber==%22HG*%22%20OR%20callNumber==%22HJ*%22%20OR%20callNumber==%22TS*%22%', 
'Chemistry' => 'callNumber==%22QD*%22%20OR%20callNumber==%22TP*%22', 
'Classics' => 'callNumber==%22PA*%22', 
'ComputerScience' => 'callNumber==%22TK*%22', 
'Dance' => 'callNumber==%22DV*%22', 
'Economics' => 'callNumber==%22HB*%22%20OR%20callNumber==%22HC*%22%20OR%20callNumber==%22G*%22%20OR%20HDcallNumber==%22HE*%22%20OR%20callNumber==%22HF*%22%20OR%20callNumber==%22HG*%22%20OR%20callNumber==%22HJ*%22%20OR%20callNumber==%22HX*%22', 
'Education' => 'callNumber==%22L*%22', 
'English' => 'callNumber==%22PR*%22', 
'EnvironmentalScience' => 'callNumber==%22G*%22%20OR%20callNumber==%22GA*%22%20OR%20callNumber==%22GB*%22%20OR%20callNumber==%22GC*%22%20OR%20callNumber==%22GE*%22%20OR%20callNumber==%22GF*%22%20OR%20callNumber==%22S*%22', 
'ExerciseScience' => 'callNumber==%22GV*%22%20OR%20callNumber==%22QM*%22%20OR%20callNumber==%22QP*%22', 
'Film' => 'callNumber==%22PN*%22', 
'ForeignLanguagesandLiteratures' => 'callNumber==%22PB*%22%20OR%20callNumber==%22PC*%22%20OR%20callNumber==%22PD*%22%20OR%20callNumber==%22PF*%22%20OR%20callNumber==%22PG*%22%20OR%20callNumber==%22PH*%22%20OR%20callNumber==%22PJ*%22%20OR%20callNumber==%22PK*%22%20OR%20callNumber==%22PL*%22%20OR%20callNumber==%22PM*%22%20OR%20callNumber==%22PQ*%22%20OR%20callNumber==%22PT*%22', 
'GenderStudies' => 'callNumber==%22HQ*%22', 
'GeneralWorks' => 'callNumber==%22A*%22%20OR%20callNumber==%22C*%22', 
'Geology' => 'callNumber==%22G*%22%20OR%20callNumber==%22GA*%22%20OR%20callNumber==%22GB*%22%20OR%20callNumber==%22GC*%22%20OR%20callNumber==%22GE*%22%20OR%20callNumber==%22GF*%22%20OR%20callNumber==%22QE*%22%20OR%20callNumber==%22TN*%22', 
'Government' => 'callNumber==%22HX*%22%20OR%20callNumber==%22J*%22%20OR%20callNumber==%22KZ*%22', 
'HealthandMedicine' => 'callNumber==%22R*%22', 
'History' => 'callNumber==%22D*%22%20OR%20callNumber==%22E*%22%20OR%20callNumber==%22F*%22%20OR%20callNumber==%22HN*%22%20OR%20callNumber==%22LA*%22%', 
'InternationalAffairs' => 'callNumber==%22JV*%22%20OR%20callNumber==%22JX*%22%20OR%20callNumber==%22JZ*%22%20OR%20callNumber==%22KZ*%22', 
'JudaicStudies' => 'callNumber==%22BM*%22%', 
'LibraryScience' => 'callNumber==%22Z*%22%', 
'MathematicsandStatistics' =>'callNumber==%22QA*%22%20OR%20callNumber==%22HA*%22', 
'MuseumStudies' => 'callNumber==%22AM*%22', 
'Music' => 'callNumber==%22M*%22', 
'Philosphy'=>'callNumber==%22B*%22%20OR%20callNumber==%22BC*%22%20OR%20callNumber==%22BD*%22%20OR%20callNumber==%22BH*%22%20OR%20callNumber==%22BJ*%22', 
'Physics' => 'callNumber==%22QC*%22', 
'Psychology'=>'callNumber==%22BF*%22', 
'Religion' => 'callNumber==%22BL*%22%20OR%20callNumber==%22BM*%22%20OR%20callNumber==%22BP*%22%20OR%20callNumber==%22BQ*%22%20OR%20callNumber==%22BR*%22%20OR%20callNumber==%22BS*%22%20OR%20callNumber==%22BT*%22%20OR%20callNumber==%22BV*%22%20OR%20callNumber==%22BX*%22%20OR%20callNumber==%22KBM*%22%20OR%20callNumber==%22KBP*%22%20OR%20callNumber==%22KBR*%22%20OR%20callNumber==%22KBU*%22', 
'SocialWork' => 'callNumber==%22HN*%22%20OR%20callNumber==%22HV*%22', 
'Sociology' => 'callNumber==%22HM*%22%20OR%20callNumber==%22HN*%22%20OR%20callNumber==%22HQ*%22%20OR%20callNumber==%22HS*%22%20OR%20callNumber==%22HT*%22%20OR%20callNumber==%22HV*%22', 
'Theater' => 'callNumber==%22PN*%22');

?>