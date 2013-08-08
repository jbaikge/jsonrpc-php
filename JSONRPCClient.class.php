<?php
/*!
 * JSON RPC Client - implements http://json-rpc.org/wiki/specification
 *
 * Thanks to http://jsonrpcphp.org/
 * 
 * @author Jake Tews <jtews@300brand.com>
 * @date Mon Jul 30 11:10:29 EDT 2012
 */
class JSONRPCClient {
	public $notify = false; ///< Notification? Public for $client->notify = true

	private $url; ///< JSON RPC server URL

	public function __construct($url) {
		$this->url = $url;
	}
	public function __call($method, $args) {
		if ($this->notify) {
			$id = null;
		} else {
			$id = intval(microtime(true) * 1e4);
		}
		// RPC Request
		$req = new JSONRPCRequest($id);
		$req->method = $method;
		$req->params = $args;
		// cURL gives better stream control than internal stream_context + fopen
		// wrappers
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_HTTPHEADER     => array(
				'Content-Type: application/json',
			),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS     => $req->toJSON(),
			CURLOPT_URL            => $this->url,
		));
		$json = curl_exec($ch);
		curl_close($ch);
		// If notifying, bail now
		if ($this->notify) {
			return null;
		}
		// Handle server response
		try {
			$resp = JSONRPCResponse::fromJSON($json);
			if ($resp->id != $id) {
				throw new Exception("Invalid ID returned. Expected {$id}, got {$resp->id}.");
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage() . NEWLINE . "JSON: " . $json);
		}
		if ($resp->error != null) {
			throw new Exception($resp->error);
		}
		return $resp->result;
	}
}
