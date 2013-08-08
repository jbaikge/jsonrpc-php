<?php
/*!
 * JSON RPC Request - implements http://json-rpc.org/wiki/specification
 *
 * Thanks to http://jsonrpcphp.org/
 * 
 * @author Jake Tews <jtews@300brand.com>
 * @date Mon Jul 30 11:27:41 EDT 2012
 */
class JSONRPCRequest {
	public $id;
	public $jsonrpc = "2.0";
	public $method;
	public $params;
	public function __construct($id) {
		$this->id = $id;
	}
	public static function fromJSON($json) {
		$r = new self(null);
		$d = json_decode($json);
		foreach (get_class_vars(__CLASS__) as $name => $value) {
			$r->$name = $d->$name;
		}
		return $r;
	}
	public function toJSON() {
		return json_encode($this, JSON_NUMERIC_CHECK);
	}
}
