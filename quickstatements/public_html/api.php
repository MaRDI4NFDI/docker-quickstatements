<?PHP
/**
 * Quickstatements API
 * Forked from https://phabricator.wikimedia.org/source/tool-quickstatements/browse/master/
 * see https://github.com/MaRDI4NFDI/docker-quickstatements/wiki
 */

// return JSON by default
if ( !isset($_REQUEST['openpage']) ) {
	header('Content-type: application/json; charset=UTF-8');
	header("Cache-Control: no-cache, must-revalidate");
}
ini_set('memory_limit','1500M');

require_once ( 'HandlerFactory.php' ) ;

/**
 * Return API response
 */
function fin ( $status = '' ) {
	global $out ;
	if ( $status != '' ) $out['status'] = $status ;
	print json_encode ( $out ) ; // , JSON_PRETTY_PRINT 
	exit ( 0 ) ;
}

/** output array is passed between action calls */
$out = array ( 'status' => 'OK' ) ;

/** Implements template method pattern to handle api requests */
$factory = new HandlerFactory();
try {
    if (isset($_REQUEST['oauth_verifier'])) {
        $handler = $factory->createHandler('oauth_verifier');    	
        $out = $handler->handle($out);
    } elseif (array_key_exists('action', $_REQUEST)) {
        $action = $_REQUEST['action'];
        $handler = $factory->createHandler($action);
        $out = $handler->handle($out);
    }
    // if no action specified, do nothing, do not call $handler->handle($out)
    
} catch (Exception $e) {
    error_log($e->getMessage());
    exit(1);
}
fin();
?>
