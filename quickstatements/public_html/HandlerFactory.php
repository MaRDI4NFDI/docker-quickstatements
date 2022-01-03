<?PHP
require_once ( 'quickstatements.php' ) ;

class HandlerFactory {
    function createHandler($name) {
        switch ($name) {

            // AUTHORIZATION
            case 'oauth_verifier':
                return new OauthVerifier();
                break;
            case 'oauth_redirect':
                return new OauthRedirect();
                break;
            case 'is_logged_in':
                return new IsLoggedIn();
                break;
                
            // DATA IMPORT
            case 'import':
                return new Import();
                break;
            case 'run_single_command':
                return new RunSingleCommand();
                break;
            case 'run_batch':
                return new RunBatch();
                break;
                
            default:
                throw new Exception('Unknown handler ' . $name);
        }
    }
}

interface Handler {
    public function handle($out);
}

/**
 * Verifies if user is authorized
 * Rerdirects to QS_PUBLIC_SCHEME_HOST_AND_PORT, defined in config.json, set in docker-compose
 */
class OauthVerifier extends Quickstatements implements Handler {
    public function handle($out) {
    	$oa = $this->getOA() ; // Answer to OAuth
    	header( "Location: " . $this->getToolBase() );
    	exit(0) ;
    }
}

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
class OauthRedirect extends Quickstatements implements Handler {
    public function handle($out) {
    	$oa = $this->getOA();
    	$oa->doAuthorizationRedirect();
    	exit(0);
    }
}

/**
 * Gets the authorization data through Open Authorization OAuth.
 * - Creates $this->oa object from magnustools/public_html/php/oauth.php
 * - checks authorization, writes an error into $oa otherwise
 * - writes authorisation flags into $out['data']
 *
 * @return void
 */
class IsLoggedIn extends Quickstatements implements Handler {
    public function handle($out) {
    	$oa = $this->getOA();
    	$ili = $oa->isAuthOK(); // $ili is true/false
    	$out['data'] = (object) array();
    	if ( $ili ) {
    		$out['data'] = $oa->getConsumerRights();
    	}
    	$out['data']->is_logged_in = $ili;
    	return $out;
    }
} 

/**
 * Receives POST request with the data to import.
 * Stores the data in $out.
 */
class Import extends Quickstatements implements Handler {
    public function handle($out) {
        // request parameters    
    	$this->format = get_request('format' , 'v1');
    	$this->username = get_request('username' , '');
    	$this->token = get_request('token' , '');
    	$this->openpage = get_request('openpage' , 0) * 1;
 
        // request data
    	$data = get_request('data', '' );
    	$out = $this->importData($data, $this->format ,false );

        // compression
    	$compress = get_request('compress', 1) * 1;
    	if ($compress) {
    		$this->use_command_compression = true ;
    		$out['data']['commands'] = $this->compressCommands($out['data']['commands']);
    	}
    
        // kind of request
    	$temporary = get_request('temporary', false);
    	$submit = get_request('submit', false);

    	if ($temporary) {
        	$this->_handle_temporary();
    	}
    
    	if ($submit) {
        	$this->_handle_submit();
    	}
    	
    	return $out;
    }
    
    private function _handle_temporary() {
  		$dir = './tmp' ;
   		if (!file_exists($dir)) mkdir ($dir);
   		$filename = tempnam( $dir, 'qs_');
   		$handle = fopen($filename, "w");
   		fwrite($handle, json_encode($out));
   		fclose($handle);
   		$out['data'] = preg_replace('/^.+\//', '', $filename);
   
   		if ($this->openpage) {
   			$url = "./#/batch/?tempfile=" . urlencode ($out['data']) ;
   			print "<html><head><meta http-equiv=\"refresh\" content=\"0;URL='{$url}'\" /></head><body></body></html>" ;
   			exit(0);
   		}
   
   		fin() ;
    }
    
    private function _handle_submit() {
  		$batchname = get_request('batchname', '');
  		$site = get_request('site', '');
  
  		if ($site != '') $this->config->site = $site;
  		$user_id = $this->getUserIDfromNameAndToken($this->username , $this->token);
  		if (!isset($user_id)) {
  			unset($out['data']) ;
  			fin("User name and token do not match") ;
  		}
  
  		$batch_id = $this->addBatch($out['data']['commands'], $user_id, $batchname, $site);
  		unset($out['data']);
  		if ($batch_id === false) {
  			$out['status'] = $this->last_error_message ;
  		} else {
  			$out['batch_id'] = $batch_id ;
  		}
    }
}

class RunSingleCommand extends Quickstatements implements Handler {
    public function handle($out) {
    	$site = strtolower(trim(get_request('site', '')));
    	if (!$this->setSite($site)) {
    		throw new Exception("Error while setting site '{$site}': " . $this->last_error_message);
    	} 
  		$oa = $this->getOA();
  		$oa->delay_after_create_s = 0;
  		$oa->delay_after_edit_s = 0;
  		$this->last_item = get_request('last_item', '');
  		$command = json_decode(get_request('command', ''));
  		if ($command == null) {
  			$out['status'] = 'Bad command JSON';
  			$out['debug'] = get_request('command', '');
  			throw new Exception('Bad command JSON');
  			
  		}
  		// $command->data->claims[0]->mainsnak->datavalue->type = 'string';
    	// error_log(print_r($command->data->claims[0]->mainsnak->datavalue->type, true));
    	//error_log(print_r($command->data->claims[0]->mainsnak, true));
    	$out['command'] = $this->runSingleCommand($command);
    	$out['last_item'] = $this->last_item;
    	
        // Throw Exception on command error
        if (array_key_exists('command', $out)) {
            if ($out['command']->status == 'error') {
                throw new Exception($out['command']->message);
            }
        }
    	return $out;
    }
}

class RunBatch extends Quickstatements implements Handler {
    public function handle($out) {
    	$user_id = $this->getCurrentUserID();
    	$name = trim(get_request('name' , ''));
    	$site = strtolower(trim(get_request('site' , '')));
    	if ($user_id === false) {
    		$out['status'] = $this->last_error_message ;
    	} else {
    		$commands = json_decode(get_request('commands', '[]'));
    		$batch_id = $this->addBatch($commands, $user_id, $name, $site);
    		if ($batch_id === false) {
    			$out['status'] = $this->last_error_message;
    		} else {
    			$out['batch_id'] = $batch_id;
    		}
    	}
    //	$this->addBatch ( $commands ) ;
    	return $out;
    }

}
?>