<?php

/* Collection specification */

/* use typedata, length */
$C_SPEC['unique_key'] 	= 'int, 10';
$C_SPEC['date'] 		= 'string, 10';
$C_SPEC['obs'] 			= 'string, 10';

// loc
$C_SPEC['locnID'] 		= 'int, 10';
$C_SPEC['plot'] 		= 'string, 10';

// coll
$C_SPEC['collCode'] 	= 'string, 10';
$C_SPEC['indivID'] 		= 'int, 10';


$C_SPEC['taxon']['id'] 			= 'int, 4';
$C_SPEC['taxon']['rank'] 		= 'string, 10';
$C_SPEC['taxon']['morphotype'] 	= 'string, 10';
$C_SPEC['taxon']['fam'] 		= 'string, 50';
$C_SPEC['taxon']['gen'] 		= 'string, 50';
$C_SPEC['taxon']['sp'] 			= 'string, 50';
$C_SPEC['taxon']['subtype'] 	= 'string, 10';
$C_SPEC['taxon']['ssp'] 		= 'string, 10';
$C_SPEC['taxon']['auth'] 		= 'string, 10';
$C_SPEC['taxon']['notes'] 		= 'string, 10';

?>

 	 	 	 	 	 	 	 	