if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $_REQUEST = array_merge($_REQUEST, (array) json_decode(trim(file_get_contents('php://input')), true));
}
