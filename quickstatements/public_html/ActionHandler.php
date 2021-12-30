<?PHP
require_once ( 'quickstatements.php' ) ;


/**
 * Handles actions comming into the API.
 */
class ActionHandler extends Quickstatements {
    function handle($action, $out) {
        switch($action) {
            /*
            case 'import':
                $this->import();
                break;
            */
            case 'oauth_redirect':
                $this->oauth_redirect($out);
                break;
            /*
            case 'get_token':
                $this->get_token();
                break;
            */
            case 'is_logged_in':
                $out = $this->is_logged_in($out);
                break;
            /*
            case 'get_batch_info':
                $this->get_batch_info();
                break;
            case 'get_batches_info':
                $this->get_batches_info();
                break;            
            case 'get_commands_from_batch':
                $this->get_commands_from_batch();
                break;
            case 'run_single_command':
                $this->run_single_command();
                break;
            case 'start_batch':
                $this->toggle_batch();
                break;
            case 'stop_batch':
                $this->toggle_batch();
                break;
            case 'run_batch':
                $this->run_batch();
                break;
            case 'get_batch':
                $this->get_batch();
                break;
            case 'reset_errors':
                $this->reset_errors();
                break;
            */
            default:
                throw new Exception('Unknown action ' . $action);
        }
        return $out;
    }

/*   
    function import() {
    	ini_set('memory_limit','1500M');
    
    	$format = get_request ( 'format' , 'v1' ) ;
    	$username = get_request ( 'username' , '' ) ;
    	$token = get_request ( 'token' , '' ) ;
    	$temporary = get_request ( 'temporary' , false ) ;
    	$openpage = get_request ( 'openpage' , 0 ) * 1 ;
    	$submit = get_request ( 'submit' , false ) ;
    	$data = get_request ( 'data' , '' ) ;
    	$compress = get_request ( 'compress' , 1 ) * 1 ;
    	$out = $this->importData ( $data , $format , false ) ;
    	if ( $compress ) {
    		$this->use_command_compression = true ;
    		$out['data']['commands'] = $this->compressCommands ( $out['data']['commands'] ) ;
    	}
    
    	if ( $temporary ) {
    		$dir = './tmp' ;
    		if ( !file_exists($dir) ) mkdir ( $dir ) ;
    		$filename = tempnam ( $dir , 'qs_' ) ;
    		$handle = fopen($filename, "w");
    		fwrite($handle, json_encode($out) );
    		fclose($handle);
    		$out['data'] = preg_replace ( '/^.+\//' , '' , $filename ) ;
    
    		if ( $openpage ) {
    			$url = "./#/batch/?tempfile=" . urlencode ( $out['data'] ) ;
    			print "<html><head><meta http-equiv=\"refresh\" content=\"0;URL='{$url}'\" /></head><body></body></html>" ;
    			exit(0);
    		}
    
    		fin() ;
    	}
    
    	if ( $submit ) {
    		$batchname = get_request ( 'batchname' , '' ) ;
    		$site = get_request ( 'site' , '' ) ;
    
    		if ( $site != '' ) $this->config->site = $site ;
    		$user_id = $this->getUserIDfromNameAndToken ( $username , $token ) ;
    		if ( !isset($user_id) ) {
    			unset ( $out['data'] ) ;
    			fin ( "User name and token do not match" ) ;
    		}
    
    		$batch_id = $this->addBatch ( $out['data']['commands'] , $user_id , $batchname , $site ) ;
    		unset ( $out['data'] ) ;
    		if ( $batch_id === false ) {
    			$out['status'] = $this->last_error_message ;
    		} else {
    			$out['batch_id'] = $batch_id ;
    		}
    	}
    
    }
    */
    /**
     * - Uses $oa object created in is_logged_in to get a token and authorize the user.
     * - Reads OAUth secret and key from /quickstatements/data/oauth.ini
     *   (oauth.ini is created by entrypoint.sh, but has to be filled 
     *    by running mediawiki/QuickStatements.sh inside the wiki container)
     * - redirects to the URL given in WB_PUBLIC_SCHEME_HOST_AND_PORT in docker-compose
     * 
     * @return void
     * @see magnustools/public_html/php/oauth.php
     */
    function oauth_redirect($out) {
    	$oa = $this->getOA();
    	$oa->doAuthorizationRedirect();
    	exit(0) ;    
    } 
    
    /*    
    function get_token() {
    	$force_generate = get_request ( 'force_generate' , 0 ) * 1 ;
    	$oa = $this->getOA() ;
    	$ili = $oa->isAuthOK() ; # Is Logged In
    	$out['data'] = (object) array() ;
    	if ( $ili ) {
    		$cr = $oa->getConsumerRights() ;
    		$user_name = $cr->query->userinfo->name ;
    		$out['data']->token = $this->generateToken ( $user_name , $force_generate ) ;
    	}
    	$out['data']->is_logged_in = $ili ;
    
    } 
    */
    /**
     * Gets the authorization data through Open Authorization OAuth.
     * - Creates $this->oa object from magnustools/public_html/php/oauth.php
     * - checks authorization, writes an error into $oa otherwise
     * - writes authorisation flags into $out['data']
     *
     * @return void
     */
    function is_logged_in($out) {
    	$oa = $this->getOA();
    	$ili = $oa->isAuthOK(); // $ili is true/false
    	$out['data'] = (object) array();
    	if ( $ili ) {
    		$out['data'] = $oa->getConsumerRights();
    	}
    	$out['data']->is_logged_in = $ili; 
    	return $out;
    }
    
    /*    
    function get_batch_info() {
    	$batch = get_request ( 'batch' , '0' ) * 1 ;
    
    	if ( $batch == 0 ) {
    		$out['status'] = 'Missing batch number' ;
    	} else {
    		$out['data'] = $this->getBatchStatus ( array($batch) ) ;
    	}
    
    } 
    
    function get_batches_info() {
    	$out['debug'] = $_REQUEST ;
    	
    	$user = get_request ( 'user' , '' ) ;
    	$limit = get_request ( 'limit' , '20' ) * 1 ;
    	$offset = get_request ( 'offset' , '0' ) * 1 ;
    	
    	$db = $this->getDB() ;
    	$sql = "SELECT DISTINCT batch.id AS id FROM batch" ;
    	if ( $user != '' ) $sql .= ",{$this->auth_db}.user" ;
    
    	$conditions = array() ;
    	if ( $user != '' ) $conditions[] = "user.id=batch.user AND user.name='" . $db->real_escape_string($user) . "'" ;
    	if ( count($conditions) > 0 ) $sql .= ' WHERE ' . implode ( ' AND ' , $conditions ) ;
    
    	$sql .= " ORDER BY ts_last_change DESC" ;
    	$sql .= " LIMIT $limit" ;
    	if ( $offset != 0 ) $sql .= " OFFSET $offset" ;
    
    	if(!$result = $db->query($sql)) {
    		$out['status'] = $db->error ;
    	} else {
    		$batches = array() ;
    		while ( $o = $result->fetch_object() ) $batches[] = $o->id ;
    		$out['data'] = $this->getBatchStatus ( $batches ) ;
    	}
    
    } 
    
    function get_commands_from_batch() {
    	$batch_id = get_request ( 'batch' , 0 ) * 1 ;
    	$start = get_request ( 'start' , 0 ) * 1 ;
    	$limit = get_request ( 'limit' , 0 ) * 1 ;
    	$filter = get_request ( 'filter' , '' ) ;
    
    	$db = $this->getDB() ;
    	$sql = "SELECT * FROM command WHERE batch_id={$batch_id} AND num>={$start}" ; // num BETWEEN {$start} AND {$end}
    	if ( $filter != '' ) {
    		$filter = explode ( ',' , $filter ) ;
    		foreach ( $filter AS $k => $v ) {
    			$v = $db->real_escape_string ( trim ( strtoupper ( $v ) ) ) ;
    			$filter[$k] = $v ;
    		}
    		$sql .= " AND status IN ('" . implode("','",$filter) . "')" ;
    	}
    	$sql .= " ORDER BY num LIMIT {$limit}" ;
    
    	if(!$result = $db->query($sql)) {
    		$out['status'] = $db->error ;
    	} else {
    		$batches = array() ;
    		$out['data'] = [] ;
    		while ( $o = $result->fetch_object() ) {
    			$o->json = json_decode ( $o->json ) ;
    			$out['data'][] = $o ;
    		}
    	}
    
    } 
    
    function run_single_command() {
    	$site = strtolower ( trim ( get_request ( 'site' , '' ) ) ) ;
    	if ( !$this->setSite ( $site ) ) {
    		$out['status'] = "Error while setting site '{$site}': " . $this->last_error_message ;
    	} else {
    
    		$oa = $this->getOA() ;
    		$oa->delay_after_create_s = 0 ;
    		$oa->delay_after_edit_s = 0 ;
    
    		$this->last_item = get_request ( 'last_item' , '' ) ;
    		$command = json_decode ( get_request ( 'command' , '' ) ) ;
    		if ( $command == null ) {
    			$out['status'] = 'Bad command JSON' ;
    			$out['debug'] = get_request ( 'command' , '' ) ;
    		} else {
    			$out['command'] = $this->runSingleCommand ( $command ) ;
    			$out['last_item'] = $this->last_item ;
    		}
    	}
    
    } 
    
    function toggle_batch() {
    	$batch_id = get_request ( 'batch' , 0 ) * 1 ;
    	
    	$res = false ;
    	if ( $action == 'start_batch' ) $res = $this->userChangeBatchStatus ( $batch_id , 'INIT' ) ;
    	if ( $action == 'stop_batch' )  $res = $this->userChangeBatchStatus ( $batch_id , 'STOP' ) ;
    	
    	if ( !$res ) {
    		$out['status'] = $this->last_error_message ;
    	}
    
    } 
    
    function run_batch() {
    	$user_id = $this->getCurrentUserID() ;
    	$name = trim ( get_request ( 'name' , '' ) ) ;
    	$site = strtolower ( trim ( get_request ( 'site' , '' ) ) ) ;
    	if ( $user_id === false ) {
    		$out['status'] = $this->last_error_message ;
    	} else {
    		$commands = json_decode ( get_request('commands','[]') ) ;
    		$batch_id = $this->addBatch ( $commands , $user_id , $name , $site ) ;
    		if ( $batch_id === false ) {
    			$out['status'] = $this->last_error_message ;
    		} else {
    			$out['batch_id'] = $batch_id ;
    		}
    	}
    //	$this->addBatch ( $commands ) ;
    
    }
    
    function get_batch() {
    	$id = get_request ( 'id' , '' ) ;
    	$out['id'] = $id ;
    	$out['data'] = $this->getBatch ( $id ) ;
    
    } 
    
    function reset_errors() {
    	$batch_id = get_request ( 'batch_id' , 0 ) * 1 ;
    	if ( $batch_id <= 0 ) fin("Bad batch ID #{$batch_id}") ;
    
    	$out['init'] = 0 ;
    	$db = $this->getDB() ;
    	$sql = "SELECT * FROM command WHERE batch_id={$batch_id} AND `status`='ERROR'" ;
    	if(!$result = $db->query($sql)) fin($db->error) ;
    
    	$ids = [] ;
    	while ( $o = $result->fetch_object() ) {
    		if ( stristr($o->message,'no-such-entity') ) continue ; // No such item exists, no point in re-trying
    		if ( !isset($o->json) ) continue ; // No actual command
    		$j = @json_decode ( $o->json ) ;
    		if ( !isset($j) or $j === null ) continue ; // Bad JSON
    		if ( isset($j->item) and $j->item == 'LAST' ) continue ; // Don't know which item to re-apply for
    		if ( isset($j->action) and $j->action == 'CREATE' and !isset($j->data) ) continue ; // Empty CREATE command
    		$ids[] = $o->id ;
    	}
    
    	if ( count($ids) > 0 ) {
    		$sql = "UPDATE command SET `status`='INIT' WHERE id IN (" . implode(',',$ids) . ")" ;
    		$out['sql'] = $sql ;
    		if(!$result = $db->query($sql)) fin($db->error) ;
    
    		$out['init'] = count($ids) ;
    		$res = $this->userChangeBatchStatus ( $batch_id , 'INIT' ) ;
    		if ( !$res ) {
    			$out['status'] = $this->last_error_message ;
    		}
    	}
    
    }
*/
}

?>