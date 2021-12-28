<?PHP

error_reporting(E_ERROR|E_CORE_ERROR|E_ALL|E_COMPILE_ERROR); // 
ini_set('display_errors', 'On');

if ( !isset($_REQUEST['openpage']) ) {
	header('Content-type: application/json; charset=UTF-8');
	header("Cache-Control: no-cache, must-revalidate");
}

require_once ( 'ActionHandler.php' ) ;

function fin ( $status = '' ) {
	global $out ;
	if ( $status != '' ) $out['status'] = $status ;
	print json_encode ( $out ) ; // , JSON_PRETTY_PRINT 
	exit ( 0 ) ;
}

$out = array ( 'status' => 'OK' ) ;

if ( isset ( $_REQUEST['oauth_verifier'] ) ) {
	$oa = $qs->getOA() ; // Answer to OAuth
	header( "Location: " . $qs->getToolBase() );
	exit(0) ;
}

try {
    if (array_key_exists('action', $_REQUEST)) {
        $action = $_REQUEST['action']; // get_request ( 'action' , '' ) ;
        $handler = new ActionHandler();
        $handler->handle($action);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    exit(1);
}

fin();

?>