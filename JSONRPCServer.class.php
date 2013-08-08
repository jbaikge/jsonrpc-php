<?php
/*!
 * JSON RPC Server - implements http://json-rpc.org/wiki/specification
 *
 * Thanks to http://jsonrpcphp.org/
 * 
 * @author Jake Tews <jtews@300brand.com>
 * @date Mon Jul 30 10:46:00 EDT 2012
 */
class JSONRPCServer {
	public static function handle($object) {
		// Verify valid RPC Request
		if (
			$_SERVER['REQUEST_METHOD'] != 'POST'
			|| empty($_SERVER['CONTENT_TYPE'])
			|| $_SERVER['CONTENT_TYPE'] != 'application/json'
		) {
			echo "invalid";
			return false;
		}
		$in = file_get_contents('php://input');
		file_put_contents('/tmp/'.__CLASS__, "IN: $in",  FILE_APPEND);
		$req  = JSONRPCRequest::fromJSON($in);
		$resp = new JSONRPCResponse($req->id);

		try {
			$r = new ReflectionMethod($object, $req->method);
			// Method checks
			if (!$r->isPublic()) {
				throw new BadMethodCallException("Cannot call method: " . $r->getName());
			}
			$resp->result = $r->invokeArgs($object, $req->params);
		} catch (Exception $e) {
			$resp->error = get_class($e) . ': ' . $e->getMessage() . "\n\n" . $e->getTraceAsString();
		}
		// Empty IDs are notifications - they don't want a response
		file_put_contents('/tmp/'.__CLASS__, "ID: " . var_export($resp->id, true) . PHP_EOL,  FILE_APPEND);
		file_put_contents('/tmp/'.__CLASS__, "ID: " . var_export($resp->id != null, true) . PHP_EOL,  FILE_APPEND);
		if ($resp->id !== null) {
			header('Content-Type: application/json');
			echo $resp->toJSON() . PHP_EOL;
			file_put_contents('/tmp/'.__CLASS__, "OUT: " . $resp->toJSON() . PHP_EOL,  FILE_APPEND);
		}
		return true;
	}
}
