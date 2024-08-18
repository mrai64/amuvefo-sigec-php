<?php 


$campi['query'] = 'SELECT * FROM richieste_elenco ' 
. ' WHERE record_cancellabile_dal = :record_cancellabile_dal ' 
. '   AND richiesta_evasa_il = :richiesta_evasa_il '  
. ' ORDER BY record_id_in_consultatori_calendario, '
. '          oggetto_richiesta,  '
. '          record_id_richiesta ';
$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
$campi['record_id_richiesta']     = $dbh->get_datetime_forever();
