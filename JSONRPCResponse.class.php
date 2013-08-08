<?php
/*!
 * JSON RPC Response - implements http://json-rpc.org/wiki/specification
 *
 * Thanks to http://jsonrpcphp.org/
 * 
 * @author Jake Tews <jtews@300brand.com>
 * @date Mon Jul 30 11:27:41 EDT 2012
 */
class JSONRPCResponse {
	public $id;
	public $jsonrpc = "2.0";
	public $result;
	public $error;
	public function __construct($id) {
		$this->id = $id;
	}
	public static function fromJSON($json) {
		$r = new self(null);
		$d = json_decode($json);

		switch (json_last_error()) {
		case JSON_ERROR_NONE: break;
		case JSON_ERROR_DEPTH:
			throw new Exception('Maximum stack depth exceeded');
		case JSON_ERROR_STATE_MISMATCH:
			throw new Exception('Underflow or the modes mismatch');
		case JSON_ERROR_CTRL_CHAR:
			throw new Exception('Unexpected control character found');
		case JSON_ERROR_SYNTAX:
			throw new Exception('Syntax error, malformed JSON');
		case JSON_ERROR_UTF8:
			throw new Exception('Malformed UTF-8 characters, possibly incorrectly encoded');
		default:
			throw new Exception('Unknown error');
		}

		foreach (get_class_vars(__CLASS__) as $name => $value) {
			$r->$name = $d->$name;
		}
		return $r;
	}
	public function toJSON() {
		return json_encode($this, JSON_NUMERIC_CHECK);
	}
}
