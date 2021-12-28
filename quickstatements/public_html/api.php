<?PHP
/**
 * Quickstatements API
 * Forked from https://phabricator.wikimedia.org/source/tool-quickstatements/browse/master/
 * see https://github.com/MaRDI4NFDI/docker-quickstatements/wiki
 */
error_reporting(E_ERROR|E_CORE_ERROR|E_ALL|E_COMPILE_ERROR); // 
ini_set('display_errors', 'On');

if ( !isset($_REQUEST['openpage']) ) {
	header('Content-type: application/json; charset=UTF-8');
	header("Cache-Control: no-cache, must-revalidate");
}

require_once (  __DIR__ . '/ActionHandler.php' );

/**
 * Return API response
 */
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
    // Handle request parameter 'action'
    $action = $_REQUEST['action']; // get_request ( 'action' , '' ) ;
    $actionHandler = new ActionHandler();
    $actionHandler->handle($action);
    fin();

} catch (Exception $e) {
    error_log( $e->getMessage() );
    exit(1);
}

?>