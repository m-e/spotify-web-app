<?php

/**
 * @author MetalMichael
 * @copyright 2012
 * Info: Basic Database Class (Here's one I made earlier) 
 **/

class MYSQL {
	var $LinkID = false;
	var $QueryID = false;
	var $Record = array();
	var $Row;
	var $Errno = 0;
	var $Error = '';

	function __construct() {
	   $this->connect();
	}
    
    function halt($Msg) {
		$DBError="<pre>".date('[d/m/Y H:i:s]').' MySQL: '.strval($Msg).' SQL error: '.strval($this->Errno).' ('.strval($this->Error).")</pre>";
		if (DEBUG_MODE ) {
			echo $DBError;
			die;
		} else {
            die('Database error.');
		}
	}
    
	private function connect() {
		if(!$this->LinkID) {
			$this->LinkID = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_DATABASE, DB_PORT);
			if(!$this->LinkID) {
				$this->Errno = mysqli_connect_errno();
				$this->Error = mysqli_connect_error();
				$this->halt('Connection failed (host:' . $FS->Config->SQL['Host'] . ':' . $FS->Config->SQL['Port'] . ')');
            }
		}
	}

   	public function query($Query) {
		$this->connect();

		//-- Execute the query and record the execution time -------------------------------------------
		//$QueryStartTime=microtime(true);
		$this->QueryID = mysqli_query($this->LinkID,$Query);
		//$QueryEndTime=microtime(true);

		if(!$this->QueryID) {
			$this->Errno = mysqli_errno($this->LinkID);
			$this->Error = mysqli_error($this->LinkID);

			$this->halt('Invalid Query: ' . $Query);
		}

		$this->Row = 0;
		return $this->QueryID;
	}

	function inserted_id() {
		if($this->LinkID) return mysqli_insert_id($this->LinkID);
	}

	function next_record($Type=MYSQLI_BOTH, $DontEscape = array()) {
		if($this->LinkID) {
			$this->Record = mysqli_fetch_array($this->QueryID,$Type);
			$this->Row++;
			if (!is_array($this->Record)) {
				$this->QueryID = FALSE;
			} elseif($DontEscape !== FALSE){
				$this->Record = display_array($this->Record, $DontEscape);
			}
			return $this->Record;
		}
	}

	function close() {
		if($this->LinkID) {
			if(!mysqli_close($this->LinkID)) {
				$this->halt('Cannot close connection or connection did not open.');
			}
			$this->LinkID = FALSE;
		}
	}
    
    function to_array($Key = false, $Type = MYSQLI_BOTH, $Escape = true) {
        $Return = array();
        while ($Row = mysqli_fetch_array($this->QueryID, $Type)) {
            if ($Escape !== FALSE) {
                $Row = display_array($Row, $Escape);
            }
            if ($Key) {
                $Return[$Row[$Key]] = $Row;
            } else {
                $Return[] = $Row;
            }
        }
        mysqli_data_seek($this->QueryID, 0);
        return $Return;
    }

	function record_count() {
		if ($this->QueryID) return mysqli_num_rows($this->QueryID);
	}

	function affected_rows() {
		if($this->LinkID) return mysqli_affected_rows($this->LinkID);
	}

	function escape_str($Str) {
		$this->connect();
		return mysqli_real_escape_string($this->LinkID,$Str);
	}
}
?>