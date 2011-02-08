<?
require_once "common.php";
ErrorHandler::setHandler(new ThrowErrorHandler());
require_once "config.php";
require_once "BaseRequest.php";
require_once "Rmi.php";

class MipanelHttpdRequest extends BaseRequest {
	private $response;
	private $srvCtl;
	private $operationType;
	private $siteName;
	private $username;

	protected $validOperationTypes = array(
			"start",
			"stop",
			"status",
			);

	function __construct($response) {
		$this->response = $response;
	}

	function getResponse() {
		return $this->response;
	}

	function decode() {
		if (!isset($_REQUEST["operationType"]) ||
				!in_array($_REQUEST["operationType"], $this->validOperationTypes))
			throw new Exception("Invalid operation type");
		$this->operationType = $_REQUEST["operationType"];
	}

	function dispatch() {
		try {
			$this->decode();

			$domain = DomainsPeer::retrieveByPk($_REQUEST["domain_id"]);
			if ($domain === null)
				throw new Exception("Invalid domain id");

			$client = new ProcOpenRmiClient("sudo ".RMI_SERVER_PATH." 2>&1");
			$this->srvCtl = $client->createInstance("SrvCtl");
			$this->username = $domain->getUsername();
			$this->siteName = $domain->getDomain();

			/* perform the requested operation */
			switch ($this->operationType) {
			case 'start':
			case 'stop':
				/* web service disabled for this domain */
				if ($domain->getSiteId() === null)
					throw new Exception("Web service is not enabled!");

				if ($err = $this->srvCtl->sendHttpdSignal($this->siteName, $this->operationType, $this->username))
					$this->response->setFailure("Could not perform operation '" . $this->operationType . "' (" . $err . ")");
				break;
			case 'status':
				/* web service disabled for this domain */
				if ($domain->getSiteId() === null) {
					$this->response->status = false;
					return;
				}
				$this->response->httpdStatus = $this->srvCtl->httpdAlive($this->siteName);
				break;
			}
		} catch (RemoteException $e) {
			$this->response->setFailure($e->getMessage() . ": " . $e->getPrevious()->getMessage());
		} catch (Exception $e) {
			$this->response->setFailure($e->getMessage());
		}
	}
}

?>
