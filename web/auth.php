<?
require_once "common.php";
require_once "BaseForm.php";
require_once "controller/Session.php";
require_once "controller/MipanelAuthRequest.php";

class AuthForm extends BaseForm {
	function createRequest() {
		return new MipanelAuthRequest();
	}

	function showLoginForm() {
	?>
	<html>
	<head>
	<script>var isomorphicDir="/isomorphic/";</script>
	<script src="/isomorphic/system/modules/ISC_Core.js"></script>
	<script src="/isomorphic/system/modules/ISC_Foundation.js"></script>
	<script src="/isomorphic/system/modules/ISC_Containers.js"></script>
	<script src="/isomorphic/system/modules/ISC_Grids.js"></script>
	<script src="/isomorphic/system/modules/ISC_Forms.js"></script>
	<script src="/isomorphic/system/modules/ISC_DataBinding.js"></script>
	<script src="/isomorphic/skins/TreeFrog/load_skin.js"></script>
	<script src="/isomorphic/login/reloginFlow.js"></script>
	<script src="/mpl/web/js/MplAuthenticator.js"></script>
	</head>
	<body>
	<script>

	function reloadPage() {
		document.location.href = document.location.href;
	}

	isc.MplAuthenticator.validateSession(reloadPage);
	</script>
	</body>
	</html>
	<?
	}

	function write() {
		if (Session::getUser() !== null)
			return;
		$this->showLoginForm();
		die();
	}

	function form() {
	}

	function getTitle() {
		return "User Authentication";
	}

	function getFormAttributes() {
	}
}

$authForm = new AuthForm();
$authForm->write();

?>
