<?
require_once "common.php";
ErrorHandler::setHandler(new ThrowErrorHandler());

require_once "RestDataSource.php";

class RrRequest extends RestRequest {
	function createOm() 
	{
		return new Rr();
	}
	function omToArray($om)
	{
		$ret = parent::omToArray($om);
		$rr = RrPeer::retrieveByPK($om->getId());
		if ($rr->getName() == "")
			$ret["name"] = "@";
		else $ret["name"] = $rr->getName();
		if ($rr->getAux() != "0" && $rr->getType() == "MX")
			$ret["aux"] = $rr->getAux();
		else $ret["aux"] = ""; 
		
		return $ret;
	}
	function doSave()
	{
		parent::doSave();
		$rr = RrPeer::retrieveByPK($this->om->getId());
		if ($this->data["name"] == '@')
			$rr->setName('');
		$rr->save();
	}
}

$request = new RrRequest();
$request->dispatch();
echo $request->getJsonResponse();
?>
