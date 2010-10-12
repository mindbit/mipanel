<?
require_once "common.php";
require_once "config/config.php";
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
		$this->decode();

		$domain = DomainsPeer::retrieveByPk($_REQUEST["domain_id"]);
		if ($domain === null)
			throw new Exception("Invalid domain id");


		$this->response = $response;
		/* web service disabled for this domain */
		if ($domain->getSiteId() === null) {
			$this->response->status = false;
			$this->srvCtl = null;
			return;
		}

		$client = new ProcOpenRmiClient("sudo ".RMI_SERVER_PATH." 2>&1");
		$this->srvCtl = $client->createInstance("SrvCtl");
		$this->username = $domain->getUsername();
		$this->siteName = "www.".$domain->getDomain();
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
		/* web service disabled for this domain */
		if (!$this->srvCtl)
			return;

		/* perform the requested operation */
		switch ($this->operationType) {
		case "start":
		case "stop":
			$this->response->status = $this->srvCtl->sendHttpdSignal(
					$this->siteName, $this->operationType, $this->username);
			break;
		case "status":
			$this->response->status = $this->srvCtl->httpdAlive($this->siteName);
			break;
		}
	}
}

?>
